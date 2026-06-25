<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centre_availability', function (Blueprint $table) {
            // Published, marketing-facing availability snapshot for a bookable centre. One row per
            // supply node. Drives the public /destinations appointment board. Kept SEPARATE from
            // centre_slots (which is real held inventory against orders). Ops maintain this manually
            // (bulk paste / per-row edit); a snapshot decays at read time once expires_at passes, so
            // stale data reverts to "ask" automatically and the board never shows fabricated dates.
            $table->id();
            $table->foreignId('supply_node_id')                           // the centre this snapshot is for
                ->unique()->constrained('supply_nodes')->cascadeOnDelete();
            $table->date('next_available_on')->nullable();                // soonest known date; null = none known
            $table->string('band', 10)->nullable()                        // good|limited (null when no date)
                ->comment('enum: good|limited');
            $table->string('source', 10)->default('manual')               // manual|derived
                ->comment('enum: manual|derived');
            $table->string('note')->nullable();                           // internal note
            $table->dateTime('confirmed_at');                             // when last set/refreshed ("as of")
            $table->dateTime('expires_at');                               // confirmed_at + freshness window
            $table->timestamps();

            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centre_availability');
    }
};
