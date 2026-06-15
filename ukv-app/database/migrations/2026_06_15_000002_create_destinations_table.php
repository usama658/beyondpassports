<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);                                   // from: post title ("Egypt")
            $table->string('slug', 140)->unique();                         // from: post slug — join key
            $table->string('visa_type', 60)->nullable()                    // from: visa_type (free text)
                ->comment('free text today; candidate enum evisa/eta/visa-free/sticker');
            $table->boolean('required_for_uk')->default(false);            // from: required_for_uk
            $table->unsignedSmallInteger('max_stay_days')->nullable();     // from: max_stay_days
            $table->decimal('govt_fee_gbp', 10, 2)->nullable();            // from: govt_fee_gbp
            $table->decimal('tier_standard_gbp', 10, 2)->nullable();       // from: tier_standard_gbp
            $table->decimal('tier_express_gbp', 10, 2)->nullable();        // from: tier_express_gbp
            $table->decimal('tier_premium_gbp', 10, 2)->nullable();        // from: tier_premium_gbp
            $table->unsignedTinyInteger('passport_validity_months')->default(6); // from: passport_validity_months (seeded 6)
            $table->string('idp_permit_type', 40)->nullable();             // from: idp_permit_type (1926/1949/1968)
            $table->boolean('idp_required_photocard')->default(false);     // from: idp_required_photocard
            $table->boolean('idp_required_paper')->default(false);         // from: idp_required_paper
            $table->json('required_docs')->nullable();                     // from: required_docs (parsed list)
            $table->timestamps();

            $table->index('required_for_uk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
