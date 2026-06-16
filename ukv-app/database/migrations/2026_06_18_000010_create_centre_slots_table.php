<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centre_slots', function (Blueprint $table) {
            // Held-slot inventory (Wave 2, B1). Each row is a bookable appointment slot at a
            // supply node (centre/paypoint). Slots move available -> held (temporary, expiring)
            // -> booked. A held slot is reserved against an order until hold_expires_at passes.
            $table->id();
            $table->foreignId('supply_node_id')                           // the centre this slot belongs to
                ->constrained('supply_nodes')->cascadeOnDelete();
            $table->dateTime('slot_at');                                  // the appointment date+time
            $table->string('status')->default('available')               // available|held|booked
                ->comment('enum: available|held|booked');
            $table->dateTime('hold_expires_at')->nullable();             // when a temporary hold lapses
            $table->foreignId('order_id')->nullable()                    // order the slot is held/booked for
                ->constrained('orders')->nullOnDelete();
            $table->timestamps();

            $table->index(['supply_node_id', 'status', 'slot_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centre_slots');
    }
};
