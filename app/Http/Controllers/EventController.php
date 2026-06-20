<?php

namespace App\Http\Controllers;

use App\Models\EventTicket;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Buyer's ticket view (after purchase).
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
     */
    public function createWalkin(Request $request, string $username, string $productId)
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

        // Create order + ticket manually (cash/offline payment)
        $feePct = $user->transaction_fee_pct;
        $total = $validated['amount'];
        $payout = $total * (1 - $feePct / 100);
        $fee = $total - $payout;

        $order = Order::create([
            'id' => Order::generateId(),
            'buyer_email' => $validated['attendee_email'],
            'product_id' => $product->id,
            'creator_user_id' => $user->id,
            'unit_price' => $total,
            'quantity' => 1,
            'subtotal' => $total,
            'fee_pct' => $feePct,
            'fee_amount' => $fee,
            'total' => $total,
            'creator_payout' => $payout,
            'metadata' => ['attendee_name' => $validated['attendee_name'], 'walk_in' => true],
        ]);
        // payment_status, payment_method, paid_at not fillable — set directly (creator-only flow).
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

        $product->increment('sales_count');
        $user->increment('balance', $payout);
        $user->increment('total_earnings', $payout);

        return back()->with('success', "✅ Tiket walk-in berhasil dibuat untuk {$validated['attendee_name']}");
    }
}
