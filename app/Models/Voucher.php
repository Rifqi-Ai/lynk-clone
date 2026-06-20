<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'creator_user_id', 'code', 'type', 'value', 'min_purchase', 'max_discount',
    'usage_limit', 'usage_count', 'starts_at', 'expires_at', 'is_active',
])]
class Voucher extends Model
{
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_purchase' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    /**
     * Check if voucher is currently valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) return false;
        return true;
    }

    /**
     * Calculate discount amount for a given subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if (!$this->isValid()) return 0;
        if ($subtotal < $this->min_purchase) return 0;

        $discount = $this->type === 'percent'
            ? $subtotal * ($this->value / 100)
            : $this->value;

        if ($this->max_discount && $discount > $this->max_discount) {
            $discount = $this->max_discount;
        }

        // Can't exceed subtotal
        return min($discount, $subtotal);
    }
}