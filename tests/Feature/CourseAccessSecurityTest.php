<?php

namespace Tests\Feature;

use App\Http\Controllers\CourseController;
use App\Models\CourseModule;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseAccessSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $creator;

    protected Product $course;

    protected Order $paidOrder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creator = User::factory()->create(['username' => 'teacher']);

        $this->course = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'course',
            'title' => 'My Course',
            'price' => 100000,
            'status' => 'published',
            'id' => 'abcdefgh1234',
        ]);

        CourseModule::factory()->create([
            'product_id' => $this->course->id,
            'position' => 1,
            'title' => 'Module 1',
        ]);

        $this->paidOrder = Order::factory()->create([
            'id' => 'ORD-20260620-ABCD1234',
            'product_id' => $this->course->id,
            'creator_user_id' => $this->creator->id,
            'buyer_email' => 'student@example.com',
            'payment_status' => 'paid',
            'paid_at' => now(),
            'total' => 100000,
            'creator_payout' => 90000,
            'unit_price' => 100000,
            'quantity' => 1,
            'subtotal' => 100000,
            'fee_pct' => 10,
            'fee_amount' => 10000,
        ]);
    }

    public function test_course_access_requires_paid_order_for_authenticated_user(): void
    {
        $student = User::factory()->create(['email' => 'student@example.com']);
        $this->actingAs($student);

        $response = $this->get("/teacher/{$this->course->id}/learn");
        $response->assertOk();
    }

    public function test_course_access_redirects_to_product_page_when_no_order(): void
    {
        $stranger = User::factory()->create(['email' => 'stranger@example.com']);
        $this->actingAs($stranger);

        $response = $this->get("/teacher/{$this->course->id}/learn");
        $response->assertRedirect();
    }

    public function test_course_access_with_valid_guest_token_grants_access(): void
    {
        $token = CourseController::generateAccessToken($this->paidOrder);

        $response = $this->get("/teacher/{$this->course->id}/learn?token={$token}");
        $response->assertOk();
    }

    public function test_course_access_with_invalid_guest_token_rejected(): void
    {
        $response = $this->get("/teacher/{$this->course->id}/learn?token=invalid.token.here");
        $response->assertRedirect(); // Bounces to product page
    }

    public function test_course_access_with_token_for_different_product_rejected(): void
    {
        // Token signed for a DIFFERENT product should not grant access to this course
        $otherCourse = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'course',
            'status' => 'published',
            'id' => 'zzzzzzzz9999',
        ]);

        $token = CourseController::generateAccessToken($this->paidOrder);
        // Try to use this token on the OTHER course
        $response = $this->get("/teacher/{$otherCourse->id}/learn?token={$token}");
        $response->assertRedirect(); // Should bounce — token is product-bound
    }

    public function test_course_access_with_token_for_different_buyer_rejected(): void
    {
        // Create a different paid order with different buyer email
        $otherOrder = Order::factory()->create([
            'id' => 'ORD-20260620-OTHER0000',
            'product_id' => $this->course->id,
            'creator_user_id' => $this->creator->id,
            'buyer_email' => 'attacker@evil.com',
            'payment_status' => 'paid',
            'paid_at' => now(),
            'total' => 100000,
            'creator_payout' => 90000,
            'unit_price' => 100000,
            'quantity' => 1,
            'subtotal' => 100000,
            'fee_pct' => 10,
            'fee_amount' => 10000,
        ]);

        $token = CourseController::generateAccessToken($otherOrder);

        // Original student should NOT be able to use attacker's token
        $student = User::factory()->create(['email' => 'student@example.com']);
        $this->actingAs($student);

        // Token has wrong email binding — but the student IS a legitimate buyer via email match
        // so they'd get in via their own order regardless. To truly test token binding,
        // we test as a stranger:
        $stranger = User::factory()->create(['email' => 'stranger@example.com']);
        $this->actingAs($stranger);

        $response = $this->get("/teacher/{$this->course->id}/learn?token={$token}");
        $response->assertRedirect(); // Bounce — stranger has no order
    }
}
