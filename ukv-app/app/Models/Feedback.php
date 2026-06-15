<?php

namespace App\Models;

use App\Enums\FeedbackSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    /**
     * Explicit table name — Eloquent would otherwise pluralize to "feedbacks".
     */
    protected $table = 'feedback';

    protected $fillable = [
        'order_id',
        'rating',
        'comment',
        'consented',
        'testimonial_draft_id',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'consented' => 'boolean',
            'testimonial_draft_id' => 'integer',
            'source' => FeedbackSource::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
