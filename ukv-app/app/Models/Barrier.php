<?php

namespace App\Models;

use App\Enums\BarrierDetectedBy;
use App\Enums\BarrierNature;
use App\Enums\BarrierScope;
use App\Enums\BarrierStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barrier extends Model
{
    protected $fillable = [
        'title',
        'nature',
        'scope',
        'destination_id',
        'destination_slug',
        'order_id',
        'order_ref',
        'guidance',
        'status',
        'detected_by',
        'rule_key',
    ];

    protected function casts(): array
    {
        return [
            'nature' => BarrierNature::class,
            'scope' => BarrierScope::class,
            'status' => BarrierStatus::class,
            'detected_by' => BarrierDetectedBy::class,
        ];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function clientUpdates(): HasMany
    {
        return $this->hasMany(ClientUpdate::class);
    }
}
