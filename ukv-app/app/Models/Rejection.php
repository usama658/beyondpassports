<?php

namespace App\Models;

use App\Enums\RejectionReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rejection extends Model
{
    protected $fillable = [
        'order_id',
        'reason',
        'note',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'reason' => RejectionReason::class,
            'recorded_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
