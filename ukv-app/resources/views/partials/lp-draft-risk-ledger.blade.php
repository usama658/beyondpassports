{{--
  DRAFT / reusable — "Before you apply" case-file ledger section.
  Not currently on the Bold LP. To reuse: @include('partials.lp-draft-risk-ledger')
  inside a `.lpb` wrapper on a page that defines the LP tokens (--ink, --muted,
  --edge, --stamp-text, --soft, --cta, --sh, --display). $wa must be in scope
  ($wa = 'https://wa.me/'.config('ukv.whatsapp')).
--}}
@push('head')
<style>
.lpb .ledger{border-top:2px solid var(--ink);margin:32px 0 8px}
.lpb .lrow{display:grid;grid-template-columns:110px 1fr;gap:34px;padding:30px 0;border-bottom:1px solid var(--edge);align-items:start}
.lpb .lrow .idx{font-size:64px;font-weight:800;letter-spacing:-.04em;color:#fbfcfe;-webkit-text-stroke:1.5px var(--soft);line-height:.8}
.lpb .lrow .rc{max-width:64ch}
.lpb .lrow .tag{display:inline-block;font-weight:800;font-size:10.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--stamp-text);background:#eaf4f1;padding:5px 10px;border-radius:6px;margin:0 0 12px}
.lpb .lrow h3{font-size:23px;letter-spacing:-.02em;margin:0 0 9px}
.lpb .lrow .rc p{color:var(--muted);font-size:15.5px;line-height:1.6;margin:0}
@media(max-width:860px){.lpb .lrow{grid-template-columns:56px 1fr;gap:16px}.lpb .lrow .idx{font-size:40px}}
</style>
@endpush

<section class="sec alt" id="risk-cards"><div class="wrap">
  <p class="eyebrow">Before you apply</p>
  <h2 class="h2" style="max-width:22ch">Three things that decide your application before an officer opens it</h2>
  <p class="trans" style="max-width:60ch;margin:12px 0 0">You're here because something about your application feels uncertain. Here's why that instinct is right.</p>
  <div class="ledger">
    <div class="lrow"><div class="idx">01</div><div class="rc"><span class="tag">The record</span><h3>Every refusal is shared by all 29 states</h3><p>No preview, no draft round. You find out weeks later in a rejection letter — and the refusal is already on your record, visible to every Schengen country for five years through VIS.</p></div></div>
    <div class="lrow"><div class="idx">02</div><div class="rc"><span class="tag">The counter</span><h3>The questions are not small talk</h3><p>Staff note inconsistencies. If your dates don't match your booking, or your letter says business but your invitation says tourism, it's written down. You never see that note. The officer does.</p></div></div>
    <div class="lrow"><div class="idx">03</div><div class="rc"><span class="tag">The funds</span><h3>A healthy balance won't prove what they need</h3><p>Officers look for patterns, not balances. Sudden deposits raise questions. Irregular income raises questions. They're not checking whether you have money — they're checking whether you'll come back.</p></div></div>
  </div>
  <p class="trans" style="margin:22px 0 0">These aren't edge cases. They're the most common reasons applications fail — and every one is preventable with the right preparation.</p>
  <div class="ctarow"><a class="btn" href="{{ $wa }}?text=Hi%2C%20I%20want%20to%20talk%20through%20my%20Schengen%20application%20before%20I%20submit%20anything.">Talk to us before you submit anything</a><span class="em"><b>WhatsApp</b> preferred · <b>Email:</b> cases@beyondpassports.co.uk</span></div>
</div></section>
