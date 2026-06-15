<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->nullable()                // null = evergreen guide
                ->constrained()->nullOnDelete();
            $table->string('guide_type')->nullable()                       // null for evergreen; GuideType-backed otherwise
                ->comment('GuideType enum value; null = evergreen');
            $table->string('slug');                                        // public URL key
            $table->string('title');
            $table->text('excerpt');                                       // card + meta fallback
            $table->text('quick_answer')->nullable();                      // featured-snippet answer block
            $table->longText('body')->nullable();                          // rendered HTML
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->json('faq')->nullable();                               // [{q, a}, ...] -> FAQPage schema
            $table->string('status')->default('draft');                    // draft|published
            $table->timestamp('published_at')->nullable();
            $table->string('reviewed_by')->nullable();                     // E-E-A-T byline name
            $table->timestamp('reviewed_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['destination_id', 'guide_type']);              // one country guide per type
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guides');
    }
};
