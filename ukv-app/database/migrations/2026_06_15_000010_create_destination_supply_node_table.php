<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // from: supply node `destinations` slug list -> many-to-many (skipped when is_global)
        Schema::create('destination_supply_node', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')
                ->constrained('destinations')->cascadeOnDelete();
            $table->foreignId('supply_node_id')
                ->constrained('supply_nodes')->cascadeOnDelete();

            $table->unique(['destination_id', 'supply_node_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_supply_node');
    }
};
