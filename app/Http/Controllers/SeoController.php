<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Response;

/**
 * SEO endpoints:
 * - /sitemap.xml — list all public product pages + creator profiles
 * - /robots.txt — instruct crawlers, link to sitemap
 */
class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $base = url('/');

        $urls = [];

        // Homepage
        $urls[] = ['loc' => $base, 'priority' => '1.0', 'changefreq' => 'daily'];

        // Marketing pages
        $urls[] = ['loc' => $base.'/pricing', 'priority' => '0.5', 'changefreq' => 'monthly'];
        $urls[] = ['loc' => $base.'/faq', 'priority' => '0.5', 'changefreq' => 'monthly'];
        $urls[] = ['loc' => $base.'/about', 'priority' => '0.5', 'changefreq' => 'monthly'];

        // All published products
        $products = Product::where('status', 'published')
            ->select(['id', 'slug', 'user_id', 'updated_at'])
            ->with('owner:id,username')
            ->orderByDesc('updated_at')
            ->limit(5000) // protect from runaway generation
            ->get();

        foreach ($products as $product) {
            if (! $product->owner) {
                continue; // skip orphaned products
            }
            $urls[] = [
                'loc' => $base.'/'.$product->owner->username.'/'.$product->id,
                'lastmod' => $product->updated_at->toAtomString(),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ];
        }

        // All public creator profiles
        $creators = User::whereNotNull('username')
            ->whereHas('products', fn ($q) => $q->where('status', 'published'))
            ->select(['username', 'updated_at'])
            ->orderByDesc('updated_at')
            ->limit(2000)
            ->get();

        foreach ($creators as $creator) {
            $urls[] = [
                'loc' => $base.'/'.$creator->username,
                'lastmod' => $creator->updated_at->toAtomString(),
                'priority' => '0.6',
                'changefreq' => 'weekly',
            ];
        }

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    public function robots(): Response
    {
        $sitemap = url('/sitemap.xml');
        $content = <<<ROBOTS
User-agent: *
Allow: /
Disallow: /dashboard
Disallow: /checkout
Disallow: /payment/
Disallow: /api/
Disallow: /admin

Sitemap: {$sitemap}
ROBOTS;

        return response($content, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
