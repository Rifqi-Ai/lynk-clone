<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DuitkuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class PublicProfileController extends Controller
{
    /**
     * Show creator's public profile with search/filter.
     */
    public function show(Request $request, string $username)
    {
        $creator = User::where('username', $username)->firstOrFail();

        $query = $creator->publishedProducts();
        $typeFilter = $request->query('type');
        $search = trim((string) $request->query('q', ''));

        // Filter by type
        if ($typeFilter && array_key_exists($typeFilter, Product::TYPES)) {
            $query->where('type', $typeFilter);
        }

        // Search by title/description
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Sort: latest by default, or by price
        $sort = $request->query('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderByDesc('sales_count')->orderByDesc('view_count');
                break;
            default:
                $query->latest();
        }

        $products = $query->get();

        foreach ($products as $product) {
            $product->incrementQuietly('view_count');
        }

        // Featured product (only if NOT searching/filtering) — most popular or first
        $featured = null;
        if (! $typeFilter && ! $search) {
            $featured = $creator->products()
                ->where('status', 'published')
                ->where('is_featured', true)
                ->first()
                ?? $creator->products()
                    ->where('status', 'published')
                    ->orderByDesc('sales_count')
                    ->first();
            // Exclude featured from the grid below
            if ($featured) {
                $products = $products->where('id', '!=', $featured->id)->values();
            }
        }

        // Counts per type (for filter UI)
        $typeCounts = $creator->products()
            ->where('status', 'published')
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return view('public.profile', compact('creator', 'products', 'featured', 'typeCounts', 'typeFilter', 'search', 'sort'));
    }

    /**
     * Show product detail page.
     */
    public function showProduct(Request $request, string $username, string $productId)
    {
        $creator = User::where('username', $username)->firstOrFail();
        $product = $creator->products()->where('id', $productId)->firstOrFail();

        abort_unless($product->isPublished() || $product->user_id === Auth::id(), 404);
        $product->incrementQuietly('view_count');

        return view('public.product', compact('creator', 'product'));
    }

    /**
     * Show checkout page.
     */
    public function checkout(Request $request, string $username, string $productId)
    {
        $creator = User::where('username', $username)->firstOrFail();
        $product = $creator->products()->where('id', $productId)->where('status', 'published')->firstOrFail();

        // For physical products out of stock, block checkout
        if ($product->type === 'physical' && ! $product->inStock) {
            return back()->with('error', 'Sorry, this product is out of stock.');
        }

        return view('public.checkout', compact('creator', 'product'));
    }

    /**
     * Process checkout — handles all 7 product types.
     */
    public function processCheckout(
        Request $request,
        DuitkuService $duitku,
        string $username,
        string $productId
    ): RedirectResponse {
        // Rate limit per IP
        $key = 'checkout:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['payer_email' => 'Too many attempts. Please try again later.']);
        }
        RateLimiter::hit($key, 60);

        $creator = User::where('username', $username)->firstOrFail();
        $product = $creator->products()->where('id', $productId)->where('status', 'published')->firstOrFail();

        // Base validation
        $rules = [
            'payer_email' => ['required', 'email'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];

        // Type-specific validation
        switch ($product->type) {
            case 'donation':
                // SECURITY: Cap donation at Rp 100M to prevent abuse / payment-gateway errors.
                // Preset amounts are validated as a whitelist so client cannot inject arbitrary values.
                $presets = $product->donation_presets; // from model accessor (typed)
                $rules['amount'] = ['required', 'integer', 'min:1000', 'max:100000000'];
                if (! $product->meta('allow_custom', true)) {
                    $rules['amount'][] = function ($attr, $value, $fail) use ($presets) {
                        if (! in_array((int) $value, array_map('intval', $presets), true)) {
                            $fail('Please choose one of the preset amounts.');
                        }
                    };
                }
                $rules['donor_message'] = ['nullable', 'string', 'max:500'];
                break;
            case 'appointment':
                $rules['appointment_date'] = ['required', 'date', 'after_or_equal:today'];
                $rules['appointment_time'] = ['required'];
                break;
            case 'event':
                $rules['quantity'] = ['required', 'integer', 'min:1', 'max:10'];
                if ($product->meta('capacity')) {
                    $sold = $product->paidOrders()->sum('quantity');
                    $rules['quantity'][] = 'max:'.max(1, $product->meta('capacity') - $sold);
                }
                break;
            case 'physical':
                $rules['ship'] = ['required', 'array'];
                $rules['ship.name'] = ['required', 'string', 'max:100'];
                $rules['ship.phone'] = ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'];
                $rules['ship.address'] = ['required', 'string', 'max:500'];
                $rules['ship.city'] = ['required', 'string', 'max:100'];
                $rules['ship.province'] = ['required', 'string', 'max:100'];
                $rules['ship.postal_code'] = ['required', 'string', 'max:10'];
                $rules['ship.country'] = ['nullable', 'string', 'max:100'];
                $rules['ship.notes'] = ['nullable', 'string', 'max:200'];
                break;
        }

        $data = $request->validate($rules);

        // SECURITY: transaction fee MUST be in a sane range. Even if creator account is compromised
        // and someone sets transaction_fee_pct to -50 or 9999, we clamp to a safe range.
        $feePct = max(0, min(50, (float) ($creator->transaction_fee_pct ?? 10)));

        // Compute amounts
        if ($product->type === 'donation') {
            $unitPrice = (int) $data['amount'];
            $quantity = 1;
        } elseif ($product->type === 'event') {
            $quantity = $data['quantity'] ?? 1;
            $unitPrice = $product->price;
        } else {
            $quantity = $data['quantity'] ?? 1;
            $unitPrice = $product->price;
        }

        $subtotal = $unitPrice * $quantity;
        $shippingCost = 0;

        // Build metadata for order (type-specific stuff)
        $orderMetadata = [];
        if ($product->type === 'appointment') {
            $orderMetadata['appointment_date'] = $data['appointment_date'];
            $orderMetadata['appointment_time'] = $data['appointment_time'];
        }
        if ($product->type === 'donation' && ! empty($data['donor_message'])) {
            $orderMetadata['donor_message'] = $data['donor_message'];
        }
        if ($product->type === 'physical' && ! empty($data['ship'])) {
            $orderMetadata['shipping_address'] = $data['ship'];
            // Compute estimated shipping cost (flat rate for now)
            $shippingCost = 15000; // flat Rp 15K
            $orderMetadata['shipping_cost'] = $shippingCost;
            $orderMetadata['shipping_status'] = 'pending'; // pending → packed → shipped → delivered
        }

        $total = $subtotal + $shippingCost;
        $feeAmount = $total * ($feePct / 100);
        $creatorPayout = $total - $feeAmount;

        $order = Order::create([
            'buyer_user_id' => Auth::id(),
            'buyer_email' => $data['payer_email'],
            'product_id' => $product->id,
            'creator_user_id' => $creator->id,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'fee_pct' => $feePct,
            'fee_amount' => $feeAmount,
            'total' => $total,
            'creator_payout' => $creatorPayout,
            'metadata' => $orderMetadata,
            'expired_at' => now()->addHours(24),
        ]);
        // payment_status is NOT fillable — set via attribute write (bypasses mass-assignment)
        $order->payment_status = 'pending';
        $order->save();

        // For donation: no need to update metadata - current_amount is computed live
        // (See Product::getDonationRaisedAttribute)

        // Initialize Duitku payment
        try {
            $paymentUrl = $duitku->createTransaction($order, $product, $creator, $data['payer_email']);

            return redirect()->away($paymentUrl);
        } catch (\Exception $e) {
            Log::error('Duitku init failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);

            return back()->withErrors(['payer_email' => 'Payment gateway error. Please try again.']);
        }
    }
}
