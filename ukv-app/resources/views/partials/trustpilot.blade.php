{{-- Trustpilot TrustBox widget. Renders ONLY when a real Business Unit ID is configured
     (config('ukv.trustpilot.business_unit_id') via UKV_TRUSTPILOT_BUSINESS_UNIT_ID).
     No ID = nothing shown, so no fake/placeholder stars ever ship.

     Optional include params:
       $template ('micro'|'combo' or a raw template id)  default: config template (Micro Combo)
       $align    ('center'|'left'|'right')                default: center
       $height   (css px)                                 default: 24px
       $margin   (css)                                    default: 18px 0 --}}
@php
    $tp = config('ukv.trustpilot');
    $tpTemplates = [
        'micro' => '5419b732fbfb950b10de65e5', // Micro Star (rating + stars, compact)
        'combo' => '5419b6ffb0d04a076446a9af', // Micro Combo (stars + rating + count)
        'mini'  => '53aa8807dec7e10d38f59f32', // Mini (logo + stars + count)
    ];
    $tpKey = $template ?? null;
    $tpTemplateId = $tpKey ? ($tpTemplates[$tpKey] ?? $tpKey) : $tp['template_id'];
    $tpAlign  = $align ?? 'center';
    $tpHeight = $height ?? '24px';
    $tpWidth  = $width ?? '100%';
    $tpMargin = $margin ?? '18px 0';
@endphp
@if (! empty($tp['business_unit_id']))
    @once
        @push('head')
        <script type="text/javascript" src="//widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js" async></script>
        @endpush
    @endonce
    <div class="tp-wrap" style="text-align:{{ $tpAlign }};margin:{{ $tpMargin }}">
        <div class="trustpilot-widget" data-locale="en-GB"
             data-template-id="{{ $tpTemplateId }}"
             data-businessunit-id="{{ $tp['business_unit_id'] }}"
             data-style-height="{{ $tpHeight }}" data-style-width="{{ $tpWidth }}" data-theme="light">
            <a href="https://uk.trustpilot.com/review/{{ $tp['domain'] }}" target="_blank" rel="noopener">Trustpilot</a>
        </div>
    </div>
@endif
