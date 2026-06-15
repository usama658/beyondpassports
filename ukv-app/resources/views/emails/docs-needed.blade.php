@component('mail::message')
Hi {{ $name }},

To move your {{ $dest }} visa application forward (order {{ $ref }}), we need a couple of documents — usually your passport bio page and a passport-style photo.

How to send them (it's quick): {{ $baseUrl }}/how-to-send-documents/
Or just reply here / to our WhatsApp message and we'll guide you.

@include('emails.partials.footer')
@endcomponent
