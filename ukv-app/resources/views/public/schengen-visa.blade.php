@extends('layouts.public')

@section('title', 'Schengen Visa Consultancy for UK Residents | Beyond Passports')
@section('description', 'Independent Schengen visa consultancy for UK residents. Our UK and Germany teams prepare, check and submit your application, catching the mistakes that get people refused. Not a government website.')
@section('canonical', url('/schengen-visa-consultancy'))

@php
  // WhatsApp deep-link (same pattern as the other public pages).
  $waNumber = config('ukv.whatsapp') ?: '440000000000';
  $waLink = 'https://wa.me/'.$waNumber.'?text='.rawurlencode('Hi Beyond Passports, I need help with a Schengen visa');
  // Inline WhatsApp glyph (copied from destinations/index — sized via .wa-g so it never blows up).
  $waGlyph = '<svg viewBox="0 0 24 24" aria-hidden="true" class="wa-g"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>';

  // Honest FAQ pairs — reused for the on-page accordion and the FAQPage JSON-LD.
  $faqs = [
    ['q' => 'How long does a Schengen visa take?', 'a' => 'Most decisions are made within about 15 calendar days, though it can take longer in busy periods or when extra checks are needed. That timeline is set by the consulate, not by us. The biggest delay is usually getting an appointment, so it is best to start early. Express handling speeds our preparation, not the consulate decision.'],
    ['q' => 'Which country do I apply to?', 'a' => 'You apply to the country that is your main destination, where you will spend the most time. If your time is split evenly across several countries, you apply to the country you enter first. We help you work this out before you book anything.'],
    ['q' => 'Can you help after a refusal?', 'a' => 'Yes. A reapplication has to fix the real reason for the first refusal, so we work out what went wrong and put it right before you apply again. We cannot guarantee a different outcome, but we remove the avoidable reasons for a no.'],
    ['q' => 'Do you book the appointment?', 'a' => 'Yes. We confirm live availability with the visa centre, then book a biometric slot in time for your travel date and tell you exactly what to bring. Availability shown on this page is indicative and confirmed live before you pay.'],
    ['q' => 'Is your fee separate from the embassy fee?', 'a' => 'Yes. Our service fee is a clear fixed amount, shown before you pay anything. It is separate from the consulate or government visa fee, which is set by the authorities and paid to them. We tell you both so there are no surprises.'],
    ['q' => 'Are you a government service?', 'a' => 'No. We are an independent consultancy, not a government department and not a visa centre. We prepare and check your application; the consulate makes the decision.'],
  ];

  // Prevention pairs — copied verbatim from destinations/index.blade.php.
  $refusedReasons = [
    ['reason' => 'Applying through the wrong country', 'fix' => 'Apply through your main destination, or first point of entry if your trip is split evenly, not whichever centre has the easiest appointments.'],
    ['reason' => 'Reusing documents from an old or refused application', 'fix' => 'Reassess and update every document so it reflects your current trip and dates.'],
    ['reason' => 'Inconsistent information across documents', 'fix' => 'A clear cover letter, with names, dates and figures that match across the whole file.'],
    ['reason' => 'Weak proof of ties to the UK', 'fix' => 'Evidence tailored to what each consulate expects: employment, study, family or property.'],
    ['reason' => 'Unexplained funds added just before applying', 'fix' => 'Bank history that matches your normal income, with any large deposit clearly explained and evidenced.'],
    ['reason' => 'Booking non-refundable travel before the decision', 'fix' => 'Hold refundable or reservation-only bookings until the visa is granted.'],
  ];

  // JSON-LD (matches the Service + FAQPage pattern used on destinations/show).
  $serviceLd = [
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => 'Schengen visa consultancy',
    'serviceType' => 'Schengen visa application facilitation',
    'description' => 'Independent UK and Germany consultancy that prepares, checks and submits Schengen visa applications for UK residents. Service fee separate from the government fee. Not a government website.',
    'areaServed' => 'United Kingdom',
    'provider' => [
      '@type' => 'Organization',
      'name' => 'Beyond Passports',
      'url' => url('/'),
    ],
    'url' => url('/schengen-visa-consultancy'),
  ];
  $faqLd = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => collect($faqs)->map(fn ($f) => [
      '@type' => 'Question',
      'name' => $f['q'],
      'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
    ])->all(),
  ];
@endphp

