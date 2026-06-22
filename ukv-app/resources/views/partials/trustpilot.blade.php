{{-- Trustpilot TrustBox widget. Renders ONLY when a real Business Unit ID is configured
     (config('ukv.trustpilot.business_unit_id') via UKV_TRUSTPILOT_BUSINESS_UNIT_ID).
     No ID = nothing shown, so no fake/placeholder stars ever ship.
     Optional $align ('center'|'left') controls horizontal placement. --}}
@php
    $tp = config('ukv.trustpilot');
    $tpAlign = $align ?? 'center';
@endphp
@if (! empty($tp['business_unit_id']))
    @once
        @push('head')
        <script type="text/javascript" src="//widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js" async></script>
        @endpush
    @endonce
    <div class="tp-wrap" style="text-align:{{ $tpAlign }};margin:18px 0">
        {{-- TrustBox widget --}}
        <div class="trustpilot-widget" data-locale="en-GB"
             data-template-id="{{ $tp['template_id'] }}"
             data-businessunit-id="{{ $tp['business_unit_id'] }}"
             data-style-height="24px" data-style-width="100%" data-theme="light">
            <a href="https://uk.trustpilot.com/review/{{ $tp['domain'] }}" target="_blank" rel="noopener">Trustpilot</a>
        </div>
    </div>
@endif
