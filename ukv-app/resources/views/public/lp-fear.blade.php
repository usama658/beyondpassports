@php $wa = 'https://wa.me/'.(config('ukv.whatsapp') ?: '440000000000'); @endphp
{{-- LP /schengen-visa-refusal-risk (site warm-light reskin). Orphan: noindex, not in nav/footer/sitemap. CTAs read config('ukv.whatsapp'). --}}
<!doctype html><html lang="en-GB"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex, nofollow"><link rel="icon" href="{{ asset('assets/brand/favicon.svg') }}" type="image/svg+xml"><link rel="icon" href="{{ asset('assets/brand/favicon.ico') }}" sizes="any"><link rel="apple-touch-icon" href="{{ asset('assets/brand/apple-touch-icon.png') }}"><meta name="theme-color" content="#155E7A"><title>A Schengen refusal stays on your record for 5 years | Beyond Passports</title><style>@font-face{font-family:'Outfit';src:url('/fonts/outfit-400.woff2') format('woff2');font-weight:400;font-display:swap}@font-face{font-family:'Outfit';src:url('/fonts/outfit-600.woff2') format('woff2');font-weight:600;font-display:swap}@font-face{font-family:'Outfit';src:url('/fonts/outfit-700.woff2') format('woff2');font-weight:700;font-display:swap}@font-face{font-family:'Outfit';src:url('/fonts/outfit-800.woff2') format('woff2');font-weight:800;font-display:swap}
:root{
  --bg:#0A1628; --bg2:#0c1a30; --surf:#0d1c36; --surf2:#13284c;
  --line:rgba(255,255,255,.09); --line2:rgba(255,255,255,.16);
  --ink:#ECF2FB; --mut:#9DB1CE; --mut2:#6E84A6;
  --red:#FF5A5A; --redbg:rgba(255,90,90,.10);
  --grn:#25D366; --grn2:#1faa52;
  --teal:#39B89C; --petrol:#2C7E9B; --gold:#D8B871;
  --rad:18px; --rad2:26px; --wrap:1080px;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--ink);font-family:'Outfit',system-ui,Segoe UI,Roboto,sans-serif;
  line-height:1.6;-webkit-font-smoothing:antialiased;overflow-x:hidden}
