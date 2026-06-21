<?php

namespace Tests\Feature;

use App\Http\Controllers\CourseController;
use App\Models\EventTicket;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Function-level authorization tests (BFLA — Broken Function Level Authorization,
 * OWASP API5:2023).
 *
 * These tests cover endpoints that don't have a route-level middleware but DO
 * expose user-specific data (tickets, payment confirmations). The Phase 12
 * security audit flagged this as a gap; these tests pin down the contracts.
 *
 * **Pattern under test (reused from CourseController):**
 *  - Authenticated buyers: matched by `buyer_user_id === Auth::id()` OR
 *    `buyer_email === Auth::user()->email`.
 *  - Guest buyers (no account): must present a signed `?token=...` that
 *    binds (order_id + buyer_email + product_id) so a leaked token can't
 *    be reused for another purchase.
 *  - Creators: can view their own event dashboard (separate auth-gated routes).
 *
 * @see docs/security-audit-2026-06-21.md (recommendation #4 — Phase 14 follow-up)
 */
class FunctionLevelAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $creator;

    private User $buyer;

    private User $otherUser;

    private Product $eventProduct;

    private Order $paidOrder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creator = User::factory()->create(['username' => 'eventcreator']);
        $this->buyer = User::factory()->create(['email' => 'buyer@example.com']);
        $this->otherUser = User::factory()->create(['email' => 'stranger@example.com']);

        $this->eventProduct = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'event',
        ]);

        $this->paidOrder = Order::factory()->create([
            'creator_user_id' => $this->creator->id,
            'product_id' => $this->eventProduct->id,
            'buyer_user_id' => $this->buyer->id,
            'buyer_email' => 'buyer@example.com',
            'payment_status' => 'paid',
            'paid_at' => now()->subDay(),
        ]);

        EventTicket::create([
            'order_id' => $this->paidOrder->id,
            'product_id' => $this->eventProduct->id,
            'buyer_email' => 'buyer@example.com',
            'ticket_code' => 'TKT-LEGIT-1234',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // EventController@ticket — the original BFLA finding (HIGH severity).
    // Anyone with orderId in URL could view any ticket, leaking QR code +
    // buyer email + attendee name. Now requires proof of ownership.
    // ─────────────────────────────────────────────────────────────────────

    public function test_stranger_cannot_view_someone_elses_ticket_without_token(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->get("/eventcreator/{$this->eventProduct->id}/ticket/{$this->paidOrder->id}");

        $response->assertStatus(403);
        $this->assertStringNotContainsString('TKT-LEGIT-1234', $response->getContent());
    }

    public function test_guest_cannot_view_ticket_without_token(): void
    {
        $response = $this->get("/eventcreator/{$this->eventProduct->id}/ticket/{$this->paidOrder->id}");

        $response->assertStatus(403);
        $this->assertStringNotContainsString('TKT-LEGIT-1234', $response->getContent());
    }

    public function test_buyer_can_view_their_own_ticket_when_authenticated(): void
    {
        $response = $this->actingAs($this->buyer)
            ->get("/eventcreator/{$this->eventProduct->id}/ticket/{$this->paidOrder->id}");

        $response->assertStatus(200);
        $this->assertStringContainsString('TKT-LEGIT-1234', $response->getContent());
    }

    public function test_guest_can_view_ticket_with_valid_signed_token(): void
    {
        $token = CourseController::generateAccessToken($this->paidOrder);

        $response = $this->get("/eventcreator/{$this->eventProduct->id}/ticket/{$this->paidOrder->id}?token={$token}");

        $response->assertStatus(200);
        $this->assertStringContainsString('TKT-LEGIT-1234', $response->getContent());
    }

    public function test_guest_with_token_for_different_order_cannot_view_ticket(): void
    {
        // Build a token for an order belonging to a DIFFERENT product.
        $otherProduct = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'event',
        ]);
        $otherOrder = Order::factory()->create([
            'creator_user_id' => $this->creator->id,
            'product_id' => $otherProduct->id,
            'buyer_user_id' => $this->buyer->id,
            'buyer_email' => 'buyer@example.com',
            'payment_status' => 'paid',
        ]);
        $wrongToken = CourseController::generateAccessToken($otherOrder);

        // Try to use the wrong token on the original ticket URL — should be denied.
        $response = $this->get("/eventcreator/{$this->eventProduct->id}/ticket/{$this->paidOrder->id}?token={$wrongToken}");

        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────────
    // PaymentCallbackController@success / @failed — payment confirmation pages.
    // Anyone with order ID could view, leaking buyer email + order details.
    // Now require ownership proof (auth OR signed token).
    // ─────────────────────────────────────────────────────────────────────

    public function test_stranger_cannot_view_someone_elses_payment_success(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->get("/payment/success/{$this->paidOrder->id}");

        $response->assertStatus(403);
        $this->assertStringNotContainsString($this->paidOrder->buyer_email, $response->getContent());
    }

    public function test_guest_cannot_view_payment_success_without_token(): void
    {
        $response = $this->get("/payment/success/{$this->paidOrder->id}");

        $response->assertStatus(403);
    }

    public function test_buyer_can_view_their_own_payment_success_when_authenticated(): void
    {
        $response = $this->actingAs($this->buyer)
            ->get("/payment/success/{$this->paidOrder->id}");

        $response->assertStatus(200);
        $this->assertStringContainsString($this->paidOrder->id, $response->getContent());
    }

    public function test_guest_can_view_payment_success_with_valid_signed_token(): void
    {
        $token = CourseController::generateAccessToken($this->paidOrder);

        $response = $this->get("/payment/success/{$this->paidOrder->id}?token={$token}");

        $response->assertStatus(200);
    }

    public function test_stranger_cannot_view_someone_elses_payment_failed(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->get("/payment/failed/{$this->paidOrder->id}");

        $response->assertStatus(403);
    }

    public function test_guest_cannot_view_payment_failed_without_token(): void
    {
        $response = $this->get("/payment/failed/{$this->paidOrder->id}");

        $response->assertStatus(403);
    }

    public function test_creator_can_view_payment_success_for_their_own_order(): void
    {
        // Creators need to see payment confirmations too (e.g. for support requests).
        $response = $this->actingAs($this->creator)
            ->get("/payment/success/{$this->paidOrder->id}");

        $response->assertStatus(200);
    }
}
