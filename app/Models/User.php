<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'email', 'password',
    'username', 'phone', 'avatar_path', 'cover_path', 'title', 'bio',
    'social_links', 'appearance', 'plan_tier', 'transaction_fee_pct',
    'balance', 'total_earnings', 'google_id', 'custom_domain',
    'verified', 'show_branding',
])]
#[Hidden(['password', 'remember_token', 'google_id'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'social_links' => 'array',
            'appearance' => 'array',
            'balance' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'transaction_fee_pct' => 'decimal:2',
            'verified' => 'boolean',
            'show_branding' => 'boolean',
        ];
    }

    // ───── Relationships ─────

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'user_id');
    }

    public function publishedProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'user_id')->where('status', 'published');
    }

    public function ordersAsCreator(): HasMany
    {
        return $this->hasMany(Order::class, 'creator_user_id');
    }

    public function ordersAsBuyer(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_user_id');
    }

    // ───── Helpers ─────

    /**
     * Get the public profile URL.
     */
    public function getProfileUrlAttribute(): string
    {
        return url('/'.$this->username);
    }

    /**
     * Get the avatar URL with fallback.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_path && \Storage::disk('public')->exists($this->avatar_path)) {
            return \Storage::disk('public')->url($this->avatar_path);
        }
        // Default avatar with initials
        $name = $this->name ?: $this->username ?: '?';
        $initials = strtoupper(mb_substr($name, 0, 1));

        return 'https://ui-avatars.com/api/?name='.urlencode($initials).'&background=FF5722&color=fff&size=200&bold=true';
    }

    /**
     * Get display title (subtitle) with fallback.
     */
    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?: '@'.$this->username;
    }

    /**
     * Get the display name (real name or @username fallback).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: '@'.$this->username;
    }

    /**
     * Check if user is verified (creator badge).
     */
    public function getIsVerifiedAttribute(): bool
    {
        return (bool) $this->verified;
    }

    /**
     * Get follower count. MVP: no followers table yet, always 0.
     * Real impl when social features added.
     */
    public function getFollowersCountAttribute(): int
    {
        return 0;
    }

    /**
     * Get total count of paid sales (orders as creator with payment_status = 'paid').
     */
    public function getTotalSalesCountAttribute(): int
    {
        return $this->ordersAsCreator()
            ->where('payment_status', 'paid')
            ->count();
    }

    /**
     * Check if user can publish products (always true for MVP; quota checks come later).
     */
    public function canPublishMoreProducts(): bool
    {
        return true; // MVP: unlimited
    }

    /**
     * Get the brand color (default to platform green).
     */
    public function getBrandColorAttribute(): string
    {
        return $this->appearance['primary_color'] ?? '#2AB57D';
    }

    /**
     * Route binding: lookup by username OR id.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // For routes using {user}, lookup by username first (URL slug pattern)
        $field = $field ?: 'username';

        return $this->where($field, $value)->firstOrFail();
    }
}
