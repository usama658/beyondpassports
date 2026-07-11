@component('mail::message')
# New newsletter opt-in

Someone ticked the marketing-consent box and joined the visa-rule updates list.

@component('mail::table')
| Field | Detail |
| :---- | :----- |
| Email | {{ $subscriberEmail }} |
| Marketing consent | Yes (explicit tick) |
| Captured | {{ $capturedAt }} |
@endcomponent

Add them to your mailing list. Consent is on record; include an unsubscribe link in every send.

Internal operations email — not for customers.
@endcomponent
