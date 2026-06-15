<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')                                  // from: parent order
                ->constrained('orders')->cascadeOnDelete();
            $table->timestamp('occurred_at');                              // from: ukv_journey[].date (UTC)
            $table->string('agent', 120)->default('system');              // from: ukv_journey[].agent
            $table->string('channel', 20)->default('internal')            // from: ukv_journey[].channel
                ->comment('enum: call|whatsapp|email|internal|upload');
            $table->string('type', 20)->default('note')                   // from: derived
                ->comment('enum: note|email|stage_change|system');
            $table->text('text');                                         // from: ukv_journey[].text
            $table->json('meta')->nullable();                            // from: (new) structured payload
            $table->string('email_event', 40)->nullable();               // from: ukv_email_sent[] event key (idempotency)
            $table->timestamps();

            $table->index(['order_id', 'occurred_at']);
            // replicates the once-only email send guard (ukv_email_sent)
            $table->unique(['order_id', 'email_event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_events');
    }
};
