<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Published availability snapshot for one bookable centre (supply node).
 *
 * Drives the public /destinations appointment board via AvailabilityService. This is the
 * MARKETING surface, kept separate from CentreSlot (real held inventory against orders).
 *
 * Honesty by construction: status() decays at read time. Once expires_at passes, or when no
 * date is known, the centre reports "ask" ("we check live for you") regardless of stored band,
 * so a stale snapshot can never show a fabricated date. Ops keep it fresh; the board self-corrects.
 */
class CentreAvailability extends Model
{
    protected $table = 'centre_availability';

    /** Default freshness window in days (confirmed_at -> expires_at) when ops set a snapshot. */
    public const FRESHNESS_DAYS = 7;

    public const BANDS = ['good', 'limited'];

    public const SOURCES = ['manual', 'derived'];

    protected $fillable = [
        'supply_node_id',
        'next_available_on',
        'band',
        'source',
        'note',
        'confirmed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'next_available_on' => 'date',
            'confirmed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function supplyNode(): BelongsTo
    {
        return $this->belongsTo(SupplyNode::class);
    }

    /** True once the freshness window has lapsed (stale -> treated as "ask"). */
    public function isStale(): bool
    {
        return $this->expires_at === null || $this->expires_at->isPast();
    }

    /** True when the snapshot will lapse within $days (drives ops "refresh me" flags). */
    public function isExpiring(int $days = 2): bool
    {
        return ! $this->isStale()
            && $this->expires_at->lessThanOrEqualTo(Carbon::now()->addDays($days));
    }

    /**
     * Public status, the single source of truth: ok | lim | ask.
     * Expired or dateless -> ask. Otherwise mapped from band.
     */
    public function status(): string
    {
        if ($this->isStale() || $this->next_available_on === null) {
            return 'ask';
        }

        return match ($this->band) {
            'good' => 'ok',
            'limited' => 'lim',
            default => 'ask',
        };
    }
}
