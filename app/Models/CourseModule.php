<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'title', 'description', 'video_url',
        'duration_minutes', 'position', 'is_free_preview', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'position' => 'integer',
            'is_free_preview' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Extract YouTube video ID from URL.
     */
    public function getYoutubeIdAttribute(): ?string
    {
        if (! $this->video_url) {
            return null;
        }
        preg_match('/(?:youtu\.be\/|v=|\/embed\/)([\w-]{11})/', $this->video_url, $m);

        return $m[1] ?? null;
    }

    public function getVimeoIdAttribute(): ?string
    {
        if (! $this->video_url) {
            return null;
        }
        preg_match('/vimeo\.com\/(\d+)/', $this->video_url, $m);

        return $m[1] ?? null;
    }
}
