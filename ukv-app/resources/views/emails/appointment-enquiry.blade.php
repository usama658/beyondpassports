@component('mail::message')
# New appointment enquiry

Someone used the eligibility / appointment form on a landing page. They were handed to WhatsApp;
this is the server record so the lead is not lost if they never send the message.

@component('mail::table')
| Field | Detail |
| :---- | :----- |
| Name | {{ $leadName }} |
| Phone | {{ $leadPhone }} |
| Email | {{ $leadEmail ?? '(no email given)' }} |
| Source | {{ $source }} |
@endcomponent

@component('mail::button', ['url' => 'tel:' . preg_replace('/[^0-9+]/', '', $leadPhone)])
Call {{ $leadName }}
@endcomponent

Internal operations email — not for customers.
@endcomponent
