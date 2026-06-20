<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class PerformanceOptimizationsTest extends TestCase
{
    public function test_html_response_is_gzipped()
    {
        $response = $this->get('/', ['Accept-Encoding' => 'gzip']);

        $response->assertStatus(200);
        $this->assertEquals('gzip', $response->headers->get('Content-Encoding'));
    }

    public function test_avatar_url_has_dimensions()
    {
        $user = new class extends User
        {
            protected $table = 'users';

            protected $guarded = [];
        };
        $user->name = 'Test';
        $user->username = 'test';
        $user->avatar_path = null;

        $avatarUrl = $user->avatar_url;

        $this->assertStringContainsString('size=200', $avatarUrl);
    }

    public function test_html_has_preconnect_for_external_avatar_cdn()
    {
        $response = $this->get('/');
        $content = $response->getContent();

        // ui-avatars.com is the external avatar CDN
        $this->assertMatchesRegularExpression(
            '/<link[^>]+rel=["\']preconnect["\'][^>]+ui-avatars\.com/i',
            $content,
            'Expected preconnect to ui-avatars.com for faster avatar loading'
        );
    }

    public function test_html_has_at_least_3_preconnect_hints()
    {
        $response = $this->get('/');
        $content = $response->getContent();

        preg_match_all('/<link[^>]+rel=["\']preconnect["\'][^>]*>/i', $content, $matches);
        $this->assertGreaterThanOrEqual(
            3,
            count($matches[0]),
            'Expected at least 3 preconnect hints (fonts.bunny.net, ui-avatars.com, etc)'
        );
    }
}