.wrap{max-width:var(--wrap);margin:0 auto;padding:0 24px}
section{position:relative}
.pad{padding:60px 0}
.pad-sm{padding:48px 0}
h1,h2,h3{line-height:1.12;letter-spacing:-.02em;font-weight:800}
h1{font-size:clamp(2.1rem,5.2vw,3.6rem)}
h2{font-size:clamp(1.7rem,3.6vw,2.6rem)}
h3{font-size:1.22rem;font-weight:700;letter-spacing:-.01em}
p{color:var(--mut)}
a{color:inherit;text-decoration:none}
.lead{font-size:1.18rem;color:#C6D5EC;max-width:60ch}
/* top bar */
.topbar{background:linear-gradient(90deg,var(--grn),var(--grn2));color:#04140a;font-weight:700;
  text-align:center;font-size:.92rem;padding:10px 16px;letter-spacing:.01em}
.topbar b{color:#04140a}
.nav{position:sticky;top:0;z-index:40;background:rgba(10,22,40,.82);backdrop-filter:blur(12px);
  border-bottom:1px solid var(--line)}
.nav .wrap{display:flex;align-items:center;justify-content:space-between;height:68px}
.brand{display:flex;align-items:center;gap:11px;font-weight:800;font-size:1.18rem;letter-spacing:-.01em}
.brand .mark{width:34px;height:34px;flex:0 0 34px}
.brand b{font-weight:800}.brand span{font-weight:500;color:var(--mut)}
.navcta{font-size:.86rem;font-weight:700;color:#04140a;background:var(--grn);padding:9px 16px;border-radius:999px}
.navpill{display:flex;gap:4px;background:rgba(255,255,255,.04);border:1px solid var(--line);border-radius:999px;padding:5px}
.navpill .nl{color:var(--mut);font-weight:600;font-size:.9rem;padding:8px 16px;border-radius:999px;text-decoration:none}
.navpill .nl:hover,.navpill .nl.active{background:rgba(57,184,156,.16);color:#bdeede}
@media(max-width:960px){.navpill{display:none}}
span[id]{scroll-margin-top:84px;display:block}
.teamline{display:inline-flex;align-items:center;gap:9px;font-weight:700;color:var(--mut)}
.teamline .tlchip{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.05);border:1px solid var(--line);padding:6px 11px;border-radius:999px;font-size:.85rem;color:var(--ink)}
.teamline .tlchip .flag{width:22px;height:14px;border-radius:2px;display:block}
/* label */
.label{display:inline-flex;align-items:center;gap:8px;font-size:.74rem;font-weight:800;letter-spacing:.18em;
  text-transform:uppercase;color:var(--red);margin-bottom:18px}
.label::before{content:"";width:26px;height:2px;background:var(--red)}
.label.teal{color:var(--teal)}.label.teal::before{background:var(--teal)}
.label.gold{color:var(--gold)}.label.gold::before{background:var(--gold)}
/* hero */
.hero{position:relative;overflow:hidden;border-bottom:1px solid var(--line);
  background:radial-gradient(900px 480px at 80% -8%,rgba(44,126,155,.20),transparent 60%) center/cover no-repeat}
.hero::before{content:"";position:absolute;inset:0;
  background:linear-gradient(90deg,rgba(10,22,40,.97),rgba(10,22,40,.9) 48%,rgba(10,22,40,.74)),
             radial-gradient(900px 480px at 78% -8%,rgba(44,126,155,.28),transparent 60%),
             radial-gradient(700px 420px at 8% 110%,rgba(57,184,156,.14),transparent 60%);pointer-events:none}
.hero .wrap{position:relative;padding:96px 24px 84px}
.hero h1{max-width:18ch}
.hero h1 .hl{color:var(--red)}
.hero .lead{margin-top:22px}
/* buttons */
.btn{display:inline-flex;align-items:center;gap:11px;font-weight:800;font-size:1.02rem;
  padding:16px 28px;border-radius:14px;cursor:pointer;border:0;transition:transform .15s ease,box-shadow .15s}
.btn-wa{background:linear-gradient(180deg,#2bdc6e,#1faa52);color:#04140a;
  box-shadow:0 14px 34px -12px rgba(37,211,102,.6)}
.btn-wa:hover{transform:translateY(-2px);box-shadow:0 18px 40px -10px rgba(37,211,102,.7)}
.btn-wa .wi{width:20px;height:20px;display:inline-block}
.btn-block{width:100%;justify-content:center}
.btn-ghost{background:transparent;border:1px solid var(--line2);color:var(--ink)}
.sub{font-size:.9rem;color:var(--mut2);margin-top:12px}
.cta-row{margin-top:30px;display:flex;flex-wrap:wrap;gap:14px;align-items:center}
/* grid + cards */
.grid{display:grid;gap:18px}
.g2{grid-template-columns:repeat(2,1fr)}.g3{grid-template-columns:repeat(3,1fr)}.g4{grid-template-columns:repeat(4,1fr)}
.card{background:linear-gradient(180deg,var(--surf2),var(--surf));border:1px solid var(--line);
  border-radius:var(--rad);padding:26px}
.card .n{font-size:.78rem;font-weight:800;letter-spacing:.14em;color:var(--mut2);text-transform:uppercase}
.card h3{margin:.4rem 0 .55rem}
.card p{font-size:.98rem}
.callout{background:var(--redbg);border:1px solid rgba(255,90,90,.3);border-left:4px solid var(--red);
  border-radius:14px;padding:24px 26px;margin-top:26px}
.callout p{color:#F4D6D6}
/* board */
.board{background:#06101f;border:1px solid var(--line);border-radius:var(--rad2);overflow:hidden;
  box-shadow:0 30px 80px -40px rgba(0,0,0,.9)}
.board .bh{display:flex;justify-content:space-between;align-items:center;padding:18px 24px;
  border-bottom:1px solid var(--line);background:linear-gradient(180deg,#0a1830,#06101f)}
.board .bh .dot{width:9px;height:9px;border-radius:50%;background:var(--grn);box-shadow:0 0 12px var(--grn);display:inline-block;margin-right:8px}
.board .bh small{color:var(--mut2);font-weight:700;letter-spacing:.1em;text-transform:uppercase;font-size:.72rem}
.brow{display:grid;grid-template-columns:1.4fr 1fr auto;gap:16px;align-items:center;padding:17px 24px;
  border-bottom:1px solid var(--line);font-variant-numeric:tabular-nums}
.brow:last-child{border-bottom:0}
.brow .co{font-weight:700;font-size:1.06rem;display:flex;align-items:center;gap:11px}
.brow .flag{width:26px;height:18px;border-radius:3px;flex:0 0 26px;background:var(--surf2);overflow:hidden;border:1px solid var(--line)}
.brow .wait{color:var(--mut);font-size:.95rem}
.brow .tag{justify-self:end;font-weight:800;font-size:.82rem;padding:6px 13px;border-radius:999px}
.tag.go{color:#062;background:rgba(37,211,102,.16);color:#7ef0a8}
.tag.mid{color:#f5d98a;background:rgba(216,184,113,.14)}
.tag.slow{color:#ffb4b4;background:rgba(255,90,90,.14)}
.tag.fast{color:#9fe9ff;background:rgba(57,184,156,.16)}
/* stats */
.stat{background:linear-gradient(180deg,var(--surf2),var(--surf));border:1px solid var(--line);
  border-radius:var(--rad);padding:30px 24px;text-align:center}
.stat .big{font-size:clamp(2rem,4vw,2.9rem);font-weight:800;letter-spacing:-.03em;
  background:linear-gradient(180deg,#fff,#bcd2ef);-webkit-background-clip:text;background-clip:text;color:transparent}
.stat .lab{color:var(--mut);font-size:.95rem;margin-top:8px}
.stat .big.go{background:none;-webkit-text-fill-color:#7ef0a8;color:#7ef0a8}
.stat .big.slow{background:none;-webkit-text-fill-color:#ffb4b4;color:#ffb4b4}
.stat .big.gold{background:none;-webkit-text-fill-color:var(--gold);color:var(--gold)}
/* steps */
.step{display:grid;grid-template-columns:64px 1fr;gap:22px;padding:26px 0;border-top:1px solid var(--line)}
.step .num{width:54px;height:54px;border-radius:14px;display:flex;align-items:center;justify-content:center;
  font-weight:800;font-size:1.2rem;background:linear-gradient(180deg,var(--surf2),var(--surf));
  border:1px solid var(--line2);color:var(--teal)}
.step h3{margin-bottom:6px}
.step .meta{display:inline-block;margin-top:10px;font-size:.82rem;font-weight:700;color:var(--gold);
  background:rgba(216,184,113,.1);border:1px solid rgba(216,184,113,.25);padding:4px 12px;border-radius:999px}
/* chat proof */
.chat{background:var(--surf);border:1px solid var(--line);border-left:4px solid var(--grn);
  border-radius:14px;padding:22px 24px}
.chat p{color:#D9E5F5;font-size:1.02rem}
.chat .who{margin-top:12px;font-weight:700;color:var(--teal);font-size:.92rem}
/* pricing */
.price{display:flex;flex-direction:column;background:linear-gradient(180deg,var(--surf2),var(--surf));
  border:1px solid var(--line);border-radius:var(--rad2);padding:30px 26px;position:relative}
.price.feat{border-color:rgba(57,184,156,.5);box-shadow:0 24px 60px -34px rgba(57,184,156,.5)}
.price.feat::after{content:"Most chosen";position:absolute;top:-12px;right:22px;font-size:.72rem;font-weight:800;
  letter-spacing:.1em;text-transform:uppercase;background:var(--teal);color:#04140a;padding:5px 12px;border-radius:999px}
.price .pt{font-size:.82rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--mut2)}
.price .amt{font-size:2.6rem;font-weight:800;letter-spacing:-.03em;margin:8px 0 4px}
.price .amt small{font-size:1rem;color:var(--mut);font-weight:600}
.price .desc{font-size:.95rem;color:var(--mut);min-height:0}
.price ul{list-style:none;margin:18px 0 22px;display:flex;flex-direction:column;gap:10px}
.price li{position:relative;padding-left:26px;font-size:.95rem;color:#CDDBEE}
.price li::before{content:"";position:absolute;left:0;top:8px;width:14px;height:8px;border-left:2px solid var(--teal);
  border-bottom:2px solid var(--teal);transform:rotate(-45deg)}
.price .foot{margin-top:auto}
.note{text-align:center;color:var(--mut2);font-size:.92rem;margin-top:22px;max-width:64ch;margin-left:auto;margin-right:auto}
/* faq */
.faq{border-top:1px solid var(--line);padding:24px 0}
.faq h3{margin-bottom:8px;font-size:1.08rem}
.faq p{font-size:.98rem}
/* section heading block */
.sh{max-width:60ch;margin-bottom:40px}
.sh h2 .hl{color:var(--teal)}
.center{text-align:center;margin-left:auto;margin-right:auto}
/* footer */
.foot{border-top:1px solid var(--line);padding:54px 0;color:var(--mut2);font-size:.88rem;text-align:center}
.foot .wrap{display:flex;flex-direction:column;align-items:center;gap:16px}
.foot .brand{justify-content:center}
.disc{max-width:74ch;line-height:1.55;margin:0 auto}
@media(max-width:860px){.g2,.g3,.g4{grid-template-columns:1fr}.brow{grid-template-columns:1fr auto}.brow .wait{display:none}
 .nav .navcta{display:none}.hero .wrap{padding:64px 22px}}

/* live wait-times marquee */
.dot{width:9px;height:9px;border-radius:50%;display:inline-block;flex:0 0 9px}
.dot.live{background:var(--grn);box-shadow:0 0 10px var(--grn)}
.dot.fast{background:#39B89C;box-shadow:0 0 8px rgba(57,184,156,.7)}
.dot.go{background:#7ef0a8}.dot.mid{background:#f5d98a}.dot.slow{background:#ff8f8f}
.ticker{border:1px solid var(--line);border-radius:16px;background:#06101f;overflow:hidden;
  box-shadow:0 24px 60px -40px rgba(0,0,0,.9)}
.ticker .thead{display:flex;align-items:center;gap:10px;padding:13px 20px;border-bottom:1px solid var(--line);
  background:linear-gradient(180deg,#0a1830,#06101f);font-weight:700;font-size:.92rem}
.ticker .thead small{margin-left:auto;color:var(--mut2);font-weight:700;letter-spacing:.12em;
  text-transform:uppercase;font-size:.68rem}
.tickwrap{position:relative;overflow:hidden;-webkit-mask-image:linear-gradient(90deg,transparent,#000 6%,#000 94%,transparent);
  mask-image:linear-gradient(90deg,transparent,#000 6%,#000 94%,transparent)}
.ticktrack{display:flex;width:max-content;animation:tickscroll 34s linear infinite}
.tickwrap:hover .ticktrack{animation-play-state:paused}
.tick{display:flex;align-items:center;gap:11px;padding:15px 26px;border-right:1px solid var(--line);white-space:nowrap;font-variant-numeric:tabular-nums}
.tick .c{font-weight:700;font-size:1.02rem}
.tick .tn{color:var(--mut2);font-size:.82rem}
.tick .d{font-weight:800;font-size:.92rem;padding:4px 11px;border-radius:999px;margin-left:4px}
.tick .d.fast{color:#9fe9ff;background:rgba(57,184,156,.16)}
.tick .d.go{color:#7ef0a8;background:rgba(37,211,102,.14)}
.tick .d.mid{color:#f5d98a;background:rgba(216,184,113,.14)}
.tick .d.slow{color:#ffb4b4;background:rgba(255,90,90,.14)}
@keyframes tickscroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}
@media(prefers-reduced-motion:reduce){.ticktrack{animation:none}}
/* trustpilot widget */
.tp{display:inline-flex;align-items:center;gap:10px;background:#fff;color:#191919;border-radius:10px;
  padding:9px 14px;font-weight:700;font-size:.9rem}
.tp .tlog{display:inline-flex;align-items:center;gap:6px;font-weight:800;letter-spacing:-.01em}
.tp .stars{display:inline-flex;gap:3px}
.tp .sq{width:21px;height:21px;background:#00b67a;display:inline-flex;align-items:center;justify-content:center;border-radius:3px}
.tp .sq svg{width:14px;height:14px;fill:#fff}
.tp .cap{color:#5a5a5a;font-weight:500;font-size:.82rem}
.tprow{display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-top:18px}
.tprow .tpnote{color:var(--mut2);font-size:.78rem;max-width:30ch}
/* split hero + vertical wait-times carousel */
.hsplit{display:grid;grid-template-columns:1.05fr .95fr;gap:46px;align-items:center}
.vboard{background:#06101f;border:1px solid var(--line);border-radius:20px;overflow:hidden;
  box-shadow:0 30px 80px -40px rgba(0,0,0,.9)}
.vboard .phead{display:flex;align-items:center;gap:10px;padding:16px 22px;border-bottom:1px solid var(--line);
  font-weight:700;font-size:.92rem;background:linear-gradient(180deg,#0a1830,#06101f)}
.vboard .phead small{margin-left:auto;color:var(--mut2);font-weight:700;letter-spacing:.1em;text-transform:uppercase;font-size:.66rem}
.vbwrap{height:430px;overflow:hidden; /* exactly 5 rows tall (5 x 86px) */
  -webkit-mask-image:linear-gradient(180deg,transparent 0,#000 14px,#000 calc(100% - 14px),transparent 100%);
  mask-image:linear-gradient(180deg,transparent 0,#000 14px,#000 calc(100% - 14px),transparent 100%)}
.vbtrack{display:flex;flex-direction:column;animation:vscroll 20s linear infinite}
.vbset{display:flex;flex-direction:column}
.vbwrap:hover .vbtrack{animation-play-state:paused}
.prow{display:grid;grid-template-columns:1fr auto auto;gap:14px;align-items:center;padding:0 20px;height:86px;flex:0 0 86px;border-bottom:1px solid var(--line)}
.prow .pco{font-weight:700;font-size:1.05rem;display:flex;flex-direction:column;line-height:1.25}
.prow .pco .bn{font-weight:500;color:var(--mut2);font-size:.8rem}
.prow .pd{font-weight:800;font-size:.88rem;padding:6px 12px;border-radius:999px;white-space:nowrap}
.prow .pchk{display:inline-flex;align-items:center;gap:6px;font-weight:800;font-size:.8rem;padding:9px 14px;border-radius:10px;background:linear-gradient(180deg,#2bdc6e,#1faa52);color:#04140a;white-space:nowrap;box-shadow:0 10px 22px -14px rgba(37,211,102,.6)}
.prow .pchk .wi{width:14px;height:14px}
.pd.fast{color:#9fe9ff;background:rgba(57,184,156,.16)}.pd.go{color:#7ef0a8;background:rgba(37,211,102,.14)}
.pd.mid{color:#f5d98a;background:rgba(216,184,113,.14)}.pd.slow{color:#ffb4b4;background:rgba(255,90,90,.14)}
@keyframes vscroll{from{transform:translateY(0)}to{transform:translateY(-50%)}}
/* reduced motion: stop the scroll, drop the duplicate set, grow the box so all rows show */
@media(prefers-reduced-motion:reduce){.vbtrack{animation:none}.vbwrap{height:auto;-webkit-mask-image:none;mask-image:none}.vbset[aria-hidden]{display:none}}
@media(max-width:880px){.hsplit{grid-template-columns:1fr;gap:30px}}
/* trust bar — authority row */
.tbwrap{display:grid;grid-template-columns:1.15fr 2fr;gap:22px;align-items:stretch}
.tbadge{display:flex;align-items:center;gap:16px;background:linear-gradient(135deg,rgba(57,184,156,.16),rgba(44,126,155,.10));border:1px solid rgba(57,184,156,.4);border-radius:18px;padding:24px}
.tbadge .sh{width:50px;height:50px;flex:0 0 50px;color:var(--teal);display:flex;align-items:center;justify-content:center;border:1px solid rgba(57,184,156,.5);border-radius:14px;background:rgba(10,22,40,.4)}
.tbadge .sh svg{width:28px;height:28px}
.tbadge b{font-size:1.05rem;display:block}
.tbadge .meta{color:var(--mut);font-size:.85rem;margin:2px 0 6px}
.tbadge a{color:var(--teal);font-size:.85rem;font-weight:700}
.tbstats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.tbstats .s{background:linear-gradient(180deg,var(--surf2),var(--surf));border:1px solid var(--line);border-radius:16px;padding:20px;text-align:center;display:flex;flex-direction:column;justify-content:center}
.tbstats .num{font-size:1.9rem;font-weight:800;letter-spacing:-.03em;background:linear-gradient(180deg,#fff,#bcd2ef);-webkit-background-clip:text;background-clip:text;color:transparent}
.tbstats .lab{color:var(--mut);font-size:.82rem;margin-top:4px}
@media(max-width:880px){.tbwrap{grid-template-columns:1fr}}
@media(max-width:560px){.tbstats{grid-template-columns:1fr}}
/* track record — dark premium panel */
.trpanel{background:linear-gradient(180deg,#0b1c34,#081429);border:1px solid var(--line);border-radius:24px;padding:48px;text-align:center;position:relative;overflow:hidden}
.trpanel::before{content:"";position:absolute;inset:0;background:radial-gradient(600px 300px at 50% -20%,rgba(57,184,156,.22),transparent 60%);pointer-events:none}
.trpanel>*{position:relative}
.trpanel .label{margin-left:auto;margin-right:auto;width:max-content}
.trrow{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;margin:30px 0 24px}
.trrow .s{border-left:1px solid var(--line);padding:6px 0}.trrow .s:first-child{border-left:0}
.trrow .num{font-size:2.6rem;font-weight:800;letter-spacing:-.03em;background:linear-gradient(180deg,#fff,#bcd2ef);-webkit-background-clip:text;background-clip:text;color:transparent}
.trrow .l{color:var(--mut);font-size:.86rem;margin-top:6px}
@media(max-width:760px){.trrow{grid-template-columns:1fr;gap:14px}.trrow .s{border-left:0;border-top:1px solid var(--line);padding-top:14px}.trrow .s:first-child{border-top:0}}
/* pricing — comparison table */
.ptbl{width:100%;border-collapse:separate;border-spacing:0;background:linear-gradient(180deg,var(--surf2),var(--surf));border:1px solid var(--line);border-radius:18px;overflow:hidden}
.ptbl th,.ptbl td{padding:17px 20px;text-align:left;border-bottom:1px solid var(--line)}
.ptbl tfoot .btn{white-space:nowrap}
.ptbl thead th{background:#0a1830;vertical-align:bottom;text-align:center}
.ptbl thead th:first-child{text-align:left}
.ptbl thead .pt{display:block;font-size:.78rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--mut2)}
.ptbl thead th.feat .pt{color:var(--teal)}
.ptbl thead .amt{font-size:1.9rem;font-weight:800;letter-spacing:-.03em;display:block;margin-top:2px}
.ptbl thead .amtq{font-size:1.9rem;font-weight:800;letter-spacing:-.03em;display:block;margin-top:2px;color:#fff}
.ptbl thead th.feat .amtq{color:#fff}
.ptbl thead th.feat{color:var(--teal);box-shadow:inset 0 3px 0 var(--teal)}
.ptbl td.feat{background:rgba(57,184,156,.06)}
.ptbl .feat .amt{color:var(--teal)}
.ptbl tbody td{color:#CDDBEE;font-size:.95rem;text-align:center}
.ptbl tbody td:first-child{color:var(--mut);font-weight:600;text-align:left}
.ptbl .yes{color:var(--teal);font-weight:800}.ptbl .no{color:#3a4a63}
.ptbl tfoot td{border-bottom:0;text-align:center}
.ptbl tfoot .btn{width:100%}
@media(max-width:760px){.ptbl th,.ptbl td{padding:11px 12px;font-size:.82rem}.ptbl thead .amt{font-size:1.4rem}}
/* how it works — alternating spine */
.htw{position:relative}
.htw::before{content:"";position:absolute;left:50%;top:0;bottom:0;width:2px;background:linear-gradient(180deg,rgba(57,184,156,.5),rgba(57,184,156,.1));transform:translateX(-50%)}
.htw .row{position:relative;display:grid;grid-template-columns:1fr 64px 1fr;align-items:center;margin-bottom:24px}
.htw .num{grid-column:2;width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;color:#04140a;background:linear-gradient(180deg,#5fe6c8,var(--teal));margin:0 auto;z-index:1}
.htw .card{grid-row:1;background:linear-gradient(180deg,var(--surf2),var(--surf));border:1px solid var(--line);border-radius:16px;padding:22px}
.htw .card.left{grid-column:1}.htw .card.right{grid-column:3}
.htw .card h3{font-weight:700;font-size:1.12rem;margin-bottom:6px}
.htw .card p{color:var(--mut);font-size:.96rem}
.htw .card .meta{display:inline-block;font-size:.8rem;font-weight:700;color:var(--gold);background:rgba(216,184,113,.1);border:1px solid rgba(216,184,113,.25);padding:4px 12px;border-radius:999px}
.htw .card .foot{display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-top:14px}
.htw .link-wa{display:inline-flex;align-items:center;gap:7px;font-weight:700;font-size:.9rem;color:var(--teal)}
.htw .link-wa .wi{width:15px;height:15px}
.htw .link-wa .ar{transition:transform .15s}
.htw .card:hover .link-wa .ar{transform:translateX(3px)}
@media(max-width:880px){.htw::before{left:27px}.htw .row{grid-template-columns:54px 1fr;gap:18px}.htw .num{grid-column:1;margin:0}.htw .card.left,.htw .card.right{grid-column:2}}
/* final CTA — petrol gradient panel */
.ctapanel{position:relative;overflow:hidden;text-align:center;border-radius:26px;padding:72px 40px;background:linear-gradient(135deg,#0f3344,#155E7A 60%,#1f7e6e)}
.ctapanel::after{content:"";position:absolute;right:-40px;bottom:-60px;width:320px;height:320px;background:radial-gradient(circle,rgba(255,255,255,.12),transparent 65%);pointer-events:none}
.ctapanel>*{position:relative}
.ctapanel h2{color:#fff;max-width:20ch;margin:0 auto;font-size:clamp(2rem,4.4vw,3rem)}
.ctapanel .lead{color:#dff0f5;max-width:58ch;margin:18px auto 28px}
.ctapanel .sub{color:rgba(255,255,255,.72);margin-top:14px}
.ctapanel .mark{position:absolute;left:30px;top:24px;width:40px;height:40px;opacity:.9}
/* FAQ — two-column grid (open tiles) */
.faqgrid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.faqgrid .c{background:linear-gradient(180deg,var(--surf2),var(--surf));border:1px solid var(--line);border-top:3px solid var(--teal);border-radius:14px;padding:22px 24px}
.faqgrid .c h3{font-weight:700;font-size:1.08rem}
.faqgrid .c p{color:var(--mut);font-size:.96rem;margin-top:8px}
.faqgrid .c:last-child:nth-child(odd){grid-column:1/-1}
@media(max-width:760px){.faqgrid{grid-template-columns:1fr}}
/* reviews — WhatsApp bubble footer + result chip */
.chat{border-top-left-radius:4px}
.chatft{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-top:14px}
.appro{display:inline-flex;align-items:center;gap:7px;font-size:.76rem;font-weight:800;color:#7ef0a8;background:rgba(37,211,102,.12);border:1px solid rgba(37,211,102,.3);padding:5px 12px;border-radius:999px}
.appro::before{content:"";width:13px;height:7px;border-left:2px solid #7ef0a8;border-bottom:2px solid #7ef0a8;transform:rotate(-45deg)}
/* what gets people refused — alert cards 2x2 */
.ralert{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.ralert .c{background:linear-gradient(180deg,var(--surf2),var(--surf));border:1px solid var(--line);border-left:4px solid var(--red);border-radius:16px;padding:24px;display:flex;gap:16px}
.ralert .ic{width:40px;height:40px;flex:0 0 40px;color:var(--red);display:flex;align-items:center;justify-content:center;border-radius:11px;background:var(--redbg)}
.ralert .ic svg{width:22px;height:22px}
.ralert .c h3{font-weight:700;font-size:1.1rem}
.ralert .c p{color:var(--mut);font-size:.95rem;margin-top:6px}
@media(max-width:880px){.ralert{grid-template-columns:1fr}}
/* fear mechanism — severity meter rows */
.fmeter .row{display:grid;grid-template-columns:54px 1fr 120px;gap:20px;align-items:center;padding:18px 22px;background:linear-gradient(180deg,var(--surf2),var(--surf));border:1px solid var(--line);border-radius:14px;margin-bottom:12px}
.fmeter .num{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;background:var(--red)}
.fmeter .row h3{font-weight:700;font-size:1.05rem}.fmeter .row p{color:var(--mut);font-size:.93rem;margin-top:4px}
.fmeter .bar{height:8px;border-radius:999px;background:rgba(255,255,255,.08);overflow:hidden}
.fmeter .bar span{display:block;height:100%;background:linear-gradient(90deg,#ff9a9a,var(--red))}
.fmeter .row:nth-child(1) .bar span{width:25%}.fmeter .row:nth-child(2) .bar span{width:50%}.fmeter .row:nth-child(3) .bar span{width:75%}.fmeter .row:nth-child(4) .bar span{width:100%}
@media(max-width:760px){.fmeter .row{grid-template-columns:42px 1fr}.fmeter .bar{display:none}}
/* topbar — petrol gradient, centered */
.tbpetrol{background:linear-gradient(90deg,#103a4d,#155E7A 55%,#1f7e6e);border-bottom:1px solid rgba(255,255,255,.12);font-size:.86rem}
.tbp{max-width:var(--wrap);margin:0 auto;padding:11px 22px;display:flex;align-items:center;justify-content:center;gap:28px;color:#dff0f5;font-weight:600;flex-wrap:wrap}
.tbp .chip{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);padding:5px 12px;border-radius:999px;font-weight:700}
.tbp .chip svg{width:15px;height:15px;color:#bfe9dd}
.tbp .sepd{opacity:.5}.tbp a{color:#fff;font-weight:800;text-decoration:underline;text-underline-offset:3px}
.tbp .tbflags{display:inline-flex;align-items:center;gap:7px}
.tbp .tbfl{width:22px;height:14px;border-radius:3px;overflow:hidden;border:1px solid rgba(255,255,255,.35);display:inline-flex}
.tbp .tbfl .flag{width:100%;height:100%;display:block}
@media(max-width:760px){.tbp .x,.tbp .sepd{display:none}}
</style></head><body>
@include('partials.lp-chrome')
<section class="hero"><div class="wrap"><div class="hsplit"><div><div class="label ">What most consultants won&#x27;t tell you</div><h1 style="font-size:clamp(2rem,4.4vw,3.1rem)">A Schengen refusal stays on your record for <span class="hl">5 years</span>.</h1><p class="lead" style="margin-top:18px">Every embassy in Europe can see it. Your next application starts with a red flag. We stop that from happening.</p><div class="cta-row"><a class="btn btn-wa" href="{{ $wa }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Check my eligibility on WhatsApp</a></div><div class="tprow"><div class="tp"><span class="tlog"><span class="sq"><svg viewBox="0 0 24 24"><path d="M12 2l2.9 6.3 6.9.7-5.1 4.6 1.4 6.8L12 17.8 5.9 20.4l1.4-6.8L2.2 9l6.9-.7z"/></svg></span>Trustpilot</span><span class="stars"><span class="sq"><svg viewBox="0 0 24 24"><path d="M12 2l2.9 6.3 6.9.7-5.1 4.6 1.4 6.8L12 17.8 5.9 20.4l1.4-6.8L2.2 9l6.9-.7z"/></svg></span><span class="sq"><svg viewBox="0 0 24 24"><path d="M12 2l2.9 6.3 6.9.7-5.1 4.6 1.4 6.8L12 17.8 5.9 20.4l1.4-6.8L2.2 9l6.9-.7z"/></svg></span><span class="sq"><svg viewBox="0 0 24 24"><path d="M12 2l2.9 6.3 6.9.7-5.1 4.6 1.4 6.8L12 17.8 5.9 20.4l1.4-6.8L2.2 9l6.9-.7z"/></svg></span><span class="sq"><svg viewBox="0 0 24 24"><path d="M12 2l2.9 6.3 6.9.7-5.1 4.6 1.4 6.8L12 17.8 5.9 20.4l1.4-6.8L2.2 9l6.9-.7z"/></svg></span></span><span class="cap">Reviews</span></div><span class="tpnote">Live rating loads from Trustpilot once the Business Unit ID is connected.</span></div><p class="sub">You will speak to a Senior Consultant. Not a chatbot.</p></div><div><div class="vboard"><div class="phead"><span class="dot live"></span><b>Your appointment wait depends on which door you knock on</b><small>Updated daily</small></div><div class="vbwrap"><div class="vbtrack"><div class="vbset"><div class="prow"><span class="pco">Germany<span class="bn">London, TLScontact</span></span><span class="pd fast">5 working days</span><a class="pchk" href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to book my Germany Schengen appointment.') }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Book now</a></div><div class="prow"><span class="pco">Spain<span class="bn">BLS London</span></span><span class="pd go">10 to 20 days</span><a class="pchk" href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to book my Spain Schengen appointment.') }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Book now</a></div><div class="prow"><span class="pco">Greece</span><span class="pd go">7 to 14 days</span><a class="pchk" href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to book my Greece Schengen appointment.') }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Book now</a></div><div class="prow"><span class="pco">Netherlands<span class="bn">limited</span></span><span class="pd mid">mid July</span><a class="pchk" href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to book my Netherlands Schengen appointment.') }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Book now</a></div><div class="prow"><span class="pco">Italy<span class="bn">peak</span></span><span class="pd slow">4 to 8 weeks</span><a class="pchk" href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to book my Italy Schengen appointment.') }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Book now</a></div><div class="prow"><span class="pco">France<span class="bn">backlogged</span></span><span class="pd slow">4 to 8 weeks</span><a class="pchk" href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to book my France Schengen appointment.') }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Book now</a></div></div><div class="vbset" aria-hidden="true"><div class="prow"><span class="pco">Germany<span class="bn">London, TLScontact</span></span><span class="pd fast">5 working days</span><a class="pchk" href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to book my Germany Schengen appointment.') }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Book now</a></div><div class="prow"><span class="pco">Spain<span class="bn">BLS London</span></span><span class="pd go">10 to 20 days</span><a class="pchk" href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to book my Spain Schengen appointment.') }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Book now</a></div><div class="prow"><span class="pco">Greece</span><span class="pd go">7 to 14 days</span><a class="pchk" href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to book my Greece Schengen appointment.') }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Book now</a></div><div class="prow"><span class="pco">Netherlands<span class="bn">limited</span></span><span class="pd mid">mid July</span><a class="pchk" href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to book my Netherlands Schengen appointment.') }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Book now</a></div><div class="prow"><span class="pco">Italy<span class="bn">peak</span></span><span class="pd slow">4 to 8 weeks</span><a class="pchk" href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to book my Italy Schengen appointment.') }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Book now</a></div><div class="prow"><span class="pco">France<span class="bn">backlogged</span></span><span class="pd slow">4 to 8 weeks</span><a class="pchk" href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to book my France Schengen appointment.') }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Book now</a></div></div></div></div></div></div></div></div></section><section class="pad-sm" style="border-bottom:1px solid var(--line)"><div class="wrap"><div class="tbwrap"><div class="tbadge"><div class="sh"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l8 3v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V5z"/><path d="M9 12l2 2 4-4"/></svg></div><div><b>UK & Germany Registered</b><div class="meta">Reg. F202300859 &middot; regulated by the Office of the Immigration Services Commissioner</div><a href="{{ $wa }}" target="_blank" rel="noopener">Verify on gov.uk &rarr;</a></div></div><div class="tbstats"><div class="s"><div class="num">{{ App\Support\SiteStats::applications() }}+</div><div class="lab">Applications filed since 2019</div></div><div class="s"><div class="num">130</div><div class="lab">Refused. We publish our failures</div></div><div class="s"><div class="num">11</div><div class="lab">Turned away last month, cases not ready</div></div></div></div></div></section><span id="how"></span><section class="pad-sm"><div class="wrap"><div class="sh center"><div class="label ">The fear mechanism</div><h2>The Visa Information System <span class="hl">remembers everything.</span></h2></div><div class="fmeter"><div class="row"><div class="num">1</div><div><h3>You get refused</h3><p>You apply for a Schengen visa. You get refused. That refusal gets logged in a shared EU database called VIS.</p></div><div class="bar"><span></span></div></div><div class="row"><div class="num">2</div><div><h3>27 countries can see it</h3><p>France. Germany. Italy. Spain. All of them. They see the refusal before they even open your next application.</p></div><div class="bar"><span></span></div></div><div class="row"><div class="num">3</div><div><h3>It stays for 5 years</h3><p>Not 1. Not 2. Five years on a shared record that follows every future application.</p></div><div class="bar"><span></span></div></div><div class="row"><div class="num">4</div><div><h3>You start at minus one</h3><p>Your next application does not start at zero. The burden of proof flips to you. You now have to prove you are not a risk.</p></div><div class="bar"><span></span></div></div></div><div class="callout"><p><b>We reviewed 600+ refusal letters last year.</b> Over half were preventable. Wrong bank statements. Missing employer letters. Itineraries that did not add up. The kind of thing a 30 minute review would have caught.</p></div><div class="cta-row" style="justify-content:center"><a class="btn btn-wa" href="{{ $wa }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Send us your documents for a free risk check</a></div></div></section><span id="track"></span><section class="pad-sm"><div class="wrap"><div class="sh center"><div class="label ">What gets people refused</div><h2>90 seconds. <span class="hl">That is all an officer spends on your file.</span></h2></div><div class="ralert"><div class="c"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2 1 21h22z"/><path d="M12 9v5M12 17.5v.5"/></svg></div><div><h3>Unexplained deposit</h3><p>You moved money between your own accounts. To you it is obvious. To the consulate it is unexplained funds. They will not ask. They will refuse.</p></div></div><div class="c"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2 1 21h22z"/><path d="M12 9v5M12 17.5v.5"/></svg></div><div><h3>Cover letter does not match itinerary</h3><p>Your employer letter says 10 days off. Your booking is for 12. One number off. That is enough.</p></div></div><div class="c"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2 1 21h22z"/><path d="M12 9v5M12 17.5v.5"/></svg></div><div><h3>Wrong consulate</h3><p>You flew into France but spend more nights in Italy. Wrong jurisdiction. Automatic refusal. Fee gone.</p></div></div><div class="c"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2 1 21h22z"/><path d="M12 9v5M12 17.5v.5"/></svg></div><div><h3>Insurance below threshold</h3><p>Schengen requires minimum {{ App\Support\SiteStats::insuranceMin() }} medical cover. Your policy says 25,000. Nobody told you. The consulate noticed.</p></div></div></div><div class="cta-row" style="justify-content:center"><a class="btn btn-wa" href="{{ $wa }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Tell us what is wrong before the consulate does</a></div></div></section><span id="pricing"></span><section class="pad"><div class="wrap"><div class="sh"><div class="label ">Pricing</div><h2>What it costs.</h2></div><div class="grid g3"><div class="price"><div class="pt">Basic</div><div class="amt">{{ config('ukv.pricing.placeholder') }}</div><p class="desc">You prepared the application yourself. We check every page before you submit.</p><ul><li>Full document review against consulate requirements</li><li>Bank statement analysis</li><li>Cover letter review</li><li>Risk assessment with honest feedback</li></ul><div class="foot"><a class="btn btn-wa btn-block" href="{{ $wa }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Book a document review</a></div></div><div class="price feat"><div class="pt">Popular</div><div class="amt">{{ config('ukv.pricing.placeholder') }}</div><p class="desc">Cover letter, itinerary, financial structuring, full preparation. We review, you submit.</p><ul><li>Everything in Basic</li><li>Cover letter written for your case</li><li>Itinerary planning and hotel guidance</li><li>Appointment booking assistance</li><li>Tracking until decision</li></ul><div class="foot"><a class="btn btn-wa btn-block" href="{{ $wa }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Start full application</a></div></div><div class="price"><div class="pt">Advanced</div><div class="amt">{{ config('ukv.pricing.placeholder') }}</div><p class="desc">Different strategy required. A rebuild, not a retry.</p><ul><li>Prior refusal analysis</li><li>New application narrative</li><li>Evidence strategy for the real weakness</li><li>Tracking until decision</li></ul><div class="foot"><a class="btn btn-wa btn-block" href="{{ $wa }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Ask about reapplication</a></div></div></div><p class="note">Embassy fee is separate and goes directly to the authorities. We never touch it. No payment until after your free risk check.</p></div></section><span id="reviews"></span><section class="pad"><div class="wrap"><div class="sh center"><div class="label teal">Real cases from this year</div><h2>What people say after we caught it.</h2></div><div class="grid g3"><div class="chat"><p>“Applied for France myself. Refused. The consultant I paid before missed that my insurance had 25,000 cover instead of 30,000. Beyond Passports caught it in the first review. Reapplied. Approved in 11 days.”</p><div class="chatft"><span class="who">K.M., Nurse</span><span class="appro">Approved in 11 days</span></div></div><div class="chat"><p>“My cover letter said visiting friends but my hotel was 200km from where my friends live. Nobody told me that was a problem. These lot found it in 20 minutes.”</p><div class="chatft"><span class="who">D.S., Engineer</span><span class="appro">Caught in 20 minutes</span></div></div><div class="chat"><p>“Two refusals. Different consultants both times. Beyond Passports told me to wait 4 months, fix my employment gap, then reapply through Germany. Approved first try.”</p><div class="chatft"><span class="who">A.R., IT Contractor</span><span class="appro">Approved first try</span></div></div></div><p class="note" style="text-align:center">Real cases from this year. Details changed for privacy.</p></div></section><section class="pad"><div class="wrap"><div class="ctapanel" style="background:linear-gradient(120deg,rgba(10,22,40,.93),rgba(19,40,76,.82)),radial-gradient(700px 320px at 85% 10%,rgba(57,184,156,.20),transparent 60%),radial-gradient(900px 480px at 80% -8%,rgba(44,126,155,.20),transparent 60%) center/cover no-repeat"><svg class="mark" viewBox="0 0 240 210" fill="none" aria-hidden="true">
<path d="M150 66 Q149 59 156 59 L206 67 Q213 68 212 75 L205 162 Q204 169 197 168 L147 160 Q140 159 141 152 Z" fill="#39B89C"/>
<path d="M140 86 C102 62 70 50 44 44 C66 62 102 82 132 100 Z" fill="#fff"/>
<path d="M142 98 C108 80 80 72 56 70 C80 86 110 98 136 108 Z" fill="#fff"/>
<path d="M144 112 C128 138 106 160 80 176 C112 156 138 128 150 104 Z" fill="#fff"/>
<g stroke="#fff" stroke-width="2.2" fill="none"><circle cx="178" cy="128" r="14"/><path d="M178 114v28M164 128h28M178 114q-8 14 0 28M178 114q8 14 0 28"/></g></svg><h2>That refusal stays for <span style="color:#ffd0d0">5 years</span>. The review takes 20 minutes.</h2><p class="lead">Send us your documents on WhatsApp. A senior consultant reviews your file and tells you exactly where the risks are. If everything looks solid, we will say that too. No payment for the initial review.</p><a class="btn btn-wa" href="{{ $wa }}" target="_blank" rel="noopener"><svg class="wi" viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.7c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-3.8 1.1 1.1-3.7-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.4 9.6 10.2 5 16 5s10.6 4.6 10.6 10.5S21.8 25.7 16 25.7zm6-7.8c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2s-.9 1.1-1.1 1.3c-.2.2-.4.2-.7.1-1.9-.9-3.1-1.7-4.4-3.8-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.6s-.8-1.9-1-2.6c-.3-.7-.6-.6-.8-.6h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.5 3.8 6 5.3 2.2 1 3.1 1 4.2.9.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>Talk to a consultant on WhatsApp</a><p class="sub">Most people hear back within {{ App\Support\SiteStats::responseSla() }} during business hours.</p></div></div></section>@include('partials.lp-footer')
<style id="site-light-theme">
/* ===== Beyond Passports site scheme: paper #F4F6FA, ink #16222E, petrol #155E7A, teal #2E9A8C ===== */
:root{
  --bg:#F4F6FA;--surf:#ffffff;--surf2:#EEF3F9;--line:rgba(20,34,46,.12);
  --ink:#16222E;--mut:#5d6b76;--mut2:#84919e;--teal:#2E9A8C;--gold:#155E7A;--red:#C0463D;--grn:#25D366;
}
body{background:var(--bg);color:var(--ink)}
h1,h2,h3,h4{color:var(--ink)}
.hl{color:#2E9A8C !important;-webkit-text-fill-color:#2E9A8C !important}
.lead,.sub,.note,.lab,.t,.bn,.tn,.cap,.tpnote{color:#5d6b76 !important}
.label{color:#155E7A !important}.label::before{background:#155E7A !important}
/* hero -> photographic bg with LIGHT scrim (ink text stays readable on the left) */
.hero{background:
  linear-gradient(90deg,rgba(244,246,250,.96),rgba(244,246,250,.80) 42%,rgba(244,246,250,.22)),
  radial-gradient(900px 480px at 80% -8%,rgba(44,126,155,.20),transparent 60%) center/cover no-repeat !important}
.hero::before{background:
  radial-gradient(700px 420px at 6% 115%,rgba(21,94,122,.10),transparent 60%) !important}
/* generic surfaces -> white card + hairline + soft shadow */
.stat,.card,.price,.vboard,.board,.ptbl,.chat,.callout,.fmeter,
.trpanel,.tprow .tp,.faqgrid .c,.ralert .c,.htw .card,.step{
  background:#ffffff !important;border:1px solid rgba(20,34,46,.12) !important;color:#16222E !important;
  box-shadow:0 14px 34px -24px rgba(20,34,46,.30)}
.faqgrid .c p,.htw .card p,.chat p,.card p{color:#46535f !important}
.callout,.callout p{color:#46535f !important}.callout b{color:#16222E !important}
/* stats numbers */
.stat .big{background:none !important;-webkit-text-fill-color:#16222E !important;color:#16222E !important}
.stat .big.go{ -webkit-text-fill-color:#1F6E63 !important;color:#1F6E63 !important}
.stat .big.slow{-webkit-text-fill-color:#C0463D !important;color:#C0463D !important}
.stat .big.gold{-webkit-text-fill-color:#155E7A !important;color:#155E7A !important}
/* track-record panel -> photographic bg with light scrim (own rule beats group white) */
.trpanel{background:
  linear-gradient(180deg,rgba(255,255,255,.92),rgba(255,255,255,.84)),
  radial-gradient(900px 480px at 80% -8%,rgba(44,126,155,.20),transparent 60%) center/cover no-repeat !important}
.trpanel::before{display:none !important}
.trpanel .s .num,.trrow .num{color:#155E7A !important}
/* pricing table */
.ptbl thead th{background:#EEF3F9 !important}
.ptbl thead .pt{color:#84919e !important}
.ptbl thead .amtq{color:#16222E !important}
.ptbl td,.ptbl th{border-color:rgba(20,34,46,.10) !important}
.ptbl .yes{color:#2E9A8C !important}.ptbl .no{color:#b8c0c8 !important}
/* vertical wait-times board -> light (own rules, not grouped) */
.vboard{background:#ffffff !important;border:1px solid rgba(20,34,46,.12) !important;box-shadow:0 16px 40px -26px rgba(20,34,46,.32) !important}
.vboard .phead{background:#EEF3F9 !important;color:#16222E !important;border-bottom:1px solid rgba(20,34,46,.10) !important}
.vboard .phead small{color:#84919e !important}
.prow{border-color:rgba(20,34,46,.08) !important}
.prow .pco{color:#16222E !important}
.phead,.bh,.thead{color:#16222E !important}
/* trust-bar Option C: single white panel, registration badge left (divider) + divided stats right */
.tbwrap{background:#ffffff !important;border:1px solid rgba(20,34,46,.12) !important;border-radius:18px !important;padding:22px 26px !important;box-shadow:0 14px 34px -26px rgba(20,34,46,.3) !important;align-items:center !important}
.tbadge{background:transparent !important;border:0 !important;border-right:1px solid rgba(20,34,46,.12) !important;border-radius:0 !important;padding:0 24px 0 0 !important}
.tbadge b{color:#16222E !important}
.tbadge .sh{background:rgba(21,94,122,.1) !important;border:0 !important;color:#155E7A !important}
.tbstats .s{background:transparent !important;border:0 !important;border-left:1px solid rgba(20,34,46,.12) !important;border-radius:0 !important;box-shadow:none !important;text-align:left !important;padding:6px 0 6px 18px !important}
.tbstats .s:first-child{border-left:0 !important;padding-left:0 !important}
.tbstats .s .num,.tbstats .num{background:none !important;-webkit-text-fill-color:#155E7A !important;color:#155E7A !important;font-size:1.7rem !important}
.tbstats .lab{color:#5d6b76 !important}
/* nav pills + brand (kill inline white "Beyond") */
.nav .pill,.nav{background:transparent !important}
.nav a{color:#16222E !important}
.nav a:hover{color:#155E7A !important}
.nav .brand span[style]{color:#16222E !important}
.nav .brand span{color:#5d6b76 !important}
/* how-it-works meta pills: gold -> petrol (site has no gold) */
.htw .card .meta,.step .meta,.card .meta{
  background:rgba(21,94,122,.08) !important;border:1px solid rgba(21,94,122,.22) !important;color:#155E7A !important}
/* refused alert + fear meter accents */
.ralert .c{border-left:3px solid #C0463D !important}
.ralert .ic{color:#C0463D !important}
.fmeter .num{color:#fff !important;background:#C0463D !important}
/* how-it-works step nodes -> petrol filled + white number (site primary) */
.htw .num{background:#155E7A !important;color:#fff !important}
/* card foot inherits page-footer .foot padding:54px 0 -> reset to a thin divider */
.htw .card .foot{padding:14px 0 0 !important;border-top:1px solid rgba(20,34,46,.10) !important;margin-top:14px !important}
.fmeter .bar span{background:#C0463D !important}
/* tags */
.tag.go,.pd.go{color:#1F6E63 !important;background:rgba(46,154,140,.12) !important}
.tag.slow,.pd.slow{color:#C0463D !important;background:rgba(192,70,61,.10) !important}
.tag.mid,.pd.mid{color:#9a6b1f !important;background:rgba(154,107,31,.12) !important}
.tag.fast,.pd.fast{color:#155E7A !important;background:rgba(21,94,122,.12) !important}
/* result chips on reviews */
.appro{color:#1F6E63 !important;background:rgba(46,154,140,.12) !important}
/* keep WhatsApp green; petrol CTA panel + petrol topbar stay (on brand) */
.btn-ghost{color:#155E7A !important;border-color:rgba(21,94,122,.4) !important}
/* trustpilot row */
.tprow .tp .tlog,.tprow .stars{color:#16222E !important}
.teamline,.teamline .tlchip{color:#46535f !important;border-color:rgba(20,34,46,.18) !important}
/* topbar is petrol (dark) -> its teamline chips must stay light */
.tbpetrol .teamline,.tbpetrol .teamline .tlchip{color:rgba(255,255,255,.9) !important;border-color:rgba(255,255,255,.3) !important}
/* ===== CTA buttons: site .btn system (radius 12, weight 700, Outfit) ===== */
.btn{border-radius:12px !important;font-weight:700 !important;font-family:'Outfit',system-ui,sans-serif !important}
/* primary WhatsApp CTAs -> petrol fill + WA icon (hero "C") */
.btn-wa{background:#155E7A !important;color:#fff !important;box-shadow:none !important;padding:14px 26px !important}
.btn-wa:hover{background:#0F4A61 !important;box-shadow:0 0 0 3px rgba(21,94,122,.18) !important}
.btn-wa .wi{fill:#fff !important}
/* ghost -> petrol outline */
.btn-ghost{background:transparent !important;color:#155E7A !important;border:1.5px solid #155E7A !important}
/* pricing: featured plan stays WhatsApp green, others ghost */
.ptbl tfoot .btn-wa{background:#25D366 !important;color:#06310f !important;padding:11px 18px !important}
.ptbl tfoot .btn-wa .wi{fill:#06310f !important}
/* nav CTA -> petrol filled (a.navcta beats the generic .nav a ink colour) */
.navcta{background:#155E7A !important;border-radius:12px !important;font-weight:700 !important;padding:10px 18px !important}
.nav a.navcta{color:#fff !important}
/* wait-times board "Check" pills -> flat site WhatsApp green */
.prow .pchk{background:#25D366 !important;color:#06310f !important;box-shadow:none !important;border-radius:10px !important;font-weight:700 !important}
.prow .pchk .wi{fill:#06310f !important}
/* final CTA panel (petrol) -> white button */
.ctapanel .btn-wa{background:#fff !important;color:#155E7A !important;transition:transform .12s ease,box-shadow .18s ease,background .18s ease !important}
.ctapanel .btn-wa .wi{fill:#155E7A !important}
.ctapanel .btn-wa:hover{background:#EAF2F5 !important;transform:translateY(-2px) !important;box-shadow:0 14px 30px -12px rgba(0,0,0,.45) !important}
.ctapanel .btn-wa:active{transform:translateY(0) !important}
/* CTA panel stays dark petrol -> text must be LIGHT (beats the global muted rule) */
.ctapanel h2{color:#ffffff !important}
.ctapanel .lead,.ctapanel p{color:#dff0f5 !important}
.ctapanel .sub,.ctapanel .note{color:rgba(255,255,255,.78) !important}
</style>
</body></html>