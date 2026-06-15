<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_requests', function (Blueprint $table) {
            // One row = one public document-checklist request. The computed checklist is
            // SNAPSHOTTED into `items` at creation so the saved link / PDF / email stay
            // stable even if the underlying DocumentRequirement rules change later.
            $table->id();
            $table->uuid('token')->unique();                               // public, shareable handle (route key)
            $table->foreignId('destination_id')->nullable()               // chosen destination (name->slug->FK)
                ->constrained('destinations')->nullOnDelete();
            $table->json('inputs');                                        // wizard answers (trip_purpose, is_minor, ...)
            $table->json('items');                                         // computed checklist snapshot (engine shape)
            $table->string('email', 190)->nullable();                     // transactional delivery target (opt-in)
            $table->string('phone', 40)->nullable();                      // WhatsApp delivery target (opt-in)
            $table->json('channels')->nullable();                         // requested delivery channels
            $table->boolean('marketing_consent')->default(false);         // nurture/marketing only; separate from transactional
            $table->string('ip', 45)->nullable();                         // capture origin (IPv4/IPv6) for abuse + GDPR record
            $table->timestamps();
            // token is unique() above => already indexed (public route key lookups).
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_requests');
    }
};
