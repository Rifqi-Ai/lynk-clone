<?php

namespace App\Models;

use App\Models\Concerns\HasProductAccessors;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Polymorphic Product model — supports 7 types via 'type' column + 'metadata' JSON.
 *
 * Common fields (columns): id, user_id, type, title, slug, description, price,
 *                          compare_at_price, thumbnail_path, status, sales_count
 *
 * Type-specific (metadata JSON):
 *   - digital:    { file_path, file_name, file_size, download_limit_per_purchase }
 *   - donation:   { preset_amounts: [10000, 25000, 50000, 100000], allow_custom: true, goal_amount, current_amount }
 *   - appointment:{ duration_minutes, buffer_minutes, availability: [...] }
 *   - event:      { event_date, end_date, stream_url, capacity, tickets_sold, location }
 *   - course:     { modules: [...], total_duration_minutes, level, certificate }
 *   - blog:       { body_markdown, is_paywalled, preview_text }
 *   - physical:   { stock_quantity, weight_grams, requires_shipping, dimensions }
 *
 * **Accessors** (17) live in `HasProductAccessors` trait. This keeps the model
 * focused on data + relationships + state transitions. See the trait for
 * the accessor catalog organized by section (URL/asset, pricing, type metadata,
 * type-specific).
 *
 * @see App\Models\Concerns\HasProductAccessors
 */
#[Fillable([
    'user_id', 'type', 'title', 'slug', 'description',
    'price', 'compare_at_price', 'thumbnail_path',
    'file_path', 'file_name', 'file_size', 'download_limit_per_purchase',
    'status', 'sales_count', 'view_count', 'is_featured',
])]
// NOTE: 'id' and 'metadata' are NOT in $fillable.
// - 'id' is generated via Product::generateId() in creating hook (collision-safe).
// - 'metadata' is NEVER mass-assigned. It must be set via ProductController::extractMetadata()
//   which only ever copies whitelisted type-specific keys (no user-controlled keys can leak in).
// This prevents attackers from injecting arbitrary JSON via POST fields like metadata[is_admin]=1.
class Product extends Model
{
    use HasFactory;
    use HasProductAccessors;

    public $incrementing = false;

    protected $keyType = 'string';

    /** All 7 supported product types */
    public const TYPES = [
        'digital' => ['label' => 'Digital Product', 'icon' => '📦', 'color' => 'bg-brand-100'],
        'donation' => ['label' => 'Donation', 'icon' => '☕', 'color' => 'bg-amber-100'],
        'appointment' => ['label' => 'Appointment', 'icon' => '📅', 'color' => 'bg-blue-100'],
        'event' => ['label' => 'Event / Webinar', 'icon' => '🎟️', 'color' => 'bg-pink-100'],
        'course' => ['label' => 'Course', 'icon' => '🎓', 'color' => 'bg-purple-100'],
        'blog' => ['label' => 'Blog Post', 'icon' => '📝', 'color' => 'bg-yellow-100'],
        'physical' => ['label' => 'Physical Product', 'icon' => '🛍️', 'color' => 'bg-cyan-100'],
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'file_size' => 'integer',
            'download_limit_per_purchase' => 'integer',
            'sales_count' => 'integer',
            'view_count' => 'integer',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->id)) {
                $product->id = static::generateId();
            }
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->title);
            }
            if (empty($product->metadata)) {
                $product->metadata = [];
            }
        });
    }

    /**
     * Generate a 12-char nanoid-style ID using cryptographically secure randomness.
     */
    public static function generateId(): string
    {
        // 36^12 = 4.7 trillion combinations, collision-safe for billions of products
        $alphabet = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $id = '';
        for ($i = 0; $i < 12; $i++) {
            $id .= $alphabet[random_int(0, 35)];
        }

        return $id;
    }

    // ───── Relationships ─────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'product_id');
    }

    public function paidOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'product_id')->where('payment_status', 'paid');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class, 'product_id')->orderBy('position');
    }

    // ───── Behavior helpers (not accessors — state predicates) ─────

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
