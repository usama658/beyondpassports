<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // New table in the port (no direct WP meta) — captures the customer review the
        // review_request email solicits. See 01-schema.md §11 ambiguity note.
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()                     // from: order
                ->constrained('orders')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();            // from: (new) 1-5
            $table->text('comment')->nullable();                         // from: (new) raw customer text
            $table->boolean('consented')->default(false);                 // from: ukv_story_consent (mirrors order)
            $table->unsignedBigInteger('testimonial_draft_id')->nullable(); // from: drafted post id
            $table->string('source', 20)->default('review_request')       // from: (new)
                ->comment('enum: review_request|manual|import');
            $table->timestamps();                                        // created_at from: (new)

            $table->index('order_id');
            $table->index('rating');
            $table->index('consented');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
