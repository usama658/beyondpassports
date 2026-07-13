{{-- CTA band block. Renders the themed .cta-band section (styled by ukv.css) so it is pixel-identical
     to the coded pages. Editable: heading, subtext, primary button. WhatsApp consult CTA from code. --}}
@php
  $heading = trim((string) ($data['heading'] ?? ''));
  $subtext = trim((string) ($data['subtext'] ?? ''));
  $btnLabel = trim((string) ($data['button_label'] ?? ''));
  $btnUrl = trim((string) ($data['button_url'] ?? ''));
@endphp
@if ($heading !== '')
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>{{ $heading }}</h2>
  @if ($subtext !== '')<p style="max-width:48ch;color:#eef0f1">{{ $subtext }}</p>@endif
  <div class="row">
    @if ($btnLabel !== '')<a href="{{ $btnUrl !== '' ? $btnUrl : '#' }}" class="btn">{{ $btnLabel }}</a>@endif
    @include('partials.consult-cta')
  </div>
</div></section>
@endif
