@component('mail::message')
# New checklist lead

Someone asked us to deliver their tailored document checklist. This is the first point they
gave us a contact detail.

@component('mail::table')
| Field | Detail |
| :---- | :----- |
| Destination | {{ $destination }} |
| Email | {{ $email !== '' ? $email : '—' }} |
| Phone | {{ $phone !== '' ? $phone : '—' }} |
| Delivery channels | {{ $channels }} |
| Marketing opt-in | {{ $marketingConsent ? 'Yes' : 'No' }} |
@endcomponent

@if ($savedLink !== '')
Their saved checklist: {{ $savedLink }}
@endif

Internal operations email — not for customers.
@endcomponent
