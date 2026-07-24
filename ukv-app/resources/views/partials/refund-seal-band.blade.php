{{-- Refund Promise wax-seal header band. Crowns a card whose padding is 26px
     and radius 20px (e.g. lp-bold .formcard). Include as the FIRST child.
     Honest copy: no guarantee of a decision; gov fees excluded (see /legal#refunds). --}}
@once
@push('head')
<style>
.lpb .rsb{margin:-26px -26px 22px;padding:18px 24px;background:radial-gradient(120% 140% at 85% 0,#0f4a61,#12233c);border-radius:20px 20px 0 0;color:#fff;display:flex;align-items:center;gap:15px;position:relative;overflow:hidden}
.lpb .rsb::after{content:"";position:absolute;right:-30px;top:-30px;width:128px;height:128px;border:1px solid rgba(121,207,194,.25);border-radius:50%}
.lpb .rsb::before{content:"";position:absolute;right:-8px;bottom:-46px;width:108px;height:108px;border:1px solid rgba(121,207,194,.18);border-radius:50%}
.lpb .rsb .seal{width:50px;height:50px;border-radius:50%;background:radial-gradient(circle at 42% 36%,#3fb3a3,#1f6e63);display:flex;align-items:center;justify-content:center;flex:none;box-shadow:0 6px 16px -6px rgba(0,0,0,.5),inset 0 1px 0 rgba(255,255,255,.4)}
.lpb .rsb .seal svg{width:25px;height:25px;fill:none;stroke:#fff;stroke-width:1.8}
.lpb .rsb .tx{position:relative}
.lpb .rsb .k{font-weight:700;font-size:10px;letter-spacing:.2em;text-transform:uppercase;color:var(--on-dark)}
.lpb .rsb .v{font-weight:800;font-size:17px;letter-spacing:-.01em;margin-top:4px;line-height:1.15}
.lpb .rsb .s{font-size:12.5px;color:#a9c0c8;margin-top:2px}
@media(max-width:420px){.lpb .rsb{padding:16px 20px;gap:12px}.lpb .rsb .v{font-size:15.5px}}
</style>
@endpush
@endonce
<div class="rsb" aria-label="Our Refund Promise">
  <span class="seal"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z"/><path d="m9 12 2 2 4-4.5"/></svg></span>
  <div class="tx">
    <div class="k">Refund Promise</div>
    <div class="v">If it's refused, you're covered.</div>
    <div class="s">Fee back, or a free next application</div>
  </div>
</div>
