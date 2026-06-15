<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            // Freshness system (Module B). Facts are factual data (fees/times/docs/validity)
            // that must be kept current for a YMYL visa product. `facts_checked_at` is bumped
            // whenever a human verifies the country's data against the official source (the
            // Filament data-review action / the change-proposal Accept action); a daily command
            // flags destinations whose review is overdue (older than review_interval_days).
            $table->timestamp('facts_checked_at')->nullable()->after('required_docs'); // last human verification
            $table->integer('review_interval_days')->default(90)->after('facts_checked_at'); // review cadence
            $table->json('sources')->nullable()->after('review_interval_days'); // [{label, url}] official citations
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn(['facts_checked_at', 'review_interval_days', 'sources']);
        });
    }
};
