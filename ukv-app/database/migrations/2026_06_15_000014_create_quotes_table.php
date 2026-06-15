<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')                                 // from: order
                ->constrained('orders')->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->default(0);                // from: ukv_quote_amount (bespoke price)
            $table->string('status', 20)->default('none')                 // from: ukv_quote_status (UKV_QUOTE_STATUSES)
                ->comment('enum: none|sent|paid');
            $table->string('payment_link', 255)->nullable();             // from: ukv_quote_link (Stripe Payment Link)
            $table->timestamp('sent_at')->nullable();                    // from: ukv_quote_sent_at (epoch)
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
