@extends('layouts.public')

@section('title', 'Checklist on its way | Beyond Passports')
@section('description', 'Your tailored document checklist has been emailed. We are opening WhatsApp so our UK team can help you get it right.')

@push('head')
<meta name="robots" content="noindex">
<style>
  /* Reuses the apply-thanks design verbatim — only the copy differs. */
  .ct-thanks { background: radial-gradient(900px 440px at 50% -14%, #e7f1f4, var(--paper)); padding: 84px 0; }
  .tk-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: stretch; max-width: 900px; margin: 0 auto; }
  .tk-left { background: var(--white); border: 1px solid var(--paper-edge); border-radius: 20px; box-shadow: var(--lift-1); padding: 36px; }
  .tk-seal { width: 58px; height: 58px; border-radius: 50%; background: rgba(46,154,140,.12); border: 1.5px solid rgba(46,154,140,.4); display: grid; place-items: center; }
  .tk-seal svg { width: 28px; height: 28px; stroke: var(--stamp-text); fill: none; stroke-width: 2.4; stroke-linecap: round; stroke-linejoin: round; }
  .tk-left h1 { font-size: 26px; color: var(--ink); font-weight: 800; letter-spacing: -.02em; margin: 16px 0 10px; }
  .tk-left p { color: var(--muted); font-size: 15px; line-height: 1.6; margin: 0 0 18px; }
  .tk-estep { display: flex; align-items: center; gap: 12px; background: #f6f9fb; border: 1px solid var(--paper-edge); border-radius: 12px; padding: 13px 15px; font-size: 14px; color: #33454f; }
  .tk-estep .ic { width: 30px; height: 30px; border-radius: 8px; background: rgba(21,94,122,.1); display: grid; place-items: center; flex: none; }
  .tk-estep .ic svg { width: 17px; height: 17px; stroke: var(--cta); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
  .tk-links { display: flex; gap: 16px; flex-wrap: wrap; margin: 18px 0 0; }
  .tk-links a { font-size: 14px; font-weight: 600; color: var(--cta); text-decoration: none; }
  .tk-panel { background: radial-gradient(420px 240px at 100% 0, rgba(37,211,102,.22), transparent 60%), var(--navy); border-radius: 20px; box-shadow: var(--lift-2); padding: 36px; color: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; }
  .tk-ring { width: 104px; height: 104px; position: relative; margin: 0 0 20px; }
  .tk-ring svg { transform: rotate(-90deg); }
  .tk-ring circle { fill: none; stroke-width: 8; }
  .tk-ring .bg { stroke: rgba(255,255,255,.14); }
  .tk-ring .fg { stroke: #25D366; stroke-linecap: round; stroke-dasharray: 283; stroke-dashoffset: 283; animation: tkFill 3s linear forwards; }
  .tk-ring .wac { position: absolute; inset: 0; display: grid; place-items: center; }
  .tk-ring .wac .disc { width: 54px; height: 54px; border-radius: 50%; background: #25D366; display: grid; place-items: center; box-shadow: 0 6px 16px -6px rgba(37,211,102,.8); }
  .tk-ring .wac .disc svg { width: 30px; height: 30px; fill: #fff !important; stroke: none !important; }
  .tk-ring .wac .disc svg path { fill: #fff !important; stroke: none !important; }
  .tk-panel .pk { font-size: 11px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: var(--soft); margin: 0 0 6px; }
  .tk-panel h2 { font-size: 20px; font-weight: 800; margin: 0 0 8px; color: #fff; }
  .tk-panel .sub { font-size: 13.5px; color: rgba(255,255,255,.6); margin: 0 0 20px; }
  .tk-panel .sub b { color: #fff; }
  .tk-wa { display: inline-flex; align-items: center; justify-content: center; gap: 9px; background: #25D366; color: #fff; font-weight: 800; padding: 14px 24px; border-radius: 12px; text-decoration: none; font-size: 15.5px; box-shadow: 0 12px 26px -12px rgba(37,211,102,.7); }
  .tk-wa svg { width: 19px; height: 19px; fill: #fff; }
  @keyframes tkFill { from { stroke-dashoffset: 283; } to { stroke-dashoffset: 0; } }
  @media (max-width: 820px) { .tk-grid { grid-template-columns: 1fr; gap: 22px; } }
</style>
@endpush

@section('content')
@php
  $waGlyph = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>';
@endphp
<section class="ct-thanks"><div class="wrap">
  <div class="tk-grid">
    <div class="tk-left">
      <span class="tk-seal" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg></span>
      <h1>Your checklist is on its way.</h1>
      <p>We've emailed your tailored document list for <strong>{{ $destination }}</strong>, usually within a few minutes. Check your inbox (and spam, just in case). When you're ready, our UK team will confirm your exact requirements before you apply.</p>
      @if ($email)
      <div class="tk-estep">
        <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
        Your checklist has been emailed to {{ $email }}.
      </div>
      @endif
      <div class="tk-links">
        <a href="{{ url('/apply') }}">Start my application →</a>
        <a href="{{ url('/') }}">Back to home</a>
      </div>
    </div>

    @if ($waUrl)
    <div class="tk-panel">
      <div class="tk-ring">
        <svg width="104" height="104" viewBox="0 0 104 104" aria-hidden="true"><circle class="bg" cx="52" cy="52" r="45"/><circle class="fg" cx="52" cy="52" r="45"/></svg>
        <span class="wac" aria-hidden="true"><span class="disc">{!! $waGlyph !!}</span></span>
      </div>
      <p class="pk">Opening WhatsApp</p>
      <h2>We're opening WhatsApp so we can help</h2>
      <p class="sub">The chat opens in <b id="tk-count">3</b> seconds, prefilled with your trip details.</p>
      <a href="{{ $waUrl }}" class="tk-wa" id="tk-wa" target="_blank" rel="noopener">{!! $waGlyph !!} Open WhatsApp now</a>
    </div>
    @endif
  </div>
</div></section>

@if ($waUrl)
<script>
  (function () {
    var wa = @json($waUrl);
    if (!wa) { return; }
    var el = document.getElementById('tk-count');
    var n = 3;
    var go = function () { try { window.location.assign(wa); } catch (e) { window.location.href = wa; } };
    var t = setInterval(function () {
      n--;
      if (el) { el.textContent = n < 0 ? 0 : n; }
      if (n <= 0) { clearInterval(t); go(); }
    }, 1000);
    setTimeout(go, 3600);
  })();
</script>
@endif
@endsection
