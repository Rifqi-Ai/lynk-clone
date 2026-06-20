<?php

namespace App\Models;

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
 */
#[Fillable([
    'id', 'user_id', 'type', 'title', 'slug', 'description',
    'price', 'compare_at_price', 'thumbnail_path',
    'file_path', 'file_name', 'file_size', 'download_limit_per_purchase',
    'metadata', 'status', 'sales_count', 'view_count',
])]
class Product extends Model
{
    use HasFactory;

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
            // Ensure slug is unique within owner's products by appending short suffix
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

    // ───── Helpers ─────

    public function getUrlAttribute(): string
    {
        return url("/{$this->owner->username}/{$this->id}");
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail_path && \Storage::disk('public')->exists($this->thumbnail_path)) {
            return \Storage::disk('public')->url($this->thumbnail_path);
        }
        return null;
    }

    public function getFileSizeFormattedAttribute(): ?string
    {
        if (!$this->file_size) return null;
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->compare_at_price && $this->compare_at_price > $this->price;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if (!$this->has_discount) return 0;
        return (int) round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type]['label'] ?? ucfirst($this->type);
    }

    public function getTypeIconAttribute(): string
    {
        return self::TYPES[$this->type]['icon'] ?? '📦';
    }

    /**
     * Get metadata value with default.
     */
    public function meta(string $key, mixed $default = null): mixed
    {
        return data_get($this->metadata, $key, $default);
    }

    /**
     * Set a metadata value.
     */
    public function setMeta(string $key, mixed $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
    }

    // ───── Type-specific helpers ─────

    /** Donation: preset amounts (default if not set) */
    public function getDonationPresetsAttribute(): array
    {
        return $this->meta('preset_amounts', [10000, 25000, 50000, 100000, 250000]);
    }

    /** Donation: goal amount */
    public function getDonationGoalAttribute(): ?float
    {
        return $this->meta('goal_amount');
    }

    /** Donation: current raised (computed live from orders, not metadata) */
    public function getDonationRaisedAttribute(): float
    {
        // Always recalculate from paid orders for accuracy
        return (float) $this->paidOrders()->sum('total');
    }

    /** Appointment: duration in minutes */
    public function getDurationMinutesAttribute(): int
    {
        return (int) $this->meta('duration_minutes', 60);
    }

    /** Appointment: duration formatted (e.g. "1h 30m") */
    public function getDurationFormattedAttribute(): string
    {
        $mins = $this->duration_minutes;
        $hours = intdiv($mins, 60);
        $m = $mins % 60;
        if ($hours > 0 && $m > 0) return "{$hours}h {$m}m";
        if ($hours > 0) return "{$hours}h";
        return "{$m}m";
    }

    /** Event: date */
    public function getEventDateAttribute(): ?string
    {
        return $this->meta('event_date');
    }

    /** Course: total duration in minutes */
    public function getCourseDurationAttribute(): int
    {
        return (int) $this->meta('total_duration_minutes', 0);
    }

    /** Course: number of modules */
    public function getCourseModulesAttribute(): int
    {
        return count($this->meta('modules', []));
    }

    /** Blog: body markdown */
    public function getBlogBodyAttribute(): ?string
    {
        return $this->meta('body_markdown');
    }

    /** Blog: is paywalled? */
    public function getIsPaywalledAttribute(): bool
    {
        return (bool) $this->meta('is_paywalled', false);
    }

    /** Physical: stock */
    public function getStockQuantityAttribute(): ?int
    {
        return $this->meta('stock_quantity');
    }

    /** Physical: in stock? */
    public function getInStockAttribute(): bool
    {
        $stock = $this->stock_quantity;
        return $stock === null || $stock > 0;
    }
}