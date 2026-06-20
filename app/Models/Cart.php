<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'id', 'creator_user_id', 'buyer_user_id', 'buyer_email',
    'voucher_id', 'voucher_discount', 'expires_at',
])]
class Cart extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'voucher_discount' => 'decimal:2',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Cart $cart) {
            if (empty($cart->id)) {
                $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $random = '';
                for ($i = 0; $i < 8; $i++) {
                    $random .= $alphabet[random_int(0, 35)];
                }
                $cart->id = 'CART-'.$random;
            }
            if (empty($cart->expires_at)) {
                $cart->expires_at = now()->addDays(7);
            }
        });
    }

    // ───── Relationships ─────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    // ───── Helpers ─────

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(fn ($i) => $i->unit_price * $i->quantity);
    }

    public function getTotalAttribute(): float
    {
        return max(0, $this->subtotal - $this->voucher_discount);
    }

    public function getItemCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
