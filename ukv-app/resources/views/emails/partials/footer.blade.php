{{-- Mandatory compliance footer — appended VERBATIM to every lifecycle email.
     Hard compliance requirement (docs/superpowers/port/03-emails.md §2):
       1. Not a government website (independent service).
       2. Service fee is separate from any government fee.
       3. No faster approval — express speeds OUR handling, not the decision.
     Do not edit the wording. --}}
@php $emailSocial = array_filter(config('ukv.social', [])); @endphp
@if (! empty($emailSocial))
@component('mail::subcopy')
Follow us for visa-rule updates: @foreach ($emailSocial as $k => $url)[{{ ucfirst($k) }}]({{ $url }})@if (! $loop->last) · @endif @endforeach
@endcomponent
@endif
@component('mail::subcopy')
Independent service — not a government website. Our service fee is separate from any government fee. Express tiers speed up our handling, not the government's decision.
@endcomponent
