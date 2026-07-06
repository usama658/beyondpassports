@extends('layouts.public')

@section('title', 'Schengen Visa Help — Your Application Has One Chance | Beyond Passports')
@section('description', 'Independent UK Schengen visa help. First-time or refused, we prepare applications that stand up, decode refusal letters and monitor appointments. Free case check on WhatsApp. Not a government website.')

@php $wa = 'https://wa.me/'.config('ukv.whatsapp'); @endphp

@push('head')
<style>
/* ===== Bold LP — page-scoped under .lpb (avoids ukv.css class collisions) ===== */
.lpb{--ink:#16222E;--ink2:#0f2028;--paper:#F4F6FA;--cta:#155E7A;--cta-d:#0F4A61;--stamp:#2E9A8C;--soft:#A9CCDA;--stamp-text:#1F6E63;--on-dark:#79CFC2;--muted:#5d6b76;--edge:#dde3ec;--wa:#25D366;--amber:#E9B872;--red:#c0492f;
  --display:"Outfit",system-ui,sans-serif;--mono:ui-monospace,"Outfit",monospace;--sh:0 18px 44px -26px rgba(20,34,46,.34);--sh2:0 30px 66px -30px rgba(20,34,46,.42);
  background:var(--paper);color:var(--ink);font:400 18px/1.62 var(--display);-webkit-font-smoothing:antialiased}
