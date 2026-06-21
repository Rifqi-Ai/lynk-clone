<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Product attribute accessors.
 *
 * Phase 17 Task #7 (code-quality-audit-2026-06-21.md Tier 2 #5): extracts
 * 17 accessors from the Product model into a focused trait. The Product
 * model originally had 12+ return types (URLs, formatted values,
 * type-specific getters for donation/appointment/event/course/blog/physical).
 *
 * **Why a trait (not a separate class):**
 *  - Laravel's accessor system is bound via __get() on the model — they
 *    MUST live on the model class (or a trait). Service classes can't
 *    register accessors transparently.
 *  - Keeps the relationship $product->url discoverable in IDEs that
 *    follow the trait.
 *  - Trait can be unit-tested in isolation if needed (mock Model + apply trait).
 *
 * **Sections** (organized top-down so the most-frequently-used accessors
 * appear first):
 *  - URL/asset accessors (5) — url, checkout_url, thumbnail_url, file_url, file_size_formatted
 *  - Pricing accessors (2) — has_discount, discount_percentage
 *  - Type metadata (2) — type_label, type_icon
 *  - Metadata helpers (2) — meta(), setMeta()
 *  - Type-specific accessors (8) — donation_*, duration_*, event_date, course_*, blog_*, stock_*, in_stock, track_inventory, read_time
 *
 * @see app/Models/Product.php
 * @see tests/Feature/ProductAccessorsTest.php
 */
trait HasProductAccessors
{
    // ──────────────────────────────────────────────────────────────
    // URL/asset accessors
    // ──────────────────────────────────────────────────────────────

    public function getUrlAttribute(): string
    {
        return url("/{$this->owner->username}/{$this->id}");
    }

    /**
     * Checkout URL — routes to {username}/{productId}/checkout.
     */
    public function getCheckoutUrlAttribute(): string
    {
        return url("/{$this->owner->username}/{$this->id}/checkout");
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail_path && Storage::disk('public')->exists($this->thumbnail_path)) {
            return Storage::disk('public')->url($this->thumbnail_path);
        }

        return null;
    }

    /**
     * Digital: public URL to download file.
     */
    public function getFileUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return Storage::disk('public')->url($this->file_path);
    }

    public function getFileSizeFormattedAttribute(): ?string
    {
        if (! $this->file_size) {
            return null;
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2).' '.$units[$i];
    }

    // ──────────────────────────────────────────────────────────────
    // Pricing accessors
    // ──────────────────────────────────────────────────────────────

    public function getHasDiscountAttribute(): bool
    {
        return $this->compare_at_price && $this->compare_at_price > $this->price;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if (! $this->has_discount) {
            return 0;
        }

        return (int) round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100);
    }

    // ──────────────────────────────────────────────────────────────
    // Type metadata
    // ──────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type]['label'] ?? ucfirst($this->type);
    }

    public function getTypeIconAttribute(): string
    {
        return self::TYPES[$this->type]['icon'] ?? '📦';
    }

    // ──────────────────────────────────────────────────────────────
    // Metadata helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Get metadata value with default.
     */
    public function meta(string $key, mixed $default = null): mixed
    {
        return data_get($this->metadata, $key, $default);
    }

    /**
     * Set a metadata value (in-memory; call ->save() to persist).
     */
    public function setMeta(string $key, mixed $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
    }

    // ──────────────────────────────────────────────────────────────
    // Type-specific accessors
    // ──────────────────────────────────────────────────────────────

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

    /** Donation: current raised (computed live from paid orders, not metadata) */
    public function getDonationRaisedAttribute(): float
    {
        return (float) $this->paidOrders()->sum('total');
    }

    /** Appointment: duration in minutes (default 60) */
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
        if ($hours > 0 && $m > 0) {
            return "{$hours}h {$m}m";
        }
        if ($hours > 0) {
            return "{$hours}h";
        }

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

    /** Blog: estimated reading time in minutes (avg 200 words/min, min 1) */
    public function getReadTimeAttribute(): int
    {
        $body = $this->meta('body_markdown', '');
        $wordCount = str_word_count(strip_tags($body));

        return max(1, (int) ceil($wordCount / 200));
    }

    /** Physical: stock quantity */
    public function getStockQuantityAttribute(): ?int
    {
        return $this->meta('stock_quantity');
    }

    /** Physical: in stock? (null = unlimited inventory) */
    public function getInStockAttribute(): bool
    {
        $stock = $this->stock_quantity;

        return $stock === null || $stock > 0;
    }

    /** Physical: whether inventory is tracked (default true for safety) */
    public function getTrackInventoryAttribute(): bool
    {
        return (bool) $this->meta('track_inventory', true);
    }
}
