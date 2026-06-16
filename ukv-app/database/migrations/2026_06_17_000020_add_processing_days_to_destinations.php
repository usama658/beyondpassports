<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Typical end-to-end processing time (days) for this destination, used by the document-checklist
 * .ics reminder to compute the "start your application by" deadline (travel_date − processing_days
 * − buffer). Nullable: when unset the tool falls back to config('ukv.checklist.default_processing_days').
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table): void {
            $table->unsignedSmallInteger('processing_days')->nullable()->after('passport_validity_months');
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table): void {
            $table->dropColumn('processing_days');
        });
    }
};
