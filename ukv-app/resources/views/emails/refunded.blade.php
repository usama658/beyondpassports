@component('mail::message')
Hi {{ $name }},

We've processed a refund of our service fee for your {{ $dest }} application (order {{ $ref }}). Please note the government fee is non-refundable as it was already paid to the authority on your behalf.

The refund will appear on your original payment method shortly. If you have any questions, just reply or message us on WhatsApp.

@include('emails.partials.footer')
@endcomponent