.lpb *{box-sizing:border-box}
.lpb h1,.lpb h2,.lpb h3,.lpb h4{font-weight:800;line-height:1.12;letter-spacing:-.02em;margin:0}
.lpb .hl{color:var(--stamp-text)}.lpb .hl-r{color:var(--red)}
.lpb a{color:var(--cta);text-decoration:none}
.lpb .wrap{max-width:1140px;margin:0 auto;padding:0 28px}
.lpb .eyebrow{font-weight:800;font-size:.74rem;letter-spacing:.18em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 14px;display:flex;align-items:center;gap:9px}
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
/* HERO — 2-col split w/ form */
.lpb .hero{padding:46px 0 46px;background:linear-gradient(180deg,#EAF1F4,var(--paper) 72%)}
.lpb .hgrid{display:grid;grid-template-columns:1.02fr .98fr;gap:46px;align-items:center}
.lpb .hero h1{font-size:clamp(2.1rem,5vw,3.6rem);letter-spacing:-.02em;max-width:16ch;margin:14px 0 0}
.lpb .hsub{color:var(--muted);font-size:1.18rem;line-height:1.5;max-width:44ch;margin:14px 0 0}
.lpb .heyebrow{color:var(--stamp-text);margin-bottom:0}
.lpb .formcard{background:#fff;border:1px solid var(--edge);border-radius:20px;padding:26px;box-shadow:var(--sh2)}
.lpb .formcard .fl{font-weight:800;font-size:13px;letter-spacing:.1em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 15px;display:flex;align-items:center;gap:8px}
.lpb .formcard .fl .dot{width:8px;height:8px;border-radius:50%;background:var(--wa)}
.lpb .form .row{display:flex;gap:12px;margin:0 0 12px}.lpb .form .fld{flex:1}
.lpb .form label{display:block;font-size:15px;font-weight:700;margin:0 0 6px;color:var(--ink)}
.lpb .form input[type=text]{width:100%;background:var(--paper);border:1px solid var(--edge);border-radius:10px;padding:14px 15px;color:var(--ink);font:600 17px var(--display)}
.lpb .form input:focus{outline:2px solid var(--stamp);outline-offset:1px;border-color:transparent}
.lpb .form .cons{display:flex;gap:8px;align-items:flex-start;margin:13px 0 0;color:var(--muted);font-size:14.5px;line-height:1.45}
.lpb .form .cons input{width:16px;height:16px;flex:none;margin-top:2px}
.lpb .halt{color:var(--muted);font-size:14.5px;margin:16px 0 0}.lpb .halt b{color:var(--ink)}
.lpb .tp{display:flex;align-items:center;gap:11px;flex-wrap:wrap;margin:20px 0 0}
.lpb .tp-logo{display:inline-flex;align-items:center;gap:6px;font-weight:800;font-size:15.5px;color:var(--ink)}
.lpb .tp-logo .s{width:19px;height:19px;fill:#00B67A}
.lpb .tp-stars{display:inline-flex;gap:3px}
.lpb .tp-stars i{width:23px;height:23px;background:#00B67A;display:inline-flex;align-items:center;justify-content:center;border-radius:3px}
.lpb .tp-stars i svg{width:15px;height:15px;fill:#fff}
.lpb .tp-note{color:var(--muted);font-size:12.5px;flex-basis:100%;margin:2px 0 0}
.lpb .combo{position:relative;margin:0 0 12px}
.lpb .cbwrap{position:relative}
.lpb .cbwrap::after{content:"▾";position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;pointer-events:none}
.lpb .cb-input{padding-right:34px!important;cursor:pointer}
.lpb .cb-list{position:absolute;z-index:20;left:0;right:0;top:calc(100% + 6px);background:#fff;border:1px solid var(--edge);border-radius:12px;box-shadow:var(--sh2);max-height:230px;overflow:auto;padding:6px;display:none;list-style:none;margin:0}
.lpb .cb-list.open{display:block}
.lpb .cb-list li{padding:10px 12px;border-radius:8px;font-size:16.5px;font-weight:600;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:10px}
.lpb .cb-list li:hover{background:#eef4f6;color:var(--cta)}
.lpb .cb-list li .fc{font-size:10px;font-weight:800;letter-spacing:.1em;color:var(--muted)}
.lpb .cb-list li.none{color:var(--muted);font-weight:500;cursor:default}.lpb .cb-list li.none:hover{background:transparent}
/* TRUST BAR */
.lpb .tbar-f{background:radial-gradient(600px 200px at 30% 0,rgba(21,94,122,.5),transparent),var(--ink2);color:#dbe8ea;padding:16px 0}
.lpb .tbar-f .trow{display:flex;justify-content:center;gap:38px;flex-wrap:wrap}
.lpb .tbar-f .ti{display:inline-flex;align-items:center;gap:9px;font-size:16px}
.lpb .tbar-f .ti b{color:#fff}.lpb .tbar-f .ti svg{width:20px;height:20px;color:var(--on-dark)}
/* SECTION 2 — start where you are */
.lpb .sec2 .head{text-align:center;max-width:26ch;margin:0 auto 6px;font-size:clamp(28px,3.4vw,38px)}
.lpb .sec2 .s2sub{text-align:center;color:var(--muted);font-size:18px;max-width:52ch;margin:12px auto 26px}
.lpb .grid2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.lpb .ref2{position:relative;background:radial-gradient(500px 340px at 85% 0%,rgba(192,73,47,.28),transparent 60%),var(--ink2);color:#fff;border-radius:20px;padding:28px;box-shadow:var(--sh2)}
.lpb .ref2 .refstamp{display:inline-block;white-space:nowrap;border:2.5px solid #f0a58f;color:#f0a58f;font-weight:800;letter-spacing:.16em;font-size:13px;padding:6px 14px;border-radius:7px;transform:rotate(-5deg);margin:0 0 14px;text-transform:uppercase;box-shadow:inset 0 0 0 2px rgba(240,165,143,.14)}
.lpb .ref2 .ltag{font-weight:800;font-size:13px;letter-spacing:.13em;text-transform:uppercase;color:#f0a58f;margin:0 0 8px}
.lpb .ref2 h3{color:#fff;font-size:24px;margin:0 0 8px}.lpb .ref2 p{color:#cfd9dd;font-size:16.5px;line-height:1.55;margin:0 0 16px}
.lpb .ref2 .btn{background:#f0a58f;color:#3a1a12}
.lpb .chips{display:flex;flex-direction:column;gap:11px}
.lpb .sit{display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--edge);border-left:3px solid var(--cta);border-radius:12px;padding:14px 16px;transition:transform .12s,box-shadow .12s}
.lpb .sit:hover{transform:translateY(-2px);box-shadow:var(--sh)}
.lpb .sit .tx{min-width:0}
.lpb .sit .st{font-weight:800;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--stamp-text);display:block;margin:0 0 4px}
.lpb .sit .q{font-weight:700;font-size:17.5px;margin:0 0 4px}
.lpb .sit .d{color:var(--muted);font-size:15.5px;line-height:1.45;margin:0}
.lpb .sit .chev{margin-left:auto;width:26px;height:26px;border-radius:50%;background:#eef4f6;color:var(--cta);font-size:15px;font-weight:700;display:flex;align-items:center;justify-content:center;flex:none}
/* RISK — ledger: drafted out, see partials/lp-draft-risk-ledger.blade.php */
/* BOARD — scarcity heat cards (light, colour-coded) */
.lpb .bd .btop{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px;margin:0 0 26px}
.lpb .bd .intro{color:var(--muted);font-size:16px;max-width:60ch;margin:12px 0 0}
.lpb .bd .live{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--edge);border-radius:999px;padding:8px 14px;font-weight:800;font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:var(--stamp-text)}
.lpb .bgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.lpb .hot{--c:#c0492f;--cbg:#fdecea;--cbd:#f2c4bb}
.lpb .tight{--c:#b5791f;--cbg:#fdf5e8;--cbd:#f0dcb8}
.lpb .open{--c:#1F6E63;--cbg:#e7f4ef;--cbd:#bfe3d8}
.lpb .hc{background:var(--cbg);border:1px solid var(--cbd);border-left:5px solid var(--c);border-radius:16px;padding:22px}
.lpb .hc .r1{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin:0 0 4px}
.lpb .hc .cty{font-size:20px;font-weight:800}
.lpb .hc .pill{font-weight:800;font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--c);background:#fff;border:1px solid var(--cbd);padding:5px 9px;border-radius:999px;white-space:nowrap}
.lpb .hc .st{color:var(--muted);font-size:13.5px;line-height:1.45;min-height:38px;margin:6px 0 14px}
.lpb .hc .meter{height:8px;border-radius:5px;background:#fff;border:1px solid var(--cbd);overflow:hidden;margin:0 0 12px}
.lpb .hc .meter i{display:block;height:100%;background:var(--c)}
.lpb .hc .foot{display:flex;justify-content:space-between;align-items:center}
.lpb .hc .slots{font-family:var(--mono);font-weight:800;font-size:30px;color:var(--c);line-height:1}
.lpb .hc .slots small{font-size:12px;font-weight:700;color:var(--muted);font-family:var(--display)}
.lpb .hc .av{color:var(--muted);font-size:13px;text-align:right}.lpb .hc .av b{color:var(--ink);display:block;font-size:15px}
.lpb .blegend{display:flex;gap:18px;flex-wrap:wrap;margin-top:18px;color:var(--muted);font-size:13px}
.lpb .blegend i{display:inline-block;width:12px;height:12px;border-radius:3px;margin-right:6px;vertical-align:-1px}
.lpb .bd .bfoot{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-top:24px}
.lpb .bd .bfoot .btn{width:auto;min-width:230px}
.lpb .bd .skip{margin:0 0 10px}.lpb .bd .skip a{color:var(--cta);font-weight:600}
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
.lpb .letter .refstamp{position:absolute;top:20px;right:18px;white-space:nowrap;transform:rotate(8deg);border:2.5px solid var(--red);color:var(--red);font-weight:800;letter-spacing:.16em;font-size:14px;padding:6px 14px;border-radius:7px;opacity:.85;text-transform:uppercase;box-shadow:inset 0 0 0 2px rgba(192,73,47,.12)}
.lpb .letter .lfoot{border-top:1px solid #e6e2d8;margin-top:16px;padding-top:12px;color:#9aa0a3;font-size:11px}
.lpb .lnote{padding:16px;background:#fff;border:1px solid var(--edge);border-radius:12px;color:var(--muted);font-size:14px;line-height:1.55;margin-top:16px}.lpb .lnote b{color:var(--ink)}
.lpb .dec .selh{font-size:18px;margin:0 0 3px}
.lpb .acc{background:#fff;border:1px solid var(--edge);border-radius:12px;margin:0 0 11px;overflow:hidden}
.lpb .acc .h{display:flex;justify-content:space-between;gap:14px;padding:16px 18px;font-weight:700;font-size:14.5px;line-height:1.35;cursor:pointer}
.lpb .acc .h .pm{color:var(--cta);font-size:22px;font-weight:400;flex:none;line-height:.8}
.lpb .acc .b{padding:0 18px 18px;color:var(--muted);font-size:14px;line-height:1.6;display:none}
.lpb .acc.open .b{display:block}
.lpb .acc .b .lab{color:var(--ink);font-weight:700;display:block;margin:11px 0 2px}
/* FEAR — VIS severity meter (exact refusal-risk design) */
.lpb .fear{background:#0A1628;color:#ECF2FB}
.lpb .fear .flabel{display:inline-flex;align-items:center;gap:8px;font-size:.74rem;font-weight:800;letter-spacing:.18em;text-transform:uppercase;color:#FF5A5A;margin-bottom:18px}
.lpb .fear .fsh{max-width:60ch;margin:0 auto 40px;text-align:center}
.lpb .fear .fsh h2{color:#fff;font-size:clamp(1.7rem,3.6vw,2.6rem);font-weight:800;line-height:1.12;letter-spacing:-.02em}
.lpb .fear .fsh h2 .hl{color:#39B89C}
.lpb .fmeter .row{display:grid;grid-template-columns:54px 1fr 120px;gap:20px;align-items:center;padding:18px 22px;background:linear-gradient(180deg,#13284c,#0d1c36);border:1px solid rgba(255,255,255,.09);border-radius:14px;margin-bottom:12px}
.lpb .fmeter .num{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;background:#FF5A5A}
.lpb .fmeter .row h3{font-weight:700;font-size:1.05rem;color:#fff;margin:0}
.lpb .fmeter .row p{color:#9DB1CE;font-size:.93rem;margin:4px 0 0}
.lpb .fmeter .bar{height:8px;border-radius:999px;background:rgba(255,255,255,.08);overflow:hidden}
.lpb .fmeter .bar span{display:block;height:100%;background:linear-gradient(90deg,#ff9a9a,#FF5A5A)}
.lpb .fmeter .row:nth-child(1) .bar span{width:25%}.lpb .fmeter .row:nth-child(2) .bar span{width:50%}.lpb .fmeter .row:nth-child(3) .bar span{width:75%}.lpb .fmeter .row:nth-child(4) .bar span{width:100%}
.lpb .fear .callout{background:rgba(255,90,90,.10);border:1px solid rgba(255,90,90,.3);border-left:4px solid #FF5A5A;border-radius:14px;padding:20px 24px;margin:24px 0}
.lpb .fear .callout p{color:#c9d6e8;font-size:1rem;line-height:1.6;margin:0}.lpb .fear .callout p b{color:#fff}
.lpb .fear .fcta{display:flex;justify-content:center;margin-top:24px}
.lpb .fear .fcta .btn{width:auto;min-width:280px;background:#25D366}
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
/* URGENCY — split + action card */
.lpb .band{background:radial-gradient(760px 420px at 12% 0%,rgba(21,94,122,.55),transparent 62%),radial-gradient(760px 440px at 92% 100%,rgba(46,154,140,.42),transparent 60%),var(--ink2);color:#fff}
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
  .lpb .hgrid,.lpb .grid2,.lpb .dec .grid,.lpb .tr .grid,.lpb .band .wrap{grid-template-columns:1fr}
  .lpb .tbar-f .trow{gap:18px}
  .lpb .band .wrap{gap:26px}
  .lpb .dec .side{position:static}
  .lpb .bgrid{grid-template-columns:1fr 1fr}
  .lpb .faq .fgrid{grid-template-columns:1fr}.lpb .faq .fcard.wide{grid-column:auto}
  .lpb .fmeter .row{grid-template-columns:42px 1fr}.lpb .fmeter .bar{display:none}
  .lpb .sec{padding:56px 0}.lpb .hero{padding:28px 0 44px}
}
</style>
@endpush

@section('content')
<div class="lpb">

{{-- HERO — 2-col split w/ form --}}
<section class="hero"><div class="wrap"><div class="hgrid">
  <div class="hleft">
    <p class="eyebrow heyebrow">Schengen visas · UK applicants</p>
    <h1>A Schengen refusal stays on your record for <span class="hl-r">5 years</span>.</h1>
    <p class="hsub">We know why applications get refused. Tell us where you're going and we'll say honestly if it's a case we can help with.</p>
    <div class="tp">
      <span class="tp-logo"><svg class="s" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l2.9 6.3 6.9.7-5.1 4.6 1.4 6.8L12 17.8 5.9 20.4l1.4-6.8L2.2 9l6.9-.7z"/></svg>Trustpilot</span>
      <span class="tp-stars">@for($i=0;$i<5;$i++)<i><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l2.9 6.3 6.9.7-5.1 4.6 1.4 6.8L12 17.8 5.9 20.4l1.4-6.8L2.2 9l6.9-.7z"/></svg></i>@endfor</span>
      <span class="tp-note">Live rating loads from Trustpilot once the Business Unit ID is connected.</span>
    </div>
    <p class="halt">Prefer to type it yourself? <b>WhatsApp</b> or <a href="mailto:cases@beyondpassports.co.uk">cases@beyondpassports.co.uk</a></p>
  </div>
  <form class="formcard form" id="lpbCaseForm" autocomplete="off">
    <p class="fl"><span class="dot"></span>Free case check · reply within 24 hours</p>
    <div class="row"><div class="fld"><label for="lpb-name">Your name</label><input type="text" id="lpb-name" placeholder="Jane Smith"></div><div class="fld"><label for="lpb-phone">Phone (UK)</label><input type="text" id="lpb-phone" placeholder="07…"></div></div>
    <div class="combo" id="lpbDest">
      <label for="lpb-dest">Destination</label>
      <div class="cbwrap"><input type="text" id="lpb-dest" class="cb-input" placeholder="Search or select a Schengen country…"></div>
      <ul class="cb-list" id="lpbDestList"></ul>
    </div>
    <button class="btn wa" type="submit">@include('partials.wa-glyph')Get my free case check</button>
    <label class="cons"><input type="checkbox" checked><span>I agree to be contacted about my enquiry. We never share your details. <a href="/legal">Privacy</a>.</span></label>
  </form>
</div></div></section>

{{-- TRUST BAR --}}
<section class="tbar-f"><div class="wrap"><div class="trow">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m9 12 2 2 4-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Schengen visa</b> experts</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v10M9.5 9.2c0-1 1.1-1.7 2.5-1.7s2.5.7 2.5 1.7-1.1 1.6-2.5 1.6-2.5.7-2.5 1.7 1.1 1.7 2.5 1.7 2.5-.7 2.5-1.7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg><span><b>No hidden</b> fees</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>7-day</b> support</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M5 21V9l7-5 7 5v12M9 21v-6h6v6" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg><span>Registered in <b>UK &amp; Europe</b></span></span>
</div></div></section>

{{-- SECTION 2 — start where you are --}}
<section class="sec sec2"><div class="wrap">
  <h2 class="head">Not applying fresh? <span class="hl">Start where you are.</span></h2>
  <p class="s2sub">Already refused, mid-process, or just need someone to handle it — pick the path that fits.</p>
  <div class="grid2">
    <div class="ref2">
      <span class="refstamp">Refused</span>
      <p class="ltag">Refusal recovery</p>
      <h3>Start refusal recovery</h3>
      <p>The letter doesn't tell you the real reason. We decode it, find what actually triggered it, and rebuild — or tell you honestly if it can't be recovered.</p>
      <a class="btn" href="{{ $wa }}?text=Hi%2C%20my%20Schengen%20visa%20was%20refused.%20I%27d%20like%20a%20free%20review%20of%20my%20letter.">Send my refusal letter for a free review →</a>
    </div>
    <div class="chips">
      <a class="sit" href="{{ $wa }}?text=Hi%2C%20I%20need%20a%20Schengen%20appointment%20but%20every%20slot%20is%20gone.%20Can%20you%20help%20me%20find%20one%3F"><span class="tx"><span class="st">Need appointment</span><span class="q">"Every slot is gone"</span><p class="d">We monitor all 27 states daily and secure slots most never find.</p></span><span class="chev">→</span></a>
      <a class="sit" href="{{ $wa }}?text=Hi%2C%20I%27ve%20done%20this%20before%20and%20just%20want%20you%20to%20handle%20my%20Schengen%20application."><span class="tx"><span class="st">Experienced</span><span class="q">"Just handle it for me"</span><p class="d">You know it works. We handle paperwork, appointment and details.</p></span><span class="chev">→</span></a>
      <a class="sit" href="{{ $wa }}?text=Hi%2C%20we%27re%20applying%20for%20Schengen%20visas%20together%20and%20our%20documents%20are%20different.%20Can%20you%20help%3F"><span class="tx"><span class="st">Couple or family</span><span class="q">"We're applying together"</span><p class="d">One weak file affects everyone. We prepare them together.</p></span><span class="chev">→</span></a>
    </div>
  </div>
</div></section>

{{-- RISK "Before you apply" ledger — drafted out for now. To restore:
     @include('partials.lp-draft-risk-ledger') --}}

{{-- BOARD — scarcity heat cards. Colour = typical demand, illustrative (not a live feed). --}}
<section class="sec alt bd" id="appointments"><div class="wrap">
  <p class="trans skip"><a href="#refusal">Not looking for appointments? Skip to refusal recovery →</a></p>
  <div class="btop"><div><p class="eyebrow">Typical availability</p><h2 class="h2">Appointment availability this week</h2><p class="intro">Every Schengen application needs an in-person appointment. Colour shows how tight each country typically is right now — for most, the next slot is weeks away.</p></div><span class="live"><span class="dot"></span>Typical this week</span></div>
  <div class="bgrid">
    <div class="hc hot"><div class="r1"><span class="cty">France</span><span class="pill">Very tight</span></div><p class="st">Slots appear and vanish within minutes.</p><div class="meter"><i style="width:94%"></i></div><div class="foot"><div class="slots">12<small> this week</small></div><div class="av">Our avg<b>3–7 days</b></div></div></div>
    <div class="hc hot"><div class="r1"><span class="cty">Italy</span><span class="pill">Very tight</span></div><p class="st">Regularly no availability for weeks.</p><div class="meter"><i style="width:97%"></i></div><div class="foot"><div class="slots">08<small> this week</small></div><div class="av">Our avg<b>5–10 days</b></div></div></div>
    <div class="hc tight"><div class="r1"><span class="cty">Spain</span><span class="pill">Tight</span></div><p class="st">High demand, very limited same-month.</p><div class="meter"><i style="width:86%"></i></div><div class="foot"><div class="slots">06<small> this week</small></div><div class="av">Our avg<b>5–9 days</b></div></div></div>
    <div class="hc tight"><div class="r1"><span class="cty">Greece</span><span class="pill">Tight</span></div><p class="st">Limited UK allocation, seasonal demand.</p><div class="meter"><i style="width:84%"></i></div><div class="foot"><div class="slots">05<small> this week</small></div><div class="av">Our avg<b>4–8 days</b></div></div></div>
    <div class="hc tight"><div class="r1"><span class="cty">Netherlands</span><span class="pill">Tight</span></div><p class="st">Short windows that fill within hours.</p><div class="meter"><i style="width:88%"></i></div><div class="foot"><div class="slots">07<small> this week</small></div><div class="av">Our avg<b>3–6 days</b></div></div></div>
    <div class="hc open"><div class="r1"><span class="cty">Germany</span><span class="pill">Some slots</span></div><p class="st">Released in unpredictable batches.</p><div class="meter"><i style="width:74%"></i></div><div class="foot"><div class="slots">09<small> this week</small></div><div class="av">Our avg<b>4–8 days</b></div></div></div>
  </div>
  <div class="blegend"><span><i style="background:#c0492f"></i>Very tight</span><span><i style="background:#b5791f"></i>Tight</span><span><i style="background:#1F6E63"></i>Some slots</span><span>· every Schengen country covered — ask us about yours</span></div>
  <div class="bfoot"><span class="urgent">⏱ Travelling within 3 weeks? Tell us now — some slots can't wait.</span>
    <a class="btn" href="{{ $wa }}?text=Hi%2C%20I%20need%20a%20Schengen%20appointment.%20My%20travel%20dates%20are%3A%20">Secure my appointment →</a></div>
</div></section>

{{-- DECODER — redacted letter + selector --}}
<section class="sec alt dec" id="refusal"><div class="wrap">
  <div class="top"><p class="eyebrow">Refusal recovery</p><h2 class="h2">A refusal is not the end. Your next move decides everything.</h2><p class="micro">A rushed reapplication with the same paperwork gets the same result. A new application to a different country carries the same flag. We take refused cases apart, find what actually went wrong, then rebuild.</p></div>
  <div class="grid">
    <div class="side">
      <div class="letter">
        <div class="refstamp">Refused</div>
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

{{-- FEAR — VIS severity meter (exact refusal-risk design) --}}
<section class="sec fear" id="vis-risk"><div class="wrap">
  <div class="fsh"><div class="flabel">The fear mechanism</div><h2>The Visa Information System <span class="hl">remembers everything.</span></h2></div>
  <div class="fmeter">
    <div class="row"><div class="num">1</div><div><h3>You get refused</h3><p>You apply for a Schengen visa. You get refused. That refusal gets logged in a shared EU database called VIS.</p></div><div class="bar"><span></span></div></div>
    <div class="row"><div class="num">2</div><div><h3>27 countries can see it</h3><p>France. Germany. Italy. Spain. All of them. They see the refusal before they even open your next application.</p></div><div class="bar"><span></span></div></div>
    <div class="row"><div class="num">3</div><div><h3>It stays for 5 years</h3><p>Not 1. Not 2. Five years on a shared record that follows every future application.</p></div><div class="bar"><span></span></div></div>
    <div class="row"><div class="num">4</div><div><h3>You start at minus one</h3><p>Your next application does not start at zero. The burden of proof flips to you. You now have to prove you are not a risk.</p></div><div class="bar"><span></span></div></div>
  </div>
  <div class="callout"><p><b>We reviewed 600+ refusal letters last year.</b> Over half were preventable. Wrong bank statements. Missing employer letters. Itineraries that did not add up. The kind of thing a 30 minute review would have caught.</p></div>
  <div class="fcta"><a class="btn wa" href="{{ $wa }}?text=Hi%2C%20I%27d%20like%20a%20free%20risk%20check%20on%20my%20Schengen%20documents%20before%20I%20apply.">@include('partials.wa-glyph')Send us your documents for a free risk check</a></div>
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
(function(){
  // Destination searchable select
  var COUNTRIES=["Austria","Belgium","Bulgaria","Croatia","Czechia","Denmark","Estonia","Finland","France","Germany","Greece","Hungary","Iceland","Italy","Latvia","Liechtenstein","Lithuania","Luxembourg","Malta","Netherlands","Norway","Poland","Portugal","Romania","Slovakia","Slovenia","Spain","Sweden","Switzerland"];
  var CODES={Austria:"AT",Belgium:"BE",Bulgaria:"BG",Croatia:"HR",Czechia:"CZ",Denmark:"DK",Estonia:"EE",Finland:"FI",France:"FR",Germany:"DE",Greece:"GR",Hungary:"HU",Iceland:"IS",Italy:"IT",Latvia:"LV",Liechtenstein:"LI",Lithuania:"LT",Luxembourg:"LU",Malta:"MT",Netherlands:"NL",Norway:"NO",Poland:"PL",Portugal:"PT",Romania:"RO",Slovakia:"SK",Slovenia:"SI",Spain:"ES",Sweden:"SE",Switzerland:"CH"};
  var inp=document.getElementById('lpb-dest'),list=document.getElementById('lpbDestList'),combo=document.getElementById('lpbDest');
  if(inp&&list){
    function render(q){q=(q||'').toLowerCase();list.innerHTML='';
      var items=COUNTRIES.filter(function(c){return c.toLowerCase().indexOf(q)>-1}).map(function(c){return {n:c,c:CODES[c]}});
      [{n:"Not sure yet",c:"—"},{n:"Multiple countries",c:"—"}].forEach(function(e){if(e.n.toLowerCase().indexOf(q)>-1)items.push(e)});
      if(!items.length){list.innerHTML='<li class="none">No match — type your destination</li>';return;}
      items.forEach(function(o){var li=document.createElement('li');li.innerHTML='<span>'+o.n+'</span><span class="fc">'+o.c+'</span>';li.onmousedown=function(ev){ev.preventDefault();inp.value=o.n;list.classList.remove('open');};list.appendChild(li);});}
    inp.addEventListener('focus',function(){render(inp.value);list.classList.add('open');});
    inp.addEventListener('input',function(){render(inp.value);list.classList.add('open');});
    document.addEventListener('click',function(e){if(combo&&!combo.contains(e.target))list.classList.remove('open');});
  }
  var f=document.getElementById('lpbCaseForm');if(!f)return;f.addEventListener('submit',function(e){
    e.preventDefault();
    var n=document.getElementById('lpb-name').value.trim(),p=document.getElementById('lpb-phone').value.trim(),d=inp?inp.value.trim():'';
    var msg="Hi, I'd like a free case check on my Schengen visa.";
    if(d)msg+=' My destination is '+d+'.';
    if(n)msg+=' My name is '+n+'.';if(p)msg+=' My number is '+p+'.';
    window.open('{{ $wa }}?text='+encodeURIComponent(msg),'_blank');
  });
})();
</script>
@endsection
