{{-- Refund Promise wax-seal header band. Crowns a card whose padding is 26px
     and radius 20px (e.g. lp-bold .formcard). Include as the FIRST child.
     Pass ['flush' => true] for a padding-less, overflow-hidden card (e.g. the
     dark FAQ .bp boarding-pass): margins zero out, the card clips the corners,
     and a dashed teal rule separates it from the header below.
     Honest copy: no guarantee of a decision; gov fees excluded (see /legal#refunds). --}}
@once
@push('head')
<style>
.lpb .rsb{margin:-26px -26px 22px;padding:18px 24px;background:radial-gradient(120% 140% at 85% 0,#0f4a61,#12233c);background-size:150% 150%;background-position:78% 0;border-radius:20px 20px 0 0;color:#fff;display:flex;align-items:center;gap:15px;position:relative;overflow:hidden;transition:filter .25s ease,background-position .5s ease}
.lpb .rsb::after{content:"";position:absolute;right:-30px;top:-30px;width:128px;height:128px;border:1px solid rgba(121,207,194,.25);border-radius:50%;transition:border-color .3s ease,transform .45s ease}
.lpb .rsb::before{content:"";position:absolute;right:-8px;bottom:-46px;width:108px;height:108px;border:1px solid rgba(121,207,194,.18);border-radius:50%;transition:border-color .3s ease,transform .45s ease}
.lpb a.rsb:hover{background-position:58% 0}
.lpb a.rsb:hover::after{border-color:rgba(121,207,194,.55);transform:scale(1.12)}
.lpb a.rsb:hover::before{border-color:rgba(121,207,194,.42);transform:scale(1.15)}
@media(prefers-reduced-motion:reduce){.lpb a.rsb,.lpb a.rsb:hover{background-position:78% 0}.lpb a.rsb:hover::after,.lpb a.rsb:hover::before{transform:none}}
.lpb .rsb .seal{width:50px;height:50px;border-radius:50%;background:radial-gradient(circle at 42% 36%,#3fb3a3,#1f6e63);display:flex;align-items:center;justify-content:center;flex:none;box-shadow:0 6px 16px -6px rgba(0,0,0,.5),inset 0 1px 0 rgba(255,255,255,.4)}
.lpb .rsb .seal svg{width:25px;height:25px;fill:none;stroke:#fff;stroke-width:1.8}
.lpb .rsb .tx{position:relative}
.lpb .rsb .k{font-weight:700;font-size:10px;letter-spacing:.2em;text-transform:uppercase;color:var(--on-dark)}
.lpb .rsb .v{font-weight:800;font-size:17px;letter-spacing:-.01em;margin-top:4px;line-height:1.15}
.lpb .rsb .s{font-size:12.5px;color:#a9c0c8;margin-top:2px}
.lpb .rsb.flush{margin:0;border-radius:0;padding:18px 30px;border-bottom:1px dashed rgba(121,207,194,.3)}
/* interactive: whole band links to the Refund Promise */
.lpb a.rsb{text-decoration:none;color:#fff;cursor:pointer;transition:filter .2s ease}
.lpb .rsb .seal,.lpb .rsb .arrow{transition:transform .2s ease,box-shadow .2s ease}
.lpb a.rsb:hover{filter:brightness(1.07)}
.lpb a.rsb:hover .seal{transform:scale(1.08) rotate(-4deg);box-shadow:0 9px 22px -6px rgba(0,0,0,.6),inset 0 1px 0 rgba(255,255,255,.55)}
.lpb .rsb .arrow{margin-left:auto;color:var(--on-dark);font-weight:800;font-size:19px;position:relative;z-index:1}
.lpb a.rsb:hover .arrow{transform:translateX(4px)}
.lpb a.rsb:focus-visible{outline:2px solid var(--on-dark);outline-offset:2px}
@media(prefers-reduced-motion:reduce){.lpb a.rsb:hover .seal,.lpb a.rsb:hover .arrow{transform:none}}
@media(max-width:420px){.lpb .rsb{padding:16px 20px;gap:12px}.lpb .rsb .v{font-size:15.5px}.lpb .rsb.flush{padding:16px 22px}}
</style>
@endpush
@endonce
<a class="rsb {{ ($flush ?? false) ? 'flush' : '' }}" href="{{ $href ?? '/legal#refunds' }}" aria-label="Our Refund Promise: fee back if refused, or a free next application. Read the terms.">
  <span class="seal"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z"/><path d="m9 12 2 2 4-4.5"/></svg></span>
  <div class="tx">
    <div class="k">Refund Promise</div>
    <div class="v">If it's refused, you're covered.</div>
    <div class="s">Fee back, or a free next application</div>
  </div>
  <span class="arrow" aria-hidden="true">&rarr;</span>
</a>
