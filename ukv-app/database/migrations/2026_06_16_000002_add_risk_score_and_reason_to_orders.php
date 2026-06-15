<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Advisory fraud/risk scoring (Phase-2 #128). `risk_flag` already exists on the
            // orders table (entry migration) — these add the *why* + *how much* so an agent
            // can triage a flagged order. FraudService::assess() is the sole writer; the
            // signal is advisory only and never blocks a customer.
            $table->unsignedSmallInteger('risk_score')->default(0)->after('risk_flag'); // sum of heuristic weights
            $table->json('risk_reason')->nullable()->after('risk_score');               // list<string> reason codes/labels
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['risk_score', 'risk_reason']);
        });
    }
};
