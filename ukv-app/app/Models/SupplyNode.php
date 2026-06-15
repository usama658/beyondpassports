<?php

namespace App\Models;

use App\Enums\SupplyNodeType;
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
    ];

    protected function casts(): array
    {
        return [
            'type' => SupplyNodeType::class,
            'is_global' => 'boolean',
        ];
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class);
    }
}
