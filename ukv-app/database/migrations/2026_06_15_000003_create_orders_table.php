<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // --- Identity / customer ---
            $table->string('order_ref', 32)->unique();                     // from: ukv_order_ref (UKV-YYYY-NNNNNN)
            $table->string('name', 160)->nullable();                       // from: ukv_name
            $table->string('email', 190)->nullable();                      // from: ukv_email
            $table->string('passport_number', 40)->nullable();             // from: ukv_passport_number (PII)
            $table->string('hubspot_deal_id', 40)->nullable();             // from: ukv_hubspot_deal

            // --- Destination + pricing ---
            $table->foreignId('destination_id')->nullable()                // from: ukv_destination (name->slug->FK)
                ->constrained('destinations')->nullOnDelete();
            $table->string('destination_name', 120)->nullable();           // from: ukv_destination (snapshot)
            $table->string('tier', 20)->nullable()                         // from: ukv_tier (label -> lowercase key)
                ->comment('enum: standard|express|premium');
            $table->decimal('service_fee', 10, 2)->nullable();             // from: ukv_service_fee (refundable)
            $table->decimal('govt_fee', 10, 2)->nullable();                // from: ukv_govt_fee (non-refundable)
            $table->decimal('total', 10, 2)->nullable();                   // from: ukv_total

            // --- Pipeline / journey-critical ---
            $table->string('status', 30)->default('paid')                  // from: ukv_status
                ->comment('enum: paid|awaiting_docs|doc_review|submitted|awaiting_decision|delivered|won|rejected|refunded');
            $table->string('status_last', 30)->nullable();                 // from: ukv_status_last (gate revert target)
            $table->string('blocker', 30)->default('none')                 // from: ukv_blocker (UKV_BLOCKERS)
                ->comment('enum: none|docs_missing|payment_pending|eligibility|customer_deciding');
            $table->string('priority', 20)->default('normal')              // from: ukv_priority
                ->comment('enum: normal|high|urgent');
            $table->string('next_action', 255)->nullable();                // from: ukv_next_action
            $table->date('next_due')->nullable();                          // from: ukv_next_due
            $table->date('travel_date')->nullable();                       // from: ukv_travel_date
            $table->boolean('risk_flag')->default(false);                  // from: ukv_risk_flag
            $table->string('value_note', 255)->nullable();                 // from: ukv_value_note

            // --- Eligibility lane + intake axes ---
            $table->string('eligibility', 20)->nullable()                  // from: ukv_eligibility
                ->comment('enum: standard|manual_review|cleared|referred');
            $table->text('eligibility_note')->nullable();                  // from: ukv_eligibility_note
            $table->string('nationality', 80)->nullable();                 // from: ukv_nationality
            $table->string('residence_country', 80)->nullable();           // from: ukv_residence_country
            $table->string('residency_status', 20)->nullable()             // from: ukv_residency_status (UKV_RESIDENCY_STATUS)
                ->comment('enum: citizen|permanent|visa_holder|other');
            $table->date('residency_visa_expiry')->nullable();             // from: ukv_residency_visa_expiry
            $table->string('trip_purpose', 20)->default('tourist')         // from: ukv_trip_purpose (UKV_TRIP_PURPOSE)
                ->comment('enum: tourist|business|transit|study|other');
            $table->string('visa_entries', 20)->nullable();                // from: ukv_visa_entries (free text; candidate enum)
            $table->string('applicant_name', 160)->nullable();             // from: ukv_applicant_name
            $table->string('guardian_name', 160)->nullable();              // from: ukv_guardian_name
            $table->string('dual_nationality', 80)->nullable();            // from: ukv_dual_nationality
            $table->boolean('is_minor')->default(false);                   // from: ukv_is_minor
            $table->boolean('prior_refusal')->default(false);              // from: ukv_prior_refusal
            $table->boolean('insurance_required')->default(false);         // from: ukv_insurance_required

            // --- Government submission ---
            $table->string('govt_ref', 80)->nullable();                    // from: ukv_govt_ref (GWF/IHS/submission no.)
            $table->boolean('govt_fee_paid')->default(false);              // from: ukv_govt_fee_paid
            $table->timestamp('govt_fee_paid_at')->nullable();             // from: ukv_govt_fee_paid_at (epoch)

            // --- Passport ---
            $table->date('passport_expiry')->nullable();                   // from: ukv_passport_expiry (Barrier Rule 2)

            // --- Documents / QA ---
            $table->unsignedTinyInteger('required_docs_count')->nullable(); // from: ukv_required_docs (mirror of dest count)
            $table->boolean('qa_signed_off')->default(false);              // from: ukv_qa_signed_off
            $table->json('doc_review')->nullable();                        // from: ukv_doc_review (AI advisory verdict)
            $table->boolean('docs_purged')->default(false);                // from: ukv_docs_purged

            // --- Ownership / SLA ---
            $table->foreignId('owner_id')->nullable()                      // from: ukv_owner (WP user id; 0 -> null)
                ->constrained('users')->nullOnDelete();
            $table->boolean('sla_escalated')->default(false);              // from: ukv_sla_escalated

            // --- Add-ons / logistics ---
            $table->string('group_id', 16)->nullable();                    // from: ukv_group_id (GRP-XXXXXXXX, self-group token, not FK)
            $table->boolean('premium_slot')->default(false);               // from: ukv_premium_slot
            $table->decimal('premium_slot_fee', 10, 2)->nullable();        // from: ukv_premium_slot_fee (not in total)
            $table->timestamp('premium_slot_added_at')->nullable();        // from: ukv_premium_slot_added_at (epoch)
            $table->boolean('story_consent')->default(false);              // from: ukv_story_consent

            // --- Refund ---
            $table->decimal('refund_amount', 10, 2)->nullable();           // from: ukv_refund_amount (service fee only)
            $table->string('refund_reason', 255)->nullable();              // from: ukv_refund_reason
            $table->timestamp('refunded_at')->nullable();                  // from: ukv_refunded_at (epoch)

            // --- Lifecycle / retention ---
            $table->timestamp('closed_at')->nullable();                    // from: ukv_closed_at (epoch); starts retention clock
            $table->timestamps();                                          // created_at from: ukv_created / post_date

            // --- Indexes ---
            $table->index('status');
            $table->index('owner_id');
            $table->index('group_id');
            $table->index('email');
            $table->index('eligibility');
            $table->index('closed_at');
            $table->index(['status', 'destination_id']); // rejection-rate / open-orders scans (also covers destination_id)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
