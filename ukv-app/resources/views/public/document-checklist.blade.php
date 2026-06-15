@extends('layouts.public')

@section('title', 'Document checklist — see exactly what you need for your trip | UKVisaCo')
@section('description', "Free document checklist for your UK visa, eVisa or ETA trip. Answer a few questions and get your tailored list on screen instantly — keep it, share it or have it sent to you. Independent service — not a government website.")

@push('head')
<style>
  /* document-checklist.blade.php — page-scoped wizard layout only. Palette/type/components
     inherited from ukv.css; form styling mirrors apply.blade.php's boarding-pass intake. */
  .dct-hero{padding:48px 0 0;text-align:center}
  .dct-hero h1{font-size:clamp(32px,4.8vw,50px);color:var(--navy);letter-spacing:-.015em;max-width:20ch;margin:0 auto .3em}
  .dct-hero p.lede{font-size:18px;color:#33454f;max-width:54ch;margin:0 auto}
  .dct-grid{display:grid;grid-template-columns:1fr;gap:28px;max-width:760px;margin:22px auto 0}
  /* form grid inside the boarding-pass body */
  .ukv-form .grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .ukv-form .field{margin:14px 0 0}
  .ukv-form .field--full{grid-column:1 / -1}
  .ukv-form .field[hidden]{display:none}
  .ukv-form label{display:block;font-family:var(--body);font-weight:600;font-size:13px;color:#4a5b65;margin:0 0 5px;letter-spacing:.01em}
  .ukv-form .req{color:var(--cta)}
  .ukv-form input,.ukv-form select{width:100%;padding:12px;border:1px solid var(--paper-edge);border-radius:6px;font:inherit;font-size:15px;background:var(--white);color:var(--ink)}
  .ukv-form [aria-invalid="true"]{border-color:#c0392b;box-shadow:0 0 0 1px #c0392b}
  .ukv-form .hint{font-family:var(--mono);font-size:11px;color:var(--hint);margin:5px 0 0;letter-spacing:.04em}
  .ukv-form fieldset{border:0;margin:0;padding:0}
  .ukv-form .legend{font-family:var(--mono);font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--stamp-text);margin:26px 0 2px;border-top:1px dashed var(--paper-edge);padding-top:18px}
  .ukv-form .legend:first-of-type{border-top:0;padding-top:0;margin-top:8px}
  /* server-side validation summary (no-JS fallback) */
  .server-errors{background:#fdeceb;border:1px solid #f3c6c2;color:#8a2a22;border-radius:6px;padding:12px 16px;font-size:14px;margin:0 0 18px}
  .server-errors ul{margin:6px 0 0;padding-left:20px}
  /* compliance microcopy under the form */
  .compliance{font-size:12.5px;color:var(--muted);line-height:1.6;margin:16px auto 0;max-width:760px}
  .compliance strong{color:var(--ink)}
  /* "what you get" reassurance row */
  .dct-perks{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:6px 0 0;list-style:none;padding:0}
  .dct-perks li{border:1px solid var(--paper-edge);border-radius:10px;padding:14px 16px;background:#f7fafb}
  .dct-perks .pk{font-family:var(--mono);font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 4px}
  .dct-perks p{margin:0;font-size:13px;color:#33454f;line-height:1.5}
  @media (max-width:620px){
    .ukv-form .grid2{grid-template-columns:1fr}
    .dct-perks{grid-template-columns:1fr}
  }
</style>
@endpush

@section('content')

{{-- HERO --}}
<section class="dct-hero"><div class="wrap">
  <p class="eyebrow">Free document checklist</p>
  <h1>See exactly which documents you need.</h1>
  <p class="lede">Answer a few quick questions about your trip and get your tailored checklist on screen — free, with nothing to pay and no sign-up. Keep it, share it, or have it sent to you.</p>
  <div class="dct-grid" style="margin-top:18px">
    <ul class="dct-perks reveal">
      <li><p class="pk">On screen, free</p><p>Your full tailored list appears instantly — no contact details needed.</p></li>
      <li><p class="pk">Yours to keep</p><p>Get a saved link you can come back to and share with anyone travelling with you.</p></li>
      <li><p class="pk">Confirmed by a human</p><p>When you apply, our UK team confirms your exact list before anything is submitted.</p></li>
    </ul>
  </div>
</div></section>
<div class="mrz"><div class="wrap"><span>UKV&lt;DOCUMENT&lt;CHECKLIST&lt;TAILORED&lt;TO&lt;YOUR&lt;TRIP&lt;FREE&lt;ON&lt;SCREEN&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;</span></div></div>

{{-- WIZARD --}}
<section style="padding-top:0"><div class="wrap">
  <div class="dct-grid">

    <div class="checker reveal" id="dct-card">
      <div class="stub"><span>DOCUMENT CHECKLIST</span><span>UKV&lt;DOCS&lt;&lt;&lt;</span></div>
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

          <p class="legend">Your situation</p>
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

          <button type="submit" class="btn" style="margin-top:22px">Show my checklist →</button>
          <p class="hint" style="text-align:center;margin-top:14px">Free · no sign-up · your tailored list appears on the next screen</p>
        </form>
      </div>
    </div>

    {{-- COMPLIANCE STRIP --}}
    <p class="compliance reveal">
      <strong>UKVisaCo is an independent service and is not a government website.</strong>
      This checklist is general guidance to help you prepare — your exact requirements depend on your nationality, residence and full situation, and we confirm them before anything is submitted.
      Any service fee is separate from, and additional to, any government or scheme fee. No approval is guaranteed.
    </p>

  </div>
</div></section>

{{-- CTA BAND --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Know what you need — then let's sort it</h2>
  <p style="max-width:52ch;color:#cdd9e1">Get your checklist free, then start your application when you're ready. A UK-based team checks every case before anything is submitted.</p>
  <div class="row"><a href="{{ url('/apply') }}" class="btn">Start my application →</a><a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--wa">Chat on WhatsApp</a></div>
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
  });
</script>
@endpush
