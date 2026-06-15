@component('mail::message')
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
@endcomponent
