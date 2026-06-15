@extends('layouts.public')

@section('title', 'Upload your documents — secure post-payment upload | UKVisaCo')
@section('description', 'Already paid? Upload the documents we asked for using your order reference and email. Secure file upload — PDF, JPG or PNG. Independent service, not a government website.')

{{-- Status lookup-style action page: do not index. --}}
@push('head')
<meta name="robots" content="noindex,nofollow">
<style>
  /* documents — page-scoped layout only. Palette/type/components inherited from ukv.css. */
  .docs-hero{padding:56px 0 0;text-align:center}
  .docs-hero .inner{max-width:720px;margin:0 auto}
  .docs-hero h1{font-size:clamp(34px,5vw,54px);color:var(--navy);letter-spacing:-.015em;margin:0 0 12px}
  .docs-hero p.lede{font-size:18px;color:#33454f;max-width:54ch;margin:0 auto}

  .upload-wrap{max-width:640px;margin:0 auto}
  .checker.upload{text-align:left}
  .upload .file-input{width:100%;padding:12px;border:1px dashed var(--paper-edge);border-radius:8px;font:inherit;font-size:15px;background:#f7fafb}
  .upload .file-input:focus{outline:2px solid var(--cta);outline-offset:1px;border-color:var(--cta)}
  .upload .field-hint{font-size:13px;color:var(--muted);margin:6px 0 0;line-height:1.45}

  .compliance{margin:18px 0 0;padding:14px 16px;border:1px dashed var(--paper-edge);border-left:3px solid var(--gold);border-radius:8px;background:var(--white)}
  .compliance p{margin:0;font-size:13.5px;color:var(--muted);line-height:1.55}
  .compliance strong{color:var(--ink)}

  .privacy-note{font-family:var(--mono);font-size:11px;color:var(--hint);letter-spacing:.04em;margin:14px 0 0}

  .form-error{display:none;background:#fdeceb;border:1px solid #f3c6c2;color:#8a2a22;border-radius:6px;padding:11px 13px;font-size:14px;margin:14px 0 0;line-height:1.5}
  .form-error.show{display:block}
  .form-error ul{margin:6px 0 0;padding-left:1.1em}
  .form-ok{display:none;background:#eaf3f2;border:1px solid #b9ddd9;color:#0a5450;border-radius:6px;padding:16px 16px;font-size:15px;margin:14px 0 0;line-height:1.5}
  .form-ok.show{display:block}
  .form-ok strong{color:#073f3c}
  .form-ok ul{margin:8px 0 0;padding-left:1.1em}
  .form-ok li{margin:0 0 3px}
  .file-rejects{margin:8px 0 0;font-size:13.5px;color:#8a2a22}
  .file-rejects li{margin:0 0 3px}
</style>
@endpush

@section('content')

{{-- 1. HERO --}}
<section class="docs-hero"><div class="wrap">
  <div class="inner reveal">
    <p class="eyebrow">Secure document upload</p>
    <h1>Send us your documents</h1>
    <p class="lede">Already paid and ready to send what we asked for? Enter your order reference and the email on your application, then attach your file. We'll confirm the moment it lands with us.</p>
  </div>
</div></section>

{{-- MRZ strip --}}
<div class="mrz"><div class="wrap"><span>UKV&lt;CO&lt;SECURE&lt;DOCUMENT&lt;UPLOAD&lt;&lt;PDF&lt;JPG&lt;PNG&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;</span></div></div>

{{-- 2. UPLOAD FORM (posts to POST /documents/upload, progressively enhanced via fetch) --}}
<section class="alt"><div class="wrap">
  <div class="upload-wrap reveal">
    <div class="checker upload">
      <div class="stub"><span>DOCUMENT UPLOAD</span><span>UKV&lt;UPLOAD&lt;&lt;&lt;</span></div>
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
          <p class="field-hint">It's in your confirmation email — looks like <code>UKV-2026-004821</code>.</p>

          <label for="email">Email on your application</label>
          <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email') }}"
            autocomplete="email"
            placeholder="you@example.com"
            maxlength="255"
            required
            aria-required="true">
          <p class="field-hint">Must match the email you used when you paid — this is how we confirm it's you.</p>

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
            <p><strong>Keep yourself safe:</strong> only upload the documents we've asked you for. Never type your passport number, card details or other sensitive numbers into the boxes above — those fields are for your reference and email only. If anything feels off, stop and call us before sending.</p>
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

          <button type="submit" class="btn">Upload document</button>
        </form>
      </div>
    </div>
  </div>
</div></section>

{{-- 3. HELP BAND --}}
<section style="padding:40px 0"><div class="wrap">
  <div class="help reveal" style="max-width:760px;margin:0 auto;display:flex;flex-wrap:wrap;gap:16px;align-items:center;justify-content:space-between;border:1px solid var(--paper-edge);border-radius:12px;background:var(--white);padding:18px 22px">
    <p style="margin:0;font-size:15px;color:#33454f">Not sure which documents to send, or can't find your reference? We'll help.</p>
    <div class="links" style="display:flex;gap:10px;flex-wrap:wrap">
      <a href="tel:{{ config('ukv.phone_e164') ?: '+440000000000' }}" class="btn btn--ghost" style="padding:13px 22px">Call us</a>
      <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--ghost" style="padding:13px 22px">WhatsApp</a>
      <a href="{{ url('/track') }}" class="btn btn--ghost" style="padding:13px 22px">Track application</a>
    </div>
  </div>
</div></section>

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
      var html = '<strong>Thanks — we’ve received your upload.</strong>';
      if (accepted.length) {
        html += '<ul>';
        accepted.forEach(function (f) { html += '<li>Received: ' + escapeHtml(f.name) + '</li>'; });
        html += '</ul>';
      }
      if (rejected.length) {
        html += '<ul class="file-rejects">';
        rejected.forEach(function (f) {
          html += '<li>Couldn’t accept ' + escapeHtml(f.name) + ' — ' + escapeHtml(f.error) + '</li>';
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
        showError('Please enter your order reference — it’s in your confirmation email.', ref);
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
                m += '<li>' + escapeHtml(f.name) + ' — ' + escapeHtml(f.error) + '</li>';
              });
              m += '</ul>';
            }
            showError(m);
          } else {
            showError('Sorry — something went wrong uploading that. Please try again, or call or WhatsApp us.');
          }
        })
        .catch(function () {
          showError('Sorry — we couldn’t send that file. Please try again, or call or WhatsApp us.');
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
