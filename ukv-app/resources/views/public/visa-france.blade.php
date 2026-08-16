{{-- /visa/france — destination-page PILOT (competitor-SWOT-driven, docs/competitor-swot.md).
     STAGING: renders only when config('ukv.destinations.france_pilot') is true OR the request
     carries ?preview=bp2026; otherwise the route falls through to the drafted redirect.
     Section stack + conversion copy per the approved list appended to competitor-swot.md. --}}
@extends('layouts.public')

@section('title', 'France Visa from the UK | TLScontact Appointments Tracked Daily | Beyond Passports')
@section('description', 'France Schengen visa help from the UK. We watch the TLScontact calendar in London, Manchester and Edinburgh and take the earliest date. £130 total, £90 only after your appointment is booked.')

@php
  $wa = 'https://wa.me/'.config('ukv.whatsapp');
  $faqs = [
    ['q' => 'How do I get a France Schengen visa appointment from the UK?', 'a' => 'France uses TLScontact centres in London, Manchester and Edinburgh. You need a France-Visas application reference before the TLS calendar will show you dates. We complete the France-Visas form, generate your reference, then watch the calendar and take the earliest date that fits your travel.'],
    ['q' => 'How long does a France visa take from the UK?', 'a' => 'The consulate\'s published processing time is 10 to 15 working days after your biometric appointment, longer in peak season. Appointment lead time comes on top, so start 6 to 8 weeks before travel where you can. We cannot speed up the consulate\'s decision, only how fast your file is ready and submitted.'],
    ['q' => 'I\'m on a UK residence permit. Can I apply for a France visa from the UK?', 'a' => 'Yes. If you legally reside in the UK you apply from the UK, whatever passport you hold. Your UK immigration status documents form part of the file, and we check them before anything is submitted.'],
    ['q' => 'What does the France visa cost in total?', 'a' => 'Three parts: the consulate fee of €90 for adults (paid to the consulate), the TLScontact service charge (paid to TLS, confirmed at booking), and our £130 fee, of which £90 is only due once your appointment is booked.'],
    ['q' => 'What travel insurance do I need for France?', 'a' => 'Schengen rules require medical cover of at least €30,000, valid across the whole Schengen Area for your full stay. A compliant certificate is part of the file pack we prepare.'],
    ['q' => 'I was refused a Schengen visa before. Does that affect a France application?', 'a' => 'A refusal sits on the Schengen system for five years and every consulate can see it. It does not block a new application, but the new file has to answer the refusal reason. We review the refusal letter first and tell you honestly whether and how to reapply.'],
    ['q' => 'I\'m self-employed. What extra documents does France ask for?', 'a' => 'Typically your last tax return or SA302, business bank statements, and proof of your business (Companies House entry or invoices). We give you the exact list for your situation and check each document before submission.'],
    ['q' => 'When are France appointments hardest to get?', 'a' => 'Spring and summer, and the weeks before school holidays. The TLScontact calendar releases dates in batches, so empty today does not mean empty tomorrow. That is why we watch it rather than asking you to keep refreshing.'],
  ];
@endphp

@push('head')
<style>
.vfr{--edge:#dde3ec;--stamp:#2E9A8C;--stampt:#1F6E63;--cta:#155E7A;--paper:#F4F6FA;--muted:#5d6b76;--ink:#16222E;--wa:#25D366;--mint:#8fe3c9;
  font-family:'Outfit',system-ui,-apple-system,sans-serif;color:var(--ink);background:var(--paper)}
.vfr .wrap{max-width:1080px;margin:0 auto;padding:0 20px}
.vfr section{padding:44px 0}
.vfr h2{font-size:clamp(24px,3.4vw,32px);font-weight:800;letter-spacing:-.02em;margin:0 0 8px}
.vfr .sub{color:var(--muted);font-size:15px;max-width:62ch;margin:0 0 22px}
.vfr .eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:800;letter-spacing:.09em;text-transform:uppercase;color:var(--stampt);margin-bottom:10px}
.vfr .eyebrow::before{content:'';width:26px;height:3px;border-radius:2px;background:var(--stamp)}

