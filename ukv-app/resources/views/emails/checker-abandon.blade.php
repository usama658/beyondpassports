@component('mail::message')
Hi {{ $name }},

It looks like you started a visa check for {{ $dest }} but didn't finish. If you'd like a hand, we can guide you through it — just reply and we'll pick up where you left off.

@include('emails.partials.footer')
@endcomponent
