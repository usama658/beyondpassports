{{-- Refund Promise standalone band (premium gradient section). Drop inside a
     .wrap so it stays contained. Self-contained @once CSS; no font CDN.
     Honest copy: no guarantee of a decision; gov fees excluded (see /legal#refunds). --}}
@once
@push('head')
<style>
.lpb .rband{position:relative;border-radius:20px;padding:30px 34px;overflow:hidden;color:#fff;margin-top:44px;
  background:linear-gradient(135deg,#12233c 0%,#0f4a61 62%,#1f6e63 100%);box-shadow:var(--sh2)}
.lpb .rband::after{content:"";position:absolute;inset:0;background:radial-gradient(90% 160% at 100% 0,rgba(121,207,194,.28),transparent 55%);pointer-events:none}
.lpb .rband .in{position:relative;display:flex;align-items:center;gap:26px;flex-wrap:wrap}
.lpb .rband .badge{width:60px;height:60px;border-radius:16px;background:rgba(255,255,255,.1);border:1px solid rgba(121,207,194,.4);display:flex;align-items:center;justify-content:center;flex:none}
.lpb .rband .badge svg{width:30px;height:30px;fill:none;stroke:#fff;stroke-width:1.8}
.lpb .rband .tx{flex:1;min-width:260px}
.lpb .rband .k{font-weight:700;font-size:10.5px;letter-spacing:.22em;text-transform:uppercase;color:var(--on-dark);margin-bottom:8px}
.lpb .rband .tx h3{font-weight:800;font-size:23px;letter-spacing:-.02em;line-height:1.16;margin:0}
.lpb .rband .pills{display:flex;gap:9px;margin-top:14px;flex-wrap:wrap}
.lpb .rband .pill{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.11);border:1px solid rgba(255,255,255,.16);border-radius:999px;padding:7px 13px;font-size:13px;font-weight:600}
.lpb .rband .pill svg{width:14px;height:14px;fill:none;stroke:var(--on-dark);stroke-width:2}
.lpb .rband .go{display:inline-flex;align-items:center;gap:9px;background:#fff;color:var(--ink);border-radius:12px;padding:13px 20px;font-weight:700;font-size:15px;white-space:nowrap;text-decoration:none;flex:none}
.lpb .rband .go:hover{background:#eef4f3}
.lpb .rband .go svg,.lpb .rband .go .wa-g{width:18px;height:18px;fill:var(--wa);flex:none}
@media(max-width:720px){.lpb .rband{padding:26px 22px}.lpb .rband .go{width:100%;text-align:center}}
</style>
@endpush
@endonce
<div class="rband">
  <div class="in">
    <span class="badge"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z"/><path d="m9 12 2 2 4-4.5"/></svg></span>
    <div class="tx">
      <div class="k">Our Refund Promise</div>
      <h3>{{ $heading ?? "Refused? Your service fee comes back, or your next application is free." }}</h3>
      <div class="pills">
        <span class="pill"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>100% service fee back</span>
        <span class="pill"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>or a free next application</span>
        <span class="pill"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>you choose</span>
      </div>
    </div>
    <a class="go" href="{{ $ctaHref ?? '/legal#refunds' }}">@if(($ctaIcon ?? null) === 'wa')@include('partials.wa-glyph')@endif{{ $ctaText ?? 'How it works' }} &rarr;</a>
  </div>
</div>
