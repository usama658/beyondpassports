<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One-shot nurture: stamp when the "you built a checklist — ready to apply?" follow-up went out, so
 * the scheduled command never emails the same lead twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_requests', function (Blueprint $table) {
            $table->timestamp('nurture_sent_at')->nullable()->after('consent_at');
        });
    }

    public function down(): void
    {
        Schema::table('checklist_requests', function (Blueprint $table) {
            $table->dropColumn('nurture_sent_at');
        });
    }
};
