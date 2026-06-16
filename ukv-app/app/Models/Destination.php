<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Destination extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image_path',
        'visa_type',
        'required_for_uk',
        'max_stay_days',
        'govt_fee_gbp',
        'tier_standard_gbp',
        'tier_express_gbp',
        'tier_premium_gbp',
        'passport_validity_months',
        'idp_permit_type',
        'idp_required_photocard',
        'idp_required_paper',
        'required_docs',
        'facts_checked_at',
        'review_interval_days',
        'sources',
        'processing_days',
    ];

    protected function casts(): array
    {
        return [
            'required_for_uk' => 'boolean',
            'max_stay_days' => 'integer',
            'govt_fee_gbp' => 'decimal:2',
            'tier_standard_gbp' => 'decimal:2',
            'tier_express_gbp' => 'decimal:2',
            'tier_premium_gbp' => 'decimal:2',
            'passport_validity_months' => 'integer',
            'idp_required_photocard' => 'boolean',
            'idp_required_paper' => 'boolean',
            'required_docs' => 'array',
            'facts_checked_at' => 'datetime',
            'review_interval_days' => 'integer',
            'sources' => 'array',
            'processing_days' => 'integer',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function barriers(): HasMany
    {
        return $this->hasMany(Barrier::class);
    }

    public function guides(): HasMany
    {
        return $this->hasMany(Guide::class);
    }

    public function supplyNodes(): BelongsToMany
    {
        return $this->belongsToMany(SupplyNode::class);
    }

    /**
     * Destinations whose facts review is overdue (Module B): never reviewed, or last reviewed more
     * than `review_interval_days` ago. Computed in PHP (per-row cadence) over a cheap candidate set
     * — `null`/old timestamps are pulled, then filtered by each row's own interval.
     */
    public function scopeOverdueForReview(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where(function (Builder $q) use ($now): void {
            $q->whereNull('facts_checked_at')
                ->orWhereRaw('DATE_ADD(facts_checked_at, INTERVAL review_interval_days DAY) <= ?', [$now]);
        });
    }
}
