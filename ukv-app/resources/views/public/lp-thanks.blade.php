@extends('layouts.public')

@section('title', 'Case check received | Beyond Passports')
@section('description', 'Your Schengen visa case check is in. A UK-based specialist will reply, and we are opening WhatsApp so your details reach our team there too.')

@php $wa = 'https://wa.me/'.config('ukv.whatsapp'); @endphp

@push('head')
<meta name="robots" content="noindex">
<style>
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
  .tk-home { display: inline-block; margin: 18px 0 0; font-size: 14px; font-weight: 600; color: var(--cta); text-decoration: none; }
  .tk-panel { background: radial-gradient(420px 240px at 100% 0, rgba(37,211,102,.22), transparent 60%), var(--navy); border-radius: 20px; box-shadow: var(--lift-2); padding: 36px; color: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; }
  .tk-ring { width: 104px; height: 104px; position: relative; margin: 0 0 20px; }
  .tk-ring > svg { transform: rotate(-90deg); } /* only the progress-ring circle, not the nested WhatsApp glyph */
  .tk-ring .wac .disc svg { transform: none; }  /* belt-and-braces: keep the glyph upright */
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
  .tk-panel[hidden] { display: none; }
  @media (max-width: 820px) { .tk-grid { grid-template-columns: 1fr; gap: 22px; } }
</style>
@endpush

@section('content')
<section class="ct-thanks"><div class="wrap">
  <div class="tk-grid">
    <div class="tk-left">
      <span class="tk-seal" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg></span>
      <h1 id="tkHead">Thanks. Your case check is in.</h1>
      <p id="tkBody">A UK-based Schengen visa specialist will review your case and reply, usually within 30 minutes during working hours. No payment is taken to check your case.</p>
      <div class="tk-estep">
        <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
        We are sending your details to our UK team on WhatsApp.
      </div>
      <a href="{{ url('/schengen-visa-consultancy') }}" class="tk-home">Back to the page</a>
    </div>

    <div class="tk-panel" id="tkPanel" hidden>
      <div class="tk-ring">
        <svg width="104" height="104" viewBox="0 0 104 104" aria-hidden="true"><circle class="bg" cx="52" cy="52" r="45"/><circle class="fg" cx="52" cy="52" r="45"/></svg>
        <span class="wac" aria-hidden="true"><span class="disc">@include('partials.wa-glyph')</span></span>
      </div>
      <p class="pk">Opening WhatsApp</p>
      <h2>Sending it to us on WhatsApp too</h2>
      <p class="sub">The chat opens in <b id="tkCount">3</b> seconds, prefilled with your details.</p>
      <a href="{{ $wa }}" class="tk-wa" id="tkWa" target="_blank" rel="noopener">@include('partials.wa-glyph') Open WhatsApp now</a>
    </div>
  </div>
</div></section>

<script>
(function () {
  var lead = null;
  try { lead = JSON.parse(sessionStorage.getItem('bpCaseLead') || 'null'); sessionStorage.removeItem('bpCaseLead'); } catch (e) {}
  if (!lead) { return; } // direct visit: default left card, no WhatsApp panel

  // dynamic content per intent (which form/CTA it landed from)
  var MAP = {
    'case':        { head: 'Your case check is in.',        body: 'A UK-based Schengen visa specialist will review your case and reply, usually within 30 minutes in working hours. No payment is taken to check your case.',                          wa: "Hi, I'd like a case check on my Schengen visa." },
    'appointment': { head: "We're on your appointment.",     body: 'We track Schengen visa appointment availability across all 29 countries daily. A UK specialist will reply about the earliest slots for your trip.',                                 wa: 'Hi, I need a Schengen visa appointment but every slot is gone. Can you help me find one?' },
    'agent':       { head: "We'll handle it for you.",       body: 'Our Schengen visa specialists will prepare your documents, book your appointment and coordinate everything. A UK specialist will reply shortly.',                                 wa: "Hi, I've done this before and just want you to prepare my Schengen documents." },
    'family':      { head: 'Your group application is in.',  body: 'Our Schengen visa specialists will prepare every file together, so no weak case drags the group down. A UK specialist will reply shortly.',                                      wa: "Hi, we're applying for Schengen visas together as a couple/family. Can you prepare our applications together so nothing gets missed?" },
    'refusal':     { head: "We'll review your refusal.",     body: 'Send us your refusal letter. Our specialists will decode the real reason and tell you honestly if it can be recovered. A UK specialist will reply shortly.',                    wa: 'Hi, my visa was refused. Can you review my letter?' },
    'documents':   { head: "We'll check your documents.",    body: 'A real UK reviewer will check your documents for the details that trip cases up, before you apply. A UK specialist will reply shortly.',                                       wa: "Hi, I'd like a risk check before I apply." }
  };
  var m = MAP[lead.intent] || MAP['case'];
  var n = (lead.name || '').trim(), d = (lead.dest || '').trim(), p = (lead.phone || '').trim();

  document.getElementById('tkHead').textContent = (n ? 'Thanks, ' + n + '. ' : 'Thanks. ') + m.head;
  document.getElementById('tkBody').textContent = m.body;

  var msg = m.wa;
  if (d) { msg += ' My destination is ' + d + '.'; }
  if (n) { msg += ' My name is ' + n + '.'; }
  if (p) { msg += ' My number is ' + p + '.'; }
  var waUrl = @json($wa) + '?text=' + encodeURIComponent(msg);
  document.getElementById('tkWa').href = waUrl;
  var panel = document.getElementById('tkPanel'); if (panel) { panel.hidden = false; }

  var el = document.getElementById('tkCount'), c = 3;
  var go = function () { try { window.location.assign(waUrl); } catch (e) { window.location.href = waUrl; } };
  var t = setInterval(function () { c--; if (el) { el.textContent = c < 0 ? 0 : c; } if (c <= 0) { clearInterval(t); go(); } }, 1000);
  setTimeout(go, 3600);
})();
</script>
@endsection
