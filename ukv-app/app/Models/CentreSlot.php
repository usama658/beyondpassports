<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class CentreSlot extends Model
{
    protected $fillable = [
        'supply_node_id',
        'slot_at',
        'status',
        'hold_expires_at',
        'order_id',
    ];

    protected function casts(): array
    {
        return [
            'slot_at' => 'datetime',
            'hold_expires_at' => 'datetime',
        ];
    }

    public function supplyNode(): BelongsTo
    {
        return $this->belongsTo(SupplyNode::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** Bookable now: status available and the slot is still in the future. */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available')
            ->where('slot_at', '>=', Carbon::now());
    }

    /** Held slots whose temporary hold has lapsed and should be returned to the pool. */
    public function scopeHeldExpired(Builder $query): Builder
    {
        return $query->where('status', 'held')
            ->where('hold_expires_at', '<', Carbon::now());
    }
}
