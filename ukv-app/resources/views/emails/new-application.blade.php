@component('mail::message')
# New application

A traveller submitted the apply form. {{ $lane }}.

@component('mail::table')
| Field | Detail |
| :---- | :----- |
| Reference | {{ $orderRef }} |
| Name | {{ $applicantName }} |
| Destination | {{ $destination }} |
| Travel date | {{ $travelDate }} |
| Service tier | {{ $tier }} |
| Email | {{ $email }} |
| Phone | {{ $phone }} |
@endcomponent

@component('mail::button', ['url' => 'tel:' . preg_replace('/[^0-9+]/', '', $phone)])
Call {{ $applicantName }}
@endcomponent

Internal operations email — not for customers.
@endcomponent
