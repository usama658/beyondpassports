@component('mail::message')
# New application

A traveller submitted the apply form. {{ $lane }}.

@component('mail::table')
| Field | Detail |
| :---- | :----- |
@foreach ($fields as $label => $value)
| {{ $label }} | {{ $value }} |
@endforeach
@endcomponent

@if ($phoneDigits)
@component('mail::button', ['url' => 'tel:' . $phoneDigits])
Call {{ $fields['Traveller name'] ?? 'the traveller' }}
@endcomponent
@endif

Internal operations email — not for customers.
@endcomponent
