<?php

namespace App\Models;

use App\Enums\DiscountContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discount extends Model
{
    protected $fillable = [
        'code',
        'amount',
        'context',
        'email',
        'used',
        'order_ref',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'context' => DiscountContext::class,
            'used' => 'boolean',
        ];
    }

    /**
     * Loose link to the redeemed-on order via the snapshot ref string.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_ref', 'order_ref');
    }
}
