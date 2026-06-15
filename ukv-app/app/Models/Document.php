<?php

namespace App\Models;

use App\Enums\DocumentMime;
use App\Enums\DocumentUploadedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'order_id',
        'disk',
        'path',
        'original_name',
        'mime',
        'size_bytes',
        'uploaded_by',
        'purged_at',
    ];

    protected function casts(): array
    {
        return [
            'mime' => DocumentMime::class,
            'uploaded_by' => DocumentUploadedBy::class,
            'size_bytes' => 'integer',
            'purged_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
