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
    /** Cookie lifetime for cart_id (7 days, in minutes). */
    private const CART_COOKIE_MINUTES = 60 * 24 * 7;

    /**
     * Cookie name pattern: 'cart_id_{creator_id}'.
     * Cart IDs are stored under a creator-scoped key to prevent cross-creator cart collisions.
     */
    private const CART_COOKIE_PREFIX = 'cart_id_';

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
     * Rate limit handled by 'throttle:cart-checkout' middleware in routes/web.php.
     *
     * Reads like prose: resolve → guard empty → validate → persist email →
     * resolve primary product → create order (or fail) → initiate payment.
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
            return $this->emptyCartRedirect($creator);
        }

        $data = $this->validateCheckoutRequest($request);
        $cart->update(['buyer_email' => $data['payer_email']]);
        $cart->load(['items.product', 'voucher']);

        $primaryProductOrError = $this->resolvePrimaryProduct($cart);
        if ($primaryProductOrError instanceof RedirectResponse) {
            return $primaryProductOrError;
        }
        $primaryProduct = $primaryProductOrError;

        $orderOrError = $this->createOrderInTransaction(
            $orders, $cart, $creator, $data['payer_email']
        );
        if ($orderOrError instanceof RedirectResponse) {
            return $orderOrError;
        }
        $order = $orderOrError;

        return $this->initiatePaymentOrFail(
            $duitku, $order, $primaryProduct, $creator, $data['payer_email']
        );
    }

    // ───── processCheckout() helpers ─────

    /**
     * Redirect back to cart with an "empty cart" error.
     */
    private function emptyCartRedirect(User $creator): RedirectResponse
    {
        return redirect()->route('cart.show', $creator->username)
            ->with('error', 'Your cart is empty.');
    }

    /**
     * Validate the checkout form. Returns the validated payload.
     */
    private function validateCheckoutRequest(Request $request): array
    {
        return $request->validate([
            'payer_email' => ['required', 'email'],
        ]);
    }

    /**
     * Return the primary product for the order, or a RedirectResponse
     * if the cart contains no longer available items.
     */
    private function resolvePrimaryProduct(Cart $cart): Product|RedirectResponse
    {
        $primaryItem = $cart->items->first();
        if (! $primaryItem || ! $primaryItem->product || ! $primaryItem->product->isPublished()) {
            return back()->withErrors([
                'payer_email' => 'One or more products in your cart are no longer available.',
            ]);
        }

        return $primaryItem->product;
    }

    /**
     * Create the order in a DB transaction (cart clear + order creation atomic).
     * Returns Order on success, or RedirectResponse on failure (logged + user-facing error).
     */
    private function createOrderInTransaction(
        OrderService $orders,
        Cart $cart,
        User $creator,
        string $payerEmail
    ): Order|RedirectResponse {
        try {
            return DB::transaction(function () use ($orders, $cart, $creator, $payerEmail) {
                $order = $orders->createCartOrder(
                    creator: $creator,
                    buyer: Auth::user(),
                    items: $cart->items,
                    payerEmail: $payerEmail,
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

            return back()->withErrors([
                'payer_email' => 'Could not create order. Please try again.',
            ]);
        }
    }

    /**
     * Initiate the Duitku payment. On gateway failure, mark the order as failed
     * and return a user-facing error response.
     */
    private function initiatePaymentOrFail(
        DuitkuService $duitku,
        Order $order,
        Product $primaryProduct,
        User $creator,
        string $payerEmail
    ): RedirectResponse {
        try {
            $paymentUrl = $duitku->createTransaction($order, $primaryProduct, $creator, $payerEmail);

            return redirect()->away($paymentUrl);
        } catch (\Exception $e) {
            Log::error('Duitku cart init failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            $order->payment_status = 'failed';
            $order->save();

            return back()->withErrors([
                'payer_email' => 'Payment gateway error. Please try again.',
            ]);
        }
    }

    // ───── getOrCreateCart ─────

    /**
     * Get or create cart for current session/user.
     * Strategy: use session ID as key, store in cookie + session.
     *
     * SECURITY: cart_id cookie is validated against the Cart ID format
     * (`CART-XXXXXXXX` where X is uppercase alphanumeric) before querying DB —
     * prevents attackers from injecting arbitrary strings or claiming another
     * user's cart by setting the cookie.
     */
    protected function getOrCreateCart(Request $request, User $creator): Cart
    {
        $cartId = $request->cookie(self::CART_COOKIE_PREFIX.$creator->id);

        // BUG FIX: Cart IDs use 'CART-' + 8 uppercase alphanumeric chars,
        // NOT UUID. The previous regex required UUID format, which made
        // every existing cart cookie unrecognizable — users were always
        // getting new empty carts and could never complete checkout.
        if ($cartId && preg_match('/^CART-[A-Z0-9]{8}$/', $cartId)) {
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
        cookie()->queue(cookie(
            self::CART_COOKIE_PREFIX.$creator->id,
            $cart->id,
            self::CART_COOKIE_MINUTES
        ));

        return $cart;
    }
}
