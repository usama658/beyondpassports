<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for the post-pay document-detail step on the public /documents page.
 *
 * The customer authenticates by order reference + email (same non-enumerating model as the
 * upload form — the controller does the ref+email match; this request only shape-checks input).
 * Every detail field is optional: the form may be submitted incrementally, and a blank field
 * simply leaves that axis unknown for the RequirementService. The string axes are constrained
 * to their allowed sets so the checklist engine only ever sees canonical values.
 */
class DocumentDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public, ref+email authenticated in the controller
    }

    /**
     * Coerce the yes/no radio for payer_is_applicant into a real bool before the rules run.
     */
    protected function prepareForValidation(): void
    {
        $payer = $this->input('payer_is_applicant');
        if (is_string($payer)) {
            $v = strtolower(trim($payer));
            if (in_array($v, ['yes', 'y', 'true', '1', 'on'], true)) {
                $payer = true;
            } elseif (in_array($v, ['no', 'n', 'false', '0', 'off'], true)) {
                $payer = false;
            } else {
                $payer = null;
            }
            $this->merge(['payer_is_applicant' => $payer]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ref' => ['required', 'string', 'max:32'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'employment_status' => ['nullable', Rule::in(['employed', 'self_employed', 'student', 'retired', 'unemployed', 'other'])],
            'accommodation_type' => ['nullable', Rule::in(['hotel', 'host', 'own_property', 'other'])],
            'funding_source' => ['nullable', Rule::in(['self', 'sponsored'])],
            'return_date' => ['nullable', 'date'],
            'payer_is_applicant' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'employment_status' => 'employment status',
            'accommodation_type' => 'accommodation type',
            'funding_source' => 'funding source',
            'return_date' => 'return date',
            'payer_is_applicant' => 'who is paying',
        ];
    }

    /**
     * The validated detail fields only (no auth fields), ready to fill onto the order.
     *
     * @return array<string, mixed>
     */
    public function detailAttributes(): array
    {
        return $this->only([
            'employment_status',
            'accommodation_type',
            'funding_source',
            'return_date',
            'payer_is_applicant',
        ]);
    }
}
