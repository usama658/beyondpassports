<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A model-flagged difference between a destination's stored facts and its official source page
 * (Module C, #138). Created `open` by DataChangeService; resolved by a human in the Filament inbox
 * (Accept writes proposed_value to the destination field + bumps facts_checked_at; Dismiss closes
 * it). NEVER auto-applied.
 */
class DataChangeProposal extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_DISMISSED = 'dismissed';

    protected $fillable = [
        'destination_id',
        'field',
        'current_value',
        'proposed_value',
        'source_url',
        'model_summary',
        'status',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
