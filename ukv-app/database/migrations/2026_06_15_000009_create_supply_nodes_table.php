<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('node_key', 80)->unique();                     // from: node `id` (type-slug, deterministic)
            $table->string('type', 20)                                    // from: `type` (UKV_SUPPLY_TYPES)
                ->comment('enum: centre|courier|paypoint|embassy');
            $table->string('name', 160);                                  // from: `name`
            $table->string('contact', 255)->nullable();                   // from: `contact` (URL/phone)
            $table->string('sla', 160)->nullable();                       // from: `sla` (free text)
            $table->text('notes')->nullable();                           // from: `notes`
            $table->boolean('is_global')->default(false);                 // from: `destinations` empty -> global
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_nodes');
    }
};
