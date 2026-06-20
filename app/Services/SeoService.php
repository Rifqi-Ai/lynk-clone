<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Centralizes SEO meta tags + JSON-LD structured data.
 *
 * Returns an array of head tags ready to be rendered in <head>.
 * Keeps all pages using the same SEO conventions:
 * - canonical URL
 * - Open Graph (Facebook, LinkedIn)
 * - Twitter card
 * - JSON-LD structured data (Google rich results)
 */
class SeoService
{
    /**
     * Meta tags + JSON-LD for a single product page.
     */
    public function product(Product $product, User $creator): array
    {
        $url = url('/'.$creator->username.'/'.$product->id);
        $title = "{$product->title} — @{$creator->username} on Lynk";
        $description = Str::limit($product->description ?: $product->title, 160);
        $image = $product->thumbnail_url ?: $creator->avatar_url;

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $url,
            'og' => [
                'og:title' => $product->title,
                'og:description' => $description,
                'og:image' => $image,
                'og:type' => 'product',
                'og:url' => $url,
                'product:price:amount' => (string) (float) $product->price,
                'product:price:currency' => 'IDR',
            ],
            'twitter' => [
                'twitter:card' => 'summary_large_image',
                'twitter:title' => $product->title,
                'twitter:description' => $description,
                'twitter:image' => $image,
            ],
            'json_ld' => [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $product->title,
                'description' => Str::limit($product->description ?? '', 500),
                'image' => $image,
                'brand' => [
                    '@type' => 'Brand',
                    'name' => '@'.$creator->username,
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'price' => (float) $product->price,
                    'priceCurrency' => 'IDR',
                    'availability' => $product->status === 'published'
                        ? 'https://schema.org/InStock'
                        : 'https://schema.org/OutOfStock',
                    'url' => $url,
                    'seller' => [
                        '@type' => 'Person',
                        'name' => $creator->name,
                    ],
                ],
            ],
        ];
    }

    /**
     * Meta tags + JSON-LD for a creator's public profile.
     */
    public function profile(User $creator, int $productCount, int $followerCount = 0): array
    {
        $url = url('/'.$creator->username);
        $title = $creator->name." (@{$creator->username}) — Lynk";
        $description = $creator->bio
            ? Str::limit($creator->bio, 160)
            : "{$creator->name} on Lynk — {$productCount} products";

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $url,
            'og' => [
                'og:title' => $title,
                'og:description' => $description,
                'og:image' => $creator->avatar_url,
                'og:type' => 'profile',
                'og:url' => $url,
            ],
            'twitter' => [
                'twitter:card' => 'summary',
                'twitter:title' => $title,
                'twitter:description' => $description,
                'twitter:image' => $creator->avatar_url,
            ],
            'json_ld' => [
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => $creator->name,
                'alternateName' => '@'.$creator->username,
                'description' => $description,
                'image' => $creator->avatar_url,
                'url' => $url,
            ],
        ];
    }

    /**
     * Meta tags + JSON-LD for the homepage.
     */
    public function homepage(): array
    {
        $url = url('/');

        return [
            'title' => 'Lynk — Platform Jual Beli Kreator Indonesia',
            'description' => 'Jual produk digital, course, event, dan terima donasi dari penggemarmu. Setup 5 menit, langsung bisa jualan.',
            'canonical' => $url,
            'og' => [
                'og:title' => 'Lynk — Platform Jual Beli Kreator Indonesia',
                'og:description' => 'Jual produk digital, course, event, dan terima donasi. Setup 5 menit.',
                'og:type' => 'website',
                'og:url' => $url,
            ],
            'twitter' => [
                'twitter:card' => 'summary_large_image',
                'twitter:title' => 'Lynk — Platform Jual Beli Kreator Indonesia',
            ],
            'json_ld' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => 'Lynk',
                'description' => 'Platform SaaS untuk kreator Indonesia menjual produk digital, course, event, dan donasi.',
                'url' => $url,
            ],
        ];
    }

    /**
     * Render the head tags as HTML.
     */
    public function render(array $seo): string
    {
        $html = [];

        $html[] = "<title>{$this->escape($seo['title'])}</title>";
        $html[] = "<meta name=\"description\" content=\"{$this->escape($seo['description'])}\">";
        if (isset($seo['canonical'])) {
            $html[] = "<link rel=\"canonical\" href=\"{$this->escape($seo['canonical'])}\">";
        }

        foreach ($seo['og'] ?? [] as $key => $value) {
            $html[] = "<meta property=\"{$key}\" content=\"{$this->escape($value)}\">";
        }

        foreach ($seo['twitter'] ?? [] as $key => $value) {
            $html[] = "<meta name=\"{$key}\" content=\"{$this->escape($value)}\">";
        }

        if (isset($seo['json_ld'])) {
            $json = json_encode($seo['json_ld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $html[] = "<script type=\"application/ld+json\">{$json}</script>";
        }

        return implode("\n    ", $html);
    }

    private function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
