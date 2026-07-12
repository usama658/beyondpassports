<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageRevision extends Model
{
    protected $fillable = ['page_id', 'title', 'blocks', 'seo_title', 'seo_description', 'og_image', 'editor_id'];

    protected function casts(): array
    {
        return ['blocks' => 'array'];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
