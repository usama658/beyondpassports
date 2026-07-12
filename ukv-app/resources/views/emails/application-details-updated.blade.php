@component('mail::message')
# Application details updated

A customer saved their case details on the document page.

@component('mail::table')
| Field | Detail |
| :---- | :----- |
@foreach ($fields as $label => $value)
| {{ $label }} | {{ $value }} |
@endforeach
@endcomponent

Their checklist re-tailors automatically. Review in the admin panel when ready.

Internal operations email — not for customers.
@endcomponent
