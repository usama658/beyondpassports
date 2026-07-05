@extends('layouts.public')

@section('title', 'Schengen Visa Help — Your Application Has One Chance | Beyond Passports')
@section('description', 'Independent UK Schengen visa help. First-time or refused, we prepare applications that stand up, decode refusal letters and monitor appointments. Free case check on WhatsApp. Not a government website.')

@php $wa = 'https://wa.me/'.config('ukv.whatsapp'); @endphp

@push('head')
<style>
/* ===== Bold LP — page-scoped under .lpb (avoids ukv.css class collisions) ===== */
.lpb{--ink:#16222E;--ink2:#0f2028;--paper:#F4F6FA;--cta:#155E7A;--cta-d:#0F4A61;--stamp:#2E9A8C;--soft:#A9CCDA;--stamp-text:#1F6E63;--on-dark:#79CFC2;--muted:#5d6b76;--edge:#dde3ec;--wa:#25D366;--amber:#E9B872;--red:#c0492f;
  --display:"Outfit",system-ui,sans-serif;--mono:ui-monospace,"Outfit",monospace;--sh:0 18px 44px -26px rgba(20,34,46,.34);--sh2:0 30px 66px -30px rgba(20,34,46,.42);
  background:var(--paper);color:var(--ink);font:400 17px/1.62 var(--display);-webkit-font-smoothing:antialiased}
