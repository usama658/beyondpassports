@component('mail::message')
Hi {{ $name }},

We hope your {{ $dest }} visa (order {{ $ref }}) made your trip planning easier. If you have a moment, we'd really appreciate a short review of how we did — it helps other travellers know what to expect.

@include('emails.partials.footer')
@endcomponent
