{{-- Free Consultation CTA — single source. Lands in WhatsApp chat via SiteStats::chatUrl()
     with a consultation-specific prefilled message. Uses ukv.css .btn / .btn--ghost, so it
     pairs with the primary CTA on any page that loads ukv.css.
     Params (all optional): $label, $variant ('ghost'|'primary'), $message. --}}
@php
  $ccLabel   = $label   ?? 'Book a free consultation';
  $ccVariant = $variant ?? 'ghost';
  $ccMessage = $message ?? 'Hi Beyond Passports, I would like to book my free consultation.';
  $ccClass   = $ccVariant === 'primary' ? 'btn' : 'btn btn--ghost';
@endphp
<a href="{{ App\Support\SiteStats::chatUrl($ccMessage) }}" target="_blank" rel="noopener" class="{{ $ccClass }}" data-consult-cta>{{ $ccLabel }}</a>
