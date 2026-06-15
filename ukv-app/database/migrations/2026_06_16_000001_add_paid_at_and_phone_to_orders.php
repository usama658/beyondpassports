<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Distinct "money received" signal so the Stripe webhook idempotency no longer
            // overloads the `paid` pipeline ENTRY stage (orders are created at `paid`). (H-3)
            $table->timestamp('paid_at')->nullable()->after('total');       // Stripe checkout.session.completed timestamp

            // Callback number now persisted to a column instead of only the opening event meta. (M-4)
            $table->string('phone', 40)->nullable()->after('email');        // from: ApplyRequest phone
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['paid_at', 'phone']);
        });
    }
};
