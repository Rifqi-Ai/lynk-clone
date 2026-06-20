<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
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
        if (!$type || !array_key_exists($type, Product::TYPES)) {
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
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);
        $metadata = $this->extractMetadata($request);

        $product = new Product();
        $product->user_id = Auth::id();
        $product->type = $data['type'];
        $product->title = $data['title'];
        $product->slug = \Illuminate\Support\Str::slug($data['title']);
        $product->description = $data['description'] ?? null;
        $product->price = $data['price'];
        $product->compare_at_price = $data['compare_at_price'] ?? null;
        $product->status = $request->boolean('publish') ? 'published' : 'draft';
        $product->id = Product::generateId();
        $product->metadata = $metadata;

        // Ensure slug uniqueness
        $baseSlug = $product->slug;
        $i = 1;
        while (Product::where('user_id', Auth::id())->where('slug', $product->slug)->exists()) {
            $product->slug = $baseSlug . '-' . $i++;
        }

        // Handle thumbnail
        if ($request->hasFile('thumbnail')) {
            $product->thumbnail_path = $request->file('thumbnail')->store(
                "products/{$request->user()->id}/thumbnails",
                'public'
            );
        }

        // Handle digital file
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $product->file_path = $file->store("products/{$request->user()->id}/files", 'public');
            $product->file_name = $file->getClientOriginalName();
            $product->file_size = $file->getSize();
        }

        $product->save();

        $route = $request->boolean('publish') ? 'dashboard.products.index' : 'dashboard.products.edit';
        return redirect()->route($route, $product)
            ->with('success', $product->isPublished() ? "{$product->typeLabel} published!" : "{$product->typeLabel} saved as draft.");
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
            if ($product->thumbnail_path) Storage::disk('public')->delete($product->thumbnail_path);
            $product->thumbnail_path = $request->file('thumbnail')->store(
                "products/{$product->user_id}/thumbnails", 'public'
            );
        }

        // Only handle file for digital/course types
        if (in_array($product->type, ['digital', 'course']) && $request->hasFile('file')) {
            if ($product->file_path) Storage::disk('public')->delete($product->file_path);
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
        if ($product->thumbnail_path) Storage::disk('public')->delete($product->thumbnail_path);
        if ($product->file_path) Storage::disk('public')->delete($product->file_path);
        $product->delete();
        return redirect()->route('dashboard.products.index')->with('success', "{$product->typeLabel} deleted.");
    }

    // ───── Helpers ─────

    protected function validateProduct(Request $request, ?Product $product = null): array
    {
        $typeRule = ['required', 'in:' . implode(',', array_keys(Product::TYPES))];
        return $request->validate([
            'type' => $typeRule,
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'gt:price'],
            'download_limit_per_purchase' => ['nullable', 'integer', 'min:1', 'max:100'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'file' => ['nullable', 'file', 'max:51200'], // 50MB
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