@component('mail::message')
# New callback request

A traveller has requested a callback from the contact page.

@component('mail::table')
| Field | Detail |
| :---- | :----- |
| Name | {{ $contactName }} |
| Phone | {{ $contactPhone }} |
| Best time to call | {{ $bestTime }} |
@endcomponent

**Enquiry**

{{ $contactMessage }}

@component('mail::button', ['url' => 'tel:' . preg_replace('/[^0-9+]/', '', $contactPhone)])
Call {{ $contactName }}
@endcomponent

Internal operations email — not for customers.
@endcomponent
