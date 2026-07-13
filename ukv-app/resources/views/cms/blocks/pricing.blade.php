{{-- Pricing block. Renders the coded lp-pricing partial with tiers from config('ukv.pricing').
     Prices show only when config ukv.pricing.show is true (business-controlled); internals locked. --}}
@php $bpcTiers = config('ukv.pricing.tiers', []); @endphp
@if (! empty($bpcTiers))
@include('partials.lp-pricing', ['bpcTiers' => $bpcTiers])
@endif
