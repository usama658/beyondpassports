{{-- Pricing — Variant B (lifted "Popular", tinted side cards). Self-contained (bpc-pr- prefix).
     Prices hidden (config ukv.pricing.show=false → placeholder); each tier CTA opens WhatsApp
     with a tier-relevant query. Fee disclaimer footer stays honest (embassy fee separate). --}}
@php
  $bpcWa = config('ukv.whatsapp') ?: '447882747584';
  $bpcQuote = config('ukv.pricing.placeholder', 'Get a quote');
  $bpcWaLink = fn($q) => 'https://wa.me/'.$bpcWa.'?text='.rawurlencode('Hi Beyond Passports, '.$q);
  $bpcTiers = [
    ['nm'=>'Basic','feat'=>false,'cta'=>'Book a document review','q'=>'I would like a document review before I submit.',
      'desc'=>'You prepared the application yourself. We check every page before you submit.',
      'li'=>['Full document review against consulate requirements','Bank statement analysis','Cover letter review','Risk assessment with honest feedback']],
    ['nm'=>'Popular','feat'=>true,'badge'=>'Most chosen','cta'=>'Start full application','q'=>'I would like full application preparation.',
      'desc'=>'Cover letter, itinerary, financial structuring, full preparation. We review, you submit.',
      'li'=>['Everything in Basic','Cover letter written for your case','Itinerary planning and hotel guidance','Appointment booking assistance','Tracking until decision']],
    ['nm'=>'Advanced','feat'=>false,'cta'=>'Ask about reapplication','q'=>'I was refused before and would like to ask about reapplication.',
      'desc'=>'Different strategy required. A rebuild, not a retry.',
      'li'=>['Prior refusal analysis','New application narrative','Evidence strategy for the real weakness','Tracking until decision']],
  ];
@endphp
<section class="bpc-pr" id="pricing"><div class="bpc-pr-wrap">
  <p class="bpc-pr-eyebrow">Pricing</p>
  <h2 class="bpc-pr-h">What it costs.</h2>
  <p class="bpc-pr-sub">Clear service fees. The embassy fee is separate. No payment until after your risk check.</p>
  <div class="bpc-pr-grid">
    @foreach ($bpcTiers as $t)
    <div class="bpc-pr-card{{ $t['feat'] ? ' bpc-pr-feat' : '' }}">
      @if ($t['feat'])<span class="bpc-pr-badge">{{ $t['badge'] }}</span>@endif
      <div class="bpc-pr-nm">{{ $t['nm'] }}</div>
      <div class="bpc-pr-amt">{{ $bpcQuote }}</div>
      <p class="bpc-pr-desc">{{ $t['desc'] }}</p>
      <ul>@foreach ($t['li'] as $x)<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l4 4L19 6"/></svg>{{ $x }}</li>@endforeach</ul>
      <a class="bpc-pr-btn" href="{{ $bpcWaLink($t['q']) }}" target="_blank" rel="noopener">{{ $t['cta'] }}</a>
    </div>
    @endforeach
  </div>
  <p class="bpc-pr-foot">Embassy fee is separate and goes directly to the authorities. We never touch it. No payment until after your risk check.</p>
</div></section>
@once
<style>
  .bpc-pr{font-family:"Outfit",system-ui,sans-serif;color:#16222E;padding:46px 0;background:linear-gradient(180deg,#FBFDFD,#F4F6FA);border-top:1px solid #dde3ec;box-sizing:border-box}
  .bpc-pr *{box-sizing:border-box}
  .bpc-pr-wrap{max-width:1120px;margin:0 auto;padding:0 24px}
  .bpc-pr-eyebrow{text-align:center;font:700 12px/1 system-ui;letter-spacing:.12em;text-transform:uppercase;color:#155E7A;margin:0 0 8px}
  .bpc-pr-h{text-align:center;font:800 clamp(24px,3.4vw,34px)/1.1 "Outfit",system-ui;letter-spacing:-.02em;margin:0 0 6px}
  .bpc-pr-sub{text-align:center;color:#5d6b76;font-size:15px;margin:0 auto 34px;max-width:52ch}
  .bpc-pr-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;align-items:start}
  .bpc-pr-card{border:1px solid #dde3ec;border-radius:18px;background:#fbfdfd;padding:26px 24px;display:flex;flex-direction:column;box-shadow:0 18px 40px -30px rgba(40,50,70,.5)}
  .bpc-pr-feat{background:#fff;border:2px solid #2E9A8C;box-shadow:0 34px 70px -34px rgba(46,154,140,.55);position:relative;transform:translateY(-8px)}
  .bpc-pr-badge{position:absolute;top:-13px;left:50%;transform:translateX(-50%);background:#2E9A8C;color:#fff;font:800 11px system-ui;letter-spacing:.06em;text-transform:uppercase;padding:6px 14px;border-radius:999px;white-space:nowrap}
  .bpc-pr-nm{font:800 15px system-ui;letter-spacing:.02em;text-transform:uppercase;color:#155E7A}
  .bpc-pr-feat .bpc-pr-nm{color:#2E9A8C}
  .bpc-pr-amt{font:800 24px/1 "Outfit",system-ui;letter-spacing:-.02em;margin:10px 0 4px;color:#5d6b76}
  .bpc-pr-desc{color:#46505a;font-size:14px;line-height:1.5;margin:6px 0 18px;min-height:60px}
  .bpc-pr-card ul{list-style:none;padding:0;margin:0 0 22px;display:flex;flex-direction:column;gap:11px}
  .bpc-pr-card li{display:flex;gap:10px;font-size:14px;line-height:1.4;color:#33454f}
  .bpc-pr-card li svg{width:18px;height:18px;flex:none;color:#2E9A8C}
  .bpc-pr-btn{margin-top:auto;display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#155E7A;color:#fff;text-decoration:none;font-weight:700;font-size:14.5px;padding:13px 18px;border-radius:12px}
  .bpc-pr-btn:hover{background:#124e63}
  .bpc-pr-feat .bpc-pr-btn{background:#25D366}.bpc-pr-feat .bpc-pr-btn:hover{background:#1da851}
  .bpc-pr-foot{text-align:center;color:#5d6b76;font-size:13px;line-height:1.6;margin:26px auto 0;max-width:64ch}
  @media(max-width:820px){.bpc-pr-grid{grid-template-columns:1fr}.bpc-pr-feat{transform:none}}
</style>
@endonce
