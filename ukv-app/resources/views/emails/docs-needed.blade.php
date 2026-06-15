@component('mail::message')
Hi {{ $name }},

To move your {{ $dest }} visa application forward (order {{ $ref }}), we need a few documents — the personalised checklist below is based on the details of your application.

@include('emails.partials.doc-checklist', ['items' => $items ?? []])

How to send them (it's quick): {{ $baseUrl }}/how-to-send-documents/
Or just reply here / to our WhatsApp message and we'll guide you.

@include('emails.partials.footer')
@endcomponent
