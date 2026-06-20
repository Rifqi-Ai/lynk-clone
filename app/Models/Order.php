<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id', // generated via Order::generateId() in creating hook
    'buyer_user_id', 'buyer_email', 'product_id', 'creator_user_id',
    'unit_price', 'quantity', 'subtotal', 'fee_pct', 'fee_amount', 'total', 'creator_payout',
    'voucher_code', 'voucher_discount', 'metadata',
    'expired_at',
    // payment_status, paid_at, duitku_* are deliberately NOT fillable — only PaymentCallbackController
    // (signature-verified) and Order state machine methods can mutate these. Prevents attackers
    // from POSTing payment_status=paid to mark their own orders as paid.
])]

/**
 * Sensitive fields hidden from default JSON serialization.
 * - duitku_response: payment gateway internal response (bank codes, raw API data)
 * - metadata: may contain internal flags (fraud_score, internal_notes, admin_data)
 * Use $model->makeVisible([...]) on the route that legitimately shows them.
 */
#[Hidden(['duitku_response', 'metadata'])]
class Order extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'fee_pct' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'creator_payout' => 'decimal:2',
            'voucher_discount' => 'decimal:2',
            'quantity' => 'integer',
            'duitku_response' => 'array',
            'metadata' => 'array',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->id)) {
                $order->id = static::generateId();
            }
        });
    }

    /**
     * Generate an order ID like ORD-20260620-ABC123.
     * Uses cryptographically secure randomness.
     */
    public static function generateId(): string
    {
        $prefix = 'ORD-'.now()->format('Ymd').'-';
        $alphabet = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // no I, O for clarity
        $random = '';
        for ($i = 0; $i < 8; $i++) {
            $random .= $alphabet[random_int(0, 33)];
        }

        return $prefix.$random;
    }

    // ───── Relationships ─────

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // ───── Status helpers ─────

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function isExpired(): bool
    {
        return $this->payment_status === 'expired' ||
               ($this->payment_status === 'pending' && $this->expired_at && $this->expired_at->isPast());
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp '.number_format($this->total, 0, ',', '.');
    }
}
