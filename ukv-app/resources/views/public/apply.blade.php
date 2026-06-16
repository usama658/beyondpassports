@extends('layouts.public')

@section('title', 'Start your application — UK visas, eVisas & ETAs | Beyond Passports')
@section('description', "Begin your UK visa or eVisa application with Beyond Passports. Tell us your trip and we'll check exactly what you need. Independent service — not a government website. Service fee separate from the government fee.")

@push('head')
<style>
  /* apply.blade.php — page-scoped form layout only. Palette/type/components inherited from ukv.css. */
  .apply-hero{padding:48px 0 0}
  .apply-grid{display:grid;grid-template-columns:1fr;gap:28px;max-width:760px;margin:0 auto}
  .intro-route{margin:18px 0 4px}
  /* form grid inside the boarding-pass body */
  .ukv-form .grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .ukv-form .field{margin:14px 0 0}
  .ukv-form .field--full{grid-column:1 / -1}
  .ukv-form .field[hidden]{display:none}
  .ukv-form label{display:block;font-family:var(--body);font-weight:600;font-size:13px;color:#4a5b65;margin:0 0 5px;letter-spacing:.01em}
  .ukv-form .req{color:var(--cta)}
  .ukv-form input,.ukv-form select{width:100%;padding:12px;border:1px solid var(--paper-edge);border-radius:6px;font:inherit;font-size:15px;background:var(--white);color:var(--ink)}
  /* Page-local 45%-alpha focus ring removed — it was lower-contrast than, and overrode, the
     canonical solid --cta ring in ukv.css. Rely on the shared ring now. (audit S5) */
  /* Field error state (paired with aria-invalid set by JS on failed submit). (audit P2) */
  .ukv-form [aria-invalid="true"]{border-color:#c0392b;box-shadow:0 0 0 1px #c0392b}
  .ukv-form .field-error{display:block;color:#8a2a22;font-size:12.5px;margin:5px 0 0;font-weight:600}
  .ukv-form .hint{font-family:var(--mono);font-size:11px;color:var(--hint);margin:5px 0 0;letter-spacing:.04em}
  /* fieldset reset + group heading */
  .ukv-form fieldset{border:0;margin:0;padding:0}
  .ukv-form .legend{font-family:var(--mono);font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--stamp-text);margin:26px 0 2px;border-top:1px dashed var(--paper-edge);padding-top:18px}
  .ukv-form .legend:first-of-type{border-top:0;padding-top:0;margin-top:8px}
  /* consent row */
  .consent{display:flex;gap:10px;align-items:flex-start;margin:20px 0 0}
  .consent input{width:18px;height:18px;flex:0 0 18px;margin-top:3px}
  .consent label{font-weight:400;font-size:13px;color:var(--muted);margin:0;line-height:1.5}
  /* inline validation message */
  .form-error{display:none;background:#fdeceb;border:1px solid #f3c6c2;color:#8a2a22;border-radius:6px;padding:12px 14px;font-size:14px;margin:18px 0 0}
  .form-error.show{display:block}
  /* server-side validation summary (no-JS fallback) */
  .server-errors{background:#fdeceb;border:1px solid #f3c6c2;color:#8a2a22;border-radius:6px;padding:12px 16px;font-size:14px;margin:0 0 18px}
  .server-errors ul{margin:6px 0 0;padding-left:20px}
  /* compliance microcopy under the form */
  .compliance{font-size:12.5px;color:var(--muted);line-height:1.6;margin:16px auto 0;max-width:760px}
  .compliance strong{color:var(--ink)}
  /* outcome panels */
  .outcome{max-width:760px;margin:0 auto}
  .outcome[aria-hidden="true"]{display:none}
  .panel{background:var(--white);border:1px solid var(--paper-edge);border-radius:12px;box-shadow:var(--shadow);overflow:hidden}
  .panel .phead{background:var(--navy);color:#fff;padding:18px 22px;display:flex;align-items:center;gap:14px}
  .panel .phead svg{flex:0 0 auto}
  .panel .phead .ptag{font-family:var(--mono);font-size:11px;letter-spacing:.12em;color:var(--gold);text-transform:uppercase;margin:0 0 3px}
  .panel .phead h2{font-size:22px;color:#fff;margin:0}
  .panel .pbody{padding:24px 22px}
  .panel .pbody p{margin:0 0 14px;color:#33454f}
  /* tier cards (standard lane) */
  .tiers{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:6px 0 8px}
  .tier{border:1px solid var(--paper-edge);border-radius:10px;padding:16px;text-align:center;background:#f7fafb}
  .tier.is-featured{border-color:var(--cta);box-shadow:0 0 0 2px rgba(199,93,56,.22)}
  .tier .tname{font-family:var(--mono);font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--stamp-text)}
  .tier .tprice{font-family:var(--display);font-size:30px;font-weight:600;color:var(--navy);margin:6px 0 2px}
  .tier .tdesc{font-size:12.5px;color:var(--muted)}
  .tier .tbadge{display:inline-block;font-family:var(--mono);font-size:9px;letter-spacing:.1em;background:var(--gold);color:var(--navy);border-radius:99px;padding:2px 8px;margin-top:8px}
  /* summary chip list in review panel */
  .case-summary{list-style:none;margin:0 0 16px;padding:14px 16px;background:#f7fafb;border:1px dashed var(--paper-edge);border-radius:8px;font-size:13.5px;color:#33454f}
  .case-summary li{display:flex;justify-content:space-between;gap:16px;padding:4px 0}
  .case-summary .k{font-family:var(--mono);font-size:11px;letter-spacing:.06em;color:var(--hint);text-transform:uppercase}
  .case-summary .v{font-weight:600;text-align:right}
  .micro-note{font-family:var(--mono);font-size:11px;color:var(--hint);margin:14px 0 0;letter-spacing:.03em}
  @media (max-width:620px){
    .ukv-form .grid2{grid-template-columns:1fr}
    .tiers{grid-template-columns:1fr}
  }
</style>
@endpush

@php
    // --- Document Requirements Engine: pre-apply preview ----------------------------------
    // /apply is a Route::view (no controller), so $docItems is computed here at the view level
    // (NOT inside the partial — the partial stays pure presentational). If the visitor arrived
    // with a ?destination=<slug|name> param we preview that destination; otherwise we show the
    // partial's generic "we'll confirm after you apply" empty state. This block does not touch
    // the form / consent / routing JS.
    $docItems = [];
    $previewDest = null;
    $destParam = request()->query('destination');
    if ($destParam) {
        $previewDest = ($navDestinations ?? collect())
            ->first(fn ($d) => $d->slug === $destParam || $d->name === $destParam);
        if ($previewDest) {
            $docItems = app(\App\Services\RequirementService::class)->preview($previewDest);
        }
    }
@endphp

@section('content')

{{-- INTRO --}}
<section class="mesh-hero mesh-hero--sm"><div class="wrap">
  <div class="apply-grid">
    <div class="reveal" style="text-align:center">
      <p class="eyebrow">Start your application</p>
      <h1 style="font-size:clamp(32px,4.6vw,46px);color:var(--navy);letter-spacing:-.015em">Tell us your trip — we'll confirm exactly what you need.</h1>
      <p class="lede" style="max-width:52ch;margin:0 auto">Answer a few questions about your travel. We check your details, prepare your paperwork and keep every step tracked. Takes about two minutes.</p>
      <div class="intro-route">
        <svg viewBox="0 0 400 200" style="width:100%;max-height:120px;opacity:.85" aria-hidden="true"><use href="#ukv-route"></use></svg>
      </div>
    </div>
  </div>
</div></section>

{{-- FORM (boarding-pass styled) --}}
<section style="padding-top:0"><div class="wrap">
  <div class="apply-grid">

    {{-- INTAKE FORM --}}
    <div class="checker reveal" id="form-card">
      <div class="stub"><span>Application</span><span>New request</span></div>
      <div class="cbody">

        {{-- Server-side validation summary: shown only on a no-JS POST that fails ApplyRequest. --}}
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

        <form class="ukv-form" id="apply-form" method="POST" action="{{ url('/apply') }}" novalidate>
          @csrf

          <p class="legend">Your trip</p>
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
              <label for="trip_purpose">Trip purpose <span class="req" aria-hidden="true">*</span></label>
              <select id="trip_purpose" name="trip_purpose" required aria-required="true">
                <option value="">Choose…</option>
                <option value="tourist" @selected(old('trip_purpose') === 'tourist')>Tourism / holiday</option>
                <option value="business" @selected(old('trip_purpose') === 'business')>Business</option>
                <option value="study" @selected(old('trip_purpose') === 'study')>Study</option>
                <option value="other" @selected(old('trip_purpose') === 'other')>Other</option>
              </select>
            </div>
            <div class="field">
              <label for="travel_date">Approximate travel date <span class="req" aria-hidden="true">*</span></label>
              <input type="date" id="travel_date" name="travel_date" value="{{ old('travel_date') }}" required aria-required="true">
            </div>
            <div class="field">
              <label for="visa_entries">Entries needed</label>
              <select id="visa_entries" name="visa_entries">
                <option value="">No preference</option>
                <option value="single" @selected(old('visa_entries') === 'single')>Single entry</option>
                <option value="multiple" @selected(old('visa_entries') === 'multiple')>Multiple entry</option>
              </select>
              <p class="hint">Optional — we'll advise if your trip needs more.</p>
            </div>
          </div>

          <p class="legend">Traveller</p>
          <div class="grid2">
            <div class="field field--full">
              <label for="applicant_name">Traveller's full name (as on passport) <span class="req" aria-hidden="true">*</span></label>
              <input type="text" id="applicant_name" name="applicant_name" value="{{ old('applicant_name') }}" autocomplete="name" placeholder="e.g. Alex Taylor" required aria-required="true">
            </div>
            <div class="field">
              <label for="nationality">Passport nationality <span class="req" aria-hidden="true">*</span></label>
              <select id="nationality" name="nationality" required aria-required="true">
                <option value="">Choose…</option>
                <option value="UK" @selected(old('nationality') === 'UK')>United Kingdom</option>
                <option value="Other" @selected(old('nationality') === 'Other')>Other nationality</option>
              </select>
            </div>
            <div class="field">
              <label for="residence_country">Country of residence <span class="req" aria-hidden="true">*</span></label>
              <select id="residence_country" name="residence_country" required aria-required="true">
                <option value="">Choose…</option>
                <option value="UK" @selected(old('residence_country') === 'UK')>United Kingdom</option>
                <option value="Other" @selected(old('residence_country') === 'Other')>Outside the UK</option>
              </select>
            </div>
            <div class="field">
              <label for="residency_status">Residency status <span class="req" aria-hidden="true">*</span></label>
              <select id="residency_status" name="residency_status" required aria-required="true">
                <option value="">Choose…</option>
                <option value="citizen" @selected(old('residency_status') === 'citizen')>Citizen</option>
                <option value="permanent" @selected(old('residency_status') === 'permanent')>Settled / permanent resident</option>
                <option value="visa_holder" @selected(old('residency_status') === 'visa_holder')>Visa holder</option>
              </select>
            </div>
            <div class="field">
              <label for="dual_nationality">Dual nationality (if any)</label>
              <input type="text" id="dual_nationality" name="dual_nationality" value="{{ old('dual_nationality') }}" placeholder="Optional — e.g. Irish">
            </div>
            <div class="field">
              <label for="is_minor">Is the traveller a minor (under 18)? <span class="req" aria-hidden="true">*</span></label>
              <select id="is_minor" name="is_minor" required aria-required="true">
                <option value="">Choose…</option>
                <option value="no" @selected(old('is_minor') === 'no')>No</option>
                <option value="yes" @selected(old('is_minor') === 'yes')>Yes</option>
              </select>
            </div>
            {{-- Guardian name: revealed by JS when minor=yes; required server-side via required_if. --}}
            <div class="field field--full" id="guardian-field" @unless(old('is_minor') === 'yes') hidden @endunless>
              <label for="guardian_name">Guardian's full name <span class="req" aria-hidden="true">*</span></label>
              <input type="text" id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}" autocomplete="name" placeholder="Parent or legal guardian">
              <p class="hint">Required for travellers under 18.</p>
            </div>
            <div class="field field--full">
              <label for="prior_refusal">Any previous visa refusal (for this traveller, any country)? <span class="req" aria-hidden="true">*</span></label>
              <select id="prior_refusal" name="prior_refusal" required aria-required="true">
                <option value="">Choose…</option>
                <option value="no" @selected(old('prior_refusal') === 'no')>No</option>
                <option value="yes" @selected(old('prior_refusal') === 'yes')>Yes</option>
              </select>
              <p class="hint">We ask because a prior refusal can change what's required. It does not mean we can't help.</p>
            </div>
          </div>

          <p class="legend">Passport</p>
          <div class="grid2">
            <div class="field">
              <label for="passport_expiry">Passport expiry date</label>
              <input type="date" id="passport_expiry" name="passport_expiry" value="{{ old('passport_expiry') }}">
              <p class="hint">Optional now — we'll confirm validity rules for your destination.</p>
            </div>
          </div>

          <p class="legend">How we reach you</p>
          <div class="grid2">
            <div class="field">
              <label for="email">Email <span class="req" aria-hidden="true">*</span></label>
              <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" required aria-required="true">
            </div>
            <div class="field">
              <label for="phone">Phone <span class="req" aria-hidden="true">*</span></label>
              <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="+44 …" required aria-required="true">
            </div>
          </div>

          <p class="legend">Service level</p>
          <div class="grid2">
            <div class="field field--full">
              <label for="tier">Choose your service tier</label>
              <select id="tier" name="tier">
                <option value="standard" @selected(old('tier', 'standard') === 'standard')>Standard — full check &amp; submission at our usual pace</option>
                <option value="express" @selected(old('tier') === 'express')>Express — we prioritise our handling</option>
                <option value="premium" @selected(old('tier') === 'premium')>Premium — top of the queue + priority support</option>
              </select>
              <p class="hint">This is our service fee only. Express speeds our handling, not the government's decision. No approval is guaranteed.</p>
            </div>
          </div>

          <div class="consent">
            <input type="checkbox" id="consent" name="consent" value="1" @checked(old('consent')) required aria-required="true">
            <label for="consent">I agree to Beyond Passports contacting me about this application and accept that this is an independent service, not a government website. I understand the service fee is separate from any government fee.</label>
          </div>

          <div class="consent">
            <input type="checkbox" id="begin_now" name="begin_now" value="1" @checked(old('begin_now')) required aria-required="true">
            <label for="begin_now">I ask Beyond Passports to <strong>begin work on my application straight away</strong>. I understand I have a 14-day right to cancel, but that if I cancel after work has started I’ll pay for what’s already done, and that once the service is fully performed I lose the right to cancel. See our <a href="{{ route('legal') }}#terms">cancellation &amp; refunds</a> policy.</label>
          </div>

          <div class="form-error" id="form-error" role="alert" aria-live="assertive">Please complete every required field and tick both consent boxes.</div>

          <button type="submit" class="btn">Continue →</button>
          <p class="hint" style="text-align:center;margin-top:14px">No payment taken yet · we check your details before anything is submitted</p>
        </form>
      </div>
    </div>

    {{-- COMPLIANCE STRIP --}}
    <p class="compliance reveal">
      <strong>Beyond Passports is an independent service and is not a government website.</strong>
      Our service fee is separate from, and additional to, any government or scheme fee.
      Express speeds <strong>our</strong> handling — it does not speed up or change the government's decision, and we cannot guarantee approval.
      For non-standard cases, we confirm the exact requirements and give you a personalised quote after a quick human check.
    </p>

    {{-- DOCUMENTS YOU'LL LIKELY NEED (Document Requirements Engine preview) --}}
    {{-- Only shown when the visitor arrived with a recognised ?destination= param; otherwise
         the generic apply landing stays focused on the form. Pure presentational include. --}}
    @if (! empty($docItems))
      <div class="reveal" style="background:var(--white);border:1px solid var(--paper-edge);border-radius:12px;box-shadow:var(--shadow);padding:24px 22px">
        @include('partials.doc-checklist', ['items' => $docItems, 'personalised' => false])
      </div>
    @endif

    {{-- OUTCOME: STANDARD LANE --}}
    <div class="outcome" id="outcome-standard" role="region" aria-label="Standard service result" aria-hidden="true" tabindex="-1">
      <div class="panel">
        <div class="phead">
          <svg width="34" height="34" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg>
          <div>
            <p class="ptag">Standard service · eligible</p>
            <h2>You're on our standard service — continue to payment</h2>
          </div>
        </div>
        <div class="pbody">
          <p>Good news — based on your answers, your application follows our standard, fixed-fee route. We've recorded your chosen tier; review it below and continue when ready.</p>
          <div class="tiers">
            <div class="tier" data-tier="standard">
              <div class="tname">Standard</div>
              <div class="tprice">£39</div>
              <div class="tdesc">Full check &amp; submission at our usual pace</div>
            </div>
            <div class="tier is-featured" data-tier="express">
              <div class="tname">Express</div>
              <div class="tprice">£59</div>
              <div class="tdesc">We prioritise your handling</div>
              <span class="tbadge">Most popular</span>
            </div>
            <div class="tier" data-tier="premium">
              <div class="tname">Premium</div>
              <div class="tprice">£89</div>
              <div class="tdesc">Top of the queue + priority support</div>
            </div>
          </div>
          <p class="micro-note">Tier fee is our service fee only and is separate from the government fee. Express speeds our handling, not the government's decision. No approval is guaranteed.</p>
          <button type="button" class="btn" id="pay-btn">Continue to secure payment →</button>
          <button type="button" class="btn btn--ghost" id="edit-standard" style="margin-top:10px">← Edit my answers</button>
        </div>
      </div>
    </div>

    {{-- OUTCOME: MANUAL-REVIEW LANE --}}
    <div class="outcome" id="outcome-review" role="region" aria-label="Manual review result" aria-hidden="true" tabindex="-1">
      <div class="panel">
        <div class="phead">
          <svg width="34" height="34" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-route"></use></svg>
          <div>
            <p class="ptag">Manual review · human check</p>
            <h2>Your case needs a quick human check</h2>
          </div>
        </div>
        <div class="pbody">
          <p>Thanks — we've got your details. For your situation, the exact requirements and the correct fee depend on your nationality and where you live, so we confirm them by hand rather than show a one-size-fits-all price.</p>
          <ul class="case-summary" id="review-summary" aria-label="Your answers">
            {{-- filled by JS --}}
          </ul>
          <p>A UK-based adviser will review your answers, confirm precisely what you need and send a <strong>personalised quote</strong> — usually within one business day. No payment is taken until you've approved that quote.</p>
          <p class="micro-note">We quote after a human check because rules and price depend on your nationality and residence. Our service fee is separate from the government fee, and no approval can be guaranteed.</p>
          <button type="button" class="btn" id="callback-btn">Request my callback →</button>
          <button type="button" class="btn btn--ghost" id="edit-review" style="margin-top:10px">← Edit my answers</button>
        </div>
      </div>
    </div>

  </div>
</div></section>

{{-- CTA BAND --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Real people checking real applications</h2>
  <p style="max-width:52ch;color:#cdd9e1">A UK-based team reviews every case before anything is submitted. We're an independent service — not a government website — and we'll always tell you honestly what you need.</p>
  <div class="row"><a href="{{ url('/') }}#how" class="btn btn--ghost" style="color:#fff;border-color:#cdd9e1">How it works</a><a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--wa">Chat on WhatsApp</a></div>
</div></section>

@endsection

@push('head')
<script>
  // apply.blade.php — eligibility "capture-and-route" branching, wired to the Laravel /apply endpoint.
  // The client-side eligibility check below is only a PREVIEW; the SERVER response from
  // POST /apply is authoritative for which lane (standard / manual_review) applies.
  // Progressive enhancement: with JS off the form does a normal same-origin POST (@csrf token
  // is in the markup), and ApplyRequest validation errors render in the .server-errors block.
  document.addEventListener('DOMContentLoaded', function () {
    var form      = document.getElementById('apply-form');
    if (!form) return;
    var formCard  = document.getElementById('form-card');
    var errBox    = document.getElementById('form-error');
    var paneStd   = document.getElementById('outcome-standard');
    var paneRev   = document.getElementById('outcome-review');
    var summary   = document.getElementById('review-summary');
    var submitBtn = form.querySelector('button[type="submit"]');
    var guardian  = document.getElementById('guardian-field');
    var guardianInput = document.getElementById('guardian_name');
    var minorSel  = document.getElementById('is_minor');

    var RM = window.matchMedia('(prefers-reduced-motion:reduce)').matches;
    // Same-origin app: POST straight to /apply. No external API base needed.
    var APPLY_URL    = @json(url('/apply'));
    var CHECKOUT_URL = @json(url('/checkout'));
    var CSRF = form.querySelector('input[name="_token"]').value;
    var lastOrderRef = '';

    var LABELS = {
      nationality:      { UK: 'United Kingdom', Other: 'Other nationality' },
      residence_country:{ UK: 'United Kingdom', Other: 'Outside the UK' },
      residency_status: { citizen: 'Citizen', permanent: 'Settled / permanent resident', visa_holder: 'Visa holder' },
      trip_purpose:     { tourist: 'Tourism / holiday', business: 'Business', study: 'Study', other: 'Other' },
      is_minor:         { no: 'No', yes: 'Yes' },
      prior_refusal:    { no: 'No', yes: 'Yes' }
    };

    function show(el) { el.setAttribute('aria-hidden', 'false'); }
    function hide(el) { el.setAttribute('aria-hidden', 'true'); }

    // Reveal/hide the guardian field when the minor answer changes.
    function syncGuardian() {
      var isMinor = minorSel.value === 'yes';
      guardian.hidden = !isMinor;
      guardianInput.required = isMinor;
      if (!isMinor) guardianInput.value = '';
    }
    minorSel.addEventListener('change', syncGuardian);
    syncGuardian();

    // Client-side PREVIEW of the standard lane (matches the documented standard criteria).
    function isStandard(d) {
      return d.nationality      === 'UK'
          && d.residence_country=== 'UK'
          && d.residency_status === 'citizen'
          && d.trip_purpose     === 'tourist'
          && d.is_minor         === 'no'
          && d.prior_refusal    === 'no';
    }

    // Read the form using canonical field names (same keys ApplyRequest expects).
    function collect() {
      return {
        destination:       form.destination.value,
        trip_purpose:      form.trip_purpose.value,
        travel_date:       form.travel_date.value,
        visa_entries:      form.visa_entries.value,
        applicant_name:    form.applicant_name.value.trim(),
        nationality:       form.nationality.value,
        residence_country: form.residence_country.value,
        residency_status:  form.residency_status.value,
        dual_nationality:  form.dual_nationality.value.trim(),
        is_minor:          form.is_minor.value,
        guardian_name:     form.guardian_name.value.trim(),
        prior_refusal:     form.prior_refusal.value,
        passport_expiry:   form.passport_expiry.value,
        email:             form.email.value.trim(),
        phone:             form.phone.value.trim(),
        tier:              form.tier.value || 'standard',
        consent:           form.consent.checked ? 1 : 0,
        begin_now:         form.begin_now.checked ? 1 : 0
      };
    }

    function valid(d) {
      if (!form.checkValidity()) return false;
      return d.destination && d.trip_purpose && d.travel_date && d.applicant_name &&
             d.nationality && d.residence_country && d.residency_status &&
             d.is_minor && d.prior_refusal && d.email && d.phone && d.consent && d.begin_now;
    }

    // --- Per-field error identification (WCAG 3.3.1 / 4.1.3 — audit P2) -----------------
    // Mirrors the track.blade.php pattern: each offending control gets aria-invalid="true"
    // + aria-errormessage pointing at a per-field message; the generic #form-error banner
    // (role=alert aria-live=assertive) still announces the summary.
    function fieldError(ctrl) {
      // The control's own per-field error <p>, created lazily and reused.
      var id = ctrl.id + '-error';
      var msg = document.getElementById(id);
      if (!msg) {
        msg = document.createElement('p');
        msg.id = id;
        msg.className = 'field-error';
        // Place the message right after the control (before any existing .hint).
        ctrl.parentNode.insertBefore(msg, ctrl.nextSibling);
      }
      return msg;
    }
    function markInvalid(ctrl, message) {
      ctrl.setAttribute('aria-invalid', 'true');
      ctrl.setAttribute('aria-errormessage', ctrl.id + '-error');
      fieldError(ctrl).textContent = message;
      // Clear the flag (and message) as soon as the visitor edits the field.
      var clear = function () {
        ctrl.removeAttribute('aria-invalid');
        var m = document.getElementById(ctrl.id + '-error');
        if (m) m.textContent = '';
        ctrl.removeEventListener('input', clear);
        ctrl.removeEventListener('change', clear);
      };
      ctrl.addEventListener('input', clear);
      ctrl.addEventListener('change', clear);
    }
    function clearAllInvalid() {
      var marked = form.querySelectorAll('[aria-invalid="true"]');
      for (var i = 0; i < marked.length; i++) {
        marked[i].removeAttribute('aria-invalid');
        var m = document.getElementById(marked[i].id + '-error');
        if (m) m.textContent = '';
      }
    }
    // Flag every required control that is currently empty/invalid; return the first one.
    function flagInvalidFields() {
      clearAllInvalid();
      var controls = form.querySelectorAll('input[required], select[required]');
      var first = null;
      for (var i = 0; i < controls.length; i++) {
        var c = controls[i];
        // Skip hidden (e.g. guardian field when not required).
        if (c.disabled || c.closest('[hidden]')) continue;
        var empty = c.type === 'checkbox' ? !c.checked : !String(c.value).trim();
        if (empty || !c.checkValidity()) {
          var label = form.querySelector('label[for="' + c.id + '"]');
          var name = label ? label.textContent.replace(/\s*\*\s*$/, '').trim() : 'This field';
          markInvalid(c, name + ' is required.');
          if (!first) first = c;
        }
      }
      return first;
    }

    function row(k, v) {
      return '<li><span class="k">' + k + '</span><span class="v">' + v + '</span></li>';
    }

    function fillSummary(d) {
      summary.innerHTML =
        row('Destination', d.destination) +
        row('Purpose', LABELS.trip_purpose[d.trip_purpose] || d.trip_purpose) +
        row('Passport', LABELS.nationality[d.nationality] || d.nationality) +
        row('Residence', LABELS.residence_country[d.residence_country] || d.residence_country) +
        row('Status', LABELS.residency_status[d.residency_status] || d.residency_status) +
        row('Minor', LABELS.is_minor[d.is_minor] || d.is_minor) +
        row('Prior refusal', LABELS.prior_refusal[d.prior_refusal] || d.prior_refusal);
    }

    function route(pane) {
      formCard.style.display = 'none';
      hide(pane === paneStd ? paneRev : paneStd);
      show(pane);
      pane.scrollIntoView({ behavior: RM ? 'auto' : 'smooth', block: 'start' });
      pane.focus({ preventScroll: true });
    }

    function backToForm() {
      hide(paneStd); hide(paneRev);
      formCard.style.display = '';
      formCard.scrollIntoView({ behavior: RM ? 'auto' : 'smooth', block: 'start' });
    }

    function showError(msg) {
      errBox.textContent = msg;
      errBox.classList.add('show');
    }
    var DEFAULT_ERR = errBox.textContent;

    function setSubmitting(on) {
      if (!submitBtn) return;
      submitBtn.disabled = on;
      submitBtn.textContent = on ? 'Checking your details…' : 'Continue →';
    }

    // Route based on the SERVER's authoritative response: { lane, order_ref, next, checkout_hint }
    function routeFromServer(resp, d) {
      lastOrderRef = (resp && resp.order_ref) || '';
      if (resp && resp.lane === 'standard') {
        route(paneStd);
      } else {
        fillSummary(d);
        route(paneRev);
      }
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var d = collect();
      if (!valid(d)) {
        showError(DEFAULT_ERR);                 // generic role=alert aria-live banner
        var firstInvalid = flagInvalidFields(); // per-field aria-invalid + aria-errormessage
        if (firstInvalid) firstInvalid.focus();
        return;
      }
      clearAllInvalid();
      errBox.classList.remove('show');

      setSubmitting(true);
      fetch(APPLY_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(d)
      })
      .then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(function (resp) {
        setSubmitting(false);
        routeFromServer(resp, d);
      })
      .catch(function () {
        setSubmitting(false);
        // Network/server error: nothing was submitted. Tell the traveller honestly and let
        // them retry — do NOT route forward on a failed submit.
        showError('We couldn’t reach our servers just now, so nothing has been submitted. Please check your connection and try again, or contact us to continue.');
      });
    });

    document.getElementById('edit-standard').addEventListener('click', backToForm);
    document.getElementById('edit-review').addEventListener('click', backToForm);

    // STANDARD lane → hand off to Stripe via the Laravel checkout route.
    document.getElementById('pay-btn').addEventListener('click', function () {
      if (!lastOrderRef) return;
      window.location = CHECKOUT_URL + '/' + encodeURIComponent(lastOrderRef);
    });

    // MANUAL-REVIEW lane → the case is already lodged by /apply; confirm the callback.
    document.getElementById('callback-btn').addEventListener('click', function () {
      if (!lastOrderRef) return;
      alert('Thanks — your case (' + lastOrderRef + ') is with our UK team. A UK-based adviser will call you back, usually within one business day.');
    });
  });
</script>
@endpush
