<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')                                 // from: order
                ->constrained('orders')->cascadeOnDelete();
            $table->string('centre', 160)->nullable();                    // from: ukv_appointment_centre
            $table->string('reference', 120)->nullable();                 // from: ukv_appointment_ref (shared w/ govt-fields)
            $table->date('scheduled_at')->nullable();                     // from: ukv_appointment_at (date only)
            $table->string('status', 20)->default('not_required')         // from: ukv_appointment_status (UKV_APPOINTMENT_STATUSES)
                ->comment('enum: not_required|to_book|booked|attended|completed');
            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
