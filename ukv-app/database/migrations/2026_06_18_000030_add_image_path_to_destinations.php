<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hero/card image for a destination (relative public path or absolute URL). Drives the photo-led
 * destination cards. Nullable: falls back to the skyline illustration when unset.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table): void {
            $table->string('image_path')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table): void {
            $table->dropColumn('image_path');
        });
    }
};