.lpb *{box-sizing:border-box}
.lpb h1,.lpb h2,.lpb h3,.lpb h4{font-weight:700;line-height:1.05;letter-spacing:-.025em;margin:0}
.lpb a{color:var(--cta);text-decoration:none}
.lpb .wrap{max-width:1140px;margin:0 auto;padding:0 28px}
.lpb .eyebrow{font-weight:800;font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 14px;display:flex;align-items:center;gap:9px}
.lpb .eyebrow::before{content:"";width:26px;height:2px;background:var(--stamp)}
.lpb .btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;background:var(--cta);color:#fff;font-weight:700;padding:15px 24px;border-radius:12px;font-size:16px;box-shadow:var(--sh);width:100%;border:0;cursor:pointer}
.lpb .btn:hover{background:var(--cta-d)}
.lpb .btn svg{width:18px;height:18px;fill:#fff;flex:none}
.lpb .wa{background:var(--wa)}.lpb .wa:hover{background:#1eb457}
.lpb .sec{padding:80px 0}
.lpb .sec.alt{background:#fff;border-block:1px solid var(--edge)}
.lpb .em{color:var(--muted);font-size:14.5px}.lpb .em b{color:var(--ink)}
.lpb .micro{color:var(--muted);font-size:14px;line-height:1.55}
.lpb .trans{color:var(--muted);font-size:15.5px;line-height:1.55}
.lpb .h2{font-size:clamp(27px,3.4vw,40px);letter-spacing:-.03em;max-width:20ch}
.lpb .dot{width:9px;height:9px;border-radius:50%;background:var(--stamp);box-shadow:0 0 0 5px rgba(46,154,140,.22);display:inline-block}
.lpb .urgent{display:inline-flex;align-items:center;gap:9px;background:#fdf3e7;border:1px solid #f0d9b8;color:#8a5a1a;font-size:14.5px;font-weight:600;padding:12px 18px;border-radius:12px}
.lpb .ctarow{display:flex;gap:16px;align-items:center;flex-wrap:wrap;margin-top:20px}
.lpb .ctarow .btn{width:auto;min-width:230px}
/* HERO — dual-lane w/ photo */
.lpb .hero{position:relative;padding:52px 0 54px;isolation:isolate;overflow:hidden}
.lpb .hero::before{content:"";position:absolute;inset:0;z-index:-2;background:url('{{ asset('assets/img/lp/hero.jpg') }}') center 28%/cover no-repeat}
.lpb .hero::after{content:"";position:absolute;inset:0;z-index:-1;background:linear-gradient(180deg,rgba(9,18,24,.86) 0%,rgba(9,18,24,.6) 40%,rgba(244,246,250,.5) 80%,var(--paper) 100%)}
.lpb .heyebrow{justify-content:center;color:var(--on-dark);margin-bottom:6px}
.lpb .hhead{text-align:center;max-width:22ch;margin:0 auto 6px;font-size:clamp(30px,4vw,46px);letter-spacing:-.035em;color:#fff;text-shadow:0 2px 20px rgba(0,0,0,.4)}
.lpb .hsub{text-align:center;color:#e6eef1;font-size:18px;max-width:52ch;margin:12px auto 30px;text-shadow:0 1px 10px rgba(0,0,0,.35)}
.lpb .lanes{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.lpb .lane{border-radius:20px;padding:30px;box-shadow:var(--sh2)}
.lpb .fresh{background:#fff;border:1px solid var(--edge)}
.lpb .ref{background:radial-gradient(500px 340px at 85% 0%,rgba(192,73,47,.28),transparent 60%),var(--ink2);color:#fff}
.lpb .lane .ltag{font-weight:800;font-size:11px;letter-spacing:.14em;text-transform:uppercase;margin:0 0 10px}
.lpb .fresh .ltag{color:var(--stamp-text)}.lpb .ref .ltag{color:#f0a58f}
.lpb .lane h3{font-size:23px;margin:0 0 6px}.lpb .ref h3{color:#fff}
.lpb .lane .ld{font-size:14.5px;line-height:1.5;margin:0 0 18px}.lpb .fresh .ld{color:var(--muted)}.lpb .ref .ld{color:#cfd9dd}
.lpb .form label{display:block;font-size:12.5px;font-weight:600;margin:0 0 5px;color:var(--ink)}
.lpb .form .row{display:flex;gap:10px;margin:0 0 10px}.lpb .form .fld{flex:1}
.lpb .form input[type=text]{width:100%;background:var(--paper);border:1px solid var(--edge);border-radius:10px;padding:11px 12px;color:var(--ink);font:600 15px var(--display)}
.lpb .form input:focus{outline:2px solid var(--stamp);outline-offset:1px;border-color:transparent}
.lpb .form .cons{display:flex;gap:7px;align-items:flex-start;margin:10px 0 0;color:var(--muted);font-size:12px;line-height:1.4}
.lpb .form .cons input{width:15px;height:15px;flex:none;margin-top:2px}
.lpb .ref .stamp{display:inline-block;border:2.5px solid #f0a58f;color:#f0a58f;font-weight:800;letter-spacing:.16em;font-size:13px;padding:6px 14px;border-radius:7px;transform:rotate(-5deg);margin:0 0 16px;text-transform:uppercase;box-shadow:inset 0 0 0 2px rgba(240,165,143,.14)}
.lpb .ref .rl{height:7px;border-radius:3px;background:rgba(255,255,255,.14);margin:8px 0}.lpb .ref .rl.s{width:60%}.lpb .ref .rl.m{width:85%}
.lpb .ref .btn{margin-top:18px}
.lpb .ref .rlink{display:block;margin-top:12px;color:#f0a58f;font-weight:700;font-size:13px}
.lpb .reflist{list-style:none;margin:16px 0 6px;padding:0}
.lpb .reflist li{position:relative;padding:5px 0 5px 26px;color:#d7e2e6;font-size:13.5px;line-height:1.4}
.lpb .reflist li::before{content:"✓";position:absolute;left:0;top:5px;color:#f0a58f;font-weight:800}
.lpb .halt{text-align:center;color:var(--muted);font-size:13.5px;margin:22px 0 0}.lpb .halt b{color:var(--ink)}
.lpb .sitrow{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:24px 0 0}
.lpb .sit{display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--edge);border-left:3px solid var(--cta);border-radius:12px;padding:13px 15px;transition:transform .12s,box-shadow .12s}
.lpb .sit:hover{transform:translateY(-2px);box-shadow:var(--sh)}
.lpb .sit .tx{min-width:0}
.lpb .sit .st{font-weight:800;font-size:9px;letter-spacing:.13em;text-transform:uppercase;color:var(--stamp-text);display:block;margin:0 0 2px}
.lpb .sit .q{font-weight:700;font-size:14.5px;line-height:1.2;margin:0 0 2px}
.lpb .sit .d{color:var(--muted);font-size:12px;line-height:1.35;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;margin:0}
.lpb .sit .chev{margin-left:auto;width:26px;height:26px;border-radius:50%;background:#eef4f6;color:var(--cta);font-size:15px;font-weight:700;display:flex;align-items:center;justify-content:center;flex:none}
/* RISK — ledger */
.lpb .ledger{border-top:2px solid var(--ink);margin:32px 0 8px}
.lpb .lrow{display:grid;grid-template-columns:110px 1fr;gap:34px;padding:30px 0;border-bottom:1px solid var(--edge);align-items:start}
.lpb .lrow .idx{font-size:64px;font-weight:800;letter-spacing:-.04em;color:#fbfcfe;-webkit-text-stroke:1.5px var(--soft);line-height:.8}
.lpb .lrow .rc{max-width:64ch}
.lpb .lrow .tag{display:inline-block;font-weight:800;font-size:10.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--stamp-text);background:#eaf4f1;padding:5px 10px;border-radius:6px;margin:0 0 12px}
.lpb .lrow h3{font-size:23px;letter-spacing:-.02em;margin:0 0 9px}
.lpb .lrow .rc p{color:var(--muted);font-size:15.5px;line-height:1.6;margin:0}
/* BOARD — departures (dark) w/ photo */
.lpb .bd{position:relative;color:#fff;isolation:isolate;overflow:hidden}
.lpb .bd::before{content:"";position:absolute;inset:0;z-index:-2;background:url('{{ asset('assets/img/lp/board.jpg') }}') center/cover no-repeat}
.lpb .bd::after{content:"";position:absolute;inset:0;z-index:-1;background:linear-gradient(180deg,rgba(9,18,24,.93),rgba(9,18,24,.88)),radial-gradient(700px 400px at 85% 0%,rgba(21,94,122,.4),transparent 60%)}
.lpb .bd .eyebrow{color:var(--on-dark)}.lpb .bd .h2{color:#fff}
.lpb .bd .intro{color:#b9ccd3;max-width:62ch;margin:12px 0 28px;font-size:16px}
.lpb .board{background:#0a141a;border:1px solid rgba(255,255,255,.1);border-radius:16px;overflow:hidden;box-shadow:var(--sh2)}
.lpb .bh{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;background:rgba(255,255,255,.03);border-bottom:1px solid rgba(255,255,255,.1)}
.lpb .bd .live{display:inline-flex;align-items:center;gap:8px;color:var(--on-dark);font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.lpb .bd .upd{color:rgba(255,255,255,.45);font-size:12px}
.lpb .ch,.lpb .brow{display:grid;grid-template-columns:1.2fr 2.2fr .9fr 1fr;gap:16px;align-items:center}
.lpb .ch{padding:11px 24px;font-size:10.5px;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.4);border-bottom:1px solid rgba(255,255,255,.08)}
.lpb .brow{padding:15px 24px;border-bottom:1px solid rgba(255,255,255,.055)}
.lpb .brow .cty{color:#fff;font-weight:700;font-size:16px}
.lpb .brow .st{color:rgba(255,255,255,.58);font-size:13.5px}
.lpb .brow .sl{font-family:var(--mono);font-weight:800;font-size:23px;color:var(--amber);text-align:right;font-variant-numeric:tabular-nums;letter-spacing:.02em}
.lpb .brow .av{color:rgba(255,255,255,.72);font-size:13.5px;text-align:right;font-variant-numeric:tabular-nums}
.lpb .rt{text-align:right}
.lpb .toggle{text-align:center;color:var(--on-dark);font-weight:700;font-size:14px;padding:14px;background:rgba(255,255,255,.02)}
.lpb .bd .foot{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-top:24px}
.lpb .bd .em{color:#a9c2ca}.lpb .bd .em b{color:#fff}.lpb .bd .btn{background:var(--stamp);width:auto;min-width:230px}
.lpb .bd .skip{margin:0 0 10px}.lpb .bd .skip a{color:var(--on-dark);font-weight:600}
/* DECODER — redacted letter + selector */
.lpb .dec .top{max-width:60ch;margin:0 0 34px}.lpb .dec .top h2{margin:0 0 12px}
.lpb .dec .grid{display:grid;grid-template-columns:.85fr 1.15fr;gap:44px;align-items:start}
.lpb .dec .side{position:sticky;top:96px}
.lpb .dec .side .btn{margin-top:18px;width:100%}
.lpb .letter{background:#fbfaf7;border:1px solid #e6e2d8;border-radius:12px;padding:26px 26px 30px;box-shadow:var(--sh);position:relative;overflow:hidden;font-size:13px}
.lpb .letter .lh{display:flex;justify-content:space-between;border-bottom:1px solid #e6e2d8;padding-bottom:12px;margin-bottom:14px}
.lpb .letter .lh b{font-size:12px;letter-spacing:.04em;color:#3d4750}.lpb .letter .lh span{color:#9aa0a3;font-size:11px}
.lpb .letter .rl{height:8px;border-radius:3px;background:#e7e3d8;margin:9px 0}
.lpb .letter .rl.s{width:64%}.lpb .letter .rl.m{width:88%}.lpb .letter .rl.l{width:96%}
.lpb .letter .hl{background:rgba(192,73,47,.1);border-left:3px solid var(--red);padding:9px 12px;margin:14px 0;border-radius:4px;color:#5c3026;font-weight:600;font-size:12.5px;line-height:1.45}
.lpb .letter .stamp{position:absolute;top:20px;right:18px;transform:rotate(8deg);border:2.5px solid var(--red);color:var(--red);font-weight:800;letter-spacing:.16em;font-size:14px;padding:6px 14px;border-radius:7px;opacity:.85;text-transform:uppercase;box-shadow:inset 0 0 0 2px rgba(192,73,47,.12)}
.lpb .letter .lfoot{border-top:1px solid #e6e2d8;margin-top:16px;padding-top:12px;color:#9aa0a3;font-size:11px}
.lpb .lnote{padding:16px;background:#fff;border:1px solid var(--edge);border-radius:12px;color:var(--muted);font-size:14px;line-height:1.55;margin-top:16px}.lpb .lnote b{color:var(--ink)}
.lpb .dec .selh{font-size:18px;margin:0 0 3px}
.lpb .acc{background:#fff;border:1px solid var(--edge);border-radius:12px;margin:0 0 11px;overflow:hidden}
.lpb .acc .h{display:flex;justify-content:space-between;gap:14px;padding:16px 18px;font-weight:700;font-size:14.5px;line-height:1.35;cursor:pointer}
.lpb .acc .h .pm{color:var(--cta);font-size:22px;font-weight:400;flex:none;line-height:.8}
.lpb .acc .b{padding:0 18px 18px;color:var(--muted);font-size:14px;line-height:1.6;display:none}
.lpb .acc.open .b{display:block}
.lpb .acc .b .lab{color:var(--ink);font-weight:700;display:block;margin:11px 0 2px}
/* TRUST — dark console + light verify */
.lpb .tr .grid{display:grid;grid-template-columns:1fr 1fr;gap:0;border-radius:20px;overflow:hidden;box-shadow:var(--sh2);border:1px solid var(--edge)}
.lpb .tr .dark{background:radial-gradient(600px 400px at 15% 0%,rgba(21,94,122,.5),transparent 62%),var(--ink2);color:#fff;padding:38px 34px}
.lpb .tr .dark .eyebrow{color:var(--on-dark)}
.lpb .tr .dark h2{color:#fff;font-size:26px;max-width:18ch;margin:0 0 8px}
.lpb .tr .dark .sub{color:#b9ccd3;font-size:14.5px;margin:0 0 26px;max-width:40ch}
.lpb .tr .stp{display:grid;grid-template-columns:auto 1fr;gap:15px;padding-bottom:20px;position:relative}
.lpb .tr .stp:not(:last-of-type)::before{content:"";position:absolute;left:16px;top:38px;bottom:0;width:2px;background:rgba(255,255,255,.14)}
.lpb .tr .stp .n{width:34px;height:34px;border-radius:9px;background:rgba(255,255,255,.08);border:1px solid var(--on-dark);color:var(--on-dark);font-weight:800;display:flex;align-items:center;justify-content:center;font-size:14px;z-index:1}
.lpb .tr .stp h3{color:#fff;font-size:15.5px;margin:5px 0 5px}.lpb .tr .stp p{color:#a9c0c8;font-size:13px;line-height:1.5;margin:0}
.lpb .tr .statline{margin-top:8px;display:flex;align-items:baseline;gap:10px;border-top:1px solid rgba(255,255,255,.14);padding-top:18px}
.lpb .tr .statline b{font-size:28px;font-weight:800;color:var(--on-dark)}.lpb .tr .statline span{color:rgba(255,255,255,.75);font-size:13px}
.lpb .tr .lite{background:#fff;padding:38px 34px}
.lpb .tr .lite h3{font-size:18px;margin:0 0 3px}.lpb .tr .lite .sub{color:var(--muted);font-size:13.5px;margin:0 0 8px}
.lpb .vlink{display:flex;align-items:flex-start;gap:13px;padding:15px 0;border-bottom:1px solid var(--edge);color:inherit}
.lpb .vlink:last-of-type{border-bottom:0}
.lpb .vlink .tick{width:24px;height:24px;border-radius:7px;background:#e7f3ee;color:var(--stamp-text);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;flex:none;margin-top:1px}
.lpb .vlink h4{font-size:15px;margin:0 0 3px;display:flex;align-items:center;gap:6px;color:var(--ink)}
.lpb .vlink h4 .ext{color:var(--cta);font-size:12px;font-weight:700}
.lpb .vlink p{color:var(--muted);font-size:13.5px;line-height:1.5;margin:0}
.lpb .vlink:hover h4 .ext{text-decoration:underline}
.lpb .founder{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid var(--edge);border-radius:15px;padding:16px 20px;margin-top:16px}
.lpb .founder .ph{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#155E7A,#2E9A8C);flex:none}
.lpb .founder b{display:block;font-size:16px}.lpb .founder span{color:var(--muted);font-size:13px}
/* FAQ — two-up open cards */
.lpb .faq .feye{justify-content:center}
.lpb .faq .fhead{text-align:center;max-width:30ch;margin:0 auto 6px}
.lpb .faq .fsub{text-align:center;color:var(--muted);font-size:15px;margin:0 auto 30px;max-width:52ch}
.lpb .faq .fgrid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.lpb .faq .fcard{background:#fff;border:1px solid var(--edge);border-radius:16px;padding:24px;box-shadow:0 12px 30px -26px rgba(20,34,46,.5)}
.lpb .faq .fcard.key{border-color:var(--stamp);background:linear-gradient(180deg,#f6fbf9,#fff)}
.lpb .faq .fcard.wide{grid-column:1/-1}
.lpb .faq .fq{font-weight:700;font-size:17px;line-height:1.28;margin:0 0 10px;display:flex;gap:10px;align-items:flex-start}
.lpb .faq .qg{color:var(--cta);font-weight:800;font-size:15px;flex:none}
.lpb .faq .fcard.key .qg{color:var(--stamp-text)}
.lpb .faq .fa{color:var(--muted);font-size:14.5px;line-height:1.6;margin:0}.lpb .faq .fa b{color:var(--ink)}
.lpb .faq .wacard{display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;background:var(--paper)}
.lpb .faq .wabtn{display:inline-flex;align-items:center;gap:8px;background:var(--wa);color:#fff;font-weight:700;padding:12px 20px;border-radius:11px;text-decoration:none}
.lpb .faq .wabtn svg{width:16px;height:16px;fill:#fff}
/* URGENCY — split + action card w/ photo */
.lpb .band{position:relative;color:#fff;isolation:isolate;overflow:hidden}
.lpb .band::before{content:"";position:absolute;inset:0;z-index:-2;background:url('{{ asset('assets/img/lp/band.jpg') }}') center 40%/cover no-repeat}
.lpb .band::after{content:"";position:absolute;inset:0;z-index:-1;background:linear-gradient(180deg,rgba(9,18,24,.9),rgba(9,18,24,.82)),radial-gradient(760px 440px at 92% 100%,rgba(46,154,140,.34),transparent 60%)}
.lpb .band .wrap{padding:64px 28px;max-width:1140px;display:grid;grid-template-columns:1.15fr .85fr;gap:44px;align-items:center}
.lpb .band .eyebrow{color:var(--on-dark)}
.lpb .band h2{font-size:clamp(28px,3.4vw,40px);color:#fff;margin:14px 0 16px;max-width:16ch}
.lpb .band p{color:#cfe0e6;font-size:16px;line-height:1.6;margin:0 0 12px;max-width:52ch}
.lpb .band .em2{color:#fff;font-weight:600}
.lpb .band .ucard{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.16);border-radius:18px;padding:28px}
.lpb .band .ucard .k{font-weight:800;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--on-dark);margin:0 0 8px}
.lpb .band .ucard h3{color:#fff;font-size:22px;margin:0 0 8px}
.lpb .band .ucard .d{color:#b9ccd3;font-size:14px;margin:0 0 18px}
.lpb .band .ucard .btn{width:100%;min-width:0}
.lpb .band .ucard .re{display:flex;justify-content:center;gap:16px;margin:14px 0 0;color:#a9c2ca;font-size:12.5px;flex-wrap:wrap}
.lpb .band .ucard .re b{color:#fff}
@media(max-width:860px){
  .lpb .lanes,.lpb .sitrow,.lpb .dec .grid,.lpb .tr .grid,.lpb .band .wrap{grid-template-columns:1fr}
  .lpb .band .wrap{gap:26px}
  .lpb .lrow{grid-template-columns:56px 1fr;gap:16px}.lpb .lrow .idx{font-size:40px}
  .lpb .dec .side{position:static}
  .lpb .bd .ch{display:none}
  .lpb .brow{grid-template-columns:1fr auto;gap:4px 12px;padding:14px 18px}
  .lpb .brow .st{grid-column:1}.lpb .brow .sl{grid-row:1/3;align-self:center}.lpb .brow .av{grid-column:1;color:rgba(255,255,255,.5)}
  .lpb .faq .fgrid{grid-template-columns:1fr}.lpb .faq .fcard.wide{grid-column:auto}
  .lpb .sec{padding:56px 0}.lpb .hero{padding:28px 0 44px}
}
</style>
@endpush

@section('content')
<div class="lpb">

{{-- HERO — dual-lane --}}
<section class="hero"><div class="wrap">
  <p class="eyebrow heyebrow">Schengen visas · UK applicants</p>
  <h1 class="hhead">Your visa application has one chance. Most people waste it.</h1>
  <p class="hsub">We know why. Start where you are — we'll say honestly if it's a case we can help with.</p>
  <div class="lanes">
    <div class="lane fresh">
      <p class="ltag">Applying now · first time or unsure</p>
      <h3>Get a free case check</h3>
      <p class="ld">Tell us your situation. We reply within 24 hours and turn you away if it's not one we can help with.</p>
      <form class="form" id="lpbCaseForm">
        <div class="row"><div class="fld"><label for="lpb-name">Your name</label><input type="text" id="lpb-name" placeholder="Jane Smith"></div><div class="fld"><label for="lpb-phone">Phone (UK)</label><input type="text" id="lpb-phone" placeholder="07…"></div></div>
        <button class="btn wa" type="submit">@include('partials.wa-glyph')Get my free case check</button>
        <label class="cons"><input type="checkbox" checked><span>I agree to be contacted about my enquiry. We never share your details. <a href="/legal">Privacy</a>.</span></label>
      </form>
    </div>
    <div class="lane ref">
      <span class="stamp">Refused</span>
      <p class="ltag">Refusal recovery</p>
      <h3>Start refusal recovery</h3>
      <p class="ld">The letter doesn't tell you the real reason. We decode it, find what actually triggered it, and rebuild — or tell you honestly if it can't be recovered.</p>
      <ul class="reflist">
        <li>We read the exact refusal ground on your letter</li>
        <li>Rebuild the file so it can't be flagged again</li>
        <li>Or tell you straight if it isn't recoverable</li>
      </ul>
      <a class="btn" style="background:#f0a58f;color:#3a1a12" href="{{ $wa }}?text=Hi%2C%20my%20Schengen%20visa%20was%20refused.%20I%27d%20like%20a%20free%20review%20of%20my%20letter.">Send my refusal letter for a free review →</a>
      <a class="rlink" href="#refusal">Or see how we decode a refusal letter ↓</a>
    </div>
  </div>
  <div class="sitrow">
    <a class="sit" href="{{ $wa }}?text=Hi%2C%20I%20need%20a%20Schengen%20appointment%20but%20every%20slot%20is%20gone.%20Can%20you%20help%20me%20find%20one%3F"><span class="tx"><span class="st">Need appointment</span><span class="q">"Every slot is gone"</span><p class="d">We monitor all 27 states daily and secure slots most never find.</p></span><span class="chev">→</span></a>
    <a class="sit" href="{{ $wa }}?text=Hi%2C%20I%27ve%20done%20this%20before%20and%20just%20want%20you%20to%20handle%20my%20Schengen%20application."><span class="tx"><span class="st">Experienced</span><span class="q">"Just handle it for me"</span><p class="d">You know it works. We handle paperwork, appointment and details.</p></span><span class="chev">→</span></a>
    <a class="sit" href="{{ $wa }}?text=Hi%2C%20we%27re%20applying%20for%20Schengen%20visas%20together%20and%20our%20documents%20are%20different.%20Can%20you%20help%3F"><span class="tx"><span class="st">Couple or family</span><span class="q">"We're applying together"</span><p class="d">One weak file affects everyone. We prepare them together.</p></span><span class="chev">→</span></a>
  </div>
  <p class="halt">Prefer to type it yourself? <b>WhatsApp</b> or <a href="mailto:cases@beyondpassports.co.uk">cases@beyondpassports.co.uk</a></p>
</div></section>

{{-- RISK — case-file ledger --}}
<section class="sec alt" id="risk-cards"><div class="wrap">
  <p class="eyebrow">Before you apply</p>
  <h2 class="h2" style="max-width:22ch">Three things that decide your application before an officer opens it</h2>
  <p class="trans" style="max-width:60ch;margin:12px 0 0">You're here because something about your application feels uncertain. Here's why that instinct is right.</p>
  <div class="ledger">
    <div class="lrow"><div class="idx">01</div><div class="rc"><span class="tag">The record</span><h3>Every refusal is shared by all 27 states</h3><p>No preview, no draft round. You find out weeks later in a rejection letter — and the refusal is already on your record, visible to every Schengen country for five years through VIS.</p></div></div>
    <div class="lrow"><div class="idx">02</div><div class="rc"><span class="tag">The counter</span><h3>The questions are not small talk</h3><p>Staff note inconsistencies. If your dates don't match your booking, or your letter says business but your invitation says tourism, it's written down. You never see that note. The officer does.</p></div></div>
    <div class="lrow"><div class="idx">03</div><div class="rc"><span class="tag">The funds</span><h3>A healthy balance won't prove what they need</h3><p>Officers look for patterns, not balances. Sudden deposits raise questions. Irregular income raises questions. They're not checking whether you have money — they're checking whether you'll come back.</p></div></div>
  </div>
  <p class="trans" style="margin:22px 0 0">These aren't edge cases. They're the most common reasons applications fail — and every one is preventable with the right preparation.</p>
  <div class="ctarow"><a class="btn" href="{{ $wa }}?text=Hi%2C%20I%20want%20to%20talk%20through%20my%20Schengen%20application%20before%20I%20submit%20anything.">Talk to us before you submit anything</a><span class="em"><b>WhatsApp</b> preferred · <b>Email:</b> cases@beyondpassports.co.uk</span></div>
</div></section>

{{-- BOARD — departures (dark). Numbers illustrative of typical demand, not a live feed. --}}
<section class="sec bd" id="appointments"><div class="wrap">
  <p class="trans skip"><a href="#refusal">Not looking for appointments? Skip to refusal recovery →</a></p>
  <p class="eyebrow">Typical availability</p>
  <h2 class="h2">Appointment availability this week</h2>
  <p class="intro">Every Schengen application needs an in-person appointment — for most countries the next slot is weeks or months away. Here's what we typically see across the highest-demand countries for UK applicants.</p>
  <div class="board">
    <div class="bh"><span class="live"><span class="dot"></span>This week</span><span class="upd">Typical demand · we check every country daily</span></div>
    <div class="ch"><span>Country</span><span>What we're seeing</span><span class="rt">This wk</span><span class="rt">Our avg</span></div>
    <div class="brow"><span class="cty">France</span><span class="st">Slots appear and vanish within minutes</span><span class="sl">12</span><span class="av">3–7 days</span></div>
    <div class="brow"><span class="cty">Italy</span><span class="st">Regularly no availability for weeks</span><span class="sl">08</span><span class="av">5–10 days</span></div>
    <div class="brow"><span class="cty">Spain</span><span class="st">High demand, very limited same-month</span><span class="sl">06</span><span class="av">5–9 days</span></div>
    <div class="brow"><span class="cty">Germany</span><span class="st">Released in unpredictable batches</span><span class="sl">09</span><span class="av">4–8 days</span></div>
    <div class="brow"><span class="cty">Greece</span><span class="st">Limited UK allocation, seasonal demand</span><span class="sl">05</span><span class="av">4–8 days</span></div>
    <div class="brow"><span class="cty">Netherlands</span><span class="st">Short windows that fill within hours</span><span class="sl">07</span><span class="av">3–6 days</span></div>
    <div class="toggle">Every Schengen country covered · ask us about yours</div>
  </div>
  <div class="foot"><span class="urgent">⏱ Travelling within 3 weeks? Tell us now — some slots can't wait.</span>
    <span style="display:flex;gap:16px;align-items:center;flex-wrap:wrap"><a class="btn" href="{{ $wa }}?text=Hi%2C%20I%20need%20a%20Schengen%20appointment.%20My%20travel%20dates%20are%3A%20">Secure my appointment</a><span class="em">Tell us your travel dates.</span></span></div>
</div></section>

{{-- DECODER — redacted letter + selector --}}
<section class="sec alt dec" id="refusal"><div class="wrap">
  <div class="top"><p class="eyebrow">Refusal recovery</p><h2 class="h2">A refusal is not the end. Your next move decides everything.</h2><p class="micro">A rushed reapplication with the same paperwork gets the same result. A new application to a different country carries the same flag. We take refused cases apart, find what actually went wrong, then rebuild.</p></div>
  <div class="grid">
    <div class="side">
      <div class="letter">
        <div class="stamp">Refused</div>
        <div class="lh"><b>CONSULATE — VISA SECTION</b><span>Annex VI</span></div>
        <div class="rl l"></div><div class="rl m"></div><div class="rl s"></div>
        <div class="hl">Ground 2: "The justification for the purpose and conditions of the intended stay was not reliable."</div>
        <div class="rl m"></div><div class="rl l"></div><div class="rl s"></div>
        <div class="lfoot">Decisions may be appealed. This mock is illustrative.</div>
      </div>
      <a class="btn" href="{{ $wa }}?text=Hi%2C%20I%27ve%20had%20a%20Schengen%20refusal%20and%20I%27d%20like%20a%20free%20review%20of%20my%20letter.">Send your letter for a free review</a>
      <div class="lnote"><b>Honest note:</b> Not every refusal is recoverable. If we don't believe we can materially improve your chances, we'll tell you. We'd rather lose a fee than damage your record.</div>
    </div>
    <div>
      <h3 class="selh">What does your refusal letter actually mean?</h3>
      <p class="micro" style="margin:0 0 16px">Find the reason on your letter. Pick the closest match.</p>
      <div class="acc open"><div class="h">"The justification for the purpose and conditions of the intended stay was not reliable." <span class="pm">–</span></div><div class="b"><span class="lab">What it means:</span>The officer didn't believe the reason for the trip — usually when documents contradict each other (a business conference with a tourist booking and no invitation).<span class="lab">What we'd do:</span>Obtain confirmed event or host documentation, align every booking to the stated purpose, draft a cover letter with the supporting correspondence, then resubmit.</div></div>
      <div class="acc"><div class="h">"Your intention to leave the territory before the visa expires could not be ascertained." <span class="pm">+</span></div><div class="b"><span class="lab">What it means:</span>The most common Schengen refusal — the officer wasn't convinced you'd return home.<span class="lab">What we'd do:</span>Build a documented picture of what pulls you back — work, property, dependents, a return booking that matches your leave — and present your travel history so a clean record counts for you.</div></div>
      <div class="acc"><div class="h">"Reasonable doubt exists as to your intention to leave" plus a second ground. <span class="pm">+</span></div><div class="b"><span class="lab">What it means:</span>Two weaknesses flagged at once. Fixing one and reapplying usually earns the same letter back.<span class="lab">What we'd do:</span>Address every listed ground in one rebuilt application, not just the easiest.</div></div>
      <div class="acc"><div class="h">My refusal reason isn't listed here. <span class="pm">+</span></div><div class="b">Send us a photo of your letter on WhatsApp and we'll read it back in plain English — what it means, whether it's recoverable, what we'd change. That read is free.<br><a href="{{ $wa }}?text=Hi%2C%20my%20refusal%20reason%20isn%27t%20on%20your%20list.%20I%27ll%20send%20a%20photo%20of%20my%20letter." style="font-weight:700;display:inline-block;margin-top:10px">Send your letter →</a></div></div>
    </div>
  </div>
</div></section>

{{-- TRUST — dark console + light verify --}}
<section class="sec tr" id="trust"><div class="wrap">
  <div class="grid">
    <div class="dark">
      <p class="eyebrow">How it works</p>
      <h2>Here's exactly what happens when you message us.</h2>
      <p class="sub">No forms, no account, no upsells. Competence first.</p>
      <div class="stp"><span class="n">1</span><div><h3>You message us</h3><p>WhatsApp or email, in your own words. No booking system, no account.</p></div></div>
      <div class="stp"><span class="n">2</span><div><h3>We review — free, within 24 hours</h3><p>We work out whether it's a case we can actually help with. Costs you nothing.</p></div></div>
      <div class="stp"><span class="n">3</span><div><h3>We tell you honestly if we can help</h3><p>If we can, we explain what we'd do and what it costs. If we can't, we tell you why.</p></div></div>
      <div class="stp"><span class="n">4</span><div><h3>If you go ahead, we handle everything</h3><p>Documents, evidence, appointment, counter prep — up to walking out of the centre.</p></div></div>
      <div class="statline"><b>24hr</b><span>We aim to reply to every case check within one working day.</span></div>
    </div>
    <div class="lite">
      <h3>Don't take our word for it.</h3><p class="sub">Verify us independently — or ask us and we'll hand you the reference:</p>
      <a class="vlink" href="{{ $wa }}?text=Hi%2C%20can%20you%20share%20your%20Companies%20House%20registration%20number%20so%20I%20can%20verify%20Beyond%20Passports%3F"><span class="tick">✓</span><div><h4>Company registration <span class="ext">Ask for our number ↗</span></h4><p>Search "Beyond Passports" on Companies House at gov.uk. Registered UK company; number publicly listed.</p></div></a>
      <a class="vlink" href="{{ $wa }}?text=Hi%2C%20can%20you%20share%20your%20ICO%20registration%20number%20so%20I%20can%20check%20the%20ICO%20register%3F"><span class="tick">✓</span><div><h4>Data handling <span class="ext">Ask for our ICO ref ↗</span></h4><p>Registered with the Information Commissioner's Office. Verify on the ICO register.</p></div></a>
      <a class="vlink" href="{{ $wa }}?text=Hi%2C%20can%20you%20send%20me%20the%20official%20document%20checklist%20for%20my%20Schengen%20destination%3F"><span class="tick">✓</span><div><h4>Destination requirements <span class="ext">Ask us for the checklist ↗</span></h4><p>Check the official embassy checklist; we cover every item plus what they don't tell you matters.</p></div></a>
      <a class="vlink" href="{{ $wa }}?text=Hi%2C%20what%20appointment%20availability%20are%20you%20seeing%20for%20my%20Schengen%20country%3F"><span class="tick">✓</span><div><h4>Appointment availability <span class="ext">Ask what we can get ↗</span></h4><p>See what's on the booking site yourself, then ask us what we can get.</p></div></a>
      <div class="founder"><span class="ph"></span><div><b>Beyond Passports</b><span>UK case team · Schengen visa specialists</span></div></div>
    </div>
  </div>
</div></section>

{{-- FAQ — two-up open cards --}}
<section class="sec alt faq" id="faq"><div class="wrap">
  <p class="eyebrow feye">Straight answers</p>
  <h2 class="h2 fhead">Questions you should be asking</h2>
  <p class="fsub">If it's not here, ask us on WhatsApp — we answer in real time, not two business days later.</p>
  <div class="fgrid">
    <div class="fcard"><p class="fq"><span class="qg">Q.</span>What do you actually do that I can't do myself?</p><p class="fa">You can do it all yourself — centres are open, checklists are online, booking is public. What we do is close the gap between what the checklist says and what the officer actually evaluates, and monitor appointment systems so you don't spend weeks refreshing a page.</p></div>
    <div class="fcard"><p class="fq"><span class="qg">Q.</span>How quickly can you get me an appointment?</p><p class="fa">It depends on the country and season — some release daily, others go weeks with nothing. We monitor all 27 states and move the moment something opens. We won't promise a date we can't control; tell us your window and we'll be straight.</p></div>
    <div class="fcard"><p class="fq"><span class="qg">Q.</span>What does this cost?</p><p class="fa">Our service fee is separate from the consulate's visa fee, paid to the government directly. We quote after the free case check — no fixed upsell, no hidden extras. Simpler than expected, you pay less.</p></div>
    <div class="fcard"><p class="fq"><span class="qg">Q.</span>Why WhatsApp instead of a form?</p><p class="fa">A visa case is a conversation, not a ticket. WhatsApp lets you send a photo of your letter, ask a follow-up, and get a real answer the same day. Forms make you wait; we'd rather just talk.</p></div>
    <div class="fcard key wide"><p class="fq"><span class="qg">Q.</span>Can you guarantee approval?</p><p class="fa"><b>No — and be wary of anyone who does.</b> The decision belongs to the consulate, not to us. What we control is preparation: a coherent file, evidence that answers the officer's real questions, and no contradictions to flag. That's what moves the odds. The outcome is never ours to promise.</p></div>
    <div class="fcard"><p class="fq"><span class="qg">Q.</span>I've never heard of Beyond Passports. Why you?</p><p class="fa">Fair. Don't trust the website — verify us. Registered UK company (search Companies House) and registered with the ICO. Message us before you pay anything; judge the free case check on its own.</p></div>
    <div class="fcard wacard"><p class="fq" style="justify-content:center">Still have a question?</p><p class="fa" style="margin-bottom:14px">We answer in real time — send it over.</p><a class="wabtn" href="{{ $wa }}?text=Hi%2C%20I%20have%20a%20question%20about%20my%20Schengen%20visa%3A%20">@include('partials.wa-glyph')Ask on WhatsApp</a></div>
  </div>
</div></section>

{{-- URGENCY — split + action card --}}
<section class="band"><div class="wrap">
  <div>
    <p class="eyebrow">Don't wait to see what happens</p>
    <h2>The window closes faster than you think.</h2>
    <p>Every week, slots get harder to find. Every month is a month closer to your travel date with nothing locked in. The people who get approved aren't luckier than you — they're the ones who didn't wait.</p>
    <p class="em2">You already know something about your application needs attention. That's why you're still reading.</p>
  </div>
  <div class="ucard">
    <p class="k">Free case check</p>
    <h3>Start in one message.</h3>
    <p class="d">Tell us your situation. We reply within 24 hours and say honestly if we can help.</p>
    <a class="btn wa" href="{{ $wa }}?text=Hi%2C%20I%27d%20like%20a%20free%20case%20check%20on%20my%20Schengen%20visa.">@include('partials.wa-glyph')Start with a free case check</a>
    <div class="re"><span><b>No cost.</b> No commitment.</span><span>cases@beyondpassports.co.uk</span></div>
  </div>
</div></section>

</div>

<script>
document.querySelectorAll('.lpb .acc .h').forEach(function(h){h.addEventListener('click',function(){
  var a=h.parentElement,open=a.classList.contains('open');a.classList.toggle('open');
  var pm=h.querySelector('.pm');if(pm)pm.textContent=open?'+':'–';
});});
(function(){var f=document.getElementById('lpbCaseForm');if(!f)return;f.addEventListener('submit',function(e){
  e.preventDefault();
  var n=document.getElementById('lpb-name').value.trim(),p=document.getElementById('lpb-phone').value.trim();
  var msg="Hi, I'd like a free case check on my Schengen visa.";
  if(n)msg+=' My name is '+n+'.';if(p)msg+=' My number is '+p+'.';
  window.open('{{ $wa }}?text='+encodeURIComponent(msg),'_blank');
});})();
</script>
@endsection
