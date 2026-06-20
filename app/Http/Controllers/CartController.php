<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use App\Services\DuitkuService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Show cart page for a creator.
     */
    public function show(Request $request, string $username)
    {
        $creator = User::where('username', $username)->firstOrFail();
        $cart = $this->getOrCreateCart($request, $creator);

        return view('public.cart', compact('creator', 'cart'));
    }

    /**
     * Add product to cart (or update quantity).
     */
    public function add(Request $request, string $username, string $productId): RedirectResponse
    {
        $creator = User::where('username', $username)->firstOrFail();
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
            ->with('success', 'Added to cart!');
    }

    /**
     * Update item quantity.
     */
    public function update(Request $request, string $username, string $productId): RedirectResponse
    {
        $creator = User::where('username', $username)->firstOrFail();
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
        $creator = User::where('username', $username)->firstOrFail();
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
        $creator = User::where('username', $username)->firstOrFail();
        $cart = $this->getOrCreateCart($request, $creator);

        $request->validate([
            'voucher_code' => ['required', 'string', 'max:50'],
        ]);

        $voucher = Voucher::where('creator_user_id', $creator->id)
            ->where('code', strtoupper($request->voucher_code))
            ->first();

        if (! $voucher || ! $voucher->isValid()) {
            return back()->withErrors(['voucher_code' => 'Invalid or expired voucher code.']);
        }

        $subtotal = $cart->subtotal;
        if ($subtotal < $voucher->min_purchase) {
            return back()->withErrors(['voucher_code' => 'Minimum purchase Rp '.number_format($voucher->min_purchase, 0, ',', '.')]);
        }

        $discount = $voucher->calculateDiscount($subtotal);

        $cart->update([
            'voucher_id' => $voucher->id,
            'voucher_discount' => $discount,
        ]);

        return redirect()->route('cart.show', $creator->username)
            ->with('success', 'Voucher applied! You saved Rp '.number_format($discount, 0, ',', '.'));
    }

    /**
     * Remove voucher.
     */
    public function removeVoucher(Request $request, string $username): RedirectResponse
    {
        $creator = User::where('username', $username)->firstOrFail();
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
        $creator = User::where('username', $username)->firstOrFail();
        $cart = $this->getOrCreateCart($request, $creator);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.show', $creator->username)
                ->with('error', 'Your cart is empty.');
        }

        // Auto-attach buyer email from logged-in user if not set
        if (! $cart->buyer_email && Auth::user()) {
            $cart->update(['buyer_email' => Auth::user()->email]);
        }

        return view('public.cart-checkout', compact('creator', 'cart'));
    }

    /**
     * Process cart checkout — creates single order with all items via OrderService.
     * Refactored in Phase 9: business logic moved to App\Services\OrderService.
     * Rate limit handled by 'throttle:cart-checkout' middleware in routes/web.php.
     */
    public function processCheckout(
        Request $request,
        DuitkuService $duitku,
        OrderService $orders,
        string $username
    ): RedirectResponse {
        $creator = User::where('username', $username)->firstOrFail();
        $cart = $this->getOrCreateCart($request, $creator);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.show', $creator->username)
                ->with('error', 'Your cart is empty.');
        }

        $data = $request->validate([
            'payer_email' => ['required', 'email'],
        ]);

        // Save buyer email to cart for re-use
        $cart->update(['buyer_email' => $data['payer_email']]);

        // Load products (with eager loading to avoid N+1)
        $cart->load(['items.product', 'voucher']);

        // Validate that at least one product still exists and is published.
        // (Avoid crashes if a product was deleted/unpublished between add-to-cart and checkout.)
        $primaryItem = $cart->items->first();
        if (! $primaryItem || ! $primaryItem->product || ! $primaryItem->product->isPublished()) {
            return back()->withErrors(['payer_email' => 'One or more products in your cart are no longer available.']);
        }
        $primaryProduct = $primaryItem->product;

        // OrderService handles all the business logic — wrapped in a single transaction
        // so cart clear and order creation are atomic.
        try {
            $order = DB::transaction(function () use ($orders, $cart, $creator, $data) {
                $order = $orders->createCartOrder(
                    creator: $creator,
                    buyer: Auth::user(),
                    items: $cart->items,
                    payerEmail: $data['payer_email'],
                    voucherCode: $cart->voucher?->code,
                    voucherDiscount: $cart->voucher_discount ?? 0,
                );

                // Clear the cart (inside same TX as order creation → atomic)
                $cart->items()->delete();
                $cart->update([
                    'voucher_id' => null,
                    'voucher_discount' => 0,
                ]);

                return $order;
            });
        } catch (\Throwable $e) {
            Log::error('Cart checkout order creation failed', [
                'cart_id' => $cart->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['payer_email' => 'Could not create order. Please try again.']);
        }

        // Initialize Duitku payment (outside the DB transaction — payment is a remote call)
        try {
            $paymentUrl = $duitku->createTransaction($order, $primaryProduct, $creator, $data['payer_email']);

            return redirect()->away($paymentUrl);
        } catch (\Exception $e) {
            Log::error('Duitku cart init failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            $order->payment_status = 'failed';
            $order->save();

            return back()->withErrors(['payer_email' => 'Payment gateway error. Please try again.']);
        }
    }

    /**
     * Get or create cart for current session/user.
     * Strategy: use session ID as key, store in cookie + session.
     *
     * SECURITY: cart_id cookie is validated as UUID — prevents attackers from injecting
     * arbitrary strings or claiming another user's cart by setting the cookie.
     */
    protected function getOrCreateCart(Request $request, User $creator): Cart
    {
        $cartId = $request->cookie('cart_id_'.$creator->id);

        // Validate format BEFORE querying DB (UUID v4 has fixed length + hyphen positions)
        if ($cartId && preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $cartId)) {
            $cart = Cart::where('id', $cartId)
                ->where('creator_user_id', $creator->id)
                ->where('expires_at', '>', now())
                ->first();
            if ($cart) {
                // Attach user if logged in
                if (Auth::id() && ! $cart->buyer_user_id) {
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
        cookie()->queue(cookie('cart_id_'.$creator->id, $cart->id, 60 * 24 * 7));

        return $cart;
    }
}
