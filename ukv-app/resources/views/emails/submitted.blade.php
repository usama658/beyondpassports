@component('mail::message')
Hi {{ $name }},

Good news — your {{ $dest }} visa application (order {{ $ref }}) has now been submitted to the official system. Timeframes vary by destination and we'll let you know as soon as there's a decision.@if (config('ukv.track.enabled')) You can follow progress here: {{ $baseUrl }}/track/@endif

@include('emails.partials.footer')
@endcomponent
