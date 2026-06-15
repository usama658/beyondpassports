<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DocumentRequirement extends Model
{
    protected $fillable = [
        'document_key',
        'label',
        'note',
        'category',
        'conditions',
        'mandatory',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'mandatory' => 'boolean',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Scope to active rules only (the only ones the engine evaluates).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
