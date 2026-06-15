@component('mail::message')
Hi {{ $name }},

Your {{ $dest }} visa is ready (order {{ $ref }}). Please check the details carefully and keep a copy with your travel documents.

How to use it at the border: {{ $baseUrl }}/using-your-visa-on-arrival/

If anything doesn't look right, contact us straight away and we'll help.

@include('emails.partials.footer')
@endcomponent
