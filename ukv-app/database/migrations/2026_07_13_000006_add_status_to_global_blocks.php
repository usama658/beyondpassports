<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Draft state for reusable blocks: a section can be parked as a draft (built, not yet shown anywhere)
 * and published when ready. Defaults to published so existing reusable blocks keep rendering.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_blocks', function (Blueprint $table) {
            $table->string('status')->default('published')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('global_blocks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
