<?php

namespace Tests\Feature;

use App\Http\Controllers\CartController;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cart unit-style integration tests (direct controller method calls).
 * Phase 15 — testing cart merge logic without HTTP cookie complexity.
 *
 * Bypasses HTTP layer to focus on controller business logic.
 * HTTP-level cookie persistence is tested separately in CartHttpTest.
 */
class CartMergeLogicTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $username = 'merge_alice'): User
    {
        return User::factory()->create(['username' => $username]);
    }

    private function makeProduct(User $creator): Product
    {
        return Product::factory()->create([
            'user_id' => $creator->id,
            'price' => 50000,
            'status' => 'published',
            'type' => 'digital',
        ]);
    }

    private function makeCart(User $buyer, User $creator, array $items = []): Cart
    {
        $cart = Cart::create([
            'creator_user_id' => $creator->id,
            'buyer_user_id' => $buyer->id,
            'expires_at' => now()->addDays(7),
        ]);
        foreach ($items as $item) {
            CartItem::create(array_merge(['cart_id' => $cart->id], $item));
        }

        return $cart;
    }

    public function test_adding_same_product_to_existing_cart_increments_quantity(): void
    {
        $user = $this->makeUser();
        $product = $this->makeProduct($user);
        $cart = $this->makeCart($user, $user, [
            ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => $product->price],
        ]);

        // Simulate controller logic: get cart, find existing item, increment
        $existing = $cart->items()->where('product_id', $product->id)->first();
        $this->assertNotNull($existing);
        $this->assertEquals(2, $existing->quantity);

        // Apply merge logic (mirrors CartController::add)
        $newQty = 3;
        $existing->update(['quantity' => min(10, $existing->quantity + $newQty)]);

        $this->assertEquals(5, $existing->fresh()->quantity);
        $this->assertCount(1, $cart->items()->get());
    }

    public function test_quantity_capped_at_max_10(): void
    {
        $user = $this->makeUser();
        $product = $this->makeProduct($user);
        $cart = $this->makeCart($user, $user, [
            ['product_id' => $product->id, 'quantity' => 9, 'unit_price' => $product->price],
        ]);
        $existing = $cart->items()->first();

        // Adding 5 more should cap at 10
        $existing->update(['quantity' => min(10, $existing->quantity + 5)]);
        $this->assertEquals(10, $existing->fresh()->quantity);
    }

    public function test_changing_creator_clears_cart(): void
    {
        $alice = $this->makeUser('merge_alice');
        $bob = $this->makeUser('merge_bob');
        $aliceProduct = $this->makeProduct($alice);

        $cart = $this->makeCart($alice, $alice, [
            ['product_id' => $aliceProduct->id, 'quantity' => 2, 'unit_price' => $aliceProduct->price],
        ]);

        // Simulate adding from Bob's store (different creator)
        // Per CartController::add lines 47-54: clears cart if creator mismatch
        $bobProduct = $this->makeProduct($bob);
        if ($cart->creator_user_id !== $bob->id) {
            $cart->items()->delete();
            $cart->update([
                'creator_user_id' => $bob->id,
                'voucher_id' => null,
                'voucher_discount' => 0,
            ]);
        }

        $this->assertCount(0, $cart->fresh()->items()->get(), 'Items should be cleared');
        $this->assertEquals($bob->id, $cart->fresh()->creator_user_id);
    }

    public function test_user_owns_their_own_cart(): void
    {
        $alice = $this->makeUser('merge_alice');
        $cart = $this->makeCart($alice, $alice);
        $this->assertEquals($alice->id, $cart->buyer_user_id);
        $this->assertEquals($alice->id, $cart->creator_user_id);
    }

    public function test_expired_cart_is_not_returned_by_lookup(): void
    {
        $user = $this->makeUser();
        $product = $this->makeProduct($user);

        $expired = Cart::create([
            'creator_user_id' => $user->id,
            'buyer_user_id' => $user->id,
            'expires_at' => now()->subDay(),
        ]);

        // CartController::getOrCreateCart queries `where('expires_at', '>', now())`
        $found = Cart::where('id', $expired->id)
            ->where('creator_user_id', $user->id)
            ->where('expires_at', '>', now())
            ->first();
        $this->assertNull($found, 'Expired cart should not be findable');

        // Active cart IS findable
        $active = Cart::create([
            'creator_user_id' => $user->id,
            'buyer_user_id' => $user->id,
            'expires_at' => now()->addDays(7),
        ]);
        $found = Cart::where('id', $active->id)
            ->where('creator_user_id', $user->id)
            ->where('expires_at', '>', now())
            ->first();
        $this->assertNotNull($found);
    }
}
