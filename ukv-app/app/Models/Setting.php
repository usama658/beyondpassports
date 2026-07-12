<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Curated site settings (key/value). Editor-editable safe globals only (announcement bar for now).
 * Reads are cached per key so a sitewide lookup in the layout costs nothing after the first hit.
 */
class Setting extends Model
{
    protected $table = 'site_settings';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = Cache::rememberForever('setting:'.$key, fn () => static::query()->where('key', $key)->value('value'));

        return $value ?? $default;
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('setting:'.$key);
    }
}
