<?php

namespace App\Http\Controllers;

use App\Models\EventTicket;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Buyer's ticket view (after purchase).
     *
     * SECURITY: BFLA fix (Phase 17 Task #2 / OWASP API5:2023). Previously
     * anyone with the orderId in the URL could view any ticket — leaking
     * the buyer's email, attendee name, AND the ticket QR code (used for
     * event entry). Now requires proof of ownership via auth OR a signed
     * token (same pattern as CourseController).
     */
    public function ticket(Request $request, string $username, string $productId, string $orderId)
    {
        $product = Product::with('owner')
            ->where('id', $productId)
            ->where('type', 'event')
            ->firstOrFail();

        if ($product->owner->username !== $username) {
            abort(404);
        }

        $order = Order::where('id', $orderId)
            ->where('product_id', $productId)
            ->where('payment_status', 'paid')
            ->firstOrFail();

        // Authorization: buyer must be authenticated as the order owner, OR
        // present a signed access token (guest checkout path).
        $user = Auth::user();
        $ownsOrder = $user && (
            $order->buyer_user_id === $user->id
            || strtolower((string) $order->buyer_email) === strtolower((string) $user->email)
        );
        $hasValidToken = CourseController::verifyAccessToken(
            $request->query('token'),
            $order->id,
            (string) $order->buyer_email,
            $product->id
        );

        if (! $ownsOrder && ! $hasValidToken) {
            abort(403, 'Akses ditolak — beli tiket atau login sebagai pemilik.');
        }

        $ticket = EventTicket::where('order_id', $orderId)->first();
        if (! $ticket) {
            // Generate one if missing (shouldn't normally happen, but safety net)
            $ticket = EventTicket::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'buyer_email' => $order->buyer_email,
                'ticket_code' => EventTicket::generateCode(),
            ]);
        }

        return view('public.event-ticket', compact('product', 'order', 'ticket'));
    }

    /**
     * Creator's check-in page (lists all tickets for their event).
     */
    public function checkinDashboard(Request $request, string $username, string $productId)
    {
        $user = Auth::user();
        if (! $user || $user->username !== $username) {
            abort(403);
        }

        $product = Product::where('id', $productId)
            ->where('user_id', $user->id)
            ->where('type', 'event')
            ->firstOrFail();

        $tickets = EventTicket::where('product_id', $product->id)
            ->with('order')
            ->orderBy('is_checked_in')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => $tickets->count(),
            'checked_in' => $tickets->where('is_checked_in', true)->count(),
            'pending' => $tickets->where('is_checked_in', false)->count(),
        ];

        return view('dashboard.event-checkin', compact('product', 'tickets', 'stats'));
    }

    /**
     * Check in a ticket by code.
     */
    public function checkin(Request $request, string $username, string $productId)
    {
        $user = Auth::user();
        if (! $user || $user->username !== $username) {
            abort(403);
        }

        $product = Product::where('id', $productId)
            ->where('user_id', $user->id)
            ->where('type', 'event')
            ->firstOrFail();

        $validated = $request->validate([
            'ticket_code' => 'required|string|max:20',
        ]);

        $ticket = EventTicket::where('product_id', $product->id)
            ->where('ticket_code', $validated['ticket_code'])
            ->first();

        if (! $ticket) {
            return back()->with('error', '❌ Tiket tidak ditemukan untuk event ini.');
        }

        if ($ticket->is_checked_in) {
            return back()->with('error', "⚠️ Tiket sudah check-in pada {$ticket->checked_in_at->format('H:i')} oleh {$ticket->checked_in_by}");
        }

        $ticket->update([
            'is_checked_in' => true,
            'checked_in_at' => now(),
            'checked_in_by' => $user->name,
        ]);

        return back()->with('success', "✅ Tiket {$ticket->ticket_code} berhasil check-in! Attendee: ".($ticket->attendee_name ?? $ticket->buyer_email));
    }

    /**
     * Manual create ticket (for offline/walk-in attendees).
     * Refactored in Phase 9: business logic moved to App\Services\OrderService.
     */
    public function createWalkin(Request $request, OrderService $orders, string $username, string $productId)
    {
        $user = Auth::user();
        if (! $user || $user->username !== $username) {
            abort(403);
        }

        $product = Product::where('id', $productId)
            ->where('user_id', $user->id)
            ->where('type', 'event')
            ->firstOrFail();

        $validated = $request->validate([
            'attendee_name' => 'required|string|max:100',
            'attendee_email' => 'required|email',
            'amount' => 'required|numeric|min:0',
        ]);

        // Create order via OrderService (consistent with online checkout)
        $order = $orders->createSingleProductOrder(
            buyer: $user, // creator is also "buyer" of their own walk-in sale
            creator: $user,
            product: $product,
            data: [
                'payer_email' => $validated['attendee_email'],
                'amount' => (int) $validated['amount'],
                'attendee_name' => $validated['attendee_name'],
            ],
        );

        // Walk-in = paid in cash → mark paid and credit creator
        // (uses direct attribute write since payment_status is not fillable)
        $order->payment_status = 'paid';
        $order->payment_method = 'offline_cash';
        $order->paid_at = now();
        $order->save();

        EventTicket::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'buyer_email' => $validated['attendee_email'],
            'attendee_name' => $validated['attendee_name'],
            'ticket_code' => EventTicket::generateCode(),
        ]);

        // Walk-in payments are immediately settled — no callback needed
        $product->increment('sales_count');
        $user->increment('balance', $order->creator_payout);
        $user->increment('total_earnings', $order->creator_payout);

        return back()->with('success', "✅ Tiket walk-in berhasil dibuat untuk {$validated['attendee_name']}");
    }
}
