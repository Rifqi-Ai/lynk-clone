<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Voucher;
use App\Services\DuitkuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class CartController extends Controller
{
    /**
     * Show cart page for a creator.
     */
    public function show(Request $request, string $username)
    {
        $creator = \App\Models\User::where('username', $username)->firstOrFail();
        $cart = $this->getOrCreateCart($request, $creator);
        return view('public.cart', compact('creator', 'cart'));
    }

    /**
     * Add product to cart (or update quantity).
     */
    public function add(Request $request, string $username, string $productId): RedirectResponse
    {
        $creator = \App\Models\User::where('username', $username)->firstOrFail();
        $product = $creator->products()->where('id', $productId)->where('status', 'published')->firstOrFail();

        $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $cart = $this->getOrCreateCart($request, $creator);

        // Cannot mix creators — clear cart if different creator
        if ($cart->creator_user_id !== $creator->id) {
            $cart->items()->delete();
            $cart->update([
                'creator_user_id' => $creator->id,
                'voucher_id' => null,
                'voucher_discount' => 0,
            ]);
        }

        $existing = $cart->items()->where('product_id', $product->id)->first();
        $qty = $request->input('quantity', 1);

        if ($existing) {
            $existing->update([
                'quantity' => min(10, $existing->quantity + $qty),
                'unit_price' => $product->price,
            ]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $qty,
                'unit_price' => $product->price,
            ]);
        }

        return redirect()->route('cart.show', $creator->username)
            ->with('success', "Added to cart!");
    }

    /**
     * Update item quantity.
     */
    public function update(Request $request, string $username, string $productId): RedirectResponse
    {
        $creator = \App\Models\User::where('username', $username)->firstOrFail();
        $cart = $this->getOrCreateCart($request, $creator);

        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $cart->items()->where('product_id', $productId)->update([
            'quantity' => $request->input('quantity'),
        ]);

        return redirect()->route('cart.show', $creator->username);
    }

    /**
     * Remove item from cart.
     */
    public function remove(Request $request, string $username, string $productId): RedirectResponse
    {
        $creator = \App\Models\User::where('username', $username)->firstOrFail();
        $cart = $this->getOrCreateCart($request, $creator);

        $cart->items()->where('product_id', $productId)->delete();

        return redirect()->route('cart.show', $creator->username)
            ->with('success', 'Removed from cart.');
    }

    /**
     * Apply voucher code.
     */
    public function applyVoucher(Request $request, string $username): RedirectResponse
    {
        $creator = \App\Models\User::where('username', $username)->firstOrFail();
        $cart = $this->getOrCreateCart($request, $creator);

        $request->validate([
            'voucher_code' => ['required', 'string', 'max:50'],
        ]);

        $voucher = Voucher::where('creator_user_id', $creator->id)
            ->where('code', strtoupper($request->voucher_code))
            ->first();

        if (!$voucher || !$voucher->isValid()) {
            return back()->withErrors(['voucher_code' => 'Invalid or expired voucher code.']);
        }

        $subtotal = $cart->subtotal;
        if ($subtotal < $voucher->min_purchase) {
            return back()->withErrors(['voucher_code' => "Minimum purchase Rp " . number_format($voucher->min_purchase, 0, ',', '.')]);
        }

        $discount = $voucher->calculateDiscount($subtotal);

        $cart->update([
            'voucher_id' => $voucher->id,
            'voucher_discount' => $discount,
        ]);

        return redirect()->route('cart.show', $creator->username)
            ->with('success', "Voucher applied! You saved Rp " . number_format($discount, 0, ',', '.'));
    }

    /**
     * Remove voucher.
     */
    public function removeVoucher(Request $request, string $username): RedirectResponse
    {
        $creator = \App\Models\User::where('username', $username)->firstOrFail();
        $cart = $this->getOrCreateCart($request, $creator);

        $cart->update([
            'voucher_id' => null,
            'voucher_discount' => 0,
        ]);

        return redirect()->route('cart.show', $creator->username);
    }

    /**
     * Proceed to cart checkout.
     */
    public function checkout(Request $request, string $username)
    {
        $creator = \App\Models\User::where('username', $username)->firstOrFail();
        $cart = $this->getOrCreateCart($request, $creator);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.show', $creator->username)
                ->with('error', 'Your cart is empty.');
        }

        // Auto-attach buyer email from logged-in user if not set
        if (!$cart->buyer_email && Auth::user()) {
            $cart->update(['buyer_email' => Auth::user()->email]);
        }

        return view('public.cart-checkout', compact('creator', 'cart'));
    }

    /**
     * Process cart checkout — creates single order with all items,
     * redirects to Duitku payment page.
     */
    public function processCheckout(
        Request $request,
        DuitkuService $duitku,
        string $username
    ): RedirectResponse {
        // Rate limit per IP
        $key = 'cart-checkout:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['payer_email' => 'Too many attempts. Please try again later.']);
        }
        RateLimiter::hit($key, 60);

        $creator = \App\Models\User::where('username', $username)->firstOrFail();
        $cart = $this->getOrCreateCart($request, $creator);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.show', $creator->username)
                ->with('error', 'Your cart is empty.');
        }

        $data = $request->validate([
            'payer_email' => ['required', 'email'],
        ]);

        // Load products
        $cart->load(['items.product', 'voucher']);
        $subtotal = $cart->subtotal;
        $voucherDiscount = $cart->voucher_discount ?? 0;
        $total = max(0, $subtotal - $voucherDiscount);

        // Compute fee on total (after voucher)
        $feePct = $creator->transaction_fee_pct ?? 10;
        $feeAmount = $total * ($feePct / 100);
        $creatorPayout = $total - $feeAmount;

        // Save buyer email to cart for re-use
        $cart->update(['buyer_email' => $data['payer_email']]);

        // Create single order covering all items
        $primaryProduct = $cart->items->first()->product; // save reference before clearing
        $order = Order::create([
            'buyer_user_id' => Auth::id(),
            'buyer_email' => $data['payer_email'],
            'product_id' => $primaryProduct->id, // primary item
            'creator_user_id' => $creator->id,
            'unit_price' => $total,
            'quantity' => $cart->item_count,
            'subtotal' => $subtotal,
            'fee_pct' => $feePct,
            'fee_amount' => $feeAmount,
            'total' => $total,
            'creator_payout' => $creatorPayout,
            'voucher_code' => $cart->voucher?->code,
            'voucher_discount' => $voucherDiscount,
            'payment_status' => 'pending',
            'expired_at' => now()->addHours(24),
            'metadata' => [
                'cart_id' => $cart->id,
                'items' => $cart->items->map(fn($i) => [
                    'product_id' => $i->product_id,
                    'title' => $i->product->title,
                    'type' => $i->product->type,
                    'quantity' => $i->quantity,
                    'unit_price' => $i->unit_price,
                ])->values()->all(),
            ],
        ]);

        // Increment product sales_count for each item
        foreach ($cart->items as $item) {
            $item->product->increment('sales_count', $item->quantity);
        }

        // Clear the cart
        $cart->items()->delete();
        $cart->update([
            'voucher_id' => null,
            'voucher_discount' => 0,
        ]);

        // Initialize Duitku payment
        try {
            $paymentUrl = $duitku->createTransaction($order, $primaryProduct, $creator, $data['payer_email']);
            return redirect()->away($paymentUrl);
        } catch (\Exception $e) {
            Log::error('Duitku cart init failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['payer_email' => 'Payment gateway error. Please try again.']);
        }
    }

    /**
     * Get or create cart for current session/user.
     * Strategy: use session ID as key, store in cookie + session.
     */
    protected function getOrCreateCart(Request $request, \App\Models\User $creator): Cart
    {
        $cartId = $request->cookie('cart_id_' . $creator->id);

        if ($cartId) {
            $cart = Cart::where('id', $cartId)
                ->where('creator_user_id', $creator->id)
                ->where('expires_at', '>', now())
                ->first();
            if ($cart) {
                // Attach user if logged in
                if (Auth::id() && !$cart->buyer_user_id) {
                    $cart->update(['buyer_user_id' => Auth::id()]);
                }
                return $cart;
            }
        }

        // Create new cart
        $cart = Cart::create([
            'creator_user_id' => $creator->id,
            'buyer_user_id' => Auth::id(),
            'expires_at' => now()->addDays(7),
        ]);

        // Store cart ID in cookie for 7 days
        cookie()->queue(cookie('cart_id_' . $creator->id, $cart->id, 60 * 24 * 7));

        return $cart;
    }
}