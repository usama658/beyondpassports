<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for the public contact / callback form (resources/views/public/contact.blade.php).
 *
 * Lightweight lead capture: there is no Order and no DB row — the controller emails the owner
 * and logs the enquiry. The blade form posts `time` for the best-call slot; we normalise that
 * to the canonical `best_time` key so the Mailable + log stay readable.
 *
 * Public endpoint, so authorize() is open; the rate limit is applied on the route (see report).
 */
class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public contact endpoint
    }

    /**
     * Accept both the blade field name (`time`) and the canonical `best_time`.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'best_time' => $this->input('best_time', $this->input('time')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'phone' => ['required', 'string', 'max:40'],
            'best_time' => ['nullable', 'string', 'max:60'],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please tell us your name so we know who to ask for.',
            'phone.required' => 'Please add a phone number so we can call you back.',
            'consent.accepted' => 'Please tick the box to confirm we can contact you about your enquiry.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'best_time' => 'best time to call',
        ];
    }
}
