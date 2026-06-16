<?php

namespace App\Models;

use App\Enums\SupplyNodeType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SupplyNode extends Model
{
    protected $fillable = [
        'node_key',
        'type',
        'name',
        'contact',
        'sla',
        'notes',
        'is_global',
        'address',
        'postcode',
        'lat',
        'lng',
        'we_book_here',
    ];

    protected function casts(): array
    {
        return [
            'type' => SupplyNodeType::class,
            'is_global' => 'boolean',
            'lat' => 'decimal:6',
            'lng' => 'decimal:6',
            'we_book_here' => 'boolean',
        ];
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class);
    }

    /** Only nodes with usable coordinates (geocoded) — the finder operates on these. */
    public function scopeLocated(Builder $query): Builder
    {
        return $query->whereNotNull('lat')->whereNotNull('lng');
    }
}
