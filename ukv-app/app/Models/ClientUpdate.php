<?php

namespace App\Models;

use App\Enums\ClientUpdateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientUpdate extends Model
{
    protected $fillable = [
        'barrier_id',
        'order_id',
        'subject',
        'body',
        'channel',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => ClientUpdateChannel::class,
            'sent_at' => 'datetime',
        ];
    }

    public function barrier(): BelongsTo
    {
        return $this->belongsTo(Barrier::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
