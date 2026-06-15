<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barriers', function (Blueprint $table) {
            $table->id();
            $table->string('title', 190)->nullable();                      // from: post title (derived)
            $table->string('nature', 20)->default('temporary')            // from: bare meta `nature` (UKV_BARRIER_NATURE)
                ->comment('enum: temporary|permanent');
            $table->string('scope', 20)->default('case')                  // from: bare meta `scope` (UKV_BARRIER_SCOPE)
                ->comment('enum: case|destination|all');
            $table->foreignId('destination_id')->nullable()               // from: bare meta `destination` (slug -> FK)
                ->constrained('destinations')->nullOnDelete();
            $table->string('destination_slug', 140)->nullable();          // from: bare meta `destination` (snapshot)
            $table->foreignId('order_id')->nullable()                     // from: bare meta `order_ref` -> order (case scope only)
                ->constrained('orders')->nullOnDelete();
            $table->string('order_ref', 32)->nullable();                  // from: bare meta `order_ref` (snapshot)
            $table->text('guidance')->nullable();                        // from: bare meta `guidance`
            $table->string('status', 20)->default('open')                 // from: bare meta `status`
                ->comment('enum: open|resolved');
            $table->string('detected_by', 20)->default('agent')           // from: bare meta `detected_by`
                ->comment('enum: agent|auto');
            $table->string('rule_key', 80)->nullable();                   // from: bare meta `rule_key` (idempotency key)
            $table->timestamps();

            $table->index('status');
            $table->index('scope');
            $table->index('destination_id');
            $table->index('order_id');
            // Open-barrier idempotency (ukv_barrier_create) is "unique on rule_key WHERE status='open'".
            // MySQL has no partial unique index; enforce that at app level (BarrierService).
            // Plain index supports the lookup.
            $table->index('rule_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barriers');
    }
};
