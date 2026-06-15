<?php

namespace App\Models;

use App\Enums\EventChannel;
use App\Enums\EventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEvent extends Model
{
    protected $fillable = [
        'order_id',
        'occurred_at',
        'agent',
        'channel',
        'type',
        'text',
        'meta',
        'email_event',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'channel' => EventChannel::class,
            'type' => EventType::class,
            'meta' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
