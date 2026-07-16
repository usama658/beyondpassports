@extends('layouts.public')

@section('title', 'Upload your documents: secure post-payment upload | Beyond Passports')
@section('description', 'Already paid? Upload the documents we asked for using your order reference and email. Secure file upload: PDF, JPG or PNG. Independent service, not a government website.')

{{-- Status lookup-style action page: do not index. --}}
@push('head')
<meta name="robots" content="noindex,nofollow">
<style>
  /* documents.blade.php — page-scoped layout only. Palette/type/components inherited from ukv.css. */

  /* ── Upload card centering ── */
  .upload-wrap{max-width:640px;margin:0 auto}

  /* ── Shared field styles (upload + detail forms) ── */
  .checker.upload{text-align:left}
  .checker.upload label{display:block;font-size:13px;font-weight:700;color:#4a5b65;margin:14px 0 5px;letter-spacing:.01em}
  .checker.upload label:first-of-type{margin-top:0}

  .checker.upload input[type=text],
  .checker.upload input[type=email],
  .checker.upload input[type=date],
  .checker.upload select{
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
  .checker.upload input[type=text]:hover,
  .checker.upload input[type=email]:hover,
  .checker.upload input[type=date]:hover,
  .checker.upload select:hover{border-color:#c4cace}
  .checker.upload input[type=text]:focus,
  .checker.upload input[type=email]:focus,
  .checker.upload input[type=date]:focus,
  .checker.upload select:focus{border-color:var(--cta);box-shadow:0 0 0 3px rgba(21,94,122,.14);outline:none}

  /* file input — dashed drop zone feel */
  .upload .file-input{
    width:100%;
    padding:14px;
    border:2px dashed var(--paper-edge);
    border-radius:12px;
    font:inherit;
    font-size:15px;
    background:#f7fafb;
    cursor:pointer;
    transition:border-color .15s,background .15s;
  }
  .upload .file-input:hover{border-color:var(--cta);background:#fff8f5}
  .upload .file-input:focus{outline:2px solid var(--cta);outline-offset:2px;border-color:var(--cta)}

  .upload .field-hint{font-size:12.5px;color:var(--muted);margin:6px 0 0;line-height:1.45}

  /* ── Compliance notice ── */
  .compliance{
    margin:20px 0 0;
    padding:16px 18px;
    border:1px solid var(--paper-edge);
    border-left:3px solid var(--cta);
    border-radius:10px;
    background:var(--white);
  }
  .compliance p{margin:0;font-size:13.5px;color:var(--muted);line-height:1.55}
  .compliance strong{color:var(--ink)}

  /* ── Privacy note ── */
  .privacy-note{font-size:12px;color:var(--muted);margin:16px 0 0;line-height:1.5}

  /* ── Error / success states ── */
  .form-error{
    display:none;
    background:#fdeceb;
    border:1px solid #f3c6c2;
    color:#8a2a22;
    border-radius:10px;
    padding:13px 16px;
    font-size:14px;
    margin:16px 0 0;
    line-height:1.5;
  }
  .form-error.show{display:block}
  .form-error ul{margin:6px 0 0;padding-left:1.1em}

  .form-ok{
    display:none;
    background:#E2F1EE;
    border:1px solid #b9ddd9;
    color:#0a5450;
    border-radius:10px;
    padding:18px 18px;
    font-size:15px;
    margin:16px 0 0;
    line-height:1.5;
  }
  .form-ok.show{display:block}
  .form-ok strong{color:#073f3c}
  .form-ok ul{margin:8px 0 0;padding-left:1.1em}
  .form-ok li{margin:0 0 3px}
  .file-rejects{margin:8px 0 0;font-size:13.5px;color:#8a2a22}
  .file-rejects li{margin:0 0 3px}

  /* ── Trust badges row (inside upload card) ── */
  .upload-trust{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin:18px 0 0;
  }
  .upload-trust span{
    display:inline-flex;
    align-items:center;
    gap:7px;
    background:var(--paper);
    border:1px solid var(--paper-edge);
    border-radius:999px;
    padding:7px 13px;
    font-size:12.5px;
    font-weight:600;
    color:#4a5b65;
  }
  .upload-trust .dot{
    width:7px;
    height:7px;
    border-radius:50%;
    background:var(--stamp-text);
    flex:0 0 7px;
  }

  /* ── Help band ── */
  .help-band{
    max-width:760px;
    margin:0 auto;
    display:flex;
    flex-wrap:wrap;
    gap:16px;
    align-items:center;
    justify-content:space-between;
    border:1px solid var(--paper-edge);
    border-radius:16px;
    background:var(--white);
    padding:20px 24px;
    box-shadow:var(--lift-1);
  }
  .help-band p{margin:0;font-size:15px;color:#33454f;line-height:1.4}
  .help-band .links{display:flex;gap:10px;flex-wrap:wrap}
</style>
@endpush

@section('content')

{{-- ── 1. HERO ── --}}
<section class="mesh-hero mesh-hero--sm">
  <div class="wrap">
    <div class="mh-grid">
      <div class="mh-copy">
        <p class="eyebrow">Secure document upload</p>
        <h1>Send us your documents</h1>
        <p class="lede">Already paid and ready to send what we asked for? Enter your order reference and the email on your application, then attach your file. We'll confirm the moment it lands with us.</p>
        <div class="mh-trust">
          <span><b>Secure</b> HTTPS upload</span>
          <span><b>Confirmed</b> on receipt</span>
          <span><b>Private</b>, team use only</span>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 2. UPLOAD FORM (posts to POST /documents/upload, progressively enhanced via fetch) ── --}}
<section class="alt" style="padding-top:0">
  <div class="wrap" style="padding-top:40px">
    <div class="upload-wrap reveal">
      <div class="checker upload">
        <div class="stub"><span>Document upload</span><span>Secure</span></div>
        <div class="cbody">
          <form id="upload-form" method="POST" action="{{ url('/documents/upload') }}" enctype="multipart/form-data" novalidate>
            @csrf

            <label for="ref">Your order reference</label>
            <input
              type="text"
              id="ref"
              name="ref"
              value="{{ old('ref') }}"
              class="ref-input"
              placeholder="UKV-2026-004821"
              autocomplete="off"
              inputmode="text"
              maxlength="32"
              style="text-transform:uppercase"
              required
              aria-required="true">
            <p class="field-hint">It's in your confirmation email. It looks like <code>UKV-2026-004821</code>.</p>

            <label for="email">Email on your application</label>
            <input
              type="email"
              id="email"
              name="email"
              value="{{ old('email') }}"
              autocomplete="email"
              placeholder="name@email.com"
              maxlength="255"
              required
              aria-required="true">
            <p class="field-hint">Must match the email you used when you paid. This is how we confirm it's you.</p>

            <label for="file">Choose your document</label>
            <input
              type="file"
              id="file"
              name="file"
              class="file-input"
              accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
              required
              aria-required="true"
              aria-describedby="file-hint">
            <p class="field-hint" id="file-hint">PDF, JPG or PNG. Clear, in-focus scans or photos work best.</p>

            <div class="compliance">
              <p><strong>Keep yourself safe:</strong> only upload the documents we've asked you for. Never type your passport number, card details or other sensitive numbers into the boxes above. Those fields are for your reference and email only. If anything feels off, stop and call us before sending.</p>
            </div>

            <p class="privacy-note">Files are sent over a secure connection and stored privately, used only to handle your application. See our <a href="{{ url('/legal') }}#privacy">Privacy notice</a>.</p>

            {{-- Server-rendered states for the JS-off path; JS toggles the same nodes. --}}
            <div class="form-error{{ ($errors->any() || session('error')) ? ' show' : '' }}" id="up-error" role="alert" aria-live="assertive">
              @if (session('error')){{ session('error') }}@endif
              @if ($errors->any())
                <ul>
                  @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                  @endforeach
                </ul>
              @endif
            </div>
            <div class="form-ok{{ session('status') ? ' show' : '' }}" id="up-ok" role="status" aria-live="polite">
              @if (session('status'))<strong>{{ session('status') }}</strong>@endif
            </div>

            <button type="submit" class="btn" style="margin-top:20px">Upload document</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 2b. APPLICATION DETAILS (post-pay detail capture -> personalises the checklist below) ── --}}
@php
    /** @var \App\Models\Order|null $order */
    $detailOrder = $order ?? null;
    // Re-use the same ref+email the customer arrived with so the detail POST can re-authenticate.
    $detailRef = $detailOrder?->order_ref ?? old('ref');
    $detailEmail = $detailOrder?->email ?? old('email');
    // Pre-fill helper: old() input wins (after a validation bounce), then the saved order value.
    $detailVal = fn (string $field) => old($field, $detailOrder?->{$field});
    $payerVal = old('payer_is_applicant', $detailOrder?->payer_is_applicant);
    // Boolean cast may give true/false/null; normalise to the radio's 'yes'/'no'/'' string.
    $payerChoice = $payerVal === null ? '' : ($payerVal ? 'yes' : 'no');
    $returnVal = $detailOrder?->return_date
        ? $detailOrder->return_date->format('Y-m-d')
        : old('return_date');
@endphp
<section style="padding:8px 0 40px">
  <div class="wrap">
    <div class="upload-wrap reveal">
      <div class="checker upload">
        <div class="stub"><span>Your application details</span><span>Optional</span></div>
        <div class="cbody">
          <p style="font-size:15px;margin:0 0 18px;color:#33454f;line-height:1.55">A few quick details about your trip help us tailor the exact document checklist below to your case. Everything here is optional, so answer what you can.</p>

          <form id="detail-form" method="POST" action="{{ url('/documents/details') }}" novalidate>
            @csrf

            {{-- Auth context: same ref+email the customer used to reach this page. --}}
            <label for="detail-ref">Your order reference</label>
            <input
              type="text"
              id="detail-ref"
              name="ref"
              value="{{ $detailRef }}"
              class="ref-input"
              placeholder="UKV-2026-004821"
              autocomplete="off"
              inputmode="text"
              maxlength="32"
              style="text-transform:uppercase"
              required
              aria-required="true">

            <label for="detail-email">Email on your application</label>
            <input
              type="email"
              id="detail-email"
              name="email"
              value="{{ $detailEmail }}"
              autocomplete="email"
              placeholder="name@email.com"
              maxlength="255"
              required
              aria-required="true">

            <label for="employment_status">Your employment status</label>
            <select id="employment_status" name="employment_status" class="ref-input">
              <option value="">Prefer not to say</option>
              @foreach ([
                  'employed' => 'Employed',
                  'self_employed' => 'Self-employed',
                  'student' => 'Student',
                  'retired' => 'Retired',
                  'unemployed' => 'Not currently working',
                  'other' => 'Other',
              ] as $value => $text)
                <option value="{{ $value }}" @selected($detailVal('employment_status') === $value)>{{ $text }}</option>
              @endforeach
            </select>

            <label for="accommodation_type">Where you'll be staying</label>
            <select id="accommodation_type" name="accommodation_type" class="ref-input">
              <option value="">Prefer not to say</option>
              @foreach ([
                  'hotel' => 'Hotel or booked accommodation',
                  'host' => 'Staying with a host (friend or family)',
                  'own_property' => 'My own property',
                  'other' => 'Other',
              ] as $value => $text)
                <option value="{{ $value }}" @selected($detailVal('accommodation_type') === $value)>{{ $text }}</option>
              @endforeach
            </select>

            <label for="funding_source">How the trip is funded</label>
            <select id="funding_source" name="funding_source" class="ref-input">
              <option value="">Prefer not to say</option>
              @foreach ([
                  'self' => 'I am funding it myself',
                  'sponsored' => 'Someone is sponsoring me',
              ] as $value => $text)
                <option value="{{ $value }}" @selected($detailVal('funding_source') === $value)>{{ $text }}</option>
              @endforeach
            </select>

            <label for="payer_is_applicant">Are you the person paying for the trip?</label>
            <select id="payer_is_applicant" name="payer_is_applicant" class="ref-input">
              <option value="">Prefer not to say</option>
              <option value="yes" @selected($payerChoice === 'yes')>Yes, I'm paying</option>
              <option value="no" @selected($payerChoice === 'no')>No, someone else is paying</option>
            </select>

            <label for="return_date">Your planned return date</label>
            <input
              type="date"
              id="return_date"
              name="return_date"
              value="{{ $returnVal }}"
              class="ref-input">
            <p class="field-hint">If you know it. This helps us confirm your trip length matches your documents.</p>

            <button type="submit" class="btn" style="margin-top:20px">Save my details</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── 2c. PERSONALISED DOCUMENT CHECKLIST (partial authored in parallel; pure presentational) ── --}}
<section class="alt">
  <div class="wrap">
    <div class="upload-wrap reveal">
      @include('partials.doc-checklist', ['items' => $docChecklist ?? [], 'personalised' => true])
    </div>
  </div>
</section>

{{-- ── 3. HELP BAND ── --}}
<section style="padding:40px 0">
  <div class="wrap">
    <div class="help-band reveal">
      <p>Not sure which documents to send, or can't find your reference? We'll help.</p>
      <div class="links">
        <a href="tel:{{ config('ukv.phone_e164') ?: '+447882747584' }}" class="btn btn--ghost" style="padding:13px 22px">@include('partials.call-glyph')Call UK</a>
        @if(config('ukv.show_de_phone'))<a href="tel:{{ config('ukv.phone_de_e164') ?: '+490000000000' }}" class="btn btn--ghost" style="padding:13px 22px">@include('partials.call-glyph')Call Europe</a>@endif
        <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '447882747584' }}" class="btn btn--ghost" style="padding:13px 22px">@include('partials.wa-glyph')WhatsApp</a>
        @if (config('ukv.track.enabled'))<a href="{{ url('/track') }}" class="btn btn--ghost" style="padding:13px 22px">Track application</a>@endif
      </div>
    </div>
  </div>
