@component('mail::message')
Hi {{ $name }},

Thanks — we've received your order for your {{ $dest }} visa. Your order reference is {{ $ref }}.

Here's exactly what happens next: {{ $baseUrl }}/how-it-works/
You can check your progress any time here: {{ $baseUrl }}/track/

We'll be in touch shortly (usually by phone or WhatsApp) and let you know which documents we need. Nothing for you to do right now.

@include('emails.partials.footer')
@endcomponent
