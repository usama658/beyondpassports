<?php

namespace App\Models;

use App\Enums\EligibilityLane;
use App\Enums\OrderBlocker;
use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\OrderTier;
use App\Enums\ResidencyStatus;
use App\Enums\TripPurpose;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $fillable = [
        // identity / customer
        'order_ref', 'name', 'email', 'phone', 'passport_number', 'hubspot_deal_id',
        // destination + pricing
        'destination_id', 'destination_name', 'tier', 'service_fee', 'govt_fee', 'total', 'paid_at',
        // pipeline
        'status', 'status_last', 'blocker', 'priority', 'next_action', 'next_due',
        'travel_date', 'risk_flag', 'risk_score', 'risk_reason', 'value_note',
        // eligibility + intake
        'eligibility', 'eligibility_note', 'nationality', 'residence_country',
        'residency_status', 'residency_visa_expiry', 'trip_purpose', 'visa_entries',
        'applicant_name', 'guardian_name', 'dual_nationality', 'is_minor',
        'prior_refusal', 'insurance_required',
        // government submission
        'govt_ref', 'govt_fee_paid', 'govt_fee_paid_at',
        // passport
        'passport_expiry',
        // documents / QA
        'required_docs_count', 'qa_signed_off', 'doc_review', 'docs_purged',
        // ownership / SLA
        'owner_id', 'sla_escalated',
        // add-ons / logistics
        'group_id', 'premium_slot', 'premium_slot_fee', 'premium_slot_added_at', 'story_consent',
        // refund
        'refund_amount', 'refund_reason', 'refunded_at',
        // lifecycle
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            // enums
            'tier' => OrderTier::class,
            'status' => OrderStatus::class,
            'blocker' => OrderBlocker::class,
            'priority' => OrderPriority::class,
            'eligibility' => EligibilityLane::class,
            'residency_status' => ResidencyStatus::class,
            'trip_purpose' => TripPurpose::class,
            // money
            'service_fee' => 'decimal:2',
            'govt_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'premium_slot_fee' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'required_docs_count' => 'integer',
            'risk_score' => 'integer',
            // dates
            'next_due' => 'date',
            'travel_date' => 'date',
            'residency_visa_expiry' => 'date',
            'passport_expiry' => 'date',
            // datetimes
            'paid_at' => 'datetime',
            'govt_fee_paid_at' => 'datetime',
            'premium_slot_added_at' => 'datetime',
            'refunded_at' => 'datetime',
            'closed_at' => 'datetime',
            // booleans
            'risk_flag' => 'boolean',
            'is_minor' => 'boolean',
            'prior_refusal' => 'boolean',
            'insurance_required' => 'boolean',
            'govt_fee_paid' => 'boolean',
            'qa_signed_off' => 'boolean',
            'docs_purged' => 'boolean',
            'sla_escalated' => 'boolean',
            'premium_slot' => 'boolean',
            'story_consent' => 'boolean',
            // json
            'doc_review' => 'array',
            'risk_reason' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (empty($order->order_ref)) {
                $order->order_ref = static::generateReference($order->created_at);
            }
        });
    }

    /**
     * Generate a unique order reference in UKV-YYYY-NNNNNN format.
     * Year is derived from created_at (or now() at creation time) — never hardcoded.
     * Sequence is the next 6-digit number within that year, zero-padded.
     */
    public static function generateReference(?Carbon $when = null): string
    {
        $when = $when ?? Carbon::now();
        $year = $when->format('Y');

        // Highest existing sequence for this year, scoped by the ref prefix.
        $prefix = "UKV-{$year}-";
        $last = static::query()
            ->where('order_ref', 'like', $prefix.'%')
            ->lockForUpdate()
            ->selectRaw('MAX(CAST(SUBSTRING(order_ref, ?) AS UNSIGNED)) AS seq', [strlen($prefix) + 1])
            ->value('seq');

        $next = ((int) $last) + 1;

        return sprintf('%s%06d', $prefix, $next);
    }

    // --- Relationships ---

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class);
    }

    public function barriers(): HasMany
    {
        return $this->hasMany(Barrier::class);
    }

    public function clientUpdates(): HasMany
    {
        return $this->hasMany(ClientUpdate::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function rejections(): HasMany
    {
        return $this->hasMany(Rejection::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}
