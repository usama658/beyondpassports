<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_requirements', function (Blueprint $table) {
            // One row = one conditional rule. Requirements are DATA (admin-editable),
            // not code. RequirementService evaluates active rules against an order
            // (or a destination + assumed context) to produce a tailored checklist.
            $table->id();
            $table->string('document_key', 80);                            // stable slug (passport, photo, employer_letter)
            $table->string('label', 190);                                  // customer-facing name
            $table->text('note')->nullable();                              // optional guidance shown under the item
            $table->string('category', 40)->default('core')                // display grouping
                ->comment('identity|funding|logistics|health|core');
            $table->json('conditions')->nullable()                         // match spec; null/{} => applies to all
                ->comment('AND across keys, OR within an array');
            $table->boolean('mandatory')->default(true);                   // required vs recommended
            $table->boolean('active')->default(true);                      // soft on/off without deleting the rule
            $table->integer('sort_order')->default(0);                     // display order within the checklist
            $table->timestamps();

            $table->index('active');
            $table->index('document_key');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_requirements');
    }
};
