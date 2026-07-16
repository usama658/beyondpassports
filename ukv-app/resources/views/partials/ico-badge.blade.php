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
{{-- Styles for .ico-badge / .ico-chip now live in public/assets/ukv.css (global), so the
     badge renders correctly on every page regardless of include order or CMS caching. --}}
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
