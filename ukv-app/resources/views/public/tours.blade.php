@extends('layouts.public')

{{-- "Plan a trip" — visa-led tour packages. Uses the site header/footer + ukv.css.
     Sections: hero (split + eligibility form) · how it works (.steps) · packages
     (overlay cards) · proof (stat band) · FAQ (accordion) · CTA (.cta-band + form).
     Numbers come from App\Support\SiteStats; packages from config('ukv.tours').
     Compliance: packages are enquiry-only (WhatsApp) until ATOL/PTR is in place —
     no prices, no checkout, no visa/approval guarantee. --}}

@section('title', 'Plan a trip — Europe tours with the Schengen visa built in | Beyond Passports')
@section('description', 'Visa-led European tour packages. We prepare the Schengen visa and book the appointment first, then wrap flights and hotels. Registered in the UK and Europe. No payment until after your free risk check.')
@section('canonical', url('/tour-packages'))

@section('content')
@include('partials.tours-body')
@endsection
