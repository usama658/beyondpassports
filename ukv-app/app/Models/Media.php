<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A reusable uploaded image. Upload once in the media library, reference by id from any image block;
 * change the file/alt in one place. Public URL is derived from the disk + path.
 */
class Media extends Model
{
    protected $table = 'media';

    protected $fillable = ['disk', 'path', 'name', 'alt'];

    /** Public URL for the stored file (absolute paths / URLs are returned unchanged). */
    public function url(): string
    {
        $path = (string) $this->path;
        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::disk($this->disk ?: 'public')->url($path);
    }

    /** Admin-facing label: the name, else the filename, else the path. */
    public function label(): string
    {
        return $this->name ?: basename((string) $this->path);
    }
}
