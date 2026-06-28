<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ResidencyStatus;
use App\Enums\TripPurpose;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for the public apply-intake form (frontend/apply.html).
 *
 * The coded form posts a few values under different keys/labels than the domain enums use
 * (e.g. residency `settled` -> `permanent`, `visa` -> `visa_holder`; destination/purpose as
 * display values). prepareForValidation() normalises those BEFORE the rules run so the
 * enum-backed `in:` checks stay strict, and so OrderService receives clean keys.
 *
 * Captured fields mirror the form's intake plus the back-end axes the domain needs
 * (passport_expiry, visa_entries, dual_nationality, tier) which the public form may or may
 * not collect yet — they are validated as optional where the form omits them.
 */
class ApplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public intake endpoint
    }

    /**
     * Normalise the form's submitted keys/values into the canonical shape the rules + service
     * expect. Accepts both the apply.html field names and the canonical names.
     */
    protected function prepareForValidation(): void
    {
        // Map the apply.html residency labels onto the ResidencyStatus enum values.
        $residencyMap = [
            'settled' => ResidencyStatus::Permanent->value,
            'permanent' => ResidencyStatus::Permanent->value,
            'visa' => ResidencyStatus::VisaHolder->value,
            'visa_holder' => ResidencyStatus::VisaHolder->value,
            'citizen' => ResidencyStatus::Citizen->value,
            'other' => ResidencyStatus::Other->value,
        ];

        $rawStatus = $this->input('residency_status', $this->input('status'));
        $rawStatus = is_string($rawStatus) ? strtolower(trim($rawStatus)) : $rawStatus;

        $this->merge([
            // canonical <- (canonical | form alias)
            'applicant_name' => $this->input('applicant_name', $this->input('name')),
            'destination' => $this->input('destination', $this->input('dest')),
            'trip_purpose' => $this->normaliseLower($this->input('trip_purpose', $this->input('purpose'))),
            'travel_date' => $this->input('travel_date', $this->input('travel-date')),
            'nationality' => $this->input('nationality'),
            'residence_country' => $this->input('residence_country', $this->input('residence')),
            'residency_status' => is_string($rawStatus) ? ($residencyMap[$rawStatus] ?? $rawStatus) : $rawStatus,
            'is_minor' => $this->toBool($this->input('is_minor', $this->input('minor'))),
            'prior_refusal' => $this->toBool($this->input('prior_refusal', $this->input('refusal'))),
            'dual_nationality' => $this->input('dual_nationality'),
            'consent' => $this->toBool($this->input('consent')),
            'begin_now' => $this->toBool($this->input('begin_now')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // --- Trip ---
            'destination' => ['required', 'string', 'max:120'],
            'tier' => ['nullable', Rule::in(['standard', 'express', 'premium'])],
            'trip_purpose' => ['required', Rule::in($this->enumValues(TripPurpose::class))],
            'visa_entries' => ['nullable', 'string', 'max:20'],
            'travel_date' => ['required', 'date', 'after_or_equal:today'],

            // --- Traveller ---
            'applicant_name' => ['required', 'string', 'max:160'],
            'nationality' => ['required', 'string', 'max:80'],
            'residence_country' => ['required', 'string', 'max:80'],
            'residency_status' => ['required', Rule::in($this->enumValues(ResidencyStatus::class))],
            'is_minor' => ['required', 'boolean'],
            // Guardian name required when the traveller is a minor.
            'guardian_name' => ['nullable', 'required_if:is_minor,true', 'string', 'max:160'],
            'prior_refusal' => ['required', 'boolean'],
            'dual_nationality' => ['nullable', 'string', 'max:80'],

            // --- Passport ---
            'passport_expiry' => ['nullable', 'date', 'after:today'],

            // --- Contact ---
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            // UK postcode — used to book the nearest application centre for the destination
            // country. Required for UK residents (the only ones we book UK in-person centres for).
            'postcode' => ['nullable', 'required_if:residence_country,UK', 'string', 'max:12'],

            // --- Consent (must be ticked) ---
            'consent' => ['accepted'],
            // CCRs 2013 reg 36: express request to begin the service within the 14-day
            // cancellation window (the service is time-sensitive and begins on payment).
            'begin_now' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'consent.accepted' => 'You must agree to the terms to continue.',
            'begin_now.accepted' => 'Please confirm you want us to begin work on your application straight away.',
            'travel_date.after_or_equal' => 'Travel date cannot be in the past.',
            'guardian_name.required_if' => 'A guardian name is required for a minor traveller.',
            'passport_expiry.after' => 'Passport expiry must be a future date.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'applicant_name' => 'traveller name',
            'residence_country' => 'country of residence',
            'residency_status' => 'residency status',
            'trip_purpose' => 'trip purpose',
            'is_minor' => 'minor',
            'prior_refusal' => 'prior refusal',
        ];
    }

    // -----------------------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------------------

    /**
     * @param  class-string  $enum
     * @return list<string>
     */
    private function enumValues(string $enum): array
    {
        return array_map(static fn ($case) => $case->value, $enum::cases());
    }

    private function normaliseLower(mixed $value): mixed
    {
        return is_string($value) ? strtolower(trim($value)) : $value;
    }

    /**
     * Coerce the form's yes/no/checkbox values into a real bool the `boolean` rule accepts.
     */
    private function toBool(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value;
        }
        if ($value === null) {
            return null; // let `required` catch it where applicable
        }
        if (is_string($value)) {
            $v = strtolower(trim($value));
            if (in_array($v, ['yes', 'y', 'true', '1', 'on'], true)) {
                return true;
            }
            if (in_array($v, ['no', 'n', 'false', '0', 'off', ''], true)) {
                return false;
            }
        }

        return $value;
    }
}
