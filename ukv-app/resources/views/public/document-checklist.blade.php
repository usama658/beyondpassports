@extends('layouts.public')

@section('title', 'Document checklist — see exactly what you need for your trip | Beyond Passports')
@section('description', "Free document checklist for your UK visa, eVisa or ETA trip. Answer a few questions and get your tailored list on screen instantly — keep it, share it or have it sent to you. Independent service — not a government website.")

@push('head')
<style>
  /* document-checklist.blade.php — page-scoped wizard layout only. Palette/type/components
     inherited from ukv.css; form styling mirrors apply.blade.php's boarding-pass intake. */

  /* ── Wizard centering grid ── */
  .dct-grid{display:grid;grid-template-columns:1fr;gap:28px;max-width:760px;margin:22px auto 0}
  /* gated checklist — free WhatsApp banner + paid tiers */
  .dct-free{max-width:920px;margin:0 auto 26px;display:flex;gap:18px;align-items:center;justify-content:space-between;flex-wrap:wrap;
    background:linear-gradient(110deg,#eafaf2,#f3fbf7);border:1px solid #bfe3d6;border-radius:18px;padding:22px 26px}
  .dct-free .l b{font:800 17px var(--display);color:var(--navy)}
  .dct-free .l p{margin:4px 0 0;font-size:13.5px;color:#3a4b55;max-width:48ch}
  .dct-free .l .tag{display:inline-block;font:800 10px var(--display);letter-spacing:.1em;text-transform:uppercase;color:#1da851;background:#d6f0e2;border-radius:999px;padding:3px 9px;margin:0 0 8px}
  .dct-wa{display:inline-flex;align-items:center;gap:9px;border:0;border-radius:12px;padding:14px 22px;font:800 15px var(--display);color:#fff;background:#25D366;text-decoration:none;white-space:nowrap;box-shadow:0 12px 26px -12px rgba(37,211,102,.7)}
  .dct-wa:hover{background:#1da851}
  .dct-wa svg{width:20px;height:20px;fill:currentColor}
  .dct-tiers{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;max-width:920px;margin:0 auto}
  .dct-tier{background:#fff;border:1px solid var(--paper-edge);border-radius:16px;padding:24px 20px;display:flex;flex-direction:column;box-shadow:0 14px 36px -28px rgba(40,50,70,.5);position:relative}
  .dct-tier.feat{border-color:var(--cta);box-shadow:0 0 0 3px rgba(21,94,122,.14)}
  .dct-tier .badge{position:absolute;top:-11px;left:50%;transform:translateX(-50%);font:800 10px var(--display);letter-spacing:.08em;text-transform:uppercase;background:var(--cta);color:#fff;border-radius:999px;padding:4px 12px;white-space:nowrap}
  .dct-tier .name{font:800 12px var(--display);letter-spacing:.12em;text-transform:uppercase;color:var(--stamp-text);margin:0}
  .dct-tier .qline{font:800 17px var(--display);color:var(--navy);margin:8px 0 2px}
  .dct-tier .qline small{display:block;font:400 12px var(--mono,monospace);color:var(--muted);margin-top:4px}
  .dct-tier .sub{font-size:13px;color:var(--muted);margin:8px 0 12px}
  .dct-tier ul{list-style:none;padding:0;margin:0 0 16px;flex:1}
  .dct-tier li{font-size:13px;color:#3a4b55;display:flex;gap:8px;margin:7px 0}
  .dct-tier .chk{color:var(--stamp);font-weight:800}
  .dct-tier .btn{width:100%;text-align:center}
  @media(max-width:760px){.dct-tiers{grid-template-columns:1fr}.dct-free{flex-direction:column;align-items:flex-start}}

  /* ── GATE (revealed after step 1) — step-1 summary + lock panel + tiers + free chat ── */
  .dct-sum{margin:0 0 18px;border:1px solid var(--paper-edge);border-radius:13px;padding:16px 18px;background:var(--paper)}
  .dct-sum .row{display:flex;align-items:center;justify-content:space-between;gap:12px}
  .dct-sum .ttl{font:800 11px var(--display);letter-spacing:.1em;text-transform:uppercase;color:var(--stamp-text)}
  .dct-sum .edit{font:700 13px var(--display);color:var(--cta);background:none;border:0;cursor:pointer}
  .dct-sum .vals{margin:10px 0 0;display:flex;flex-wrap:wrap;gap:8px}
  .dct-sum .chip{font-size:12.5px;color:#3a4b55;background:#fff;border:1px solid var(--paper-edge);border-radius:8px;padding:6px 11px}
  .dct-sum .chip b{color:var(--navy)}
  .gate{border:1px solid var(--paper-edge);border-radius:16px;overflow:hidden}
  .gate-top{background:linear-gradient(180deg,#0f2330,#16222e);color:#fff;padding:26px 24px;text-align:center}
  .gate-top .lk{width:46px;height:46px;border-radius:12px;background:rgba(169,204,218,.16);color:var(--soft);display:flex;align-items:center;justify-content:center;margin:0 auto 12px}
  .gate-top .lk svg{width:23px;height:23px;stroke:currentColor;fill:none;stroke-width:2}
  .gate-top h3{color:#fff;font-size:21px;margin:0 0 6px}
  .gate-top p{margin:0 auto;max-width:46ch;font-size:13.5px;color:rgba(255,255,255,.78);line-height:1.55}
  .gate-body{padding:22px 20px;background:#fff}
  /* free WhatsApp path — shown FIRST, centered card */
  .gate-free{display:flex;flex-direction:column;align-items:center;text-align:center;gap:14px;
    background:linear-gradient(110deg,#eafaf2,#f3fbf7);border:1px solid #bfe3d6;border-radius:14px;padding:20px 22px}
  .gate-free .tag{display:inline-block;font:800 10px var(--display);letter-spacing:.12em;text-transform:uppercase;color:#1da851;background:#d6f0e2;border-radius:999px;padding:3px 9px;margin:0 0 7px}
  .gate-free b{font:800 15px var(--display);color:var(--navy)}
  .gate-free p{margin:3px auto 0;font-size:13px;color:#3a4b55;max-width:44ch}
  .gate-or{display:flex;align-items:center;gap:14px;margin:20px 2px 18px}
  .gate-or::before,.gate-or::after{content:"";flex:1;height:1px;background:var(--paper-edge)}
  .gate-or span{font:800 11px var(--display);letter-spacing:.1em;text-transform:uppercase;color:var(--muted)}

  /* ── Form layout ── */
  .ukv-form .grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .ukv-form .field{margin:14px 0 0}
  .ukv-form .field--full{grid-column:1 / -1}
  .ukv-form .field[hidden]{display:none}
  .ukv-form label{display:block;font-family:var(--body);font-weight:600;font-size:13px;color:#4a5b65;margin:0 0 5px;letter-spacing:.01em}
  .ukv-form .req{color:var(--cta)}

  .ukv-form input,
  .ukv-form select{
    width:100%;
    padding:13px 14px;
    border:1.5px solid var(--paper-edge);
    border-radius:10px;
    font:inherit;
    font-size:15px;
    background:var(--white);
    color:var(--ink);
    transition:border-color .15s ease,box-shadow .15s ease;
  }
  .ukv-form input:hover,
  .ukv-form select:hover{border-color:#c4cace}
  .ukv-form input:focus,
  .ukv-form select:focus{border-color:var(--cta);box-shadow:0 0 0 3px rgba(21,94,122,.14);outline:none}

  .ukv-form [aria-invalid="true"]{border-color:#c0392b;box-shadow:0 0 0 3px rgba(192,57,43,.14)}
  .ukv-form .hint{font-size:12px;color:var(--muted);margin:5px 0 0;line-height:1.45}

  .ukv-form fieldset{border:0;margin:0;padding:0}
  .ukv-form .legend{
    font-family:var(--body);
    font-size:11px;
    font-weight:700;
    letter-spacing:.12em;
    text-transform:uppercase;
    color:var(--stamp-text);
    margin:28px 0 4px;
    border-top:1px dashed var(--paper-edge);
    padding-top:20px;
  }
  .ukv-form .legend:first-of-type{border-top:0;padding-top:0;margin-top:8px}
  /* step heading (replaces the small legend inside the wizard steps) */
  .dct-shead{font-size:20px;font-weight:800;color:var(--navy);letter-spacing:-.01em;margin:0 0 4px}
  .dct-ssub{font-size:13.5px;color:var(--muted);line-height:1.5;margin:0 0 20px;max-width:54ch}

  /* server-side validation summary (no-JS fallback) */
  .server-errors{
    background:#fdeceb;
    border:1px solid #f3c6c2;
    color:#8a2a22;
    border-radius:10px;
    padding:14px 18px;
    font-size:14px;
    margin:0 0 18px;
  }
  .server-errors ul{margin:6px 0 0;padding-left:20px}

  /* compliance microcopy */
  /* compliance — shield badge + text card (pick A, matches Guides + result page) */
  .compliance{display:grid;grid-template-columns:auto 1fr;gap:20px;align-items:center;margin:28px auto 0;max-width:760px;
    background:var(--white);border:1px solid var(--paper-edge);border-radius:16px;padding:20px 24px;
    box-shadow:0 12px 32px -28px rgba(40,50,70,.5)}
  .compliance .gc-badge{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;
    background:var(--navy);color:#fff;border-radius:13px;width:104px;height:104px;flex:0 0 104px;text-align:center;padding:10px}
  .compliance .gc-badge svg{width:26px;height:26px;color:var(--soft)}
  .compliance .gc-badge span{font-family:var(--body);font-size:10.5px;font-weight:800;letter-spacing:.06em;line-height:1.2}
  .compliance p{margin:0;font-size:13px;line-height:1.65;color:#3a4b55}
  .compliance strong{color:var(--navy)}
  @media (max-width:560px){.compliance{grid-template-columns:1fr;justify-items:start}}

  /* ── HERO — navy mesh (pick A) ── */
  .dct-hero{position:relative;overflow:hidden;background:var(--navy);padding:72px 0 60px}
  .dct-hero::before{content:"";position:absolute;inset:0;background:
     radial-gradient(60% 80% at 12% 16%,rgba(21,94,122,.40),transparent 60%),
     radial-gradient(55% 75% at 88% 84%,rgba(46,154,140,.42),transparent 62%)}
  .dct-hero .wrap{position:relative;z-index:2}
  .dct-hero .mh-grid{display:block}
  .dct-hero .mh-copy{max-width:none}
  .dct-hero .eyebrow{color:var(--soft)}
  .dct-hero h1{color:#fff}
  .dct-hero .lede{color:rgba(255,255,255,.82);max-width:60ch}

  /* "what you get" — glass perk cards on navy */
  .dct-perks{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:28px 0 0;list-style:none;padding:0}
  .dct-perks li{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.18);border-radius:14px;padding:18px;
    transition:box-shadow .2s ease,transform .2s ease}
  .dct-perks li:hover{transform:translateY(-2px);box-shadow:0 16px 36px -24px rgba(0,0,0,.5)}
  .dct-perks .t{width:38px;height:38px;border-radius:10px;background:rgba(169,204,218,.16);color:var(--soft);
    display:flex;align-items:center;justify-content:center;margin:0 0 11px}
  .dct-perks .t svg{width:21px;height:21px;stroke:currentColor;fill:none;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
  .dct-perks .pk{font-family:var(--body);font-weight:700;font-size:15px;color:#fff;margin:0 0 4px}
  .dct-perks p{margin:0;font-size:13px;color:rgba(255,255,255,.72);line-height:1.5}

  /* submit button row */
  .dct-submit-row{margin-top:24px;display:flex;flex-direction:column;align-items:flex-start;gap:10px}
  .dct-submit-row .btn{padding:15px 30px;font-size:16px;border-radius:12px}
  .dct-submit-row .sub-note{font-size:12.5px;color:var(--muted);line-height:1.4}

  /* ---- TWO-STEP SEGMENTED WIZARD (pick C) — JS-enhanced; no-JS shows both steps ---- */
  .dct-steps,.dct-prog,.dct-wnav{display:none}
  /* in wizard mode the segmented steps BECOME the card header (preview C has no green stub) */
  #dct-card.is-wizard .stub{display:none}
  .ukv-form.is-wizard .dct-steps{display:flex;margin:-22px -20px 0;border-radius:16px 16px 0 0}
  .ukv-form.is-wizard .dct-prog{display:block;margin:0 -20px 22px}
  .ukv-form.is-wizard .dct-wnav{display:flex}
  .ukv-form.is-wizard .dct-step{display:none}
  .ukv-form.is-wizard .dct-step.active{display:block}

  .dct-steps{border:0;border-bottom:1px solid var(--paper-edge);overflow:hidden;background:var(--paper)}
  .dct-steps .st{flex:1;display:flex;align-items:center;gap:11px;padding:14px 18px;background:transparent;border:0;font-family:inherit;text-align:left;cursor:pointer}
  .dct-steps .st+.st{border-left:1px solid var(--paper-edge)}
  .dct-steps .st .d{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;flex:0 0 28px;background:#fff;border:1.5px solid var(--paper-edge);color:var(--muted)}
  .dct-steps .st.on .d{background:var(--cta);border-color:var(--cta);color:#fff}
  .dct-steps .st.done .d{background:var(--stamp);border-color:var(--stamp);color:#fff}
  .dct-steps .st .tt{display:block;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);line-height:1.2}
  .dct-steps .st.on .tt{color:var(--navy)}
  .dct-steps .st .ts{display:block;font-size:13.5px;font-weight:700;color:var(--navy);margin-top:2px}
  .dct-steps .st:not(.on) .ts{color:var(--muted)}
  .dct-prog{height:3px;background:var(--paper-edge);margin:0 0 22px;border-radius:3px;overflow:hidden}
  .dct-prog i{display:block;height:100%;width:50%;background:var(--cta);transition:width .25s ease}

  .dct-wnav{align-items:center;justify-content:space-between;margin-top:22px;padding-top:20px;border-top:1px solid var(--paper-edge)}
  .dct-wnav .dct-back{background:#fff;border:1px solid var(--paper-edge);color:var(--muted);font-family:inherit;font-weight:700;font-size:14px;border-radius:11px;padding:11px 18px;cursor:pointer}
  .dct-wnav .dct-back:hover{border-color:#c4cace;color:var(--navy)}
  .dct-wnav .dct-next{display:inline-flex;align-items:center;gap:8px;background:var(--cta);color:#fff;border:0;font-family:inherit;font-weight:700;font-size:15px;border-radius:12px;padding:13px 22px;cursor:pointer;box-shadow:0 12px 26px -12px rgba(21,94,122,.6)}
  .dct-wnav .dct-back[disabled],.dct-wnav .dct-next[disabled]{opacity:.4;cursor:not-allowed;box-shadow:none}
  .dct-dots{display:flex;gap:7px}
  .dct-dots i{width:8px;height:8px;border-radius:50%;background:var(--paper-edge)}
  .dct-dots i.on{background:var(--cta);width:22px;border-radius:4px}
  /* in wizard mode the original submit row only shows on the last step */
  /* in wizard mode the primary button lives in the nav; keep only the sub-note line (centered) */
  .ukv-form.is-wizard .dct-submit-row{display:flex;align-items:center;margin-top:14px}
  .ukv-form.is-wizard .dct-submit-row > .btn{display:none}
  .ukv-form.is-wizard .dct-submit-row .sub-note{width:100%;text-align:center}

  @media (max-width:620px){
    .ukv-form .grid2{grid-template-columns:1fr}
    .dct-perks{grid-template-columns:1fr}
  }
</style>
@endpush

@section('content')

{{-- ── HERO ── --}}
<section class="dct-hero">
  <div class="wrap">
    <div class="mh-grid">
      <div class="mh-copy">
        <p class="eyebrow">Your document checklist</p>
        <h1>See exactly which documents you need.</h1>
        <p class="lede">Tell us about your trip and we'll build your checklist. Our UK team prepares and checks your exact documents before anything's submitted — or ask a quick question free on WhatsApp.</p>

        <ul class="dct-perks reveal">
          <li>
            <span class="t"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg></span>
            <p class="pk">Tailored to your trip</p>
            <p>Built around your destination and dates — not a generic list.</p>
          </li>
          <li>
            <span class="t"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.5 8 8 11 4.5-3 8-6 8-11V5l-8-3z"/><path d="m9 12 2 2 4-4"/></svg></span>
            <p class="pk">Checked by a human</p>
            <p>A real UK person prepares and verifies your exact documents before submission.</p>
          </li>
          <li>
            <span class="t"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24z"/></svg></span>
            <p class="pk">Free quick answers</p>
            <p>Just need to ask something? Message our UK team on WhatsApp — no payment.</p>
          </li>
        </ul>
      </div>
    </div>
  </div>
</section>

{{-- ── WIZARD ── --}}
<section style="padding-top:0">
  <div class="wrap">
    <div class="dct-grid">

      <div class="checker reveal" id="dct-card">
        <div class="stub"><span>Document checklist</span><span>Free &middot; no sign-up</span></div>
        <div class="cbody">

          {{-- Server-side validation summary (shown on a no-JS POST that fails validation). --}}
          @if ($errors->any())
            <div class="server-errors" role="alert">
              <strong>Please fix the following and try again:</strong>
              <ul>
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form class="ukv-form" id="dct-form" method="POST" action="{{ url('/document-checklist') }}" novalidate>
            @csrf

            {{-- Segmented step header + progress (shown only when JS enhances the form) --}}
            <div class="dct-steps" role="tablist" aria-label="Checklist steps">
              <button type="button" class="st on" data-go="1" aria-selected="true"><span class="d">1</span><span><span class="tt">Step 1</span><span class="ts">Your trip</span></span></button>
              <button type="button" class="st" data-go="2"><span class="d">2</span><span><span class="tt">Step 2</span><span class="ts">Your checklist</span></span></button>
            </div>
            <div class="dct-prog" aria-hidden="true"><i></i></div>

            <div class="dct-step active" data-step="1">
            <p class="dct-shead">Tell us about your trip</p>
            <p class="dct-ssub">Where you're going and roughly when — so we can flag timing and passport rules.</p>
            <div class="grid2">
              <div class="field">
                <label for="destination">Destination <span class="req" aria-hidden="true">*</span></label>
                <select id="destination" name="destination" required aria-required="true">
                  <option value="">Choose a destination…</option>
                  @foreach ($navDestinations as $d)
                    <option value="{{ $d->name }}" @selected(old('destination') === $d->name)>{{ $d->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="field">
                <label for="trip_purpose">Trip purpose</label>
                <select id="trip_purpose" name="trip_purpose">
                  <option value="">No preference</option>
                  <option value="tourist" @selected(old('trip_purpose') === 'tourist')>Tourism / holiday</option>
                  <option value="business" @selected(old('trip_purpose') === 'business')>Business</option>
                  <option value="study" @selected(old('trip_purpose') === 'study')>Study</option>
                  <option value="other" @selected(old('trip_purpose') === 'other')>Other</option>
                </select>
              </div>
              <div class="field">
                <label for="travel_date">Approximate travel date</label>
                <input type="date" id="travel_date" name="travel_date" value="{{ old('travel_date') }}">
                <p class="hint">Helps us flag passport-validity and timing rules.</p>
              </div>
              <div class="field">
                <label for="return_date">Approximate return date</label>
                <input type="date" id="return_date" name="return_date" value="{{ old('return_date') }}">
                <p class="hint">Optional — used to gauge length of stay.</p>
              </div>
              <div class="field">
                <label for="visa_entries">Entries needed</label>
                <select id="visa_entries" name="visa_entries">
                  <option value="">No preference</option>
                  <option value="single" @selected(old('visa_entries') === 'single')>Single entry</option>
                  <option value="multiple" @selected(old('visa_entries') === 'multiple')>Multiple entry</option>
                </select>
              </div>
            </div>
            </div>{{-- /step 1 --}}

            {{-- STEP 2 = the gate. Step 1 collapses to an editable summary; the full
                 checklist is unlocked by choosing a service level, or ask free on WhatsApp. --}}
            <div class="dct-step" data-step="2">

              <div class="dct-sum">
                <div class="row">
                  <span class="ttl">Your trip</span>
                  <button type="button" class="edit" data-edit>Edit &#8635;</button>
                </div>
                <div class="vals" id="dct-sum-vals">
                  <span class="chip">Destination <b>—</b></span>
                </div>
              </div>

              <div class="gate">
                <div class="gate-top">
                  <span class="lk"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg></span>
                  <h3>Unlock your full checklist</h3>
                  <p>Ask us anything free on WhatsApp — or have our UK team prepare &amp; check your exact documents before anything is submitted.</p>
                </div>
                <div class="gate-body">
                  <div class="gate-free">
                    <div>
                      <span class="tag">Free</span>
                      <b>Just need a quick answer?</b>
                      <p>Message our UK team — a real person, no payment, general guidance for your trip.</p>
                    </div>
                    <a class="dct-wa" id="dct-free-wa" href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}?text={{ urlencode('Hi Beyond Passports — I would like help with my document checklist for an upcoming trip.') }}" target="_blank" rel="noopener">
                      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg> Ask free on WhatsApp →
                    </a>
                  </div>

                  <div class="gate-or" aria-hidden="true"><span>or let us handle it</span></div>

                  <div class="dct-tiers">
                    <div class="dct-tier">
                      <p class="name">Standard</p>
                      <div class="qline">On request<small>full checklist &middot; checked</small></div>
                      <ul>
                        <li><span class="chk">&#10003;</span>Personalised document list</li>
                        <li><span class="chk">&#10003;</span>Checked by our UK team</li>
                        <li><span class="chk">&#10003;</span>PDF + email + reminders</li>
                      </ul>
                      <a href="{{ url('/apply') }}?tier=standard" class="btn btn--ghost gate-tier" data-tier="standard">Choose Standard</a>
                    </div>
                    <div class="dct-tier feat">
                      <span class="badge">Most popular</span>
                      <p class="name">Express</p>
                      <div class="qline">On request<small>priority handling</small></div>
                      <ul>
                        <li><span class="chk">&#10003;</span>Everything in Standard</li>
                        <li><span class="chk">&#10003;</span>Priority preparation</li>
                        <li><span class="chk">&#10003;</span>WhatsApp + email support</li>
                      </ul>
                      <a href="{{ url('/apply') }}?tier=express" class="btn gate-tier" data-tier="express">Choose Express</a>
                    </div>
                    <div class="dct-tier">
                      <p class="name">Premium</p>
                      <div class="qline">On request<small>hands-on, end to end</small></div>
                      <ul>
                        <li><span class="chk">&#10003;</span>Everything in Express</li>
                        <li><span class="chk">&#10003;</span>Dedicated case handler</li>
                        <li><span class="chk">&#10003;</span>Submission + tracking</li>
                      </ul>
                      <a href="{{ url('/apply') }}?tier=premium" class="btn btn--ghost gate-tier" data-tier="premium">Choose Premium</a>
                    </div>
                  </div>
                </div>
              </div>

            </div>{{-- /step 2 = gate --}}

            {{-- Wizard nav (JS only): Back · dots · Next. On the gate step, only Back shows. --}}
            <div class="dct-wnav">
              <button type="button" class="dct-back" data-back disabled>&larr; Back</button>
              <span class="dct-dots" aria-hidden="true"><i class="on"></i><i></i></span>
              <button type="button" class="dct-next" data-next>See my checklist &rarr;</button>
            </div>
          </form>

        </div>
      </div>

      {{-- COMPLIANCE STRIP — shield badge + text (pick A) --}}
      <div class="compliance reveal">
        <span class="gc-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.5 8 8 11 4.5-3 8-6 8-11V5l-8-3z"/><path d="m9 12 2 2 4-4"/></svg>
          <span>NOT A GOVT SITE</span>
        </span>
        <p>
          <strong>Beyond Passports is an independent service and is not a government website.</strong>
          This checklist is general guidance to help you prepare — your exact requirements depend on your nationality, residence and full situation, and we confirm them before anything is submitted.
          Any service fee is separate from, and additional to, any government or scheme fee. No approval is guaranteed.
        </p>
      </div>

    </div>
  </div>
</section>

{{-- CTA BAND --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Know what you need — then let's sort it</h2>
  <p style="max-width:52ch;color:#cdd9e1">Pick a service level and our UK team prepares &amp; checks your documents — or ask a quick question free on WhatsApp. Every case is checked before anything is submitted.</p>
  <div class="row">
    <a href="{{ url('/apply') }}" class="btn">Start my application &rarr;</a>
    <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--glass">Chat on WhatsApp</a>
  </div>
</div></section>

@endsection

@push('head')
<script>
  // document-checklist.blade.php — progressive enhancement only. Step 1 (the trip) is free.
  // Clicking "See my checklist" reveals the gate (step 2): choose a service level to unlock
  // the full list, or ask free on WhatsApp. No-JS users see step 1 + the gate (links work).
  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('dct-form');
    if (!form) return;
    var dest = document.getElementById('destination');

    // ── Two-step segmented wizard — step 1 free, step 2 = paywall gate ──
    var steps = Array.prototype.slice.call(form.querySelectorAll('.dct-step'));
    var tabs  = Array.prototype.slice.call(form.querySelectorAll('.dct-steps .st'));
    var nextBtn = form.querySelector('[data-next]');
    var backBtn = form.querySelector('[data-back]');
    var editBtn = form.querySelector('[data-edit]');
    var prog  = form.querySelector('.dct-prog i');
    var dots  = Array.prototype.slice.call(form.querySelectorAll('.dct-dots i'));

    // Fill the gate's "Your trip" summary + carry trip context into the unlock links.
    var labelFor = function (sel) {
      var el = document.getElementById(sel);
      if (!el || !el.value) return '';
      return el.options ? el.options[el.selectedIndex].text : el.value;
    };
    var syncGate = function () {
      var vals = document.getElementById('dct-sum-vals');
      if (vals) {
        var chips = [];
        if (dest.value) chips.push('<span class="chip">Destination <b>' + dest.value + '</b></span>');
        var purpose = labelFor('trip_purpose'); if (purpose) chips.push('<span class="chip">Purpose <b>' + purpose + '</b></span>');
        var travel = document.getElementById('travel_date'); if (travel && travel.value) chips.push('<span class="chip">Travel <b>' + travel.value + '</b></span>');
        vals.innerHTML = chips.join('') || '<span class="chip">Your trip details</span>';
      }
      // carry destination into the tier links + free-chat message
      var d = dest.value || '';
      form.querySelectorAll('.gate-tier').forEach(function (a) {
        var base = '{{ url('/apply') }}?tier=' + a.getAttribute('data-tier');
        a.href = d ? base + '&destination=' + encodeURIComponent(d) : base;
      });
      var wa = document.getElementById('dct-free-wa');
      if (wa && d) {
        var msg = 'Hi Beyond Passports — I would like help with my document checklist for ' + d + '.';
        wa.href = 'https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}?text=' + encodeURIComponent(msg);
      }
    };

    if (steps.length === 2 && nextBtn && backBtn) {
      form.classList.add('is-wizard');
      var card = document.getElementById('dct-card');
      if (card) card.classList.add('is-wizard');
      var cur = 1;
      var show = function (n) {
        cur = n;
        if (n === 2) syncGate();
        steps.forEach(function (s) { s.classList.toggle('active', s.getAttribute('data-step') === String(n)); });
        tabs.forEach(function (t) {
          var i = Number(t.getAttribute('data-go'));
          t.classList.toggle('on', i === n);
          t.classList.toggle('done', i < n);
          t.setAttribute('aria-selected', i === n ? 'true' : 'false');
        });
        if (prog) prog.style.width = (n === 1 ? 50 : 100) + '%';
        dots.forEach(function (d, i) { d.classList.toggle('on', i === (n - 1)); });
        backBtn.disabled = (n === 1);
        // The gate carries its own CTAs — hide Next once unlocked, keep Back.
        nextBtn.style.display = (n === 2) ? 'none' : '';
        form.classList.toggle('on-last', n === 2);
        var top = form.querySelector('.dct-step.active');
        if (top) top.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      };
      var guard = function () {
        if (!dest.value) {
          dest.setAttribute('aria-invalid', 'true'); dest.focus();
          var clear = function () { dest.removeAttribute('aria-invalid'); dest.removeEventListener('change', clear); };
          dest.addEventListener('change', clear);
          return false;
        }
        return true;
      };
      nextBtn.addEventListener('click', function () { if (guard()) show(2); });
      backBtn.addEventListener('click', function () { show(1); });
      if (editBtn) editBtn.addEventListener('click', function () { show(1); });
      tabs.forEach(function (t) {
        t.addEventListener('click', function () {
          var i = Number(t.getAttribute('data-go'));
          if (i === 2 && !guard()) return;
          show(i);
        });
      });
      show(1);
    }
  });
</script>
@endpush
