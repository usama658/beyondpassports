<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional grouping label for destinations (e.g. Schengen regions: Western Europe,
 * Southern Europe, Northern Europe, Central & Eastern Europe). Null for ungrouped
 * destinations. Lets the destinations index / a hub page group countries by region.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->string('region')->nullable()->after('visa_type')->index();
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn('region');
        });
    }
};