/* 1 · hero — split: copy + proof badges left, white availability form right */
.vfr .hero{padding:54px 0 50px;background:linear-gradient(180deg,#eef3f6,var(--paper) 72%)}
.vfr .hgrid{display:grid;grid-template-columns:1.35fr 1fr;gap:40px;align-items:center}
.vfr .hero h1{font-size:clamp(28px,4.2vw,44px);font-weight:800;letter-spacing:-.03em;line-height:1.08;margin:14px 0 0;max-width:20ch}
.vfr .hero h1 b{color:var(--stampt)}
.vfr .hsub{font-size:16.5px;color:var(--muted);line-height:1.55;max-width:52ch;margin:14px 0 0}
.vfr .hprice{font-size:14.5px;color:var(--ink);line-height:1.7;font-weight:600;background:rgba(46,154,140,.07);border:1px solid rgba(46,154,140,.2);border-left:3px solid var(--stamp);border-radius:12px;padding:14px 18px;margin:18px 0 0}
/* proof badges (refund-badge style) */
.vfr .pbadges{display:flex;flex-wrap:wrap;gap:12px;margin:18px 0 0}
.vfr .pbadge{display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--edge);border-radius:12px;padding:12px 15px;box-shadow:0 12px 28px -22px rgba(20,34,46,.5)}
.vfr .pbadge .sl{flex:none;width:38px;height:38px;border-radius:50%;background:rgba(46,154,140,.12);border:1.5px solid rgba(46,154,140,.42);display:grid;place-items:center;position:relative}
.vfr .pbadge .sl svg{width:19px;height:19px;fill:var(--stampt)}
.vfr .pbadge .sl .pound{font:800 17px 'Outfit',sans-serif;color:var(--stampt);position:relative;z-index:1}
.vfr .pbadge .sl.glow{animation:vfrSeal 2s ease-in-out infinite}
.vfr .pbadge .sl .wave{position:absolute;inset:-4px;border-radius:50%;border:2px solid var(--stamp);animation:vfrWave 2s ease-out infinite;pointer-events:none}
@keyframes vfrSeal{0%,100%{box-shadow:0 0 4px -2px rgba(46,154,140,.3)}50%{box-shadow:0 0 18px 3px rgba(46,154,140,.85)}}
@keyframes vfrWave{0%{transform:scale(1);opacity:.8}100%{transform:scale(1.7);opacity:0}}
.vfr .pbadge .tx b{display:block;font:800 13.5px 'Outfit',sans-serif;color:var(--ink)}
.vfr .pbadge .tx span{display:block;font:600 11.5px 'Outfit',sans-serif;color:var(--muted);margin-top:1px;line-height:1.4}
/* white availability form card */
.vfr .fcard{background:#fff;border:1px solid var(--edge);border-radius:20px;padding:24px;box-shadow:0 30px 66px -30px rgba(20,34,46,.42)}
.vfr .fc-eye{display:flex;align-items:center;gap:9px;font:800 11px 'Outfit',sans-serif;letter-spacing:.1em;text-transform:uppercase;color:var(--stampt);margin-bottom:4px}
.vfr .fc-eye i{width:7px;height:7px;border-radius:50%;background:var(--stamp);display:inline-block;animation:vfrblink 1.6s infinite}
@keyframes vfrblink{50%{opacity:.3}}
.vfr .fc-h{font:800 19px 'Outfit',sans-serif;letter-spacing:-.01em;margin:0 0 14px;display:flex;align-items:center;gap:9px}
.vfr .fc-flag{width:24px;height:16px;border-radius:3px;box-shadow:0 0 0 1px rgba(0,0,0,.1)}
.vfr .cchips{display:flex;gap:6px;flex-wrap:wrap;margin:0 0 16px}
.vfr .cchips span{font:700 11px 'Outfit',sans-serif;background:var(--paper);border:1px solid var(--edge);border-radius:8px;padding:6px 9px;color:var(--muted)}
.vfr .cchips span.dl{color:var(--stampt)}
.vfr .fl{font:800 12px 'Outfit',sans-serif;color:var(--ink);margin:0 0 7px;display:block}
.vfr .fl .rq{color:var(--red)}
.vfr .tf{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px}
.vfr .tf button{border:1.5px solid var(--edge);background:#fff;border-radius:11px;padding:11px 6px;font:700 13px 'Outfit',sans-serif;color:var(--ink);cursor:pointer;transition:all .12s}
.vfr .tf button:hover{border-color:var(--stamp)}
.vfr .tf button.sel{background:var(--stampt);border-color:var(--stampt);color:#fff}
.vfr .tf.err{outline:2px solid rgba(192,73,47,.5);outline-offset:3px;border-radius:12px}
.vfr .pc{position:relative;display:flex;border:1px solid var(--edge);border-radius:11px;background:var(--paper);margin-bottom:14px}
.vfr .pc:focus-within{border-color:var(--cta);box-shadow:0 0 0 3px rgba(21,94,122,.12);background:#fff}
.vfr .pc .cc{display:flex;align-items:center;gap:7px;padding:0 12px;border-right:1px solid var(--edge);font:700 14px 'Outfit',sans-serif;white-space:nowrap}
.vfr .pc .cc img{width:20px;height:14px;border-radius:2px}
.vfr .pc input{flex:1;border:0;background:transparent;padding:13px 14px;font:600 15px 'Outfit',sans-serif;outline:none;color:var(--ink)}
.vfr .sbtn{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;background:var(--wa);color:#fff;border:0;border-radius:13px;font:800 16px 'Outfit',sans-serif;padding:15px;cursor:pointer;box-shadow:0 14px 30px -12px rgba(37,211,102,.65);transition:filter .15s,transform .12s}
.vfr .sbtn:hover{filter:brightness(.95);transform:translateY(-1px)}
.vfr .sbtn[disabled]{opacity:.6;cursor:wait}
.vfr .sbtn svg{width:17px;height:17px;fill:#fff}
.vfr .fnote{font:600 11.5px 'Outfit',sans-serif;color:var(--muted);text-align:center;margin:10px 0 0}
.vfr .fthx{text-align:center;padding:6px 0}
.vfr .fthx[hidden]{display:none}
.vfr .fthx .tick{width:56px;height:56px;border-radius:50%;background:rgba(46,154,140,.14);border:2px solid var(--stamp);display:grid;place-items:center;margin:0 auto 12px}
.vfr .fthx .tick svg{width:26px;height:26px;fill:none;stroke:var(--stampt);stroke-width:3;stroke-linecap:round;stroke-linejoin:round}
.vfr .fthx h4{font:800 18px 'Outfit',sans-serif;margin:0 0 6px}
.vfr .fthx p{font-size:13.5px;color:var(--muted);line-height:1.6;margin:0}
@media(max-width:860px){.vfr .hgrid{grid-template-columns:1fr}}

/* 2 · trust bar — 1:1 with live .tbar-f */
.vfr .tbar-f{background:radial-gradient(600px 200px at 30% 0,rgba(21,94,122,.5),transparent),#0f2028;color:#dbe8ea;padding:0}
.vfr .tbar-f .wrap{max-width:1140px}
.vfr .tbar-f .row{display:flex;justify-content:center;gap:38px;flex-wrap:wrap;padding:16px 0}
.vfr .tbar-f .ti{display:inline-flex;align-items:center;gap:9px;font-size:16px}
.vfr .tbar-f .ti b{color:#fff}.vfr .tbar-f .ti svg{width:20px;height:20px;color:#79CFC2}
@media(max-width:560px){.vfr .tbar-f .row{gap:18px}}

/* 3+4 · price (dark navy card) + process (ringed timeline), 2-col */
.vfr .fp{background:radial-gradient(760px 300px at 50% -8%,#eef3f6,var(--paper))}
/* D2-parity · scope section/heading/eyebrow to match darkleft-D2 preview without touching other sections [fp-d2-parity] */
.vfr section.fp{padding:46px 0}
.vfr .fp h2{font-size:clamp(22px,2.8vw,28px);margin:0 0 6px}
.vfr .fp .eyebrow{font-size:11.5px;margin-bottom:9px}
.vfr .fp .eyebrow::before{width:24px}
.vfr .fp-two{display:grid;grid-template-columns:1fr 1.05fr;gap:36px;align-items:center}
/* dark navy price card (D2) */
.vfr .dprice{position:relative;overflow:hidden;background:linear-gradient(160deg,#0f2028,#16323c);color:#fff;border-radius:20px;padding:28px;box-shadow:0 40px 90px -46px rgba(20,34,46,.6)}
.vfr .dprice::after{content:'';position:absolute;right:-70px;top:-90px;width:240px;height:240px;border-radius:50%;background:radial-gradient(circle,rgba(143,227,201,.16),transparent 65%);pointer-events:none}
.vfr .dprice .eyebrow{color:var(--mint);position:relative;z-index:1}.vfr .dprice .eyebrow::before{background:var(--mint)}
.vfr .dprice h2{color:#fff;position:relative;z-index:1}
.vfr .dprice .hero-n{position:relative;z-index:1;display:flex;align-items:baseline;gap:8px;margin:6px 0 16px;flex-wrap:wrap}
.vfr .dprice .hero-n .big{font:800 48px 'Outfit',sans-serif;color:var(--mint);letter-spacing:-.03em}
.vfr .dprice .hero-n .cap{font:700 13px 'Outfit',sans-serif;color:rgba(255,255,255,.7)}
.vfr .dprice .fee{position:relative;z-index:1;background:none;border:0;max-width:none;border-radius:0}
.vfr .dprice .fr{display:flex;justify-content:space-between;gap:10px;padding:12px 0;border-bottom:1px solid rgba(255,255,255,.12);font-size:13.5px}
.vfr .dprice .fr:last-child{border-bottom:0}
.vfr .dprice .fr span:first-child{color:rgba(255,255,255,.88)}.vfr .dprice .fr small{display:block;color:rgba(255,255,255,.5);font-size:11px;font-weight:600;margin-top:1px}
.vfr .dprice .fr b{color:#fff;white-space:nowrap}
.vfr .dprice .pn{position:relative;z-index:1;display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}
.vfr .dprice .pn span{background:rgba(255,255,255,.1);border:1px solid rgba(143,227,201,.35);color:var(--mint);border-radius:11px;padding:9px 13px;font:800 12px 'Outfit',sans-serif}
.vfr .dprice .pn span.hot{background:rgba(233,184,114,.16);border-color:rgba(233,184,114,.5);color:#E9B872}
.vfr .dprice .pnote{position:relative;z-index:1;font-size:11.5px;color:rgba(255,255,255,.6);margin:14px 0 0;line-height:1.6}
/* ringed timeline */
.vfr .fp-flow h2{margin-bottom:16px}
.vfr .steps{display:flex;flex-direction:column;gap:0;background:none;border:0;box-shadow:none;border-radius:0;padding:0}
.vfr .st{display:flex;gap:14px;padding-bottom:16px;background:none;border:0;border-radius:0}
.vfr .st:last-child{padding-bottom:0}
.vfr .st .rail{flex:none;display:flex;flex-direction:column;align-items:center}
.vfr .st .n{width:34px;height:34px;border-radius:50%;background:#fff;border:2px solid var(--stamp);color:var(--stampt);display:grid;place-items:center;font:800 14px 'Outfit',sans-serif;margin:0}
.vfr .st.you .n{border-color:#b5791f;color:#b5791f}
.vfr .st .ln{flex:1;width:2px;background:linear-gradient(var(--stamp),var(--edge));margin:4px 0}.vfr .st:last-child .ln{display:none}
.vfr .st b{display:block;font-size:14.5px;margin-bottom:2px}
.vfr .st p{font-size:12px;color:var(--muted);line-height:1.5;margin:0}
.vfr .st .who{display:inline-block;font:800 9px 'Outfit',sans-serif;letter-spacing:.08em;text-transform:uppercase;color:var(--stampt);background:rgba(46,154,140,.1);border-radius:5px;padding:2px 6px;margin-bottom:5px}
.vfr .st.you .who{color:#b5791f;background:rgba(181,121,31,.1)}
@media(max-width:900px){.vfr .fp-two{grid-template-columns:1fr;gap:26px}}
@media(max-width:560px){.vfr .dprice{padding:22px}.vfr .dprice .hero-n .big{font-size:40px}}

/* 5+6 · docs accordion (left) + dark deliverables pack (right), 2-col [docs-pack-final] */
.vfr .dpx .two{display:grid;grid-template-columns:1fr 1fr;gap:36px;align-items:start}
.vfr .dpx .colh{margin-bottom:16px}
.vfr .dpx .colh h2{font-size:clamp(22px,2.8vw,28px)}
/* left — accordion (uses generic .vfr summary +/- marker) */
.vfr details{background:#fff;border:1px solid var(--edge);border-radius:14px;padding:0}
.vfr summary{cursor:pointer;font:800 14.5px 'Outfit',sans-serif;padding:15px 18px;list-style:none;display:flex;justify-content:space-between;align-items:center}
.vfr summary::after{content:'+';font-size:18px;color:var(--stampt)}
.vfr details[open] summary::after{content:'\2212'}
.vfr details ul{margin:0;padding:0 18px 15px 34px;font-size:13.5px;color:var(--muted);line-height:1.8}
.vfr .dpx details{margin-bottom:10px;box-shadow:0 14px 34px -28px rgba(20,34,46,.5)}
.vfr .dpx summary .who{font:800 9px 'Outfit',sans-serif;letter-spacing:.06em;text-transform:uppercase;color:var(--stampt);background:rgba(46,154,140,.1);border-radius:5px;padding:3px 7px;margin-left:auto;margin-right:12px}
.vfr .dpx details ul{padding:2px 18px 15px 18px;list-style:none;font-size:13px;line-height:1.5}
.vfr .dpx details li{position:relative;padding:6px 0 6px 24px;border-top:1px solid var(--edge)}
.vfr .dpx details li:first-child{border-top:0}
.vfr .dpx details li::before{content:'';position:absolute;left:2px;top:11px;width:12px;height:12px;border-radius:50%;background:rgba(46,154,140,.14);border:1.5px solid var(--stamp)}
/* right — dark deliverables pack (P2) */
.vfr .packwrap{position:relative;overflow:hidden;background:linear-gradient(160deg,#0f2028,#16323c);border-radius:20px;padding:28px;color:#fff;box-shadow:0 40px 90px -46px rgba(20,34,46,.6);display:flex;flex-direction:column}
.vfr .packwrap::after{content:'';position:absolute;right:-70px;top:-90px;width:240px;height:240px;border-radius:50%;background:radial-gradient(circle,rgba(143,227,201,.16),transparent 65%);pointer-events:none}
.vfr .packwrap .eyebrow{color:var(--mint);position:relative;z-index:1}.vfr .packwrap .eyebrow::before{background:var(--mint)}
.vfr .packwrap h2{color:#fff;position:relative;z-index:1;margin-bottom:6px;font-size:clamp(22px,2.8vw,28px)}
.vfr .packwrap .psub{position:relative;z-index:1;font-size:13px;color:rgba(255,255,255,.62);margin:0 0 16px;line-height:1.5}
.vfr .packwrap .cnt{position:relative;z-index:1;display:inline-flex;align-items:center;gap:7px;background:rgba(143,227,201,.14);border:1px solid rgba(143,227,201,.35);color:var(--mint);border-radius:999px;padding:6px 12px;font:800 11px 'Outfit',sans-serif;margin:0 0 12px;align-self:flex-start}
.vfr .pk{position:relative;z-index:1;display:flex;gap:13px;align-items:flex-start;background:rgba(255,255,255,.05);border:1px solid rgba(143,227,201,.24);border-radius:13px;padding:14px 15px;margin-bottom:10px;transition:background .15s,border-color .15s}
.vfr .pk:last-child{margin-bottom:0}
.vfr .pk:hover{background:rgba(255,255,255,.09);border-color:rgba(143,227,201,.45)}
.vfr .pk .ic{flex:none;width:40px;height:40px;border-radius:11px;background:rgba(143,227,201,.14);display:grid;place-items:center}
.vfr .pk .ic svg{width:20px;height:20px;fill:var(--mint)}
.vfr .pk b{display:block;font-size:14px;margin-bottom:2px;color:#fff}
.vfr .pk p{font-size:12.5px;color:rgba(255,255,255,.62);margin:0;line-height:1.5}
.vfr .pk .no{margin-left:auto;flex:none;color:var(--mint);font:800 11px 'Outfit',sans-serif;opacity:.5}
/* footer — B5 shield badge */
.vfr .packfoot{position:relative;z-index:1;display:flex;align-items:center;gap:13px;margin-top:16px;border-top:1px solid rgba(255,255,255,.12);padding-top:16px}
.vfr .packfoot .sh{flex:none;width:40px;height:46px;display:grid;place-items:center;filter:drop-shadow(0 6px 14px rgba(143,227,201,.25))}
.vfr .packfoot .sh svg{width:40px;height:46px}
.vfr .packfoot .tx b{display:block;font:800 12.5px 'Outfit',sans-serif;color:var(--mint);letter-spacing:.02em;margin-bottom:3px}
.vfr .packfoot .tx span{font-size:12px;color:rgba(255,255,255,.66);line-height:1.45}
@media(max-width:860px){.vfr .dpx .two{grid-template-columns:1fr}}

/* 7 · do/don't — light "do" card + dark navy "don't" card (premium bookend) [dd-bookend] */
.vfr .dd{display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:stretch}
.vfr .ddc{border-radius:18px;padding:24px}
.vfr .ddc.do{background:#fff;border:1px solid var(--edge);box-shadow:0 20px 44px -34px rgba(20,34,46,.5)}
.vfr .ddc.dont{position:relative;overflow:hidden;background:linear-gradient(160deg,#0f2028,#16323c);box-shadow:0 40px 90px -46px rgba(20,34,46,.6)}
.vfr .ddc.dont::after{content:'';position:absolute;right:-70px;top:-90px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(233,184,114,.16),transparent 65%);pointer-events:none}
.vfr .ddc .hd{display:flex;align-items:center;gap:11px;margin-bottom:16px;position:relative;z-index:1}
.vfr .ddc .hd .bdg{flex:none;width:36px;height:36px;border-radius:10px;display:grid;place-items:center}
.vfr .ddc.do .hd .bdg{background:rgba(46,154,140,.12)}.vfr .ddc.dont .hd .bdg{background:rgba(233,184,114,.16)}
.vfr .ddc .hd .bdg svg{width:19px;height:19px}.vfr .ddc.do .hd .bdg svg{fill:var(--stampt)}.vfr .ddc.dont .hd .bdg svg{fill:#E9B872}
.vfr .ddc h3{font:800 17px 'Outfit',sans-serif;margin:0}.vfr .ddc.dont h3{color:#fff}
.vfr .ddc ul{list-style:none;margin:0;padding:0}
.vfr .ddc li{position:relative;z-index:1;display:flex;gap:11px;align-items:flex-start;padding:11px 0;font-size:13.5px;line-height:1.5}
.vfr .ddc.do li{color:var(--ink);border-top:1px solid var(--edge)}.vfr .ddc.do li:first-child{border-top:0}
.vfr .ddc.dont li{color:rgba(255,255,255,.82);border-top:1px solid rgba(255,255,255,.1)}.vfr .ddc.dont li:first-child{border-top:0}
.vfr .ddc li .mk{flex:none;width:19px;height:19px;margin-top:1px}
.vfr .ddc.do li .mk svg{width:19px;height:19px;fill:var(--stampt)}
.vfr .ddc.dont li .mk svg{width:19px;height:19px;fill:#E9B872}

/* 8 · refusal — hero statement, giant "5 YEARS" watermark, centred CTA [ref-hero] */
.vfr .ref{position:relative;overflow:hidden;background:linear-gradient(160deg,#0f2028,#16323c);border-radius:20px;padding:40px 34px;color:#fff;text-align:center;box-shadow:0 44px 96px -48px rgba(20,34,46,.65)}
.vfr .ref::after{content:'';position:absolute;right:-90px;top:-110px;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(233,184,114,.14),transparent 66%);pointer-events:none}
.vfr .ref .big{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);font:800 200px 'Outfit',sans-serif;color:rgba(255,255,255,.03);letter-spacing:-.05em;z-index:0;pointer-events:none;white-space:nowrap;line-height:1}
.vfr .ref .in{position:relative;z-index:1;max-width:600px;margin:0 auto}
.vfr .ref .eyebrow{color:#E9B872;justify-content:center}.vfr .ref .eyebrow::before{background:#E9B872}
.vfr .ref h3{font-size:28px;font-weight:800;letter-spacing:-.02em;margin:0 0 8px}
.vfr .ref h3 b{color:var(--mint)}
.vfr .ref p{font-size:14px;color:rgba(255,255,255,.72);margin:0 auto 20px;line-height:1.6;max-width:52ch}
.vfr .refcta{display:inline-flex;align-items:center;gap:9px;background:var(--wa);color:#fff;border-radius:12px;font:800 14px 'Outfit',sans-serif;padding:13px 22px;text-decoration:none;white-space:nowrap;box-shadow:0 16px 34px -16px rgba(37,211,102,.6)}
.vfr .refcta svg{width:16px;height:16px;fill:#fff}
.vfr .ref .trust{margin-top:14px;font:600 12px 'Outfit',sans-serif;color:rgba(255,255,255,.5);display:flex;align-items:center;justify-content:center;gap:8px}
.vfr .ref .trust svg{width:14px;height:14px;fill:var(--mint);flex:none}
@media(max-width:560px){.vfr .ref{padding:30px 20px}.vfr .ref .big{font-size:120px}.vfr .ref h3{font-size:23px}}

/* 9 · faq */
.vfr .faq details{margin-bottom:10px}
.vfr .faq summary{font-size:14.5px}
.vfr .faq details div{padding:0 18px 15px;font-size:13.5px;color:var(--muted);line-height:1.7}

/* 9 · reviews — dark navy feature card + two light cards [reviews-B] */
.vfr .rgrid{display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:14px}
.vfr .rc{border-radius:16px;padding:22px;display:flex;flex-direction:column}
.vfr .rc.light{background:#fff;border:1px solid var(--edge);box-shadow:0 18px 40px -32px rgba(20,34,46,.5)}
.vfr .rc.dark{position:relative;overflow:hidden;background:linear-gradient(160deg,#0f2028,#16323c);color:#fff;box-shadow:0 40px 90px -46px rgba(20,34,46,.6)}
.vfr .rc.dark::after{content:'';position:absolute;right:-60px;top:-80px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(143,227,201,.16),transparent 65%);pointer-events:none}
.vfr .rc .st5{color:#e2a63d;font-size:13px;letter-spacing:2px;margin-bottom:12px;position:relative;z-index:1}
.vfr .rc p{font-size:13px;line-height:1.65;margin:0 0 16px;flex:1;position:relative;z-index:1}
.vfr .rc.light p{color:var(--ink)}
.vfr .rc.dark p{font-size:14.5px;color:#fff}
.vfr .rc .foot{display:flex;align-items:center;gap:11px;position:relative;z-index:1}
.vfr .rc .av{flex:none;width:42px;height:42px;border-radius:50%;background:linear-gradient(160deg,#2E9A8C,#1F6E63);display:grid;place-items:center;font:800 15px 'Outfit',sans-serif;color:#fff}
.vfr .rc.dark .av{background:rgba(143,227,201,.18);color:var(--mint)}
.vfr .rc .who{font:800 12.5px 'Outfit',sans-serif}
.vfr .rc.dark .who{color:#fff}
.vfr .rc .who span{display:block;font:600 10.5px 'Outfit',sans-serif;margin-top:2px}
.vfr .rc.light .who span{color:var(--muted)}
.vfr .rc.dark .who span{color:rgba(255,255,255,.55)}

/* 11 · more strip */
.vfr .more{position:relative;overflow:hidden;display:flex;align-items:center;gap:14px;flex-wrap:wrap;background:linear-gradient(160deg,#132c34,#1F6E63);border-radius:14px;padding:16px 18px;color:#fff}
.vfr .more .tx{font:800 14px 'Outfit',sans-serif}
.vfr .more .tx b{color:var(--mint)}
.vfr .more .tx span{display:block;font:600 12px 'Outfit',sans-serif;color:rgba(255,255,255,.65);margin-top:2px}
.vfr .more a{margin-left:auto;display:inline-flex;align-items:center;gap:7px;background:var(--wa);color:#fff;font:800 12.5px 'Outfit',sans-serif;padding:11px 16px;border-radius:999px;text-decoration:none;white-space:nowrap}
.vfr .more a svg{width:14px;height:14px;fill:#fff}

@media(max-width:860px){
  .vfr .pack{grid-template-columns:1fr 1fr}
  .vfr .rgrid{grid-template-columns:1fr}
  .vfr .docs,.vfr .dd{grid-template-columns:1fr}
  .vfr .crow{grid-template-columns:1fr}
  .vfr .more a{margin-left:0;width:100%;justify-content:center}
}
</style>
@endpush

@section('content')
<div class="vfr">

  {{-- 1 · HERO: mechanism headline + France availability --}}
  <section class="hero"><div class="wrap"><div class="hgrid">
    <div>
      <span class="eyebrow">France Schengen visa from the UK</span>
      <h1>We Watch the TLScontact Calendar. You Just <b>Show Up</b>.</h1>
      <p class="hsub">France appointments in London, Manchester and Edinburgh, rechecked daily. Tell us your dates, we take the earliest slot the calendar releases and confirm it with you.</p>
      <p class="hprice"><strong>&pound;130 total. &pound;90 only once your appointment is booked.</strong> Service fee refunded if the consulate refuses the file we prepared.</p>
      <div class="pbadges">
        <div class="pbadge">
          <span class="sl glow"><span class="wave" aria-hidden="true"></span><span class="pound">&pound;</span></span>
          <div class="tx"><b>Start for just &pound;40</b><span>&pound;90 only after booking</span></div>
        </div>
        <div class="pbadge">
          <span class="sl glow"><span class="wave" aria-hidden="true"></span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm.5-13H11v6l5.2 3.1.8-1.3-4.5-2.7V7z"/></svg></span>
          <div class="tx"><b>Reply within 30 minutes</b><span>A named consultant, 7 days</span></div>
        </div>
      </div>
    </div>

    <div class="fcard">
      <form id="vfr-form" autocomplete="off">
        <div class="fc-eye"><i></i>TLScontact France &middot; checked daily</div>
        <div class="fc-h"><img class="fc-flag" src="https://flagcdn.com/fr.svg" alt="">Find my France slot</div>
        <div class="cchips"><span>London</span><span>Manchester</span><span>Edinburgh</span><span class="dl"><b>checked daily</b></span></div>
        <label class="fl">When are you travelling? <span class="rq">*</span></label>
        <div class="tf" id="vfr-tf">
          <button type="button" data-tf="Within 3 weeks">Within 3 weeks</button><button type="button" data-tf="This month">This month</button><button type="button" data-tf="In 1&ndash;3 months">In 1&ndash;3 months</button><button type="button" data-tf="Just planning">Just planning</button>
        </div>
        <label class="fl" for="vfr-phone">Your phone number <span class="rq">*</span></label>
        <div class="pc"><span class="cc"><img src="https://flagcdn.com/gb.svg" alt="">+44</span><input id="vfr-phone" type="tel" inputmode="tel" placeholder="7911 123456"></div>
        <button type="submit" class="sbtn">@include('partials.wa-glyph')Check France availability</button>
        <p class="fnote">We reply within 30 minutes and take the earliest date at your nearest centre.</p>
      </form>
      <div class="fthx" hidden>
        <div class="tick"><svg viewBox="0 0 24 24"><path d="m5 13 4 4L19 7"/></svg></div>
        <h4>Request received</h4>
        <p><b>A named consultant replies within 30 minutes</b>, 7 days a week. We are already watching the TLScontact France calendar for your dates.</p>
      </div>
    </div>
  </div></div></section>

  {{-- 2 · TRUST BAR — matches live /schengen-visa-consultancy .tbar-f exactly --}}
  <section class="tbar-f"><div class="wrap"><div class="row">
    <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m9 12 2 2 4-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>France visa</b> consultancy</span></span>
    <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="12" cy="9" r="2.4" fill="none" stroke="currentColor" stroke-width="1.8"/></svg><span><b>3 UK</b> TLS centres</span></span>
    <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>30-min</b> reply</span></span>
    <a class="ti" href="https://find-and-update.company-information.service.gov.uk/company/{{ config('ukv.company_no') ?: '17331903' }}" target="_blank" rel="noopener" title="Verify our UK registration on Companies House" style="color:inherit;text-decoration:none">@include('partials.uk-eu-flags',['size'=>15])<span>Registered in <b>England and Wales</b></span></a>
  </div></div></section>

  {{-- 3+4 · PRICE (dark navy card) + PROCESS (ringed timeline), 2-column --}}
  <section class="fp"><div class="wrap"><div class="fp-two">
    <div class="dprice">
      <span class="eyebrow">Transparent pricing</span>
      <h2>What a France visa costs</h2>
      <div class="hero-n"><span class="big">&pound;130</span><span class="cap">total &middot; &pound;40 now, &pound;90 after booking</span></div>
      <div class="fee">
        <div class="fr"><span>Consulate fee (adult)<small>Paid to the French consulate</small></span><b>&euro;90</b></div>
        <div class="fr"><span>TLScontact charge<small>Paid to TLS, at booking</small></span><b>at booking</b></div>
        <div class="fr"><span>Beyond Passports fee<small>Form, FRA ref, docs, appointment, pack</small></span><b>&pound;130</b></div>
      </div>
      <div class="pn"><span class="hot">Pay now &pound;40</span><span>&pound;90 after booking</span></div>
      <p class="pnote">Consulate and TLS fees paid to them directly. Service fee refunded if the consulate refuses after we prepared the file.</p>
    </div>
    <div class="fp-flow">
      <span class="eyebrow">How it works for France</span>
      <h2>You attend one appointment.</h2>
      <div class="steps">
        <div class="st"><div class="rail"><span class="n">1</span><span class="ln"></span></div><div class="bd"><span class="who">We</span><b>France-Visas form</b><p>Official form + your FRA reference. Without it the TLS calendar shows nothing.</p></div></div>
        <div class="st"><div class="rail"><span class="n">2</span><span class="ln"></span></div><div class="bd"><span class="who">We</span><b>Build the file</b><p>Insurance, accommodation, cover letter, finances, checked line by line.</p></div></div>
        <div class="st"><div class="rail"><span class="n">3</span><span class="ln"></span></div><div class="bd"><span class="who">We</span><b>Take the earliest date</b><p>We watch the TLScontact calendar and secure the soonest slot.</p></div></div>
        <div class="st you"><div class="rail"><span class="n">4</span><span class="ln"></span></div><div class="bd"><span class="who">You</span><b>Biometrics at TLS</b><p>Attend with the pack we prepared. 15 to 20 minutes.</p></div></div>
        <div class="st"><div class="rail"><span class="n">5</span></div><div class="bd"><span class="who">Then</span><b>Travel with confidence</b><p>Decision in 10 to 15 working days, passport back through TLS.</p></div></div>
      </div>
    </div>
  </div></div></section>

  {{-- 5+6 · DOCS (accordion) + DELIVERABLES PACK (dark), 2-column --}}
  <section class="dpx"><div class="wrap"><div class="two">
    <div>
      <div class="colh">
        <span class="eyebrow">Documents</span>
        <h2>Your France checklist, by situation</h2>
        <p class="sub">One missing document at TLScontact and your travel date is gone. This is what the file check exists for.</p>
      </div>
      <details name="fdocs" open><summary>Employed <span class="who">Most common</span></summary><ul>
        <li>Passport, 3+ months validity beyond return, 2 blank pages</li>
        <li>Last 3 months' payslips + employer letter with approved leave dates</li>
        <li>Last 3 months' bank statements</li>
        <li>UK immigration status (BRP / eVisa share code where applicable)</li>
        <li>Travel insurance &euro;30,000+, flight itinerary, accommodation</li>
      </ul></details>
      <details name="fdocs"><summary>Self-employed</summary><ul>
        <li>Everything in Employed, minus the employer letter</li>
        <li>Latest tax return or SA302</li>
        <li>Business bank statements</li>
        <li>Proof of business: Companies House entry, contracts or invoices</li>
      </ul></details>
      <details name="fdocs"><summary>Student</summary><ul>
        <li>Enrolment letter with term dates from your UK institution</li>
        <li>Student bank statements, or sponsor's statements + letter</li>
        <li>UK immigration status documents</li>
      </ul></details>
      <details name="fdocs"><summary>Family / travelling with children</summary><ul>
        <li>Birth or marriage certificates linking the group</li>
        <li>Consent letter if one parent travels alone with a child</li>
        <li>One application per traveller, submitted together</li>
      </ul></details>
    </div>
    <div class="packwrap">
      <span class="eyebrow">What you actually get</span>
      <span class="cnt">&#10003; 4 things, ready to hand over</span>
      <h2>The appointment pack, before TLS</h2>
      <p class="psub">Everything an officer needs, in one folder, before you walk in.</p>
      <div class="pk"><span class="ic"><svg viewBox="0 0 24 24"><path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg></span><div><b>Completed France-Visas application</b><p>With your FRA reference, ready for the centre.</p></div><span class="no">01</span></div>
      <div class="pk"><span class="ic"><svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.8 9.9l-3.75-3.75L3 17.25zM20.7 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg></span><div><b>Tailored cover letter</b><p>Your trip, your ties, your finances, written for a visa officer.</p></div><span class="no">02</span></div>
      <div class="pk"><span class="ic"><svg viewBox="0 0 24 24"><path d="M21 16v-2l-8-5V3.5A1.5 1.5 0 0 0 11.5 2 1.5 1.5 0 0 0 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5z"/></svg></span><div><b>Itinerary + insurance certificate</b><p>Bookings structured the way consulates expect, &euro;30k-compliant cover.</p></div><span class="no">03</span></div>
      <div class="pk"><span class="ic"><svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V9h14v11z"/></svg></span><div><b>Appointment confirmation + briefing</b><p>Date, centre, what happens in the room, what to carry.</p></div><span class="no">04</span></div>
      <div class="packfoot"><span class="sh"><svg viewBox="0 0 24 24"><path d="M12 1 3 5v6c0 5.5 3.8 10.7 9 12 5.2-1.3 9-6.5 9-12V5l-9-4z" fill="rgba(143,227,201,.16)" stroke="#8fe3c9" stroke-width="1.2"/><path d="M8.5 12.2l2.3 2.3 4.6-4.8" fill="none" stroke="#8fe3c9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span><div class="tx"><b>HUMAN-CHECKED FILE</b><span>Every item checked by a named consultant before it reaches TLScontact.</span></div></div>
    </div>
  </div></div></section>

  {{-- 7 · DO / DON'T --}}
  <section><div class="wrap">
    <span class="eyebrow">Straight answers</span>
    <h2>What we do, and what we don't</h2>
    @php
      $ddTick = '<svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1.1 14.2-4-4 1.4-1.4 2.6 2.6 5.4-5.4 1.4 1.4z"/></svg>';
      $ddCross = '<svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm3.5 12.1-1.4 1.4L12 13.4l-2.1 2.1-1.4-1.4L10.6 12 8.5 9.9l1.4-1.4L12 10.6l2.1-2.1 1.4 1.4L13.4 12z"/></svg>';
    @endphp
    <div class="dd" style="margin-top:18px">
      <div class="ddc do">
        <div class="hd"><span class="bdg"><svg viewBox="0 0 24 24"><path d="M12 2 4 5v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V5z"/></svg></span><h3>We do</h3></div>
        <ul>
          <li><span class="mk">{!! $ddTick !!}</span><span>Complete the France-Visas form and generate your FRA reference</span></li>
          <li><span class="mk">{!! $ddTick !!}</span><span>Watch the TLScontact calendar and take the earliest workable date</span></li>
          <li><span class="mk">{!! $ddTick !!}</span><span>Check every document before it goes anywhere near the centre</span></li>
          <li><span class="mk">{!! $ddTick !!}</span><span>Reply within 30 minutes, from a named consultant</span></li>
          <li><span class="mk">{!! $ddTick !!}</span><span>Refund our service fee if the consulate refuses the file we prepared</span></li>
        </ul>
      </div>
      <div class="ddc dont">
        <div class="hd"><span class="bdg"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 2c1.8 0 3.5.6 4.9 1.7L5.7 16.9A8 8 0 0 1 12 4zm0 16c-1.8 0-3.5-.6-4.9-1.7L18.3 7.1A8 8 0 0 1 12 20z"/></svg></span><h3>We don't</h3></div>
        <ul>
          <li><span class="mk">{!! $ddCross !!}</span><span>Speed up the consulate's decision. Nobody can, and we won't pretend</span></li>
          <li><span class="mk">{!! $ddCross !!}</span><span>Book you through a different country to game the main-destination rule</span></li>
          <li><span class="mk">{!! $ddCross !!}</span><span>Sell queue-jumping or "premium slots" that don't exist</span></li>
          <li><span class="mk">{!! $ddCross !!}</span><span>Guarantee a visa. The decision is always the consulate's</span></li>
          <li><span class="mk">{!! $ddCross !!}</span><span>We are not the government and not affiliated with TLScontact</span></li>
        </ul>
      </div>
    </div>
  </div></section>

  {{-- 8 · REFUSAL RECOVERY --}}
  <section><div class="wrap">
    <div class="ref">
      <span class="big" aria-hidden="true">5 YEARS</span>
      <div class="in">
        <span class="eyebrow">Refused before</span>
        <h3>A refusal follows you for <b>five years.</b></h3>
        <p>Every Schengen consulate can see it, and a wrong re-application makes it worse. Before you try France again, let us find the real reason and answer it.</p>
        <a class="refcta" href="{{ $wa }}?text={{ rawurlencode('Hi Beyond Passports, I was refused a Schengen visa before and want to apply for France. Can you review my refusal letter?') }}">@include('partials.wa-glyph')Review my refusal</a>
        <div class="trust"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1 3 5v6c0 5.5 3.8 10.7 9 12 5.2-1.3 9-6.5 9-12V5l-9-4z"/></svg>Honest assessment, whether or not you become a client</div>
      </div>
    </div>
  </div></section>

  {{-- 9 · REVIEWS (France-relevant) — before FAQ --}}
  <section><div class="wrap">
    <span class="eyebrow">France clients</span>
    <h2>What France applicants say</h2>
    <div class="rgrid" style="margin-top:18px">
      <div class="rc dark"><div class="st5">★★★★★</div><p>First time applying for a Schengen visa and I had no clue where to start. They sorted my France application, checked every document, and it came back approved. Kept me posted the whole time.</p><div class="foot"><span class="av">AO</span><span class="who">Adaeze Okafor<span>May 2026 · BP-2026-103487</span></span></div></div>
      <div class="rc light"><div class="st5">★★★★★</div><p>Could not find a single appointment for France anywhere. These lot found one within 3 days. Didn't believe it until I saw the confirmation email. Proper service.</p><div class="foot"><span class="av">FH</span><span class="who">Fatima Hussain<span>Manchester · May 2026 · BP-2026-100184</span></span></div></div>
      <div class="rc light"><div class="st5">★★★★★</div><p>Needed it before a wedding on a tight timeline. Honest that they can't rush the consulate, just the paperwork. Did exactly what they said.</p><div class="foot"><span class="av">KM</span><span class="who">Kwame Mensah<span>Apr 2026 · BP-2026-100842</span></span></div></div>
    </div>
  </div></section>

  {{-- 10 · FAQ --}}
  <section style="background:#fff"><div class="wrap">
    <span class="eyebrow">France visa FAQ</span>
    <h2>Asked every week, answered straight</h2>
    <div class="faq" style="margin-top:18px;max-width:760px">
      @foreach($faqs as $f)
      <details><summary>{{ $f['q'] }}</summary><div>{{ $f['a'] }}</div></details>
      @endforeach
    </div>
  </div></section>

  {{-- 11 · MORE STRIP --}}
  <section style="padding-top:0"><div class="wrap">
    <div class="more">
      <div class="tx">Dates not working for <b>your travel</b>?<span>The calendar moves daily. Tell us your dates and we watch it for you.</span></div>
      <a href="{{ $wa }}?text={{ rawurlencode('Hi Beyond Passports, the France dates shown do not fit my travel. My dates are: ') }}">@include('partials.wa-glyph')Ask us to watch the calendar</a>
    </div>
  </div></section>

  {{-- 12 · DISCLAIMER --}}
  <section style="padding:0 0 44px"><div class="wrap">
    @include('partials.disclaimer-strip', ['text' => 'Beyond Passports is an independent consultancy registered in England and Wales. We are not the government, not the French consulate, and not TLScontact. Appointment dates shown are indicative, updated daily and confirmed live with the centre before you pay. We help prepare applications and assist with appointment booking; every visa decision rests with the relevant consulate.'])
  </div></section>

</div>

<script>
(function(){
  var form = document.getElementById('vfr-form'); if (!form) return;
  var seg = document.getElementById('vfr-tf'), timeframe = '';
  seg.querySelectorAll('button').forEach(function(b){
    b.addEventListener('click', function(){
      seg.querySelectorAll('button').forEach(function(x){ x.classList.remove('sel'); });
      b.classList.add('sel'); timeframe = b.getAttribute('data-tf') || ''; seg.classList.remove('err');
    });
  });
  form.addEventListener('submit', function(e){
    e.preventDefault();
    if (!timeframe) { seg.classList.add('err'); seg.scrollIntoView({block:'center'}); return; }
    var input = document.getElementById('vfr-phone');
    var phone = (input.value || '').trim();
    if (!phone) { input.focus(); return; }
    if (phone.charAt(0) !== '+') { phone = '+44 ' + phone.replace(/[^\d]/g,'').replace(/^0+/,''); }
    var btn = form.querySelector('.sbtn'); btn.disabled = true;
    var payload = { phone: phone, dest: 'France', intent: 'France slot - ' + timeframe, utm: (window.bpUtm ? window.bpUtm() : null) };
    fetch(@json(route('lp-bold.lead')), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    }).catch(function(){}).finally(function(){
      form.hidden = true;
      var thx = form.parentNode.querySelector('.fthx'); if (thx) thx.hidden = false;
    });
  });
})();
</script>
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]], $faqs)], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
