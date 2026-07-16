{{--
  ICO Data-Protection registration badge. Renders ONLY when config('ukv.compliance.ico_number')
  is set, so it can never show an unverified claim. Every instance links to the live public ICO
  register entry so anyone can verify it.

  Params:
    $variant  'light' (default) · 'dark' (navy backgrounds) · 'chip' (compact inline)
--}}
@php
  $icoRef = trim((string) config('ukv.compliance.ico_number', ''));
@endphp
@if ($icoRef !== '')
@php
  $icoVariant = $variant ?? 'light';
  $icoUrl = 'https://ico.org.uk/ESDWebPages/Entry/' . rawurlencode($icoRef);
  $icoShield = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z"/><path d="m9 12 2 2 4-4.5"/></svg>';
@endphp
@once
@push('head')
<style>
  .ico-badge{display:inline-flex;align-items:center;gap:11px;background:#fff;border:1px solid var(--paper-edge);
    border-radius:12px;padding:10px 14px;text-decoration:none;color:var(--ink);box-shadow:0 10px 24px -18px rgba(22,34,46,.6);transition:border-color .18s ease}
  .ico-badge:hover{border-color:var(--soft)}
  .ico-badge .ico-sh{width:32px;height:32px;flex:none;border-radius:9px;background:rgba(21,94,122,.10);display:grid;place-items:center;color:var(--cta)}
  .ico-badge .ico-sh svg{width:19px;height:19px}
  .ico-badge .ico-t{display:flex;flex-direction:column;line-height:1.2}
  .ico-badge .ico-k{font:800 13px/1.2 var(--display);color:var(--ink)}
  .ico-badge .ico-k b{color:var(--cta)}
  .ico-badge .ico-s{font-size:11.5px;color:var(--muted);margin-top:2px}
  .ico-badge .ico-s .ico-v{color:var(--cta);font-weight:700}
  .ico-badge.on-dark{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.16);color:#fff;box-shadow:none}
  .ico-badge.on-dark .ico-sh{background:rgba(143,185,198,.16);color:var(--soft)}
  .ico-badge.on-dark .ico-k{color:#fff}
  .ico-badge.on-dark .ico-k b{color:var(--soft)}
  .ico-badge.on-dark .ico-s{color:rgba(255,255,255,.6)}
  .ico-badge.on-dark .ico-s .ico-v{color:var(--soft)}
  .ico-chip{display:inline-flex;align-items:center;gap:8px;font:600 13.5px var(--display);color:inherit;text-decoration:none}
  .ico-chip svg{width:17px;height:17px;flex:none;color:var(--cta)}
  .ico-chip b{color:var(--cta)}
</style>
@endpush
@endonce
@if ($icoVariant === 'chip')
<a class="ico-chip" href="{{ $icoUrl }}" target="_blank" rel="noopener"
   title="Verify our ICO data-protection registration on the ICO register">{!! $icoShield !!}ICO registered <b>{{ $icoRef }}</b></a>
@else
<a class="ico-badge {{ $icoVariant === 'dark' ? 'on-dark' : '' }}" href="{{ $icoUrl }}" target="_blank" rel="noopener"
   title="Verify our ICO data-protection registration on the ICO register">
  <span class="ico-sh" aria-hidden="true">{!! $icoShield !!}</span>
  <span class="ico-t"><span class="ico-k">ICO Registered · <b>{{ $icoRef }}</b></span><span class="ico-s">Data protection · <span class="ico-v">verify on the register →</span></span></span>
</a>
@endif
@endif
