<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_change_proposals', function (Blueprint $table) {
            // AI change-detection inbox (Module C, #138). The weekly DataChangeService fetches each
            // destination's public official source pages and asks the model to flag differences vs
            // the stored facts. Each flagged difference becomes an `open` proposal here. NOTHING is
            // ever auto-applied — a human Accepts (writes proposed_value to the destination field +
            // bumps facts_checked_at) or Dismisses from the Filament inbox.
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->string('field');                            // destination column the model flagged
            $table->text('current_value')->nullable();          // stored value at detection time
            $table->text('proposed_value')->nullable();         // value the model read from the source
            $table->string('source_url')->nullable();           // page the proposal was derived from
            $table->text('model_summary')->nullable();          // model's evidence / rationale
            $table->string('status')->default('open');          // open|accepted|dismissed
            $table->string('resolved_by')->nullable();          // resolver name (E-E-A-T audit)
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'destination_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_change_proposals');
    }
};
