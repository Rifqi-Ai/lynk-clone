<?php

namespace Tests\Feature;

use App\Models\CourseModule;
use App\Models\Event;
use App\Models\EventTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression tests for data exposure on EventTicket + CourseModule models.
 * Phase 14 — security audit follow-up.
 *
 * Sensitive fields MUST NOT leak via JSON serialization (API responses).
 * Direct attribute access still works ($ticket->ticket_code in views).
 */
class DataExposureProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_ticket_hides_ticket_code_from_json_serialization(): void
    {
        $ticket = EventTicket::factory()->create(['ticket_code' => 'TKT-SECRET123']);

        $json = $ticket->toJson();
        $array = $ticket->toArray();

        $this->assertStringNotContainsString('TKT-SECRET123', $json);
        $this->assertStringNotContainsString('TKT-SECRET123', json_encode($array));
        $this->assertArrayNotHasKey('ticket_code', $array);
    }

    public function test_event_ticket_can_still_access_ticket_code_directly(): void
    {
        // Direct attribute access for views / controllers that legitimately need it
        $ticket = EventTicket::factory()->create(['ticket_code' => 'TKT-VISIBLE']);

        $this->assertEquals('TKT-VISIBLE', $ticket->ticket_code);
    }

    public function test_course_module_hides_video_url_from_json_serialization(): void
    {
        $module = CourseModule::factory()->create([
            'video_url' => 'https://secret.video.example/abc123',
        ]);

        $json = $module->toJson();
        $array = $module->toArray();

        $this->assertStringNotContainsString('secret.video.example', $json);
        $this->assertStringNotContainsString('secret.video.example', json_encode($array));
        $this->assertArrayNotHasKey('video_url', $array);
    }

    public function test_course_module_can_still_access_video_url_directly(): void
    {
        // Direct attribute access for course player views
        $module = CourseModule::factory()->create([
            'video_url' => 'https://player.video.example/xyz',
        ]);

        $this->assertEquals('https://player.video.example/xyz', $module->video_url);
    }

    public function test_event_ticket_make_visible_can_expose_ticket_code_when_needed(): void
    {
        // When legitimately needed (e.g., owner-facing event dashboard),
        // controllers can call ->makeVisible() to override the default hide.
        $ticket = EventTicket::factory()->create(['ticket_code' => 'TKT-OWNER']);

        $ticket->makeVisible('ticket_code');
        $array = $ticket->toArray();

        $this->assertArrayHasKey('ticket_code', $array);
        $this->assertEquals('TKT-OWNER', $array['ticket_code']);
    }

    public function test_course_module_make_visible_can_expose_video_url_for_enrolled_students(): void
    {
        // Course player view needs video_url after access check passes.
        $module = CourseModule::factory()->create([
            'video_url' => 'https://lesson.video.example/lesson-1',
        ]);

        $module->makeVisible('video_url');
        $array = $module->toArray();

        $this->assertArrayHasKey('video_url', $array);
    }
}
