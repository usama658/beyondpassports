<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Durable backstop for the dynamic weekly appointment-slots board (App\Support\SlotBoard).
 * One row per (slot_week, country) holding the running decrement count. The cache is the fast
 * read path; this table survives a cache flush and lets remaining() rebuild after one. Old weeks'
 * rows are harmless (never read once the slot_week key rolls) and can be pruned any time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slot_decrements', function (Blueprint $table) {
            $table->id();
            $table->string('slot_week', 10);   // Y-m-d of the week's Saturday
            $table->string('country', 100);
            $table->unsignedInteger('count')->default(0);
            $table->timestamps();
            $table->unique(['slot_week', 'country']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_decrements');
    }
};
