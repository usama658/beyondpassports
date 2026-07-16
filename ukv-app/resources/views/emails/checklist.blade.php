@component('mail::message')
@if ($forTeam ?? false)
# {{ $dest }} checklist — ready to share

A visitor just built this checklist on the site and was sent to WhatsApp. When they message you, paste the list below into your reply (or send them the saved link).

@if (! empty($travelDate))
**Travelling around:** {{ $travelDate }}
@endif

@include('emails.partials.doc-checklist', ['items' => $items ?? []])

@component('mail::button', ['url' => $savedLink])
Open the saved checklist
@endcomponent

This is an internal ops copy — the visitor did not receive an email.
@else
# Your {{ $dest }} document checklist

Here's the tailored list of documents you'll need for {{ $dest }}, based on the details you gave us. We've also saved it for you at the link below so you can come back to it any time.

@include('emails.partials.doc-checklist', ['items' => $items ?? []])

@component('mail::button', ['url' => $savedLink])
View & share your saved checklist
@endcomponent

When you're ready, we can prepare and submit the application for you:

@component('mail::button', ['url' => $applyUrl, 'color' => 'success'])
Start your application
@endcomponent

Tip: every document above links to the official source so you can double-check the latest requirements yourself before you apply.

@include('emails.partials.footer')
@endif
@endcomponent
