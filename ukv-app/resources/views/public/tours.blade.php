@extends('layouts.public')

{{-- "Plan a trip" — visa-led tour packages. Uses the site header/footer + ukv.css.
     Sections: hero (split + eligibility form) · how it works (.steps) · packages
     (overlay cards) · proof (stat band) · FAQ (accordion) · CTA (.cta-band + form).
     Numbers come from App\Support\SiteStats; packages from config('ukv.tours').
     Compliance: packages are enquiry-only (WhatsApp) until ATOL/PTR is in place —
     no prices, no checkout, no visa/approval guarantee. --}}

@section('title', 'Plan a trip: Europe tours with Schengen visa help built in | Beyond Passports')
@section('description', 'Visa-led European tour packages. We prepare your Schengen visa application and book your appointment first, then arrange hotels and transfers. No payment until after your risk check.')
@section('canonical', url('/tour-packages'))

@section('content')
@include('partials.tours-body')
@endsection
