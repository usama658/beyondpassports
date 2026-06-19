{{-- Money-page prevention block (design "msec-a" — inset file-check card).
     Country-templated via $name + $applyUrl (both provided by DestinationController::show).
     Process claims only — no approval %, no guarantee. Scoped under .mprev. --}}
@push('head')
<style>
  .mprev{padding:8px 0 8px}
  .mprev .filecard{
    position:relative;background:var(--white);
    border:1px solid var(--paper-edge);border-radius:16px;
    box-shadow:0 1px 2px rgba(22,34,46,.05),0 30px 60px -38px rgba(22,34,46,.45);
    padding:54px 56px 44px;overflow:hidden;
  }
  .mprev .filecard::before{content:"";position:absolute;left:0;right:0;top:0;height:4px;background:linear-gradient(90deg,var(--stamp),var(--cta));opacity:.9}
  .mprev .stamp-accent{position:absolute;top:30px;right:36px;border:2px solid rgba(46,154,140,.45);color:var(--stamp-text);border-radius:9px;padding:8px 14px;transform:rotate(-6deg);font-size:10.5px;font-weight:800;letter-spacing:.2em;text-transform:uppercase;box-shadow:inset 0 0 0 3px rgba(46,154,140,.08);opacity:.85}
  .mprev .head{max-width:680px}
  .mprev .eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:12px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 16px}
  .mprev .eyebrow::before{content:"";width:22px;height:1px;background:var(--stamp);opacity:.7}
  .mprev .head h2{margin:0 0 14px;font-size:clamp(28px,3.4vw,40px);line-height:1.08;font-weight:800;letter-spacing:-.02em;color:var(--navy)}
  .mprev .head p{margin:0;color:var(--muted);font-size:17px;line-height:1.62}
  .mprev .checks{margin:38px 0 6px;border-top:1px solid var(--paper-edge)}
  .mprev .row{display:flex;align-items:flex-start;gap:20px;padding:24px 4px;border-bottom:1px solid var(--paper-edge)}
  .mprev .medallion{flex:none;width:50px;height:50px;border-radius:50%;display:grid;place-items:center;background:var(--white);border:2px solid var(--stamp);box-shadow:0 8px 18px -9px rgba(46,154,140,.55), 0 0 0 6px rgba(46,154,140,.07)}
  .mprev .medallion svg{width:24px;height:24px;stroke:var(--stamp-text);stroke-width:2.1;fill:none;stroke-linecap:round;stroke-linejoin:round}
  .mprev .row-text h3{margin:2px 0 6px;font-size:18px;font-weight:700;letter-spacing:-.01em;color:var(--navy)}
  .mprev .row-text p{margin:0;color:var(--muted);font-size:15px;line-height:1.55;max-width:560px}
  .mprev .foot{display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap;margin-top:34px}
  .mprev .foot .note{color:var(--muted);font-size:13.5px;max-width:340px;margin:0}
  .mprev .cta{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
  .mprev .cta .btn{border-radius:11px;padding:14px 24px}
  @media(max-width:760px){
    .mprev .filecard{padding:40px 24px 32px}
    .mprev .stamp-accent{display:none}
    .mprev .foot{flex-direction:column;align-items:stretch}
    .mprev .cta{flex-direction:column;align-items:stretch}
    .mprev .cta .btn{justify-content:center;display:flex}
  }
</style>
@endpush

<section class="mprev"><div class="wrap">
  <div class="filecard reveal">
    <div class="stamp-accent" aria-hidden="true">Checked &amp; Ready</div>
    <div class="head">
      <p class="eyebrow">Built to prevent refusals</p>
      <h2>Most {{ $name }} refusals come down to a few fixable things</h2>
      <p>{{ $name }} applications usually get refused for the same avoidable reasons — unclear funds evidence, missing documents, inconsistent details. We check for every one before we submit, and a real UK person reviews the whole file.</p>
    </div>

    <div class="checks">
      <div class="row">
        <div class="medallion"><svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
        <div class="row-text"><h3>Eligibility for the {{ $visaLabel }}</h3><p>We confirm you qualify for {{ $name }}'s {{ $visaType }} — your nationality, trip and purpose — before you pay.</p></div>
      </div>
      <div class="row">
        <div class="medallion"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 15l2 2 4-4"/></svg></div>
        <div class="row-text"><h3>{{ $name }} documents</h3><p>{{ $name }} has its own document rules{{ $passport ? ' (including '.$passport.' months\' passport validity)' : '' }} — we check each one for history, source and consistency before submission.</p></div>
      </div>
      <div class="row">
        <div class="medallion"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg></div>
        <div class="row-text"><h3>Pre-submission QA</h3><p>A real UK person reviews your whole {{ $name }} file against the current {{ $visaType }} rules before it's submitted.</p></div>
      </div>
    </div>

    <div class="foot">
      <p class="note">We remove the avoidable causes of refusal. Process, not promises — the decision is always the authority's.</p>
      <div class="cta">
        <a class="btn btn--ghost" href="{{ url('/document-checklist') }}">See the {{ $name }} document checklist →</a>
        <a class="btn" href="{{ $applyUrl }}">Start my {{ $name }} application →</a>
      </div>
    </div>
  </div>
</div></section>
