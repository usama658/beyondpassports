<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barrier_id')                               // from: barrier
                ->constrained('barriers')->cascadeOnDelete();
            $table->foreignId('order_id')                                 // from: affected order
                ->constrained('orders')->cascadeOnDelete();
            $table->string('subject', 255)->nullable();                   // from: ukv_draft_client_update() subject
            $table->text('body')->nullable();                            // from: draft body (redacted)
            $table->string('channel', 20)->default('email')               // from: send path
                ->comment('enum: email|whatsapp|call');
            $table->timestamp('sent_at')->nullable();                    // from: send time (null = drafted only)
            $table->timestamps();

            // replicates the ukv_update_sent "don't re-send for this barrier" guard
            $table->unique(['barrier_id', 'order_id']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_updates');
    }
};