@push('head')
<style>
  /* Schengen consultancy landing — page-scoped only (sgc- prefix). Design system in ukv.css. */

  /* Hero — soft-sky split: copy left, white "Start here" action card right */
  .sgc-hero{background:linear-gradient(180deg,#EAF1F4 0%, #F2F5F6 55%, var(--paper) 100%);border-bottom:1px solid var(--paper-edge)}
  .sgc-hero > .wrap{padding:34px 0 32px}
  .sgc-hero-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:46px;align-items:center}
  .sgc-hero .eyebrow{color:var(--cta)}
  .sgc-hero h1{font:700 clamp(30px,4.2vw,46px)/1.05 var(--display);letter-spacing:-.03em;color:var(--ink);margin:0 0 16px;max-width:17ch}
  .sgc-hero .lede{color:var(--muted);font-size:18px;line-height:1.5;max-width:48ch;margin:0}
  .btn .wa-g{width:17px;height:17px;fill:currentColor;flex:none;vertical-align:-3px}
  /* Start-here card */
  .sgc-hcard{background:#fff;border:1px solid var(--paper-edge);border-radius:18px;padding:24px;box-shadow:0 30px 64px -34px rgba(40,50,70,.5)}
  .sgc-hcard h3{font:800 17px var(--display);color:var(--ink);margin:0 0 4px}
  .sgc-hcard > p{font-size:13.5px;color:var(--muted);margin:0 0 16px}
  .sgc-hcard .acts{display:flex;flex-direction:column;gap:12px}
  .sgc-hcard .btn{width:100%;display:inline-flex;align-items:center;justify-content:center;gap:8px}
  .sgc-hcard .btn--wa{background:#25D366;border:0;color:#fff}
  .sgc-hcard .btn--wa:hover{background:#1da851}
  .sgc-hcard .btn--ghost{background:#fff;border:1px solid var(--paper-edge);color:var(--ink)}
  .sgc-hcard .hcard-note{font-size:12px;color:var(--muted);text-align:center;margin:14px 0 0}
  .sgc-hcard .tpr{justify-content:center;gap:6px 9px}
  .sgc-hcard .tpr .tpr-box{width:18px;height:18px}
  .sgc-hcard .tpr .tpr-box svg{width:12px;height:12px}
  .sgc-hcard .tpr .tpr-word{font-size:13px}
  .sgc-hcard .tpr .tpr-meta,.sgc-hcard .tpr .tpr-logo{font-size:12px}
  @media (max-width:760px){.sgc-hero-grid{grid-template-columns:1fr;gap:26px}}

  /* Trust band — dark mesh band (matches home / services .tbar-f) */
  .tbar-f{padding:0;background:
      radial-gradient(520px 200px at 12% 0%, rgba(21,94,122,.45), transparent 60%),
      radial-gradient(520px 200px at 92% 100%, rgba(46,154,140,.42), transparent 60%),
      var(--navy);color:#fff}
  .tbar-f .row{display:flex;justify-content:center;gap:30px;flex-wrap:wrap;padding:16px 0}
  .tbar-f .ti{display:flex;align-items:center;gap:9px;font:600 14px var(--display);color:#fff;white-space:nowrap}
  .tbar-f .ti svg{width:20px;height:20px;color:var(--soft);flex:none}
  .tbar-f .ti b{color:var(--soft);font-weight:800}
  @media (max-width:560px){.tbar-f .row{gap:14px 22px}}

  /* Generic section heads */
  .sgc-head{text-align:center;max-width:62ch;margin:0 auto}
  .sgc-head .lede{margin:12px auto 0;max-width:58ch}

  /* PROBLEM cards (PAS) */
  #sgc-problem{background:linear-gradient(180deg,#FBF6F1,var(--paper))}
  .sgc-pcards{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:30px}
  .sgc-pcard{background:#fff;border:1px solid var(--paper-edge);border-radius:16px;padding:24px 22px;
    box-shadow:0 10px 30px -22px rgba(40,50,70,.5);transition:transform .25s ease,box-shadow .25s ease}
  .sgc-pcard:hover{transform:translateY(-3px);box-shadow:var(--lift-2)}
  .sgc-pcard .ico{width:44px;height:44px;border-radius:11px;display:grid;place-items:center;
    background:rgba(190,70,55,.10);color:#B3402E;margin-bottom:14px}
  .sgc-pcard .ico svg{width:22px;height:22px}
  .sgc-pcard h3{font:800 17px var(--display);color:var(--ink);margin:0 0 6px}
  .sgc-pcard p{margin:0;font-size:14.5px;line-height:1.55;color:var(--muted)}
  @media (max-width:760px){.sgc-pcards{grid-template-columns:1fr}}

  /* Region pill tabs (shared by appointment board) */
  .sgc-tabs{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:26px 0 0}
  .sgc-tab{display:inline-flex;align-items:center;gap:8px;flex:0 0 auto;white-space:nowrap;background:#fff;border:1px solid var(--paper-edge);color:var(--ink);
    border-radius:999px;padding:9px 16px;font:700 14px var(--display);cursor:pointer;transition:border-color .2s ease,color .2s ease,background .2s ease}
  .sgc-tab .c{font-size:12px;font-weight:800;color:var(--muted)}
  .sgc-tab:hover{border-color:var(--soft);color:var(--cta)}
  .sgc-tab.active{background:var(--cta);border-color:var(--cta);color:#fff;box-shadow:0 12px 26px -14px rgba(21,94,122,.6)}
  .sgc-tab.active .c{color:rgba(255,255,255,.82)}

  /* Appointment availability board (region-grouped tiles) */
  #sgc-appts .ap-note{display:inline-flex;align-items:center;gap:9px;margin:16px auto 0;font-size:12.5px;color:var(--muted);
    background:#fff;border:1px solid var(--paper-edge);border-radius:999px;padding:8px 15px}
  #sgc-appts .ap-note .d{width:8px;height:8px;border-radius:50%;background:var(--stamp);flex:none;box-shadow:0 0 0 4px rgba(92,154,123,.16)}
  #sgc-appts .sgc-tabs{margin-top:24px}
  #sgc-appts .ap-panel{display:none;margin-top:22px}
  #sgc-appts .ap-panel.active{display:block}
  #sgc-appts .ap-tiles{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
  @media (max-width:820px){#sgc-appts .ap-tiles{grid-template-columns:1fr 1fr}}
  @media (max-width:480px){#sgc-appts .ap-tiles{grid-template-columns:1fr}}
  #sgc-appts .ap-tile{display:block;text-decoration:none;background:#fff;border:1px solid var(--paper-edge);border-radius:15px;
    padding:16px 17px;box-shadow:0 12px 30px -26px rgba(40,50,70,.5);transition:transform .18s,box-shadow .18s}
  #sgc-appts .ap-tile:hover{transform:translateY(-3px);box-shadow:var(--lift-2)}
  #sgc-appts .ap-tp{display:flex;justify-content:space-between;align-items:center;margin-bottom:13px;gap:10px}
  #sgc-appts .ap-tile h4{font:800 16px var(--display);color:var(--ink);margin:0}
  #sgc-appts .ap-st{display:inline-flex;align-items:center;gap:6px;font:800 10px var(--display);letter-spacing:.05em;text-transform:uppercase;padding:4px 9px;border-radius:999px;white-space:nowrap}
  #sgc-appts .ap-st .dot{width:6px;height:6px;border-radius:50%}
  #sgc-appts .ap-st.ok{background:rgba(46,154,140,.14);color:#1F6E63}#sgc-appts .ap-st.ok .dot{background:#2E9A8C}
  #sgc-appts .ap-st.lim{background:rgba(200,146,58,.16);color:#946100}#sgc-appts .ap-st.lim .dot{background:#c8923a}
  #sgc-appts .ap-st.ask{background:rgba(21,94,122,.12);color:var(--cta)}#sgc-appts .ap-st.ask .dot{background:var(--cta)}
  #sgc-appts .ap-bar{height:6px;border-radius:999px;background:#e7edf0;overflow:hidden}
  #sgc-appts .ap-bar>i{display:block;height:100%;border-radius:999px}
  #sgc-appts .ap-bar>i.ok{background:linear-gradient(90deg,#2E9A8C,#5C9A7B)}
  #sgc-appts .ap-bar>i.lim{background:linear-gradient(90deg,#c8923a,#e0b15f)}
  #sgc-appts .ap-bar>i.ask{background:repeating-linear-gradient(90deg,#cdd7dc 0 6px,transparent 6px 12px)}
  #sgc-appts .ap-dt{font:700 14px var(--display);color:var(--ink);margin-top:11px}
  #sgc-appts .ap-lb{font-size:11px;color:var(--muted);margin-top:3px}
  #sgc-appts .ap-legend{display:flex;flex-wrap:wrap;justify-content:center;gap:18px;margin-top:28px;font-size:12px;color:var(--muted)}
  #sgc-appts .ap-legend span{display:inline-flex;align-items:center;gap:7px}
  #sgc-appts .ap-legend i{width:9px;height:9px;border-radius:50%;display:inline-block}

  /* WHO WE HELP — stamp card grid */
  #sgc-who{background:linear-gradient(180deg,#FBF6F1,var(--paper))}
  .sgc-who-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:30px}
  .sgc-who-card{background:#fff;border:1px solid var(--paper-edge);border-radius:16px;padding:22px 20px;
    box-shadow:0 10px 30px -22px rgba(40,50,70,.5);transition:transform .25s ease,box-shadow .25s ease}
  .sgc-who-card:hover{transform:translateY(-3px);box-shadow:var(--lift-2)}
  .sgc-who-card .ico{width:42px;height:42px;border-radius:11px;display:grid;place-items:center;
    background:rgba(46,154,140,.12);color:var(--stamp-text);margin-bottom:12px}
  .sgc-who-card .ico svg{width:21px;height:21px}
  .sgc-who-card h3{font:800 16px var(--display);color:var(--ink);margin:0 0 5px}
  .sgc-who-card p{margin:0;font-size:14px;line-height:1.55;color:var(--muted)}
  @media (max-width:860px){.sgc-who-grid{grid-template-columns:1fr 1fr}}
  @media (max-width:480px){.sgc-who-grid{grid-template-columns:1fr}}

  /* HOW IT WORKS — steps */
  .sgc-steps{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-top:30px}
  .sgc-step{background:#fff;border:1px solid var(--paper-edge);border-radius:16px;padding:24px 22px;
    box-shadow:0 10px 30px -22px rgba(40,50,70,.5)}
  .sgc-step .num{font:800 26px var(--display);color:var(--soft);line-height:1}
  .sgc-step .rule{height:2px;width:34px;background:var(--cta);opacity:.5;border-radius:2px;margin:12px 0 14px}
  .sgc-step h3{font:800 16px var(--display);color:var(--ink);margin:0 0 6px}
  .sgc-step p{margin:0;font-size:14px;line-height:1.55;color:var(--muted)}
  .sgc-step .free{display:inline-block;font:800 10px var(--display);letter-spacing:.05em;text-transform:uppercase;
    color:var(--stamp-text);background:#e7f3ef;border-radius:999px;padding:3px 9px;margin-top:8px}
  @media (max-width:860px){.sgc-steps{grid-template-columns:1fr 1fr}}
  @media (max-width:480px){.sgc-steps{grid-template-columns:1fr}}

  /* Why applications get refused (reason / fix rows) */
  #sgc-refused .rf-rows{display:flex;flex-direction:column;gap:14px;margin:30px auto 0;max-width:90ch}
  #sgc-refused .rf-row{display:grid;grid-template-columns:1fr 1fr;gap:0;background:#fff;border:1px solid var(--paper-edge);
    border-radius:16px;overflow:hidden;box-shadow:0 12px 30px -26px rgba(40,50,70,.5);transition:transform .2s ease,box-shadow .2s ease}
  #sgc-refused .rf-row:hover{transform:translateY(-3px);box-shadow:var(--lift-2)}
  #sgc-refused .rf-cell{padding:18px 20px;display:flex;gap:12px;align-items:flex-start}
  #sgc-refused .rf-bad{background:linear-gradient(180deg,#FBF3F1,#fff);border-right:1px solid var(--paper-edge)}
  #sgc-refused .rf-glyph{flex:0 0 26px;width:26px;height:26px;border-radius:50%;display:grid;place-items:center;margin-top:1px}
  #sgc-refused .rf-bad .rf-glyph{background:rgba(190,70,55,.12);color:#B3402E}
  #sgc-refused .rf-fix .rf-glyph{background:rgba(46,154,140,.14);color:#1F6E63}
  #sgc-refused .rf-glyph svg{width:15px;height:15px}
  #sgc-refused .rf-k{font:800 11px var(--display);letter-spacing:.06em;text-transform:uppercase;margin:0 0 4px}
  #sgc-refused .rf-bad .rf-k{color:#B3402E}
  #sgc-refused .rf-fix .rf-k{color:var(--stamp-text,#1F6E63)}
  #sgc-refused .rf-t{font:600 15px/1.45 var(--display);color:var(--ink);margin:0}
  #sgc-refused .rf-fix .rf-t{color:#3a4b55;font-weight:500}
  #sgc-refused .rf-cta{text-align:center;margin-top:30px}
  @media (max-width:680px){#sgc-refused .rf-row{grid-template-columns:1fr}
    #sgc-refused .rf-bad{border-right:0;border-bottom:1px solid var(--paper-edge)}}

  /* PROOF — review quote cards */
  #sgc-proof{background:var(--paper)}
  .sgc-quotes{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:30px}
  .sgc-tq{background:#fff;border:1px solid var(--paper-edge);border-radius:16px;padding:24px 22px;box-shadow:0 12px 30px -26px rgba(40,50,70,.5);margin:0;display:flex;flex-direction:column;gap:12px;transition:transform .25s ease,box-shadow .25s ease}
  .sgc-tq:hover{transform:translateY(-3px);box-shadow:var(--lift-2)}
  .sgc-tq .stars{color:var(--cta);letter-spacing:3px;font-size:14px}
  .sgc-tq blockquote{margin:0;font-family:var(--display);font-weight:600;font-size:15.5px;line-height:1.55;color:var(--ink)}
  .sgc-tq figcaption{color:var(--stamp-text);font-weight:700;font-size:13px;margin-top:auto}
  @media (max-width:760px){.sgc-quotes{grid-template-columns:1fr}}

  /* PRICING transparency band */
  .sgc-price{background:
      radial-gradient(420px 200px at 8% 0%, rgba(21,94,122,.40), transparent 60%),
      radial-gradient(420px 200px at 92% 100%, rgba(46,154,140,.36), transparent 60%),
      var(--navy);border-radius:20px;padding:36px 40px;box-shadow:var(--lift-2);color:#fff}
  .sgc-price .eyebrow{color:var(--soft)}
  .sgc-price h2{color:#fff;margin:6px 0 8px}
  .sgc-price > p{color:rgba(255,255,255,.82);max-width:60ch;margin:0;font-size:16px;line-height:1.6}
  .sgc-price-points{display:flex;flex-wrap:wrap;gap:10px 28px;margin-top:22px;padding-top:18px;border-top:1px solid rgba(255,255,255,.16)}
  .sgc-price-points .pp{display:inline-flex;align-items:center;gap:9px;font:700 14px var(--display);color:#fff}
  .sgc-price-points .pp svg{width:18px;height:18px;color:var(--soft);flex:none}
  @media (max-width:560px){.sgc-price{padding:28px 22px}}

  /* COUNTRY coverage chips */
  #sgc-coverage .sgc-chips{display:flex;flex-wrap:wrap;gap:9px 11px;justify-content:center;margin:26px auto 0;max-width:74ch;padding:0;list-style:none}
  #sgc-coverage .sgc-chips li{margin:0}
  #sgc-coverage .sgc-chips a{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--paper-edge);
    border-radius:10px;padding:8px 14px;font:600 14px var(--display);color:var(--ink);text-decoration:none;
    box-shadow:0 6px 16px -12px rgba(40,50,70,.5);transition:border-color .18s ease,color .18s ease,transform .18s ease}
  #sgc-coverage .sgc-chips a:hover{border-color:var(--soft);color:var(--cta);transform:translateY(-2px)}
  #sgc-coverage .sgc-chips .dot{width:7px;height:7px;border-radius:50%;background:var(--cta);flex:none}
  #sgc-coverage .cov-cta{text-align:center;margin-top:28px}

  /* CONTACT two-team rows */
  #sgc-contact{background:linear-gradient(180deg,#EAF1F4,var(--paper))}
  .sgc-contact-card{background:
      radial-gradient(360px 180px at 110% -10%, rgba(21,94,122,.30), transparent 60%),
      radial-gradient(340px 180px at -10% 120%, rgba(46,154,140,.28), transparent 60%),
      var(--navy);color:#fff;border-radius:20px;padding:30px 32px;box-shadow:var(--lift-2);max-width:560px;margin:30px auto 0}
  .sgc-contact-card .eyebrow{color:var(--soft)}
  .sgc-contact-card h3{font:800 22px var(--display);color:#fff;margin:4px 0 4px}
  .sgc-contact-card > p{color:rgba(255,255,255,.78);font-size:14px;margin:0 0 18px}
  .sgc-team{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 0;border-top:1px solid rgba(255,255,255,.14)}
  .sgc-team:first-of-type{border-top:0}
  .sgc-team-who{display:flex;align-items:center;gap:10px;min-width:0}
  .sgc-fl{flex:none;width:30px;height:20px;border-radius:4px;background:rgba(255,255,255,.16);display:inline-grid;place-items:center;font:800 10px var(--display);color:#fff}
  .sgc-team-who b{font:700 14px var(--display);color:#fff}
  .sgc-team-who small{display:block;color:rgba(255,255,255,.66);font-size:12px;margin-top:1px}
  .sgc-teambtn{flex:none;padding:9px 18px;font-size:13px;background:var(--soft);color:#16222e;border:0;display:inline-flex;align-items:center;gap:7px}
  .sgc-teambtn:hover{background:#fff}
  .sgc-wabtn{display:flex;width:100%;justify-content:center;align-items:center;gap:8px;margin-top:16px;background:#25D366;border:0;color:#fff}
  .sgc-wabtn:hover{background:#1da851}
  .sgc-wabtn .wa-g{width:18px;height:18px;fill:currentColor}

  /* FAQ — tinted panel accordion (matches services / money pages) */
  .faq-e{background:var(--paper)}
  .faq-e .sec-head{text-align:center;max-width:60ch;margin-left:auto;margin-right:auto}
  .faq-panel{background:var(--white);border:1px solid var(--paper-edge);border-radius:18px;padding:6px 30px;
    max-width:80ch;margin:24px auto 0;box-shadow:0 16px 40px -30px rgba(40,50,70,.5)}
  .faqd{max-width:none}
  .faqd details{border-bottom:1px solid var(--paper-edge);padding:18px 0}
  .faqd details:last-child{border-bottom:0}
  .faqd summary{font-family:var(--display);font-size:19px;color:var(--navy);font-weight:600;cursor:pointer;
    list-style:none;display:flex;justify-content:space-between;align-items:center;gap:16px}
  .faqd summary::-webkit-details-marker{display:none}
  .faqd summary::after{content:"+";font-size:22px;color:var(--cta);flex:0 0 auto;font-weight:700;transition:transform .15s ease}
  .faqd details[open] summary::after{content:"\2013"}
  .faqd p{margin:12px 0 0;color:#3a4b55;font-size:16px;line-height:1.65}

  /* Final CTA WhatsApp button glyph + phone rows */
  .cta-band .row .btn{display:inline-flex;align-items:center;gap:8px}
  .cta-band .row .wa-g{width:18px;height:18px;fill:currentColor;flex:none}
  .cta-band .call-g{width:16px;height:16px;fill:currentColor;flex:none}
  .sgc-cta-phones{display:flex;flex-wrap:wrap;justify-content:center;gap:10px 18px;margin-top:18px}
  .sgc-cta-phones a{display:inline-flex;align-items:center;gap:8px;color:#eef0f1;text-decoration:none;font:700 14px var(--display)}
  .sgc-cta-phones a:hover{color:#fff;text-decoration:underline}
  .sgc-disclaimer{max-width:62ch;margin:22px auto 0;font-size:12.5px;line-height:1.6;color:rgba(255,255,255,.7);text-align:center}
</style>
<script type="application/ld+json">{!! json_encode($serviceLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

{{-- 1) HERO --}}
<section class="sgc-hero"><div class="wrap"><div class="sgc-hero-grid">
  <div class="reveal">
    <p class="eyebrow">Schengen visa consultancy</p>
    <h1>Schengen visa consultancy for UK residents</h1>
    <p class="lede">An independent UK and Germany team that prepares, checks and submits your application, catching the small mistakes that get people refused. We are with you from the first eligibility check to the appointment door.</p>
  </div>
  <div class="sgc-hcard reveal">
    <h3>Start here</h3>
    <p>Check if you can apply, or message our team. No account needed.</p>
    <div class="acts">
      <a class="btn" href="{{ url('/tools') }}">Check your eligibility</a>
      <a class="btn btn--wa" href="{{ $waLink }}" target="_blank" rel="noopener">{!! $waGlyph !!} Message us</a>
    </div>
    @include('partials.trustpilot-cta', ['align' => 'center', 'theme' => 'dark', 'margin' => '16px 0 0'])
    <p class="hcard-note">Independent service. Not a government website.</p>
  </div>
</div></div></section>

{{-- 2) TRUST BAND --}}
<section class="tbar-f"><div class="wrap"><div class="row">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m9 12 2 2 4-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Schengen visa</b> experts</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v10M9.5 9.2c0-1 1.1-1.7 2.5-1.7s2.5.7 2.5 1.7-1.1 1.6-2.5 1.6-2.5.7-2.5 1.7 1.1 1.7 2.5 1.7 2.5-.7 2.5-1.7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg><span><b>No hidden</b> fees</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M5 21V9l7-5 7 5v12M9 21v-6h6v6" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg><span><b>UK &amp; Germany</b> teams</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>7-day</b> support</span></span>
</div></div></section>

{{-- 3) PROBLEM (PAS) --}}
<section id="sgc-problem"><div class="wrap">
  <div class="sgc-head reveal">
    <p class="eyebrow">The problem</p>
    <h2>Why Schengen applications get refused, and why appointments are hard right now</h2>
    <p class="lede">Most refusals are avoidable, but the system gives you little room for error. Three things trip people up.</p>
  </div>
  <div class="sgc-pcards">
    <div class="sgc-pcard reveal">
      <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span>
      <h3>Limited appointment slots</h3>
      <p>Biometric appointments at the busiest consulates fill up fast. The visa is often not the bottleneck, the appointment is, and a slow start can cost you the trip.</p>
    </div>
    <div class="sgc-pcard reveal">
      <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 15-6.7L21 8M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16M3 21v-5h5"/></svg></span>
      <h3>Changing consulate rules</h3>
      <p>Each country sets its own document requirements and they change without much notice. What worked for a friend last year may not be what your consulate asks for now.</p>
    </div>
    <div class="sgc-pcard reveal">
      <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/></svg></span>
      <h3>Small document mistakes</h3>
      <p>A date that does not match, weak proof of ties to the UK, or funds added just before applying. Minor slips on paper are enough for a consulate to say no.</p>
    </div>
  </div>
</div></section>

{{-- 4) APPOINTMENT AVAILABILITY BOARD --}}
<section id="sgc-appts"><div class="wrap">
  <div class="sgc-head reveal">
    <p class="eyebrow">Appointments</p>
    <h2>Where slots are opening now</h2>
    <p class="lede">Competitors only tell you appointments are hard. We show you where they are opening. Here is a recent snapshot by country, soonest first.</p>
    <div><span class="ap-note"><span class="d"></span>Indicative only. We confirm live availability with the centre before you pay.</span></div>
  </div>

  @php $apptRegions = $byRegion->filter(fn ($g, $r) => ! empty($r)); @endphp
  @if ($apptRegions->isNotEmpty())
  <div class="sgc-tabs" id="sgcApptTabs" role="tablist">
    @foreach ($apptRegions as $region => $group)
      <button type="button" class="sgc-tab @if($loop->first) active @endif" role="tab" data-region="{{ $region }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ str_replace(' Europe', '', $region) }} <span class="c">{{ $group->count() }}</span></button>
    @endforeach
  </div>

  @foreach ($apptRegions as $region => $group)
    <div class="ap-panel @if($loop->first) active @endif" data-region="{{ $region }}">
      <div class="ap-tiles">
        @foreach ($group as $d)
          @php
            $a = $availability[$d->id] ?? ['status' => 'ask', 'next_available_on' => null, 'confirmed_at' => null];
            $status = $a['status'];
            $label = ['ok' => 'Available', 'lim' => 'Limited', 'ask' => 'Ask us'][$status];
            $width = ['ok' => '82%', 'lim' => '34%', 'ask' => '100%'][$status];
          @endphp
          <a class="ap-tile" href="{{ url('/visa/'.$d->slug) }}">
            <div class="ap-tp">
              <h4>{{ $d->name }}</h4>
              <span class="ap-st {{ $status }}"><span class="dot"></span>{{ $label }}</span>
            </div>
            <div class="ap-bar"><i class="{{ $status }}" style="width:{{ $width }}"></i></div>
            @if($a['next_available_on'])
              <div class="ap-dt">{{ $a['next_available_on']->format('j M Y') }}</div>
              <div class="ap-lb">Next available</div>
              @if($a['confirmed_at'])
                <div class="ap-lb">as of {{ $a['confirmed_at']->format('j M') }}</div>
              @endif
            @else
              <div class="ap-dt">On request</div>
              <div class="ap-lb">We check live for you</div>
            @endif
          </a>
        @endforeach
      </div>
    </div>
  @endforeach
  @endif

  <div class="ap-legend">
    <span><i style="background:#2E9A8C"></i>Available</span>
    <span><i style="background:#c8923a"></i>Limited</span>
    <span><i style="background:#155E7A"></i>Ask us, we check live</span>
  </div>
</div></section>
<script>
  (function () {
    var tabs = Array.prototype.slice.call(document.querySelectorAll('#sgcApptTabs .sgc-tab'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('#sgc-appts .ap-panel'));
    if (!tabs.length) return;
    tabs.forEach(function (t) {
      t.addEventListener('click', function () {
        var region = t.getAttribute('data-region');
        tabs.forEach(function (x) { var on = x === t; x.classList.toggle('active', on); x.setAttribute('aria-selected', on ? 'true' : 'false'); });
        panels.forEach(function (p) { p.classList.toggle('active', p.getAttribute('data-region') === region); });
      });
    });
  })();
</script>

{{-- 5) WHO WE HELP --}}
<section id="sgc-who"><div class="wrap">
  <div class="sgc-head reveal">
    <p class="eyebrow">Who we help</p>
    <h2>Built for every kind of Schengen traveller</h2>
    <p class="lede">Whatever your situation, we tailor the application to what your consulate expects.</p>
  </div>
  <div class="sgc-who-grid">
    <div class="sgc-who-card reveal">
      <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></span>
      <h3>First-time applicants</h3>
      <p>We walk you through every document and form so a first application is done right the first time.</p>
    </div>
    <div class="sgc-who-card reveal">
      <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9 9 0 0 0-7 3.3"/><path d="M4 3v4h4"/></svg></span>
      <h3>Previously refused</h3>
      <p>We work out why the first answer was no and fix the real reason before you reapply.</p>
    </div>
    <div class="sgc-who-card reveal">
      <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></svg></span>
      <h3>Multi-country trips</h3>
      <p>We confirm the right country to apply through so one visa covers your whole itinerary.</p>
    </div>
    <div class="sgc-who-card reveal">
      <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></span>
      <h3>Business &amp; frequent travellers</h3>
      <p>We prepare strong, repeatable files for work trips and help you build a clean travel history.</p>
    </div>
  </div>
</div></section>

{{-- 6) HOW IT WORKS --}}
<section id="sgc-how"><div class="wrap">
  <div class="sgc-head reveal">
    <p class="eyebrow">How it works</p>
    <h2>Four steps to your appointment</h2>
    <p class="lede">A straightforward process with a real specialist guiding you at every stage.</p>
  </div>
  <div class="sgc-steps">
    <div class="sgc-step reveal"><div class="num">01</div><div class="rule"></div><h3>Eligibility check</h3><p>We confirm you can apply, and on the right visa, before you spend anything.</p><span class="free">Free</span></div>
    <div class="sgc-step reveal"><div class="num">02</div><div class="rule"></div><h3>Embassy or consulate match</h3><p>We work out the right country to apply through, based on your itinerary.</p></div>
    <div class="sgc-step reveal"><div class="num">03</div><div class="rule"></div><h3>Document checklist &amp; review</h3><p>A checklist for your exact circumstances, with every item checked by hand.</p></div>
    <div class="sgc-step reveal"><div class="num">04</div><div class="rule"></div><h3>Appointment booking &amp; submission</h3><p>We confirm live availability, book a slot in time and submit your application.</p></div>
  </div>
  <div style="text-align:center;margin-top:28px"><a class="btn" href="{{ url('/tools') }}">Check your eligibility &rarr;</a></div>
</div></section>

{{-- 7) PREVENTION TABLE --}}
<section id="sgc-refused"><div class="wrap">
  <div class="sgc-head reveal">
    <p class="eyebrow">Prevention</p>
    <h2>Why Schengen visa applications get refused</h2>
    <p class="lede">Most refusals come down to a handful of avoidable mistakes. Here is what tends to go wrong, and what to do instead.</p>
  </div>
  <div class="rf-rows">
    @foreach ($refusedReasons as $r)
      <div class="rf-row reveal">
        <div class="rf-cell rf-bad">
          <span class="rf-glyph" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg></span>
          <div><p class="rf-k">Why it fails</p><p class="rf-t">{{ $r['reason'] }}</p></div>
        </div>
        <div class="rf-cell rf-fix">
          <span class="rf-glyph" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
          <div><p class="rf-k">What to do instead</p><p class="rf-t">{{ $r['fix'] }}</p></div>
        </div>
      </div>
    @endforeach
  </div>
  <div class="rf-cta">
    <a class="btn" href="{{ url('/tools') }}">Check your eligibility</a>
  </div>
</div></section>

{{-- 8) PROOF --}}
<section id="sgc-proof"><div class="wrap">
  <div class="sgc-head reveal">
    <p class="eyebrow">Trusted by UK travellers</p>
    <h2>Real people, really sorted</h2>
    <div style="display:flex;justify-content:center;margin-top:10px">@include('partials.trustpilot-cta', ['align' => 'center', 'margin' => '0'])</div>
  </div>
  @if (! empty($reviews))
  <div class="sgc-quotes">
    @foreach ($reviews as $t)
      <figure class="sgc-tq reveal">
        <div class="stars" aria-label="{{ $t['rating'] ?? 5 }} out of 5 stars">{!! str_repeat('★', (int) ($t['rating'] ?? 5)) !!}</div>
        <blockquote>{{ $t['quote'] }}</blockquote>
        <figcaption>{{ $t['attribution'] }}</figcaption>
      </figure>
    @endforeach
  </div>
  @endif
  <p style="text-align:center;margin-top:24px"><a class="rlink" style="font-weight:600" href="{{ url('/reviews') }}">Read more traveller reviews &rarr;</a></p>
</div></section>

{{-- 9) PRICING TRANSPARENCY --}}
<section id="sgc-pricing"><div class="wrap">
  <div class="sgc-price reveal">
    <p class="eyebrow">Clear pricing</p>
    <h2>One fixed service fee, nothing hidden</h2>
    <p>Our service fee is a clear fixed amount, shown before you pay anything. It is separate from the consulate or government visa fee, which is set by the authorities and paid to them. There is no payment until you approve a quote.</p>
    <div class="sgc-price-points">
      <span class="pp"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>Clear fixed service fee</span>
      <span class="pp"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>Separate from the government fee</span>
      <span class="pp"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>No hidden charges</span>
      <span class="pp"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>No payment until you approve a quote</span>
    </div>
  </div>
</div></section>

{{-- 10) COUNTRY COVERAGE --}}
<section id="sgc-coverage"><div class="wrap">
  <div class="sgc-head reveal">
    <p class="eyebrow">Coverage</p>
    <h2>All 29 Schengen countries</h2>
    <p class="lede">One visa covers the whole Schengen Area. You apply through your main destination, and we help with any of them.</p>
  </div>
  @if ($destinations->isNotEmpty())
  <ul class="sgc-chips reveal">
    @foreach ($destinations as $destination)
      <li><a href="{{ url('/visa/'.$destination->slug) }}"><span class="dot"></span>{{ $destination->name }}</a></li>
    @endforeach
  </ul>
  @endif
  <div class="cov-cta">
    <a class="btn btn--ghost" href="{{ url('/destinations') }}">Browse all destinations &rarr;</a>
  </div>
</div></section>

{{-- 11) FAQ --}}
<section id="sgc-faq" class="faq-e"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Questions</p><h2>Schengen visa questions</h2></div>
  <div class="faq-panel reveal">
    <div class="faqd">
      @foreach ($faqs as $f)
        <details><summary>{{ $f['q'] }}</summary><p>{{ $f['a'] }}</p></details>
      @endforeach
    </div>
  </div>
</div></section>

{{-- 12) CONTACT / LEAD — two-team UK / DE rows --}}
<section id="sgc-contact"><div class="wrap">
  <div class="sgc-head reveal">
    <p class="eyebrow">Talk to a human</p>
    <h2>Speak to our UK or Germany team</h2>
    <p class="lede">A real person picks up. Tell us about your trip and we will tell you exactly what your application needs.</p>
  </div>
  <div class="sgc-contact-card reveal">
    <p class="eyebrow">Start here</p>
    <h3>Two teams, one application</h3>
    <p>Call whichever is easiest, or message us on WhatsApp for a quick reply.</p>
    <div class="sgc-team">
      <span class="sgc-team-who"><span class="sgc-fl">UK</span><span><b>UK team</b><small>{{ config('ukv.phone') ?: '+44 20 7946 0000' }}</small></span></span>
      <a href="tel:{{ config('ukv.phone_e164') ?: '+442079460000' }}" class="btn sgc-teambtn">@include('partials.call-glyph')Call</a>
    </div>
    <div class="sgc-team">
      <span class="sgc-team-who"><span class="sgc-fl">DE</span><span><b>Germany team</b><small>{{ config('ukv.phone_de') ?: '+49 30 0000 0000' }}</small></span></span>
      <a href="tel:{{ config('ukv.phone_de_e164') ?: '+490000000000' }}" class="btn sgc-teambtn">@include('partials.call-glyph')Call</a>
    </div>
    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn sgc-wabtn">{!! $waGlyph !!} Message on WhatsApp</a>
  </div>
</div></section>

{{-- FINAL CTA — navy band --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Start your Schengen application</h2>
  <p style="max-width:50ch;color:#eef0f1">Check your eligibility free, or message our UK and Germany team on WhatsApp and we will tell you exactly what your Schengen application needs.</p>
  <div class="row">
    <a href="{{ url('/tools') }}" class="btn">Check your eligibility</a>
    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn--glass">{!! $waGlyph !!} Chat on WhatsApp</a>
  </div>
  <div class="sgc-cta-phones">
    <a href="tel:{{ config('ukv.phone_e164') ?: '+442079460000' }}">@include('partials.call-glyph')UK {{ config('ukv.phone') ?: '+44 20 7946 0000' }}</a>
    <a href="tel:{{ config('ukv.phone_de_e164') ?: '+490000000000' }}">@include('partials.call-glyph')Germany {{ config('ukv.phone_de') ?: '+49 30 0000 0000' }}</a>
  </div>
  <p class="sgc-disclaimer">Beyond Passports is an independent consultancy, not a government website. We cannot guarantee any visa outcome; the consulate makes the decision. Express speeds our handling only, not the consulate decision. Our service fee is separate from the government visa fee.</p>
</div></section>

@endsection
