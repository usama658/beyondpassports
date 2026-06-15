<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')                                 // from: _ukv_order_doc / post_parent
                ->constrained('orders')->cascadeOnDelete();
            $table->string('disk', 20)->default('private');               // from: (new) — files must be private
            $table->string('path', 255);                                  // from: attachment file path
            $table->string('original_name', 255)->nullable();             // from: sanitized upload name
            $table->string('mime', 30)                                    // from: wp_check_filetype_and_ext (UKV_DOC_UPLOAD_ALLOWED)
                ->comment('enum: image/jpeg|image/png|application/pdf|image/heic');
            $table->unsignedInteger('size_bytes')->nullable();            // from: upload size (max 10MB)
            $table->string('uploaded_by', 20)->default('customer')        // from: journey agent on upload
                ->comment('enum: customer|agent');
            $table->timestamp('purged_at')->nullable();                  // from: retention purge (row kept for audit)
            $table->timestamps();                                        // created_at from: upload time

            $table->index('order_id');
            $table->index('purged_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
