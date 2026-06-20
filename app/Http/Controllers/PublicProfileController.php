<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DuitkuService;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PublicProfileController extends Controller
{
    /**
     * Show creator's public profile with search/filter.
     *
     * Reads top-down: resolve creator → build query → apply filters/sort →
     * fetch → increment views → resolve featured → exclude from grid →
     * compute counts → return view.
     */
    public function show(Request $request, string $username)
    {
        $creator = User::where('username', $username)->firstOrFail();

        $typeFilter = $request->query('type');
        $search = trim((string) $request->query('q', ''));
        $sort = (string) $request->query('sort', 'latest');

        $query = $creator->publishedProducts();
        $this->applyFilters($query, $typeFilter, $search);
        $this->applySort($query, $sort);

        $products = $query->get();
        $this->incrementViewCounts($products);

        $featured = $this->resolveFeaturedProduct($creator, $typeFilter, $search);
        $products = $this->excludeFeaturedFromGrid($products, $featured);

        $typeCounts = $this->computeTypeCounts($creator);

        return view('public.profile', compact(
            'creator', 'products', 'featured', 'typeCounts', 'typeFilter', 'search', 'sort'
        ));
    }

    /**
     * Show product detail page.
     */
    public function showProduct(Request $request, string $username, string $productId)
    {
        $creator = User::where('username', $username)->firstOrFail();
        $product = $creator->products()->where('id', $productId)->firstOrFail();

        abort_unless($product->isPublished() || $product->user_id === Auth::id(), 404);
        $product->incrementQuietly('view_count');

        return view('public.product', compact('creator', 'product'));
    }

    /**
     * Show checkout page.
     */
    public function checkout(Request $request, string $username, string $productId)
    {
        $creator = User::where('username', $username)->firstOrFail();
        $product = $creator->products()->where('id', $productId)->where('status', 'published')->firstOrFail();

        // For physical products out of stock, block checkout
        if ($product->type === 'physical' && ! $product->inStock) {
            return back()->with('error', 'Sorry, this product is out of stock.');
        }

        return view('public.checkout', compact('creator', 'product'));
    }

    /**
     * Process checkout — handles all 7 product types via OrderService.
     * Refactored in Phase 9: business logic moved to App\Services\OrderService.
     * Rate limit handled by 'throttle:checkout' middleware in routes/web.php.
     */
    public function processCheckout(
        Request $request,
        DuitkuService $duitku,
        OrderService $orders,
        string $username,
        string $productId
    ): RedirectResponse {
        $creator = User::where('username', $username)->firstOrFail();
        $product = $creator->products()->where('id', $productId)->where('status', 'published')->firstOrFail();

        $rules = [
            'payer_email' => ['required', 'email'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];

        // Type-specific validation
        switch ($product->type) {
            case 'donation':
                // SECURITY: Cap donation at Rp 100M to prevent abuse / payment-gateway errors.
                // Preset amounts are validated as a whitelist so client cannot inject arbitrary values.
                $presets = $product->donation_presets; // from model accessor (typed)
                $rules['amount'] = ['required', 'integer', 'min:1000', 'max:100000000'];
                if (! $product->meta('allow_custom', true)) {
                    $rules['amount'][] = function ($attr, $value, $fail) use ($presets) {
                        if (! in_array((int) $value, array_map('intval', $presets), true)) {
                            $fail('Please choose one of the preset amounts.');
                        }
                    };
                }
                $rules['donor_message'] = ['nullable', 'string', 'max:500'];
                break;
            case 'appointment':
                $rules['appointment_date'] = ['required', 'date', 'after_or_equal:today'];
                $rules['appointment_time'] = ['required'];
                break;
            case 'event':
                $rules['quantity'] = ['required', 'integer', 'min:1', 'max:10'];
                if ($product->meta('capacity')) {
                    $sold = $product->paidOrders()->sum('quantity');
                    $rules['quantity'][] = 'max:'.max(1, $product->meta('capacity') - $sold);
                }
                break;
            case 'physical':
                $rules['ship'] = ['required', 'array'];
                $rules['ship.name'] = ['required', 'string', 'max:100'];
                $rules['ship.phone'] = ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'];
                $rules['ship.address'] = ['required', 'string', 'max:500'];
                $rules['ship.city'] = ['required', 'string', 'max:100'];
                $rules['ship.province'] = ['required', 'string', 'max:100'];
                $rules['ship.postal_code'] = ['required', 'string', 'max:10'];
                $rules['ship.country'] = ['nullable', 'string', 'max:100'];
                $rules['ship.notes'] = ['nullable', 'string', 'max:200'];
                break;
        }

        $data = $request->validate($rules);

        // For donation: no need to update metadata - current_amount is computed live
        // (See Product::getDonationRaisedAttribute)

        // Get or create a buyer user record (for guest buyers, we don't create a user)
        $buyer = Auth::user() ?? new User(['email' => $data['payer_email'], 'name' => explode('@', $data['payer_email'])[0]]);

        // OrderService handles all the business logic: pricing, fee, persistence, transactions
        try {
            $order = $orders->createSingleProductOrder(
                buyer: $buyer,
                creator: $creator,
                product: $product,
                data: $data,
            );
        } catch (\Throwable $e) {
            Log::error('Order creation failed', [
                'product_id' => $product->id,
                'creator_id' => $creator->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['payer_email' => 'Could not create order. Please try again.']);
        }

        // Initialize Duitku payment (outside the order-creation transaction)
        try {
            $paymentUrl = $duitku->createTransaction($order, $product, $creator, $data['payer_email']);

            return redirect()->away($paymentUrl);
        } catch (\Exception $e) {
            Log::error('Duitku init failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            $order->payment_status = 'failed';
            $order->save();

            return back()->withErrors(['payer_email' => 'Payment gateway error. Please try again.']);
        }
    }

    // ───── show() helpers ─────

    /**
     * Apply type filter and search filter to the product query.
     * Invalid type filters are silently ignored (graceful).
     *
     * Accepts either a Builder (e.g. ->newQuery()) or a Relation
     * (e.g. ->hasMany() from creator->products()) — both proxy where().
     */
    private function applyFilters(Builder|Relation $query, ?string $typeFilter, string $search): void
    {
        if ($typeFilter && array_key_exists($typeFilter, Product::TYPES)) {
            $query->where('type', $typeFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
    }

    /**
     * Apply sort order to the product query.
     * Unknown sort values fall back to 'latest' (by created_at desc).
     */
    private function applySort(Builder|Relation $query, string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'popular' => $query->orderByDesc('sales_count')->orderByDesc('view_count'),
            default => $query->latest(),
        };
    }

    /**
     * Bump view_count for every product in the collection in a single
     * SQL UPDATE (was: N+1 — one UPDATE per product in a loop).
     * Uses increment() (not incrementQuietly) to fire model events; this
     * matches the original behavior in terms of the DB side-effect.
     */
    private function incrementViewCounts(Collection $products): void
    {
        if ($products->isEmpty()) {
            return;
        }
        Product::whereIn('id', $products->pluck('id'))->increment('view_count');
    }

    /**
     * Resolve the featured product to highlight at the top of the page.
     * Returns null when the user is searching or filtering (featured
     * would just be in the way).
     */
    private function resolveFeaturedProduct(User $creator, ?string $typeFilter, string $search): ?Product
    {
        if ($typeFilter || $search) {
            return null;
        }

        return $creator->products()
            ->where('status', 'published')
            ->where('is_featured', true)
            ->first()
            ?? $creator->products()
                ->where('status', 'published')
                ->orderByDesc('sales_count')
                ->first();
    }

    /**
     * Remove the featured product from the grid collection so it doesn't
     * appear twice on the page.
     */
    private function excludeFeaturedFromGrid(Collection $products, ?Product $featured): Collection
    {
        if (! $featured) {
            return $products;
        }

        return $products->where('id', '!=', $featured->id)->values();
    }

    /**
     * Compute per-type counts for the filter UI (e.g. "Digital (3)").
     * Only published products are counted.
     */
    private function computeTypeCounts(User $creator): array
    {
        return $creator->products()
            ->where('status', 'published')
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }
}
