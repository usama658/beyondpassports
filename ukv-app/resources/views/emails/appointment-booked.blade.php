@component('mail::message')
Hi {{ $name }},

Good news — we've booked your appointment for your {{ $dest }} visa (order {{ $ref }}). We'll send you the full details (centre, date and what to bring) separately so you're fully prepared.

Please arrive a little early and bring your passport and the documents we list. Any questions, just reply or message us.

@include('emails.partials.footer')
@endcomponent
