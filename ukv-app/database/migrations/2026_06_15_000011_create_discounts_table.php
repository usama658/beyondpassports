<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 48)->unique();                         // from: array key (CONTEXT-XXXX)
            $table->decimal('amount', 10, 2)->default(0);                 // from: `amount` (£ off)
            $table->string('context', 20)->default('code')                // from: `context` (UKV_LOYALTY/REVIEW)
                ->comment('enum: loyal|review|code');
            $table->string('email', 190)->nullable();                     // from: `email`
            $table->boolean('used')->default(false);                      // from: `used` (single-use)
            $table->string('order_ref', 32)->nullable();                  // from: `order_ref` (redeemed-on order; loose link)
            $table->timestamps();

            $table->index('email');
            $table->index('used');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
