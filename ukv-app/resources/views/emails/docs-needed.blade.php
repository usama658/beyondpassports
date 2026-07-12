@component('mail::message')
Hi {{ $name }},

To move your {{ $dest }} visa application forward (order {{ $ref }}), we need a few documents — the personalised checklist below is based on the details of your application.

@include('emails.partials.doc-checklist', ['items' => $items ?? []])

Upload them securely here (prefilled with your reference): {{ $documentsUrl }}
Or just reply here / to our WhatsApp message and we'll guide you.

@include('emails.partials.footer')
@endcomponent
