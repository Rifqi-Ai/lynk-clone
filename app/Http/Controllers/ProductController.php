<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    // ───── Constants (was magic numbers in store()) ─────

    /** Max products a creator may create per hour (DoS guard). */
    private const MAX_PRODUCTS_PER_HOUR = 20;

    /** Throttle decay window in seconds. */
    private const PRODUCT_THROTTLE_DECAY_SECONDS = 3600;

    /** Max attempts to find a unique slug before failing. */
    private const MAX_SLUG_COLLISION_ATTEMPTS = 10;

    // ───── CRUD actions ─────

    /**
     * List creator's products (all types).
     */
    public function index(Request $request)
    {
        $products = $request->user()->products()->latest()->paginate(20);

        return view('dashboard.products.index', compact('products'));
    }

    /**
     * Show create form (type chooser if no type query).
     */
    public function create(Request $request)
    {
        $type = $request->query('type');

        // No type selected — show chooser
        if (! $type || ! array_key_exists($type, Product::TYPES)) {
            return view('dashboard.products.choose-type');
        }

        $product = new Product(['type' => $type]);

        return view('dashboard.products.create', [
            'product' => $product,
            'type' => $type,
        ]);
    }

    /**
     * Store new product (any type).
     * Rate limited per user: 20/hour (prevents DoS via spam creation).
     *
     * Reads like prose: rate-limit → validate → build → upload files →
     * persist with unique slug → redirect.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->enforceProductCreationRateLimit($request);

        $data = $this->validateProduct($request);
        $metadata = $this->extractMetadata($request);

        $product = $this->buildProduct($request, $data, $metadata);
        $this->storeThumbnail($product, $request);
        $this->storeProductFile($product, $request);
        $this->saveProductWithUniqueSlug($product, $data['title']);

        return $this->redirectAfterSave($product, $request);
    }

    /**
     * Show edit form.
     */
    public function edit(Product $product)
    {
        $this->authorize($product);

        return view('dashboard.products.edit', ['product' => $product, 'type' => $product->type]);
    }

    /**
     * Update product.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize($product);

        $data = $this->validateProduct($request, $product);
        $metadata = $this->extractMetadata($request, $product);

        $product->fill([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'compare_at_price' => $data['compare_at_price'] ?? null,
            'metadata' => $metadata,
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($product->thumbnail_path) {
                Storage::disk('public')->delete($product->thumbnail_path);
            }
            $product->thumbnail_path = $request->file('thumbnail')->store(
                "products/{$product->user_id}/thumbnails", 'public'
            );
        }

        // Only handle file for digital/course types
        if (in_array($product->type, ['digital', 'course']) && $request->hasFile('file')) {
            if ($product->file_path) {
                Storage::disk('public')->delete($product->file_path);
            }
            $file = $request->file('file');
            $product->file_path = $file->store("products/{$product->user_id}/files", 'public');
            $product->file_name = $file->getClientOriginalName();
            $product->file_size = $file->getSize();
        }

        if ($request->boolean('publish')) {
            $product->status = 'published';
        } elseif ($request->boolean('unpublish')) {
            $product->status = 'draft';
        }

        $product->save();

        return redirect()->route('dashboard.products.edit', $product)
            ->with('success', "{$product->typeLabel} updated.");
    }

    /**
     * Delete product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize($product);
        if ($product->thumbnail_path) {
            Storage::disk('public')->delete($product->thumbnail_path);
        }
        if ($product->file_path) {
            Storage::disk('public')->delete($product->file_path);
        }
        $product->delete();

        return redirect()->route('dashboard.products.index')->with('success', "{$product->typeLabel} deleted.");
    }

    // ───── store() helpers ─────

    /**
     * Throttle product creation per user. Returns early with validation
     * error if the budget is exhausted.
     */
    private function enforceProductCreationRateLimit(Request $request): void
    {
        $key = 'product-create:'.$request->user()->id;
        if (RateLimiter::tooManyAttempts($key, self::MAX_PRODUCTS_PER_HOUR)) {
            $seconds = RateLimiter::availableIn($key);
            // Throw a ValidationException so the redirect back carries the error
            // to the same view the user came from (consistent with other validation).
            throw ValidationException::withMessages([
                'title' => "Too many products created. Try again in {$seconds}s.",
            ]);
        }
        RateLimiter::hit($key, self::PRODUCT_THROTTLE_DECAY_SECONDS);
    }

    /**
     * Build a new Product with the validated scalar fields set.
     * Files and slug collision handling are done in subsequent steps.
     */
    private function buildProduct(Request $request, array $data, array $metadata): Product
    {
        $product = new Product;
        $product->id = Product::generateId();
        $product->user_id = Auth::id();
        $product->type = $data['type'];
        $product->title = $data['title'];
        $product->slug = Str::slug($data['title']);
        $product->description = $data['description'] ?? null;
        $product->price = $data['price'];
        $product->compare_at_price = $data['compare_at_price'] ?? null;
        $product->status = $request->boolean('publish') ? 'published' : 'draft';
        $product->metadata = $metadata;

        return $product;
    }

    /**
     * Store the uploaded thumbnail on the public disk, if present.
     */
    private function storeThumbnail(Product $product, Request $request): void
    {
        if (! $request->hasFile('thumbnail')) {
            return;
        }
        $product->thumbnail_path = $request->file('thumbnail')->store(
            "products/{$product->user_id}/thumbnails",
            'public'
        );
    }

    /**
     * Store the uploaded digital file on the public disk, if present.
     * Only relevant for digital and course product types.
     */
    private function storeProductFile(Product $product, Request $request): void
    {
        if (! $request->hasFile('file')) {
            return;
        }
        $file = $request->file('file');
        $product->file_path = $file->store("products/{$product->user_id}/files", 'public');
        $product->file_name = $file->getClientOriginalName();
        $product->file_size = $file->getSize();
    }

    /**
     * Persist the product, retrying with a suffixed slug if the
     * (user_id, slug) unique constraint is violated.
     *
     * Catches the database-agnostic UniqueConstraintViolationException
     * (Laravel 12+) — works on MySQL, PostgreSQL, and SQLite alike.
     */
    private function saveProductWithUniqueSlug(Product $product, string $title): void
    {
        $baseSlug = $product->slug;
        for ($attempt = 0; $attempt < self::MAX_SLUG_COLLISION_ATTEMPTS; $attempt++) {
            try {
                $product->save();

                return;
            } catch (UniqueConstraintViolationException $e) {
                // Append attempt counter and retry. DB unique index is the
                // safety net against race conditions between concurrent requests.
                $product->slug = $baseSlug.'-'.($attempt + 1);
            }
        }
        throw new \RuntimeException(
            "Could not generate unique product slug for \"{$title}\" after ".
            self::MAX_SLUG_COLLISION_ATTEMPTS.' attempts.'
        );
    }

    /**
     * Determine the post-save redirect target based on whether
     * the product was published or saved as draft.
     */
    private function redirectAfterSave(Product $product, Request $request): RedirectResponse
    {
        $published = $request->boolean('publish');
        $route = $published
            ? 'dashboard.products.index'
            : 'dashboard.products.edit';

        // BUG FIX: dashboard.products.index has no {product} parameter,
        // so passing $product would attach it as ?product=... query string.
        // Only the edit route needs the product binding.
        $parameters = $published ? [] : $product;

        return redirect()->route($route, $parameters)
            ->with(
                'success',
                $published
                    ? "{$product->typeLabel} published!"
                    : "{$product->typeLabel} saved as draft."
            );
    }

    // ───── Validation + metadata helpers ─────

    protected function validateProduct(Request $request, ?Product $product = null): array
    {
        $typeRule = ['required', 'in:'.implode(',', array_keys(Product::TYPES))];

        return $request->validate([
            'type' => $typeRule,
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'gt:price'],
            'download_limit_per_purchase' => ['nullable', 'integer', 'min:1', 'max:100'],
            // SECURITY: Restrict thumbnail to actual image MIME types + reasonable dims
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=2000,max_height=2000'],
            // SECURITY: Block executable MIME types for product files (digital/course).
            // Allowed: pdf, zip, mp4, mp3, images, documents, ebooks. No php/exe/sh/htm.
            'file' => ['nullable', 'file', 'max:51200', 'mimes:pdf,zip,mp4,mp3,m4a,mov,avi,wav,ogg,webm,jpg,jpeg,png,webp,gif,svg,doc,docx,xls,xlsx,ppt,pptx,txt,csv,epub,mobi'],
        ], [
            'file.mimes' => 'File type not allowed. Supported: PDF, ZIP, video, audio, images, docs, ebooks.',
            'thumbnail.dimensions' => 'Thumbnail too large. Max 2000×2000 px.',
        ]);
    }

    /**
     * Extract type-specific metadata from request.
     */
    protected function extractMetadata(Request $request, ?Product $product = null): array
    {
        $type = $request->input('type', $product?->type ?? 'digital');
        $metadata = $product?->metadata ?? [];

        switch ($type) {
            case 'donation':
                $presets = $request->input('preset_amounts', []);
                $metadata['preset_amounts'] = array_map('intval', array_filter($presets));
                $metadata['allow_custom'] = $request->boolean('allow_custom');
                $metadata['goal_amount'] = $request->input('goal_amount') ? (int) $request->input('goal_amount') : null;
                $metadata['message_label'] = $request->input('message_label', 'Send a message (optional)');
                break;

            case 'appointment':
                $metadata['duration_minutes'] = (int) $request->input('duration_minutes', 60);
                $metadata['buffer_minutes'] = (int) $request->input('buffer_minutes', 15);
                $metadata['location_type'] = $request->input('location_type', 'online'); // online/in-person
                $metadata['location_details'] = $request->input('location_details');
                break;

            case 'event':
                $metadata['event_date'] = $request->input('event_date');
                $metadata['end_date'] = $request->input('end_date');
                $metadata['stream_url'] = $request->input('stream_url');
                $metadata['capacity'] = $request->input('capacity') ? (int) $request->input('capacity') : null;
                $metadata['location'] = $request->input('location');
                break;

            case 'course':
                $modules = $request->input('course_modules', []);
                $metadata['modules'] = array_values(array_filter(array_map('trim', $modules)));
                $metadata['total_duration_minutes'] = (int) $request->input('total_duration_minutes', 0);
                $metadata['level'] = $request->input('level', 'beginner'); // beginner/intermediate/advanced
                $metadata['certificate'] = $request->boolean('certificate');
                break;

            case 'blog':
                $metadata['body_markdown'] = $request->input('body_markdown');
                $metadata['is_paywalled'] = $request->boolean('is_paywalled');
                $metadata['preview_text'] = $request->input('preview_text'); // free preview before paywall
                break;

            case 'physical':
                $metadata['stock_quantity'] = $request->input('stock_quantity') ? (int) $request->input('stock_quantity') : null;
                $metadata['weight_grams'] = $request->input('weight_grams') ? (int) $request->input('weight_grams') : null;
                $metadata['requires_shipping'] = $request->boolean('requires_shipping', true);
                $metadata['dimensions'] = $request->input('dimensions'); // e.g. "10x10x5 cm"
                break;
        }

        return $metadata;
    }

    protected function authorize(Product $product): void
    {
        abort_unless($product->user_id === Auth::id(), 403);
    }
}
