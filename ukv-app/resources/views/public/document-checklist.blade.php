@extends('layouts.public')

@section('title', 'Document checklist — see exactly what you need for your trip | Beyond Passports')
@section('description', "Free document checklist for your UK visa, eVisa or ETA trip. Answer a few questions and get your tailored list on screen instantly — keep it, share it or have it sent to you. Independent service — not a government website.")

@push('head')
<style>
  /* document-checklist.blade.php — page-scoped wizard layout only. Palette/type/components
     inherited from ukv.css; form styling mirrors apply.blade.php's boarding-pass intake. */

  /* ── Wizard centering grid ── */
  .dct-grid{display:grid;grid-template-columns:1fr;gap:28px;max-width:760px;margin:22px auto 0}

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
  .ukv-form select:focus{border-color:var(--cta);box-shadow:0 0 0 3px rgba(199,93,56,.14);outline:none}

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
  .compliance{font-size:12.5px;color:var(--muted);line-height:1.6;margin:16px auto 0;max-width:760px}
  .compliance strong{color:var(--ink)}

  /* ── HERO — navy mesh (pick A) ── */
  .dct-hero{position:relative;overflow:hidden;background:var(--navy);padding:72px 0 60px}
  .dct-hero::before{content:"";position:absolute;inset:0;background:
     radial-gradient(60% 80% at 12% 16%,rgba(199,93,56,.40),transparent 60%),
     radial-gradient(55% 75% at 88% 84%,rgba(92,154,123,.42),transparent 62%)}
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
  .dct-perks .t{width:38px;height:38px;border-radius:10px;background:rgba(242,194,172,.16);color:var(--soft);
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
  .ukv-form.is-wizard .dct-steps{display:flex;margin:0 0 0}
  .ukv-form.is-wizard .dct-prog{display:block}
  .ukv-form.is-wizard .dct-wnav{display:flex}
  .ukv-form.is-wizard .dct-step{display:none}
  .ukv-form.is-wizard .dct-step.active{display:block}
  .ukv-form.is-wizard .legend:first-of-type{margin-top:0}

  .dct-steps{border:1px solid var(--paper-edge);border-radius:13px;overflow:hidden;background:var(--paper)}
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
  .dct-wnav .dct-next{display:inline-flex;align-items:center;gap:8px;background:var(--cta);color:#fff;border:0;font-family:inherit;font-weight:700;font-size:15px;border-radius:12px;padding:13px 22px;cursor:pointer;box-shadow:0 12px 26px -12px rgba(199,93,56,.6)}
  .dct-dots{display:flex;gap:7px}
  .dct-dots i{width:8px;height:8px;border-radius:50%;background:var(--paper-edge)}
  .dct-dots i.on{background:var(--cta);width:22px;border-radius:4px}
  /* in wizard mode the original submit row only shows on the last step */
  .ukv-form.is-wizard .dct-submit-row{display:none}
  .ukv-form.is-wizard.on-last .dct-submit-row{display:flex}
  .ukv-form.is-wizard.on-last .dct-next{display:none}

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
        <p class="eyebrow">Free document checklist</p>
        <h1>See exactly which documents you need.</h1>
        <p class="lede">Answer a few quick questions about your trip and get your tailored checklist on screen — free, with nothing to pay and no sign-up. Keep it, share it, or have it sent to you.</p>

        <ul class="dct-perks reveal">
          <li>
            <span class="t"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg></span>
            <p class="pk">On screen, free</p>
            <p>Your full tailored list appears instantly — no contact details needed.</p>
          </li>
          <li>
            <span class="t"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg></span>
            <p class="pk">Yours to keep</p>
            <p>Get a saved link you can come back to and share with anyone travelling with you.</p>
          </li>
          <li>
            <span class="t"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.5 8 8 11 4.5-3 8-6 8-11V5l-8-3z"/><path d="m9 12 2 2 4-4"/></svg></span>
            <p class="pk">Confirmed by a human</p>
            <p>When you apply, our UK team confirms your exact list before anything is submitted.</p>
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
              <button type="button" class="st" data-go="2"><span class="d">2</span><span><span class="tt">Step 2</span><span class="ts">Your situation</span></span></button>
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

            <div class="dct-step" data-step="2">
            <p class="dct-shead">A few more details</p>
            <p class="dct-ssub">Your situation helps us tailor the list — every field here is optional.</p>
            <div class="grid2">
              <div class="field">
                <label for="residency_status">Residency status</label>
                <select id="residency_status" name="residency_status">
                  <option value="">No preference</option>
                  <option value="citizen" @selected(old('residency_status') === 'citizen')>Citizen</option>
                  <option value="permanent" @selected(old('residency_status') === 'permanent')>Settled / permanent resident</option>
                  <option value="visa_holder" @selected(old('residency_status') === 'visa_holder')>Visa holder</option>
                </select>
              </div>
              <div class="field">
                <label for="employment_status">Employment status</label>
                <select id="employment_status" name="employment_status">
                  <option value="">No preference</option>
                  <option value="employed" @selected(old('employment_status') === 'employed')>Employed</option>
                  <option value="self_employed" @selected(old('employment_status') === 'self_employed')>Self-employed</option>
                  <option value="student" @selected(old('employment_status') === 'student')>Student</option>
                  <option value="retired" @selected(old('employment_status') === 'retired')>Retired</option>
                  <option value="unemployed" @selected(old('employment_status') === 'unemployed')>Not currently working</option>
                </select>
              </div>
              <div class="field">
                <label for="accommodation_type">Where you'll stay</label>
                <select id="accommodation_type" name="accommodation_type">
                  <option value="">No preference</option>
                  <option value="hotel" @selected(old('accommodation_type') === 'hotel')>Hotel / paid accommodation</option>
                  <option value="host" @selected(old('accommodation_type') === 'host')>Staying with family / friends</option>
                  <option value="own" @selected(old('accommodation_type') === 'own')>My own property</option>
                  <option value="other" @selected(old('accommodation_type') === 'other')>Other</option>
                </select>
              </div>
              <div class="field">
                <label for="funding_source">Who's funding the trip</label>
                <select id="funding_source" name="funding_source">
                  <option value="">No preference</option>
                  <option value="self" @selected(old('funding_source') === 'self')>Funding it myself</option>
                  <option value="sponsor" @selected(old('funding_source') === 'sponsor')>A sponsor (family / friend)</option>
                  <option value="employer" @selected(old('funding_source') === 'employer')>My employer</option>
                </select>
              </div>
              <div class="field">
                <label for="is_minor">Is the traveller a minor (under 18)?</label>
                <select id="is_minor" name="is_minor">
                  <option value="">No preference</option>
                  <option value="no" @selected(old('is_minor') === 'no')>No</option>
                  <option value="yes" @selected(old('is_minor') === 'yes')>Yes</option>
                </select>
              </div>
              <div class="field">
                <label for="prior_refusal">Any previous visa refusal (any country)?</label>
                <select id="prior_refusal" name="prior_refusal">
                  <option value="">No preference</option>
                  <option value="no" @selected(old('prior_refusal') === 'no')>No</option>
                  <option value="yes" @selected(old('prior_refusal') === 'yes')>Yes</option>
                </select>
                <p class="hint">A prior refusal can change what's required. It does not mean we can't help.</p>
              </div>
            </div>
            </div>{{-- /step 2 --}}

            {{-- Wizard nav (JS only): Back · dots · Next. Submit lives in the row below (last step). --}}
            <div class="dct-wnav">
              <button type="button" class="dct-back" data-back hidden>&larr; Back</button>
              <span class="dct-dots" aria-hidden="true"><i class="on"></i><i></i></span>
              <button type="button" class="dct-next" data-next>Next: your situation &rarr;</button>
            </div>

            <div class="dct-submit-row">
              <button type="submit" class="btn">Show my checklist &rarr;</button>
              <p class="sub-note">Free &middot; no sign-up &middot; your tailored list appears on the next screen</p>
            </div>
          </form>

        </div>
      </div>

      {{-- COMPLIANCE STRIP --}}
      <p class="compliance reveal">
        <strong>Beyond Passports is an independent service and is not a government website.</strong>
        This checklist is general guidance to help you prepare — your exact requirements depend on your nationality, residence and full situation, and we confirm them before anything is submitted.
        Any service fee is separate from, and additional to, any government or scheme fee. No approval is guaranteed.
      </p>

    </div>
  </div>
</section>

{{-- CTA BAND --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Know what you need — then let's sort it</h2>
  <p style="max-width:52ch;color:#cdd9e1">Get your checklist free, then start your application when you're ready. A UK-based team checks every case before anything is submitted.</p>
  <div class="row">
    <a href="{{ url('/apply') }}" class="btn">Start my application &rarr;</a>
    <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--glass">Chat on WhatsApp</a>
  </div>
</div></section>

@endsection

@push('head')
<script>
  // document-checklist.blade.php — progressive enhancement only. The form works with a plain
  // same-origin POST (@csrf in markup); JS just adds a friendly "destination required" guard
  // + a submitting state. The SERVER (POST /document-checklist) is authoritative.
  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('dct-form');
    if (!form) return;
    var dest = document.getElementById('destination');
    var submitBtn = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', function (e) {
      if (!dest.value) {
        e.preventDefault();
        dest.setAttribute('aria-invalid', 'true');
        dest.focus();
        var clear = function () { dest.removeAttribute('aria-invalid'); dest.removeEventListener('change', clear); };
        dest.addEventListener('change', clear);
        return;
      }
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Building your checklist…';
      }
    });

    // ── Two-step segmented wizard (pick C) — progressive enhancement ──
    var steps = Array.prototype.slice.call(form.querySelectorAll('.dct-step'));
    var tabs  = Array.prototype.slice.call(form.querySelectorAll('.dct-steps .st'));
    var nextBtn = form.querySelector('[data-next]');
    var backBtn = form.querySelector('[data-back]');
    var prog  = form.querySelector('.dct-prog i');
    var dots  = Array.prototype.slice.call(form.querySelectorAll('.dct-dots i'));
    if (steps.length === 2 && nextBtn && backBtn) {
      form.classList.add('is-wizard');
      var cur = 1;
      var show = function (n) {
        cur = n;
        steps.forEach(function (s) { s.classList.toggle('active', s.getAttribute('data-step') === String(n)); });
        tabs.forEach(function (t) {
          var i = Number(t.getAttribute('data-go'));
          t.classList.toggle('on', i === n);
          t.classList.toggle('done', i < n);
          t.setAttribute('aria-selected', i === n ? 'true' : 'false');
        });
        if (prog) prog.style.width = (n === 1 ? 50 : 100) + '%';
        dots.forEach(function (d, i) { d.classList.toggle('on', i === (n - 1)); });
        backBtn.hidden = (n === 1);
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
      tabs.forEach(function (t) {
        t.addEventListener('click', function () {
          var i = Number(t.getAttribute('data-go'));
          if (i === 2 && !guard()) return;
          show(i);
        });
      });
      // If the server bounced with a destination error, start on step 1; else step 1 by default.
      show(1);
    }
  });
</script>
@endpush
