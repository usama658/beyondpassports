{{--
  Reusable disclaimer strip (S1 · teal-edge icon card). LOCKED design — reuse this
  partial for every "we're independent, not the government" placement so the look
  and copy stay identical site-wide.

  Options:
    'wrap' => true  (default) — wraps in a full-width .wrap; use between sections.
    'wrap' => false           — bare card; use inside a column / grid cell.
    'text' => '...'           — override body copy (keep the same claim; edit sparingly).

  Usage:
    @include('partials.disclaimer-strip')                     (between sections)
    @include('partials.disclaimer-strip', ['wrap' => false])  (inside a column)

  Self-contained: styling ships via @once the first time it renders on a page, so it
  is correct no matter where it appears or whether other placements render. Resolved-hex
  (no theme vars) so it renders on light pages and dark landing pages alike.
--}}
@php
  $wrap = $wrap ?? true;
  $text = $text ?? '<b>Beyond Passports is an independent consultancy, not a government or embassy service.</b> We do not issue visas or decide outcomes. Every decision rests with the relevant authorities. We help you prepare and submit your own application correctly.';
@endphp
@if($wrap)<div class="wrap" style="padding-top:6px;padding-bottom:6px">@endif
  <div class="disc-strip" role="note">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 3 6v6c0 5 3.8 8.5 9 10 5.2-1.5 9-5 9-10V6l-9-4Zm-1 5h2v6h-2V7Zm0 8h2v2h-2v-2Z"/></svg>
    <p>{!! $text !!}</p>
  </div>
@if($wrap)</div>@endif
@once
<style>
  .disc-strip{display:flex;gap:12px;align-items:flex-start;background:#eef4f6;border:1px solid #d6e5e9;
    border-left:3px solid #155E7A;border-radius:12px;padding:13px 16px;font-family:"Outfit",system-ui,sans-serif}
  .disc-strip svg{width:18px;height:18px;flex:0 0 auto;fill:#155E7A;margin-top:1px}
  .disc-strip p{margin:0;font-size:12.5px;line-height:1.55;color:#3d4b55}
  .disc-strip p b{color:#16222E;font-weight:700}
</style>
@endonce
