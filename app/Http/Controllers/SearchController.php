<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Phase 17 Task #6 — Global search across creators + products.
 *
 * Closes UX audit (docs/ux-audit-2026-06-21.md) Trunk Test gap:
 *   "Search? Not present on landing — Phase 14"
 *
 * Searches:
 *  - Creators: User.username, User.name, User.bio
 *  - Products: Product.title, Product.description (only published status)
 *
 * Returns creators grouped separately from products in the results view.
 */
class SearchController extends Controller
{
    /** Max results per category to keep the page lightweight. */
    private const MAX_RESULTS_PER_CATEGORY = 20;

    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        $creators = collect();
        $products = collect();

        if ($query !== '') {
            $creators = User::query()
                ->where(function ($q) use ($query) {
                    $q->where('username', 'LIKE', "%{$query}%")
                        ->orWhere('name', 'LIKE', "%{$query}%")
                        ->orWhere('bio', 'LIKE', "%{$query}%");
                })
                // Creators with at least one published product show up in search.
                ->whereHas('products', fn ($q) => $q->where('status', 'published'))
                ->orderByDesc('id')
                ->limit(self::MAX_RESULTS_PER_CATEGORY)
                ->get();

            $products = Product::query()
                ->with('owner')
                ->where('status', 'published')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                        ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->orderByDesc('view_count')
                ->limit(self::MAX_RESULTS_PER_CATEGORY)
                ->get();
        }

        return view('search.index', [
            'query' => $query,
            'creators' => $creators,
            'products' => $products,
            'totalResults' => $creators->count() + $products->count(),
        ]);
    }
}
