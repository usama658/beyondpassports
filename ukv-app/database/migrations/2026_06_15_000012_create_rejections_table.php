<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rejections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')                                 // from: order
                ->constrained('orders')->cascadeOnDelete();
            $table->string('reason', 30)                                  // from: ukv_rejection_reason (UKV_REJECTION_REASONS)
                ->comment('enum: doc_quality|eligibility|passport_validity|portal_error|customer_withdrew|other');
            $table->text('note')->nullable();                            // from: ukv_rejection_note
            $table->timestamp('recorded_at')->useCurrent();              // from: save time / journey note
            $table->timestamps();

            $table->index(['order_id', 'reason']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rejections');
    }
};
