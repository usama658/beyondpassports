<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reusable "global" blocks: a named block (type + data) stored once and referenced from many pages.
 * Edit it in one place and every page that references it updates. Additive + inert until a page adds
 * a `global` reference block, so the CMS stays fully reversible.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // admin-facing label, e.g. "Site-wide CTA"
            $table->string('type');            // inner BlockType key: hero | rich-text | image
            $table->json('data')->nullable();  // that block type's field values
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_blocks');
    }
};
