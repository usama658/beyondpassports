{{-- Refund Promise "guarantee lockup" header (draft P1). Editorial masthead:
     soft monogram tile + tiny-caps label + strong line + hairline rule + fine print.
     Scoped to lp-bold (.lpb) tokens; self-contained @once CSS; links to /legal#refunds.
     Honest copy: no guarantee of a decision; gov fees excluded. --}}
@once
@push('head')
<style>
.lpb .rlk{display:block;text-decoration:none;color:inherit;max-width:520px}
.lpb .rlk .lockrow{display:flex;align-items:center;gap:13px;padding:15px 0 15px;border-block:1px solid var(--edge)}
.lpb .rlk .mono{width:44px;height:44px;border-radius:13px;background:linear-gradient(150deg,#eaf4f1,#d7ebe6);border:1px solid rgba(46,154,140,.32);display:flex;align-items:center;justify-content:center;flex:none;box-shadow:inset 0 1px 0 #fff;transition:transform .18s ease,box-shadow .18s ease}
.lpb .rlk .mono svg{width:22px;height:22px;fill:none;stroke:var(--stamp);stroke-width:1.9}
.lpb .rlk .k{font-weight:800;font-size:10.5px;letter-spacing:.2em;text-transform:uppercase;color:var(--stamp-text)}
.lpb .rlk .v{font-weight:800;font-size:17px;letter-spacing:-.01em;color:var(--ink);margin-top:3px;line-height:1.18}
.lpb .rlk .foot{font-size:12.5px;color:var(--muted);margin:9px 0 0}
.lpb .rlk .foot u{color:var(--cta);text-decoration:none;font-weight:700;border-bottom:1px solid rgba(21,94,122,.35)}
.lpb a.rlk:hover .mono{transform:scale(1.06);box-shadow:inset 0 1px 0 #fff,0 6px 16px -8px rgba(46,154,140,.6)}
</style>
@endpush
@endonce
<a class="rlk" href="{{ $href ?? '/legal#refunds' }}" aria-label="Our Refund Promise: fee back if refused, or a free next application. Read the terms.">
  <div class="lockrow">
    <span class="mono"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z"/><path d="m9 12 2 2 4-4.5"/></svg></span>
    <span><span class="k">Refund Promise</span><span class="v" style="display:block">Refused? Fee back, or a free next application.</span></span>
  </div>
  <p class="foot">You choose which. Government fees excluded, no guarantee of a decision. <u>Read the terms</u>.</p>
</a>
