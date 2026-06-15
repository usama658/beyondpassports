@component('mail::message')
Hi {{ $name }},

There's an update on your {{ $dest }} visa application (order {{ $ref }}). A member of our team will be in touch by phone or WhatsApp with the details and any next steps.

@include('emails.partials.footer')
@endcomponent
