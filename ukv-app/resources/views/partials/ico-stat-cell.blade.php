{{-- ICO registration cell for the .tbar-b stat band. Watermark shield behind the reference
     number (design "X"), linking the public ICO register. Renders only when the number is set;
     otherwise falls back to the original "Registered in UK & Europe" flags cell so nothing breaks.
     Drop-in replacement for one <div> cell in a .tbar-b .row. --}}
@php $icoRef = trim((string) config('ukv.compliance.ico_number', '')); @endphp
@if ($icoRef !== '')
<div><a class="tb-ico" href="https://ico.org.uk/ESDWebPages/Entry/{{ rawurlencode($icoRef) }}" target="_blank" rel="noopener" title="Verify our ICO data-protection registration on the ICO register">
  <span class="tb-ico-w"><span class="tb-ico-ghost" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z"/><path d="m9 12 2 2 4-4.5"/></svg></span><span class="n tb-ico-r">{{ $icoRef }}</span></span>
  <span class="l">ICO registered · <b class="tb-ico-v">verify &rarr;</b></span>
</a></div>
@else
<div><a href="https://find-and-update.company-information.service.gov.uk/company/{{ config('ukv.company_no') ?: '17331903' }}" target="_blank" rel="noopener" title="Verify our UK registration on Companies House" style="color:inherit;text-decoration:none"><div class="n">@include('partials.uk-eu-flags',['size'=>28])</div><div class="l">Registered in UK &amp; Europe</div></a></div>
@endif
