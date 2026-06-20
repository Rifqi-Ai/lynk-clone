<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Characterization tests for CartController::processCheckout().
 *
 * Pins down the behavior of POST /{username}/checkout before the
 * Phase 16 Task 2 refactor extracts validation, eager loading,
 * order creation, and payment initiation into named helpers.
 *
 * Covers: empty cart, email validation, primary product resolution,
 * order creation, payment redirect, error rollback, voucher usage.
 */
class CartCheckoutProcessTest extends TestCase
{
    use RefreshDatabase;

    private User $creator;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creator = User::factory()->create([
            'username' => 'checkout_creator',
        ]);
        $this->buyer = User::factory()->create([
            'email' => 'buyer@example.com',
        ]);
    }

    private function addProductToCart(Product $product, int $qty = 1): Cart
    {
        $cart = Cart::create([
            'creator_user_id' => $this->creator->id,
            'buyer_user_id' => $this->buyer->id,
            'expires_at' => now()->addDays(7),
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => $qty,
            'unit_price' => $product->price,
        ]);

        return $cart;
    }

    private function digitalProduct(int $price = 25000, string $status = 'published'): Product
    {
        return Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'digital',
            'title' => 'Checkout Test Product',
            'price' => $price,
            'status' => $status,
        ]);
    }

    private function checkoutUrl(): string
    {
        return "/{$this->creator->username}/cart/checkout";
    }

    // ───── Happy path ─────

    public function test_successful_checkout_creates_order(): void
    {
        $product = $this->digitalProduct(25000);
        $cart = $this->addProductToCart($product);
        $cartIdCookie = "cart_id_{$this->creator->id}";

        $response = $this->actingAs($this->buyer)
            ->withCookie($cartIdCookie, $cart->id)
            ->post($this->checkoutUrl(), [
                'payer_email' => 'payer@example.com',
            ]);

        // Dev mode: DuitkuService returns mock payment URL (route('payment.success', $order))
        $response->assertRedirect();
        $this->assertStringContainsString('payment/success', $response->headers->get('Location'));

        // Order was created
        $this->assertDatabaseHas('orders', [
            'buyer_user_id' => $this->buyer->id,
            'creator_user_id' => $this->creator->id,
            'buyer_email' => 'payer@example.com',
            'payment_status' => 'pending',
        ]);
    }

    public function test_successful_checkout_clears_cart_items(): void
    {
        $product = $this->digitalProduct(25000);
        $cart = $this->addProductToCart($product);
        $cartIdCookie = "cart_id_{$this->creator->id}";

        $this->assertSame(1, $cart->items()->count());

        $this->actingAs($this->buyer)
            ->withCookie($cartIdCookie, $cart->id)
            ->post($this->checkoutUrl(), [
                'payer_email' => 'payer@example.com',
            ]);

        // Cart items deleted
        $this->assertSame(0, $cart->items()->count());
    }

    public function test_successful_checkout_clears_voucher(): void
    {
        $product = $this->digitalProduct(50000);
        $voucher = Voucher::create([
            'creator_user_id' => $this->creator->id,
            'code' => 'TEST10',
            'type' => 'percent',
            'value' => 10,
            'min_purchase' => 10000,
            'max_uses' => 100,
            'used_count' => 0,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
        ]);
        $cart = $this->addProductToCart($product);
        $cart->update([
            'voucher_id' => $voucher->id,
            'voucher_discount' => 5000,
        ]);
        $cartIdCookie = "cart_id_{$this->creator->id}";

        $this->actingAs($this->buyer)
            ->withCookie($cartIdCookie, $cart->id)
            ->post($this->checkoutUrl(), [
                'payer_email' => 'payer@example.com',
            ]);

        $cart->refresh();
        $this->assertNull($cart->voucher_id);
        $this->assertSame(0, (int) $cart->voucher_discount);
    }

    public function test_buyer_email_persisted_to_cart(): void
    {
        $product = $this->digitalProduct(25000);
        $cart = $this->addProductToCart($product);
        $cartIdCookie = "cart_id_{$this->creator->id}";

        $this->actingAs($this->buyer)
            ->withCookie($cartIdCookie, $cart->id)
            ->post($this->checkoutUrl(), [
                'payer_email' => 'saved@example.com',
            ]);

        $cart->refresh();
        $this->assertSame('saved@example.com', $cart->buyer_email);
    }

    // ───── Validation failures ─────

    public function test_empty_cart_redirects_with_error(): void
    {
        // Create empty cart
        $cart = Cart::create([
            'creator_user_id' => $this->creator->id,
            'buyer_user_id' => $this->buyer->id,
            'expires_at' => now()->addDays(7),
        ]);
        $cartIdCookie = "cart_id_{$this->creator->id}";

        $response = $this->actingAs($this->buyer)
            ->withCookie($cartIdCookie, $cart->id)
            ->post($this->checkoutUrl(), [
                'payer_email' => 'payer@example.com',
            ]);

        $response->assertRedirect(route('cart.show', $this->creator->username));
        $response->assertSessionHas('error');
        $this->assertSame(0, Order::count());
    }

    public function test_missing_email_returns_validation_error(): void
    {
        $product = $this->digitalProduct(25000);
        $cart = $this->addProductToCart($product);
        $cartIdCookie = "cart_id_{$this->creator->id}";

        $this->actingAs($this->buyer)
            ->withCookie($cartIdCookie, $cart->id)
            ->post($this->checkoutUrl(), [])
            ->assertSessionHasErrors('payer_email');

        $this->assertSame(0, Order::count());
    }

    public function test_invalid_email_returns_validation_error(): void
    {
        $product = $this->digitalProduct(25000);
        $cart = $this->addProductToCart($product);
        $cartIdCookie = "cart_id_{$this->creator->id}";

        $this->actingAs($this->buyer)
            ->withCookie($cartIdCookie, $cart->id)
            ->post($this->checkoutUrl(), [
                'payer_email' => 'not-an-email',
            ])
            ->assertSessionHasErrors('payer_email');

        $this->assertSame(0, Order::count());
    }

    public function test_unpublished_product_blocks_checkout(): void
    {
        // Product was published when added to cart, but is now draft.
        $product = $this->digitalProduct(25000, 'draft');
        $cart = $this->addProductToCart($product);
        $cartIdCookie = "cart_id_{$this->creator->id}";

        $this->actingAs($this->buyer)
            ->withCookie($cartIdCookie, $cart->id)
            ->post($this->checkoutUrl(), [
                'payer_email' => 'payer@example.com',
            ])
            ->assertSessionHasErrors('payer_email');

        $this->assertStringContainsString('no longer available', (string) session('errors')->first('payer_email'));
        $this->assertSame(0, Order::count());
    }

    public function test_deleted_product_clears_cart_via_cascade(): void
    {
        // CartItem has ON DELETE CASCADE for product_id, so when a product
        // is deleted, the cart item disappears too. The cart is then
        // effectively empty, and checkout redirects with the empty-cart error.
        $product = $this->digitalProduct(25000);
        $cart = $this->addProductToCart($product);
        $cartIdCookie = "cart_id_{$this->creator->id}";

        // Product is deleted after add-to-cart.
        $product->delete();

        // Cart item was cascade-deleted.
        $this->assertSame(0, $cart->items()->count());

        $this->actingAs($this->buyer)
            ->withCookie($cartIdCookie, $cart->id)
            ->post($this->checkoutUrl(), [
                'payer_email' => 'payer@example.com',
            ])
            ->assertRedirect(route('cart.show', $this->creator->username))
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
    }

    // ───── 404s ─────

    public function test_nonexistent_creator_returns_404(): void
    {
        $this->actingAs($this->buyer)
            ->post('/nonexistent_user/cart/checkout', [
                'payer_email' => 'payer@example.com',
            ])
            ->assertNotFound();
    }

    // ───── Guest checkout (no auth) ─────

    public function test_guest_can_checkout_with_cookie_cart(): void
    {
        $product = $this->digitalProduct(25000);
        $cart = $this->addProductToCart($product);
        // buyer_user_id already set via helper, but test that order is created.
        $cartIdCookie = "cart_id_{$this->creator->id}";

        $response = $this->withCookie($cartIdCookie, $cart->id)
            ->post($this->checkoutUrl(), [
                'payer_email' => 'guest@example.com',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'buyer_email' => 'guest@example.com',
        ]);
    }
}
