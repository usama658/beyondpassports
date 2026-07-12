<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Media library: uploaded images stored once and reusable across many image blocks / pages. Additive
 * and inert until a page's image block references a media id — the CMS stays fully reversible.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('disk')->default('public');
            $table->string('path');                     // relative path on the disk, e.g. cms/hero.jpg
            $table->string('name')->nullable();          // human label / original filename
            $table->string('alt')->nullable();           // default alt text, reused wherever placed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
