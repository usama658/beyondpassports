<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_requests', function (Blueprint $table): void {
            $table->string('tier')->nullable()->after('items');
            $table->decimal('amount_gbp', 8, 2)->nullable()->after('tier');
            $table->string('currency', 3)->default('gbp')->after('amount_gbp');
            $table->timestamp('paid_at')->nullable()->after('currency');
            $table->string('stripe_session_id')->nullable()->after('paid_at');
            $table->boolean('immediate_delivery_consent')->default(false)->after('stripe_session_id');
            $table->timestamp('consent_at')->nullable()->after('immediate_delivery_consent');
        });
    }

    public function down(): void
    {
        Schema::table('checklist_requests', function (Blueprint $table): void {
            $table->dropColumn([
                'tier', 'amount_gbp', 'currency', 'paid_at',
                'stripe_session_id', 'immediate_delivery_consent', 'consent_at',
            ]);
        });
    }
};
