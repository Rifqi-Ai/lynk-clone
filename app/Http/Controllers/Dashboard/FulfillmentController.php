<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Dashboard fulfillment controller — list and update physical-product orders.
 *
 * **shipping_status** is a first-class column on the `orders` table (promoted
 * from `metadata->shipping_status` in migration 2026_06_21_120000). The
 * previous `orderByRaw("JSON_EXTRACT(metadata, '$.shipping_status') ASC")` was
 * flagged by the Phase 12 security audit as a brittle static SQL fragment AND
 * had a latent business-logic bug: alphabetical ASC sort returned orders in
 * REVERSE workflow order (delivered → packed → pending → shipped), hiding
 * pending work from creators. See @see docs/security-audit-2026-06-21.md.
 *
 * Workflow priority is encoded as a SHIPPING_STATUS_PRIORITY constant map so
 * the SQL `CASE` expression is generated from PHP data — no hardcoded SQL
 * fragments referencing `JSON_EXTRACT`. The query builder's `orderBy` is used
 * with a derived column from `selectRaw`.
 */
class FulfillmentController extends Controller
{
    /**
     * Workflow priority for sorting — lower number = shown first.
     * 'pending' (needs action) → 'packed' → 'shipped' → 'delivered' (done).
     * Used both in the order listing (sort) and implicitly in the stats cards
     * (counts per status). Keep alphabetical insertion order — the CASE expression
     * relies on iteration order being deterministic.
     */
    private const SHIPPING_STATUSES = ['pending', 'packed', 'shipped', 'delivered'];

    /**
     * Base query for physical paid orders belonging to the authenticated creator.
     * Centralized so both `index()` and the per-status counts share the same filter.
     */
    private function physicalOrdersQuery(int $creatorId)
    {
        return Order::where('creator_user_id', $creatorId)
            ->where('payment_status', 'paid')
            ->whereHas('product', fn ($q) => $q->where('type', 'physical'))
            // Include legacy orders with NULL shipping_status (pre-promotion) —
            // treat them as 'pending' by falling through to the CASE default.
            ->where(function ($q) {
                $q->whereIn('shipping_status', self::SHIPPING_STATUSES)
                    ->orWhereNull('shipping_status');
            });
    }

    /**
     * List all physical orders that need fulfillment.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Build the CASE expression from the priority map. Lower number = first in list.
        $cases = collect(self::SHIPPING_STATUSES)
            ->map(fn ($status, $i) => "WHEN shipping_status = '{$status}' THEN {$i}")
            ->implode(' ');

        $orders = $this->physicalOrdersQuery($user->id)
            ->with('product')
            ->select('orders.*')
            ->selectRaw("(CASE {$cases} ELSE ".count(self::SHIPPING_STATUSES).' END) as workflow_priority')
            ->orderBy('workflow_priority', 'asc')
            ->orderBy('paid_at', 'desc')
            ->paginate(20);

        $baseQuery = $this->physicalOrdersQuery($user->id);

        $stats = [
            // Pending counts both explicitly 'pending' AND legacy NULL (treated as pending).
            'pending' => (clone $baseQuery)
                ->where(function ($q) {
                    $q->where('shipping_status', 'pending')->orWhereNull('shipping_status');
                })
                ->count(),
            'packed' => (clone $baseQuery)->where('shipping_status', 'packed')->count(),
            'shipped' => (clone $baseQuery)->where('shipping_status', 'shipped')->count(),
            'delivered' => (clone $baseQuery)->where('shipping_status', 'delivered')->count(),
        ];

        return view('dashboard.fulfillment', compact('orders', 'stats'));
    }

    /**
     * Show single order detail with shipping info.
     */
    public function show(Request $request, string $orderId)
    {
        $user = Auth::user();
        $order = Order::where('creator_user_id', $user->id)
            ->where('id', $orderId)
            ->with('product', 'buyer')
            ->firstOrFail();

        return view('dashboard.fulfillment-detail', compact('order'));
    }

    /**
     * Update shipping status + tracking number.
     */
    public function update(Request $request, string $orderId)
    {
        $user = Auth::user();
        $order = Order::where('creator_user_id', $user->id)
            ->where('id', $orderId)
            ->where('payment_status', 'paid')
            ->whereHas('product', fn ($q) => $q->where('type', 'physical'))
            ->firstOrFail();

        $data = $request->validate([
            'shipping_status' => ['required', 'in:'.implode(',', self::SHIPPING_STATUSES)],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'courier' => ['nullable', 'string', 'max:50'],
            'shipping_notes' => ['nullable', 'string', 'max:500'],
        ]);

        // shipping_status now writes to the column directly (was metadata->shipping_status).
        // Other shipping fields stay in metadata since they're free-form and not indexed.
        $metadata = $order->metadata ?? [];
        $metadata['tracking_number'] = $data['tracking_number'] ?? null;
        $metadata['courier'] = $data['courier'] ?? null;
        $metadata['shipping_notes'] = $data['shipping_notes'] ?? null;
        $metadata['shipping_updated_at'] = now()->toIso8601String();
        if ($data['shipping_status'] === 'shipped' && empty($metadata['shipped_at'])) {
            $metadata['shipped_at'] = now()->toIso8601String();
        }
        if ($data['shipping_status'] === 'delivered' && empty($metadata['delivered_at'])) {
            $metadata['delivered_at'] = now()->toIso8601String();
        }

        $order->update([
            'shipping_status' => $data['shipping_status'],
            'metadata' => $metadata,
        ]);

        return back()->with('success', '✅ Status pengiriman diupdate: '.strtoupper($data['shipping_status']));
    }
}
