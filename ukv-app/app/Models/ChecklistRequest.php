<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ChecklistRequest extends Model
{
    protected $fillable = [
        'token',
        'destination_id',
        'inputs',
        'items',
        'email',
        'phone',
        'channels',
        'marketing_consent',
        'ip',
    ];

    protected function casts(): array
    {
        return [
            'inputs' => 'array',
            'items' => 'array',
            'channels' => 'array',
            'marketing_consent' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ChecklistRequest $request): void {
            if (empty($request->token)) {
                $request->token = (string) Str::uuid();
            }
        });
    }

    /** Public, shareable route key: /checklist/{token}. */
    public function getRouteKeyName(): string
    {
        return 'token';
    }

    // --- Relationships ---

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
