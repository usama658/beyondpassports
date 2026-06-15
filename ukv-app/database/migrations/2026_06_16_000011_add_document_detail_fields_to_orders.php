<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Post-pay document-detail step (Document Requirements Engine). The apply funnel stays
            // lean; these refine the FINAL personalised checklist the RequirementService evaluates.
            // employment_status / accommodation_type / funding_source are plain string enums-by-
            // convention (validated in the request, not cast). payer_is_applicant defaults true —
            // most travellers fund themselves; a sponsored/third-party payer flips it false.
            $table->string('employment_status')->nullable()->after('travel_date');   // employed|self_employed|student|retired|unemployed|other
            $table->string('accommodation_type')->nullable()->after('employment_status'); // hotel|host|own_property|other
            $table->string('funding_source')->nullable()->after('accommodation_type');    // self|sponsored
            $table->date('return_date')->nullable()->after('funding_source');             // drives computed stay_days
            $table->boolean('payer_is_applicant')->nullable()->default(true)->after('return_date');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'employment_status',
                'accommodation_type',
                'funding_source',
                'return_date',
                'payer_is_applicant',
            ]);
        });
    }
};
