{{-- Home prevention band — Variant B: "checklist + statement" split.
     On-brand sibling of the home bands: light paper bg, left editorial statement +
     promise + CTA, right a white ticked checklist card with teal ticks + paper-edge
     border. Page-scoped prefix: .hpv-b. Process claims only. --}}

@push('head')
<style>
  /* Variant B — refined split: statement left, ticked checklist card right. Light, on paper. */
  .hpv-b{background:var(--paper);position:relative}
  .hpv-b .split{display:grid;grid-template-columns:1fr 1.05fr;gap:48px;align-items:center}
  @media (max-width:880px){.hpv-b .split{grid-template-columns:1fr;gap:32px}}

  .hpv-b .lead h2{font-family:var(--display);font-size:clamp(26px,3.2vw,36px);font-weight:700;
    letter-spacing:-.03em;line-height:1.08;color:var(--ink);margin:14px 0 0;max-width:18ch}
  .hpv-b .lead .promise{font-size:16.5px;line-height:1.6;color:var(--muted);margin:18px 0 0;max-width:42ch}
  .hpv-b .lead .cta-row{display:flex;gap:14px;flex-wrap:wrap;align-items:center;margin-top:26px}
  .hpv-b .lead .cta-row .rlink{color:var(--stamp-text)}

  /* checklist card — white glass, paper-edge border, soft shadow (matches home cards) */
  .hpv-b .checklist{background:#fff;border:1px solid var(--paper-edge);border-radius:18px;
    padding:10px 26px;box-shadow:0 30px 64px -30px rgba(40,50,70,.45);position:relative}
  /* signature passport-stamp accent on the card's top-right corner (mirrors the hero form) */
  .hpv-b .checklist .stamp{position:absolute;top:-22px;right:-18px;background:#fff;z-index:2}
  @media (max-width:520px){.hpv-b .checklist .stamp{display:none}}
  .hpv-b .checklist li{display:flex;gap:14px;align-items:flex-start;list-style:none;
    padding:18px 0;margin:0;border-bottom:1px solid var(--paper-edge)}
  .hpv-b .checklist ul{margin:0;padding:0}
  .hpv-b .checklist li:last-child{border-bottom:0}
  .hpv-b .checklist .tick{flex:0 0 26px;width:26px;height:26px;border-radius:50%;
    background:rgba(47,143,134,.12);color:var(--stamp-text);
    display:flex;align-items:center;justify-content:center;margin-top:1px}
  .hpv-b .checklist .tick svg{width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2.6;stroke-linecap:round;stroke-linejoin:round}
  .hpv-b .checklist h3{font-family:var(--display);font-size:16px;font-weight:700;color:var(--ink);
    letter-spacing:-.01em;line-height:1.25;margin:0 0 3px}
  .hpv-b .checklist p{margin:0;font-size:14px;line-height:1.5;color:var(--muted)}
</style>
@endpush

<section class="hpv-b"><div class="wrap">
  <div class="split">
    <div class="lead reveal">
      <p class="eyebrow">How we prevent refusals</p>
      <h2>We remove the avoidable causes of refusal — before you submit.</h2>
      <p class="promise">A refusal usually means something fixable was missed. Our checks catch those things while there's still time to put them right.</p>
      <div class="cta-row">
        <a href="{{ url('/apply') }}" class="btn">Start my application →</a>
        <a href="{{ url('/apply') }}" class="rlink">Worried about refusal? See how →</a>
      </div>
    </div>

    <div class="checklist reveal">
      <svg class="stamp" width="64" height="64" viewBox="0 0 48 48" role="img" aria-label="Checked &amp; ready"><use href="#ukv-stamp"></use></svg>
      <ul>
        <li>
          <span class="tick" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg></span>
          <div><h3>Eligibility checked first</h3><p>We confirm you qualify before you pay — nationality, residence, status and trip purpose.</p></div>
        </li>
        <li>
          <span class="tick" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg></span>
          <div><h3>Documents checked — AI plus a real UK person</h3><p>Funds evidence, validity and consistency — the details that trip applications up.</p></div>
        </li>
        <li>
          <span class="tick" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg></span>
          <div><h3>Pre-submission QA gate</h3><p>Nothing is submitted until a UK reviewer has signed off the whole file.</p></div>
        </li>
        <li>
          <span class="tick" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg></span>
          <div><h3>We learn from every outcome</h3><p>Each result sharpens our checks, so the next application is stronger.</p></div>
        </li>
      </ul>
    </div>
  </div>
</div></section>
