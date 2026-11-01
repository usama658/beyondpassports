{{-- Appointment availability board — Variant B (horizontal scroll strip) for the standalone LPs.
     Self-contained (bpc-bd- prefix). Each country card's "Book now" opens WhatsApp with a
     per-country booking query. Honest board: manual wait times, "updated daily". --}}
@php
  $bpcWa = config('ukv.whatsapp') ?: '447882747584';
  $bpcBoard = [
    ['co'=>'Germany','ce'=>'London, TLScontact','wt'=>'5 working days','s'=>'Fast','c'=>'fast','fl'=>'linear-gradient(180deg,#000 33%,#D00 33% 66%,#FFCE00 66%)'],
    ['co'=>'Spain','ce'=>'BLS London','wt'=>'10 to 20 days','s'=>'Open','c'=>'go','fl'=>'linear-gradient(180deg,#AA151B 25%,#F1BF00 25% 75%,#AA151B 75%)'],
    ['co'=>'Greece','ce'=>'London','wt'=>'7 to 14 days','s'=>'Open','c'=>'go','fl'=>'linear-gradient(180deg,#0D5EAF,#fff)'],
    ['co'=>'Netherlands','ce'=>'Limited','wt'=>'Mid July','s'=>'Limited','c'=>'lim','fl'=>'linear-gradient(180deg,#AE1C28 33%,#fff 33% 66%,#21468B 66%)'],
    ['co'=>'Italy','ce'=>'Peak season','wt'=>'4 to 8 weeks','s'=>'Peak','c'=>'peak','fl'=>'linear-gradient(90deg,#009246 33%,#fff 33% 66%,#CE2B37 66%)'],
    ['co'=>'France','ce'=>'Backlogged','wt'=>'4 to 8 weeks','s'=>'Peak','c'=>'peak','fl'=>'linear-gradient(90deg,#0055A4 33%,#fff 33% 66%,#EF4135 66%)'],
  ];
  $bpcWaLink = fn($co) => 'https://wa.me/'.$bpcWa.'?text='.rawurlencode('Hi Beyond Passports, I would like to book my '.$co.' Schengen appointment.');
@endphp
<section class="bpc-bd"><div class="bpc-bd-wrap">
  <span class="bpc-bd-eyebrow">Current availability</span>
  <h2 class="bpc-bd-h">Your appointment wait depends on which door you knock on</h2>
  <p class="bpc-bd-sub">Live wait times across Schengen centres. <span class="bpc-bd-upd"><span class="bpc-bd-dot"></span> Updated daily</span></p>
  <div class="bpc-bd-scroll">
    @foreach ($bpcBoard as $r)
    <div class="bpc-bd-card">
      <div class="bpc-bd-top"><span class="bpc-bd-co"><span class="bpc-bd-fl" style="background:{{ $r['fl'] }}"></span>{{ $r['co'] }}</span><span class="bpc-bd-chip bpc-bd-{{ $r['c'] }}">{{ $r['s'] }}</span></div>
      <div class="bpc-bd-ce">{{ $r['ce'] }}</div>
      <div class="bpc-bd-wt">{{ $r['wt'] }}</div>
      <a class="bpc-bd-book" href="{{ $bpcWaLink($r['co']) }}" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24z"/></svg>Book now</a>
    </div>
    @endforeach
  </div>
</div></section>
@once
<style>
  .bpc-bd{font-family:"Outfit",system-ui,sans-serif;color:#16222E;padding:34px 0;background:#fff;box-sizing:border-box}
  .bpc-bd *{box-sizing:border-box}
  .bpc-bd-wrap{max-width:1120px;margin:0 auto;padding:0 24px}
  .bpc-bd-eyebrow{font:700 12px/1 system-ui;letter-spacing:.12em;text-transform:uppercase;color:#155E7A;background:#eef4f6;display:inline-block;padding:7px 12px;border-radius:999px;margin-bottom:12px}
  .bpc-bd-h{font:800 clamp(20px,2.6vw,28px)/1.12 "Outfit",system-ui;letter-spacing:-.02em;margin:0 0 4px}
  .bpc-bd-sub{color:#5d6b76;font-size:14px;margin:0 0 18px}
  .bpc-bd-upd{display:inline-flex;align-items:center;gap:6px;font-weight:600;font-size:12px}
  .bpc-bd-dot{width:8px;height:8px;border-radius:50%;background:#25D366;box-shadow:0 0 0 3px rgba(37,211,102,.18)}
  .bpc-bd-scroll{display:flex;gap:14px;overflow-x:auto;padding:4px 2px 10px;-webkit-overflow-scrolling:touch}
  .bpc-bd-card{flex:0 0 212px;border:1px solid #dde3ec;border-radius:14px;padding:16px;background:linear-gradient(180deg,#fff,#fbfdfd)}
  .bpc-bd-top{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:10px}
  .bpc-bd-co{font-weight:800;font-size:16px;display:flex;align-items:center;gap:7px}
  .bpc-bd-fl{width:22px;height:15px;border-radius:3px;display:inline-block;flex:none;box-shadow:0 0 0 1px rgba(0,0,0,.06)}
  .bpc-bd-ce{font-size:12px;color:#5d6b76;margin:0 0 6px}
  .bpc-bd-wt{font-weight:800;font-size:20px;letter-spacing:-.02em;margin:2px 0 14px}
  .bpc-bd-chip{font:700 11px/1 system-ui;letter-spacing:.04em;text-transform:uppercase;padding:5px 9px;border-radius:999px;white-space:nowrap}
  .bpc-bd-fast{background:#e6f6ee;color:#1d7a4d}.bpc-bd-go{background:#e5f1f0;color:#1f6e63}.bpc-bd-lim{background:#fbf1dd;color:#B7791F}.bpc-bd-peak{background:#f9e5e0;color:#C0492B}
  .bpc-bd-book{display:flex;align-items:center;justify-content:center;gap:7px;background:#25D366;color:#fff;text-decoration:none;font-weight:700;font-size:13px;padding:10px 14px;border-radius:10px;width:100%}
  .bpc-bd-book:hover{background:#1da851}
  .bpc-bd-book svg{width:15px;height:15px;fill:#fff;flex:none}
</style>
@endonce
