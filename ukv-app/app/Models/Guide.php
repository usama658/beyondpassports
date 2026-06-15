<?php

namespace App\Models;

use App\Enums\GuideType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guide extends Model
{
    protected $fillable = [
        'destination_id',
        'guide_type',
        'slug',
        'title',
        'excerpt',
        'quick_answer',
        'body',
        'meta_title',
        'meta_description',
        'faq',
        'status',
        'published_at',
        'reviewed_by',
        'reviewed_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'faq' => 'array',
            'published_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'guide_type' => GuideType::class,
            'sort_order' => 'integer',
        ];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    /**
     * Scope to live guides only (published status with a publish timestamp).
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    /**
     * Scope to a single destination's guides.
     */
    public function scopeForDestination(Builder $query, Destination $destination): Builder
    {
        return $query->where('destination_id', $destination->id);
    }
}
