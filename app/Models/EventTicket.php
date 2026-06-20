<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTicket extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'buyer_email', 'attendee_name',
        'ticket_code', 'is_checked_in', 'checked_in_at', 'checked_in_by',
    ];

    protected function casts(): array
    {
        return [
            'is_checked_in' => 'boolean',
            'checked_in_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public static function generateCode(): string
    {
        // Short human-friendly code like "TKT-A1B2C3"
        return 'TKT-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }
}