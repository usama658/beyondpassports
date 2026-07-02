{{-- Self-contained site topbar + header for the STANDALONE landing pages (lp-*.blade.php).
     These pages don't load assets/ukv.css and define their own inline theme, so the canonical
     partials.site-header can't be reused as-is (it needs ukv.css and collides on .topbar/.nav/.wrap).
     This replica mirrors the home chrome exactly but every class is prefixed `bpc-` (Beyond
     Passports Chrome) so it can NEVER collide with either ukv.css or an lp page's own styles.
     The few CSS custom properties the reused sub-partials (trustpilot-cta) expect are injected,
     scoped to .bpc-topbar, so the rating widget renders identically to home. --}}
<div class="bpc-topbar"><div class="bpc-wrap bpc-tbrow">
  <span class="bpc-tp">@include('partials.trustpilot-cta', ['align' => 'center', 'theme' => 'dark', 'margin' => '0'])</span>
  <span class="bpc-tblinks">
    <a href="tel:{{ config('ukv.phone_e164') ?: '+440000000000' }}">@include('partials.call-glyph')<b>UK Team:</b>&nbsp;{{ config('ukv.phone') ?: '+44' }}</a>
    <a href="tel:{{ config('ukv.phone_de_e164') ?: '+490000000000' }}">@include('partials.call-glyph')<b>Germany Team:</b>&nbsp;{{ config('ukv.phone_de') ?: '+49' }}</a>
    <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}">@include('partials.wa-glyph')WhatsApp</a>
  </span>
</div></div>
<header class="bpc-head"><div class="bpc-wrap">
  <a href="{{ url('/') }}" class="bpc-brand" aria-label="Beyond Passports home"><img src="{{ asset('assets/brand/bp-logo-v2.svg') }}" alt="Beyond Passports" width="150" height="38"></a>
  <button class="bpc-navtoggle" type="button" aria-expanded="false" aria-controls="bpc-nav" aria-label="Open menu" onclick="var n=document.getElementById('bpc-nav'),o=n.classList.toggle('bpc-open');this.setAttribute('aria-expanded',o)">☰</button>
  <nav class="bpc-nav" id="bpc-nav" aria-label="Primary">
    <a href="{{ url('/destinations') }}">Schengen visa</a>
    <a href="{{ url('/services') }}">Services</a>
    <a href="{{ url('/about') }}">About</a>
    <a href="{{ url('/contact') }}" class="bpc-btn bpc-btn--ghost">Contact</a>
    <a href="{{ App\Support\SiteStats::chatUrl() }}" target="_blank" rel="noopener" class="bpc-btn">Check eligibility →</a>
  </nav>
</div></header>
@once
<style>
  /* --- Beyond Passports chrome (self-contained; mirrors home topbar+header in ukv.css) --- */
  .bpc-topbar,.bpc-head{
    /* vars the reused trustpilot-cta widget resolves; scoped so lp theme vars are untouched */
    --bpc-cta:#155E7A;--bpc-ink:#16222E;--bpc-edge:#dde3ec;
    --display:"Outfit",system-ui,sans-serif;--ink:#16222E;--ink-soft:#5d6b76;--paper-edge:#dde3ec;
    font-family:"Outfit",system-ui,sans-serif;box-sizing:border-box}
  .bpc-topbar *,.bpc-head *{box-sizing:border-box}
  .bpc-wrap{max-width:1100px;margin:0 auto;padding:0 24px}
  /* topbar */
  .bpc-topbar{background:#0F4A61;color:rgba(255,255,255,.9);font-size:12.5px;padding:7px 0}
  .bpc-tbrow{display:flex;align-items:center;justify-content:space-between;gap:10px 40px;flex-wrap:wrap}
  .bpc-tp{min-width:0}
  .bpc-tblinks{display:inline-flex;align-items:center;gap:24px;min-width:0}
  .bpc-tblinks a{color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:5px;min-height:24px;white-space:nowrap}
  .bpc-tblinks a:hover{text-decoration:underline}
  .bpc-tblinks b{color:#fff}
  .bpc-tblinks svg{width:15px;height:15px;fill:currentColor}
  /* header */
  .bpc-head{position:sticky;top:0;z-index:50;background:rgba(244,245,246,.92);backdrop-filter:blur(8px);border-bottom:1px solid var(--bpc-edge)}
  .bpc-head .bpc-wrap{display:flex;align-items:center;justify-content:space-between;height:64px}
  .bpc-brand{display:block;line-height:0}
  .bpc-brand img{display:block;height:38px;width:auto}
  .bpc-nav{display:flex;align-items:center;gap:22px}
  .bpc-nav a{color:var(--bpc-ink);font-size:15px;font-weight:600;text-decoration:none;line-height:1}
  .bpc-nav a:hover{color:var(--bpc-cta)}
  .bpc-btn{display:inline-block;background:var(--bpc-cta);color:#fff;font-weight:700;padding:8px 16px;border-radius:12px;border:0;font-size:15px;line-height:1.2;text-decoration:none}
  .bpc-btn:hover{color:#fff}
  .bpc-btn--ghost{background:transparent;color:var(--bpc-cta);border:1.5px solid var(--bpc-cta)}
  .bpc-btn--ghost:hover{color:var(--bpc-cta)}
  .bpc-navtoggle{display:none;background:none;border:1px solid var(--bpc-edge);border-radius:10px;padding:8px 10px;cursor:pointer;font-size:18px;line-height:1;color:var(--bpc-ink)}
  @media (max-width:860px){.bpc-tbrow{justify-content:center;gap:6px 16px}.bpc-tblinks{flex-wrap:wrap;justify-content:center}}
  @media (max-width:900px){
    .bpc-navtoggle{display:inline-flex;align-items:center}
    .bpc-head .bpc-wrap{position:relative;flex-wrap:wrap}
    .bpc-nav{display:none;flex-direction:column;align-items:flex-start;width:100%;gap:10px;padding:10px 0 14px}
    .bpc-nav.bpc-open{display:flex}
    .bpc-btn,.bpc-btn--ghost{width:auto}
  }
</style>
@endonce
