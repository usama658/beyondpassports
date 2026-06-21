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
        'tier',
        'amount_gbp',
        'currency',
        'stripe_session_id',
        'immediate_delivery_consent',
        'consent_at',
    ];

    protected function casts(): array
    {
        return [
            'inputs' => 'array',
            'items' => 'array',
            'channels' => 'array',
            'marketing_consent' => 'boolean',
            'amount_gbp' => 'decimal:2',
            'paid_at' => 'datetime',
            'immediate_delivery_consent' => 'boolean',
            'consent_at' => 'datetime',
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

    /** Money received? Written only by the Stripe webhook path. */
    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    /**
     * Redacted projection for the UNPAID render. Returns the item count, the distinct
     * category names, and exactly ONE teaser item (its real label). Every other real
     * label is withheld so unpaid HTML never leaks the full list.
     *
     * @return array{count:int, categories:list<string>, teaser:?array{label:string,note:?string,category:string}}
     */
    public function peek(): array
    {
        $items = is_array($this->items) ? $this->items : [];

        $categories = array_values(array_unique(array_filter(
            array_map(static fn ($i) => $i['category'] ?? null, $items)
        )));

        $first = $items[0] ?? null;
        $teaser = $first === null ? null : [
            'label' => (string) ($first['label'] ?? ''),
            'note' => $first['note'] ?? null,
            'category' => (string) ($first['category'] ?? ''),
        ];

        return [
            'count' => count($items),
            'categories' => $categories,
            'teaser' => $teaser,
        ];
    }
}