</section>

@endsection

@push('head')
<script>
  // documents — upload form. Progressive enhancement: POSTs to /documents/upload via fetch when
  // JS is on (keeps the inline accepted/rejected summary, no page reload); falls back to a normal
  // multipart form POST if JS is off or fetch fails. The store() endpoint returns JSON shaped:
  //   { ok, accepted:[{name,...}], rejected:[{name,error}] }  on success/partial, or
  //   { ok:false, message }                                    on auth/validation miss.
  document.addEventListener('DOMContentLoaded', function () {
    var form   = document.getElementById('upload-form');
    var ref    = document.getElementById('ref');
    var email  = document.getElementById('email');
    var file   = document.getElementById('file');
    var err    = document.getElementById('up-error');
    var ok     = document.getElementById('up-ok');
    var submit = form ? form.querySelector('button[type="submit"]') : null;
    if (!form) return;

    function showError(html, field) {
      ok.classList.remove('show');
      ok.innerHTML = '';
      err.innerHTML = html;
      err.classList.add('show');
      if (field) field.focus();
    }

    function escapeHtml(s) {
      return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function showResult(data) {
      err.classList.remove('show');
      err.innerHTML = '';
      var accepted = (data && data.accepted) || [];
      var rejected = (data && data.rejected) || [];
      var html = '<strong>Thanks. We’ve received your upload.</strong>';
      if (accepted.length) {
        html += '<ul>';
        accepted.forEach(function (f) { html += '<li>Received: ' + escapeHtml(f.name) + '</li>'; });
        html += '</ul>';
      }
      if (rejected.length) {
        html += '<ul class="file-rejects">';
        rejected.forEach(function (f) {
          html += '<li>Couldn’t accept ' + escapeHtml(f.name) + ': ' + escapeHtml(f.error) + '</li>';
        });
        html += '</ul>';
      }
      ok.innerHTML = html;
      ok.classList.add('show');
      form.reset();
      ok.setAttribute('tabindex', '-1');
      ok.focus && ok.focus();
    }

    form.addEventListener('submit', function (e) {
      if (!ref.value.trim()) {
        e.preventDefault();
        showError('Please enter your order reference. It’s in your confirmation email.', ref);
        return;
      }
      if (!email.value.trim()) {
        e.preventDefault();
        showError('Please enter the email on your application so we can confirm it’s you.', email);
        return;
      }
      if (!file.files || !file.files.length) {
        e.preventDefault();
        showError('Please choose a file to upload (PDF, JPG or PNG).', file);
        return;
      }

      // fetch unavailable -> let the browser do the normal multipart POST.
      if (typeof window.fetch !== 'function') return;

      e.preventDefault();
      if (submit) { submit.disabled = true; }

      fetch(form.action, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form),
        credentials: 'same-origin'
      })
        .then(function (res) {
          return res.json().catch(function () { return {}; }).then(function (data) {
            return { status: res.status, data: data };
          });
        })
        .then(function (r) {
          var d = r.data || {};
          if (r.status >= 200 && r.status < 300 && d.ok) {
            showResult(d);
          } else if (r.status === 422 && d.errors) {
            // Laravel validation error bag.
            var msgs = [];
            Object.keys(d.errors).forEach(function (k) { msgs = msgs.concat(d.errors[k]); });
            showError('<ul><li>' + msgs.map(escapeHtml).join('</li><li>') + '</li></ul>');
          } else if (d.message) {
            // Auth miss / no-file / all-rejected generic message from store().
            var m = '<strong>' + escapeHtml(d.message) + '</strong>';
            if (d.rejected && d.rejected.length) {
              m += '<ul class="file-rejects">';
              d.rejected.forEach(function (f) {
                m += '<li>' + escapeHtml(f.name) + ': ' + escapeHtml(f.error) + '</li>';
              });
              m += '</ul>';
            }
            showError(m);
          } else {
            showError('Sorry, something went wrong uploading that. Please try again, or call or WhatsApp us.');
          }
        })
        .catch(function () {
          showError('Sorry, we couldn’t send that file. Please try again, or call or WhatsApp us.');
        })
        .finally(function () { if (submit) { submit.disabled = false; } });
    });

    // clear the error as the traveller corrects things
    [ref, email, file].forEach(function (el) {
      el.addEventListener('input', function () { if (err.classList.contains('show')) err.classList.remove('show'); });
    });
  });
</script>
@endpush
