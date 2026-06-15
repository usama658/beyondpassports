<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consumer Contracts (Information, Cancellation and Additional Charges) Regulations 2013, reg 36:
 * to begin a service within the 14-day cancellation window we must obtain the consumer's express
 * request to do so. This column is the durable, timestamped record of that request — evidence that
 * once the service is fully performed the consumer has lost the right to cancel under the Regs.
 * NULL = no immediate-performance request captured (we must not begin work before the 14 days).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('immediate_performance_consent_at')->nullable()->after('refunded_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('immediate_performance_consent_at');
        });
    }
};
