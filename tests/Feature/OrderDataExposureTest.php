<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression tests for Order model data exposure.
 * Phase 15 — security audit follow-up.
 *
 * Sensitive internal fields MUST NOT leak via JSON serialization.
 * Direct attribute access still works for controllers/views that legitimately need them.
 */
class OrderDataExposureTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_hides_duitku_response_from_json(): void
    {
        $order = Order::factory()->create([
            'duitku_response' => ['bank' => 'BCA', 'internal_ref' => 'SECRET-12345'],
        ]);

        $json = $order->toJson();
        $array = $order->toArray();

        $this->assertStringNotContainsString('SECRET-12345', $json);
        $this->assertStringNotContainsString('SECRET-12345', json_encode($array));
        $this->assertArrayNotHasKey('duitku_response', $array);
    }

    public function test_order_hides_metadata_from_json(): void
    {
        $order = Order::factory()->create([
            'metadata' => ['fraud_score' => 0.95, 'internal_notes' => 'flagged for review'],
        ]);

        $json = $order->toJson();
        $array = $order->toArray();

        $this->assertStringNotContainsString('fraud_score', $json);
        $this->assertStringNotContainsString('flagged for review', $json);
        $this->assertArrayNotHasKey('metadata', $array);
    }

    public function test_order_can_still_access_internal_fields_directly(): void
    {
        // Admin dashboard / payment controllers legitimately need full data
        $order = Order::factory()->create([
            'duitku_response' => ['status' => 'success'],
            'metadata' => ['key' => 'value'],
        ]);

        $this->assertEquals(['status' => 'success'], $order->duitku_response);
        $this->assertEquals(['key' => 'value'], $order->metadata);
    }

    public function test_order_make_visible_exposes_internal_when_needed(): void
    {
        // Admin-facing endpoint can call makeVisible() to override the hide
        $order = Order::factory()->create([
            'duitku_response' => ['admin_view' => true],
            'metadata' => ['admin_view' => true],
        ]);

        $order->makeVisible(['duitku_response', 'metadata']);
        $array = $order->toArray();

        $this->assertArrayHasKey('duitku_response', $array);
        $this->assertArrayHasKey('metadata', $array);
    }
}
