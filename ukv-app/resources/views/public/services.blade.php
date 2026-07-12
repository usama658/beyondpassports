@extends('layouts.public')

@section('title', 'Our Services: UK Visa, eVisa, ETA & IDP Help | Beyond Passports')
@section('description', 'Every Beyond Passports service in one place: Schengen and eVisa preparation, appointments, documents, refusal prevention, driving permits and free tools. Independent UK team. Not a government website.')

@push('head')
<style>
  /* ── services hub (journey stepper) — page-scoped only. Design system in ukv.css ── */

  /* Hero — dark concierge */
  .sv-hero {
    background:
      radial-gradient(680px 320px at 88% -10%, rgba(46,154,140,.5), transparent 60%),
      radial-gradient(620px 320px at -5% 120%, rgba(21,94,122,.6), transparent 60%),
      var(--navy);
    color: #fff;
  }
  .sv-hero .wrap { padding-top: 46px; padding-bottom: 40px; }
  .sv-hero .eyebrow { color: var(--soft); }
  .sv-hero h1 { max-width: 19ch; color: #fff; }
  .sv-hero .lede { max-width: 54ch; color: rgba(255,255,255,.82); }
  .sv-hero .row { margin-top: 18px; }
  .sv-hero .btn--ghost { border-color: rgba(255,255,255,.4); color: #fff; }

  /* Hero split + start-here card */
  .sv-hero-grid { display: grid; grid-template-columns: 1.2fr .8fr; gap: 48px; align-items: center; }
  .sv-hero-copy h1 { max-width: 16ch; }
  .sv-hero-copy .lede { margin-bottom: 0; }
  .sv-hcard { background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.18); border-radius: 18px; padding: 20px; }
  .sv-hcard h3 { margin: 0 0 3px; font-size: 17px; color: #fff; }
  .sv-hcard > p { margin: 0 0 12px; color: rgba(255,255,255,.72); font-size: 14px; }
  .sv-hbtns { display: flex; gap: 10px; }
  .sv-hcard .btn { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 7px; white-space: nowrap; padding: 11px 12px; }
  .sv-hbtns .wa-g { width: 16px; height: 16px; fill: currentColor; flex: none; }
  .cta-band .row .btn { display: inline-flex; align-items: center; gap: 8px; }
  .cta-band .row .wa-g { width: 18px; height: 18px; fill: currentColor; flex: none; }
  .btn--wa { background: #25D366; border: 0; color: #fff; }
  .btn--wa:hover { background: #1da851; }
  .sv-hsteps { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px 16px; }
  .sv-hsteps span { display: inline-flex; align-items: center; gap: 8px; font-size: 13.5px; color: rgba(255,255,255,.82); }
  .sv-hsteps b { background: rgba(255,255,255,.14); width: 22px; height: 22px; border-radius: 50%; display: grid; place-items: center; font-size: 12px; font-weight: 800; flex: none; }
  @media (max-width: 820px) { .sv-hero-grid { grid-template-columns: 1fr; gap: 28px; } }
  /* Trust bar — dark mesh band under hero (matches home .tbar-f) */
  .tbar-f { padding: 0; background:
      radial-gradient(520px 200px at 12% 0%, rgba(21,94,122,.45), transparent 60%),
      radial-gradient(520px 200px at 92% 100%, rgba(46,154,140,.42), transparent 60%),
      var(--navy); color: #fff; border-top: 1px solid rgba(255,255,255,.10); }
  .tbar-f .row { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; padding: 16px 0; }
  .tbar-f .ti { display: flex; align-items: center; gap: 9px; font: 600 14px var(--display); color: #fff; white-space: nowrap; }
  .tbar-f .ti svg { width: 20px; height: 20px; color: var(--soft); flex: none; }
  .tbar-f .ti b { color: var(--soft); font-weight: 800; }
  @media (max-width: 560px) { .tbar-f .row { gap: 14px 22px; } }

  /* Layout — sticky stepper + content */
  .sv-layout { display: grid; grid-template-columns: 250px 1fr; gap: 52px; align-items: start; padding: 56px 0 20px; }

  /* Stepper index */
  .sv-step { position: sticky; top: 88px; }
  .sv-step .lbl { font-size: 11px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; color: var(--ink-soft); margin: 0 0 18px; }
  .sv-step .item { display: flex; gap: 14px; position: relative; padding-bottom: 22px; color: inherit; }
  .sv-step .item:last-of-type { padding-bottom: 0; }
  .sv-step .item::before { content: ""; position: absolute; left: 13px; top: 28px; bottom: -2px; width: 2px; background: var(--paper-edge); }
  .sv-step .item:last-of-type::before { display: none; }
  .sv-step .dot { width: 28px; height: 28px; border-radius: 50%; border: 2px solid var(--paper-edge); background: #fff; display: grid; place-items: center; font-size: 12px; font-weight: 800; color: var(--ink-soft); flex: none; z-index: 1; transition: .18s; }
  .sv-step .item:hover .dot, .sv-step .item.on .dot { border-color: var(--stamp); background: var(--stamp); color: #fff; }
  .sv-step .tx b { display: block; font-size: 14.5px; font-weight: 700; line-height: 1.2; margin-top: 3px; color: var(--ink); }
  .sv-step .tx span { font-size: 12px; color: var(--ink-soft); }
  .sv-step .item.on .tx b { color: var(--stamp-text); }
  .sv-step .panel { margin-top: 24px; background: linear-gradient(160deg, #eef5f4, #fff); border: 1px solid var(--paper-edge); border-radius: 14px; padding: 18px; }
  .sv-step .panel b { display: block; font-size: 14px; margin-bottom: 6px; }
  .sv-step .panel p { margin: 0 0 12px; font-size: 12.5px; color: var(--ink-soft); }
  .sv-step .panel a { display: inline-block; background: var(--cta); color: #fff; padding: 9px 14px; border-radius: 9px; font-size: 12.5px; font-weight: 700; }

  /* Silo blocks */
  .sv-silo { scroll-margin-top: 88px; padding-bottom: 44px; margin-bottom: 44px; border-bottom: 1px solid var(--paper-edge); }
  .sv-silo:last-child { border-bottom: 0; margin-bottom: 0; }
  .sv-silo .sv-head { display: flex; align-items: baseline; gap: 12px; flex-wrap: wrap; }
  .sv-kicker { font-size: 11px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; color: var(--stamp-text); margin: 0 0 8px; flex-basis: 100%; }
  .sv-silo h2 { margin: 0; font-size: 27px; letter-spacing: -.01em; }
  .sv-silo .sv-star { font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--stamp-text); border: 1px solid var(--stamp); border-radius: 999px; padding: 3px 10px; }
  .sv-silo .sv-intro { margin: 8px 0 22px; color: var(--ink-soft); font-size: 15.5px; max-width: 62ch; }

  /* Split card silo: sticky header left, cards right */
  .sv-silo--split { display: grid; grid-template-columns: .8fr 2fr; gap: 36px; align-items: start; }
  .sv-silo--split .sv-silo-head { position: sticky; top: 88px; }
  .sv-silo--split .sv-intro { margin: 8px 0 0; max-width: 34ch; }
  .sv-silo--split .sv-silo-cta { margin-top: 16px; }
  .sv-silo--split .sv-grid { grid-template-columns: 1fr 1fr; }
  @media (max-width: 1100px) { .sv-silo--split .sv-grid { grid-template-columns: 1fr; } }
  @media (max-width: 900px) { .sv-silo--split { grid-template-columns: 1fr; gap: 16px; } .sv-silo--split .sv-silo-head { position: static; } .sv-silo--split .sv-grid { grid-template-columns: 1fr 1fr; } }
  @media (max-width: 560px) { .sv-silo--split .sv-grid { grid-template-columns: 1fr; } }

  .sv-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  .sv-card { position: relative; display: flex; flex-direction: column; gap: 8px; background: #fff; border: 1px solid var(--paper-edge); border-radius: 14px; padding: 20px; box-shadow: var(--lift-1); overflow: hidden; transition: transform .16s, box-shadow .16s, border-color .16s; }
  .sv-card::before { content: ""; position: absolute; inset: 0 auto 0 0; width: 3px; background: linear-gradient(var(--stamp), var(--cta)); opacity: 0; transition: opacity .16s; }
  a.sv-card:hover { transform: translateY(-3px); box-shadow: var(--lift-2); border-color: var(--stamp); }
  a.sv-card:hover::before { opacity: 1; }
  .sv-card .sv-chip { align-self: flex-start; }
  .sv-card h3 { margin: 0; font-size: 16.5px; line-height: 1.3; }
  .sv-card p { margin: 0; font-size: 13.5px; color: var(--ink-soft); line-height: 1.5; flex: 1; }
  .sv-card { transition: padding-bottom .18s ease, transform .16s, box-shadow .16s, border-color .16s; }
  .sv-card .sv-fab { position: absolute; left: 20px; bottom: 16px; }
  a.sv-card:hover { padding-bottom: 60px; }

  /* WhatsApp chat pill: hidden, appears only when the tile is hovered (shared by cards + rows) */
  .sv-fab { display: inline-flex; align-items: center; height: 38px; flex: none; background: #25D366; color: #fff; border-radius: 999px; box-shadow: 0 10px 22px -12px rgba(37,211,102,.85); opacity: 0; transform: translateY(4px); transition: opacity .18s ease, transform .18s ease; pointer-events: none; }
  .sv-fab .wa-g { width: 19px; height: 19px; fill: currentColor; flex: none; margin: 0 8px 0 12px; }
  .sv-fab .l { font-weight: 800; font-size: 13px; white-space: nowrap; padding-right: 16px; }
  a.sv-card:hover .sv-fab, a.sv-row:hover .sv-fab { opacity: 1; transform: none; pointer-events: auto; }
  @media (hover: none) { .sv-fab { opacity: 1; transform: none; pointer-events: auto; } }
  .sv-silo-cta { display: inline-flex; align-items: center; gap: 11px; margin-top: 18px; color: var(--ink); }
  .sv-silo-cta-ic { width: 38px; height: 38px; border-radius: 11px; background: #25D366; color: #fff; display: grid; place-items: center; flex: none; box-shadow: 0 10px 22px -12px rgba(37,211,102,.8); transition: background .15s, transform .15s; }
  .sv-silo-cta .wa-g { width: 18px; height: 18px; fill: currentColor; }
  .sv-silo-cta:hover .sv-silo-cta-ic { background: #1da851; transform: translateY(-1px); }
  .sv-silo-cta-tx { display: flex; flex-direction: column; line-height: 1.15; }
  .sv-silo-cta-tx b { font-size: 14px; font-weight: 800; }
  .sv-silo-cta-tx small { font-size: 11.5px; font-weight: 600; color: var(--ink-soft); }
  /* WhatsApp glyph inside hero / CTA buttons */
  .btn .wa-g { width: 17px; height: 17px; fill: currentColor; flex: none; margin-right: 2px; vertical-align: -3px; }

  /* Editorial-row layout (categories with layout = rows) */
  .sv-list { display: flex; flex-direction: column; }
  .sv-row { display: grid; grid-template-columns: 5px 1fr auto; align-items: center; gap: 18px; padding: 20px 4px; border-top: 1px solid var(--paper-edge); transition: padding .15s; }
  .sv-row:first-child { border-top: 0; }
  a.sv-row { color: inherit; }
  a.sv-row:hover { padding-left: 12px; }
  .sv-rail { align-self: stretch; min-height: 42px; border-radius: 4px; background: var(--stamp); }
  .sv-row h3 { margin: 0 0 5px; font-size: 16.5px; font-weight: 700; }
  .sv-row p { margin: 0; font-size: 13.5px; color: var(--ink-soft); max-width: 60ch; line-height: 1.5; }
  .sv-row .sv-fab { align-self: center; }

  /* Status chips */
  .sv-chip { font-size: 10.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; padding: 3px 8px; border-radius: 6px; border: 1px solid transparent; }
  .sv-chip--available  { color: #1f6b4f; background: rgba(92,154,123,.14); border-color: rgba(92,154,123,.35); }
  .sv-chip--coming-soon { color: #8a6d1f; background: rgba(202,166,68,.16); border-color: rgba(202,166,68,.4); }
  .sv-chip--on-request { color: #155e7a; background: rgba(21,94,122,.12); border-color: rgba(21,94,122,.3); }

  /* How it works + Why us reuse shared .steps / .ticks from ukv.css */

  /* FAQ — tinted panel accordion (matches driving-abroad / money pages) */
  .faq-e { background: var(--paper); }
  .faq-e .sec-head { text-align: center; max-width: 60ch; margin-left: auto; margin-right: auto; }
  .faq-panel { background: var(--white); border: 1px solid var(--paper-edge); border-radius: 18px; padding: 6px 30px; max-width: 80ch; margin: 0 auto; box-shadow: 0 16px 40px -30px rgba(40,50,70,.5); }
  .faqd { max-width: none; }
  .faqd details { border-bottom: 1px solid var(--paper-edge); padding: 18px 0; }
  .faqd details:last-child { border-bottom: 0; }
  .faqd summary { font-family: var(--display); font-size: 19px; color: var(--navy); font-weight: 600; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; gap: 16px; }
  .faqd summary::-webkit-details-marker { display: none; }
  .faqd summary::after { content: "+"; font-size: 22px; color: var(--cta); flex: 0 0 auto; font-weight: 700; transition: transform .15s ease; }
  .faqd details[open] summary::after { content: "\2013"; }
  .faqd p { margin: 12px 0 0; color: #3a4b55; font-size: 16px; line-height: 1.65; }

  @media (max-width: 900px) {
    .sv-layout { grid-template-columns: 1fr; gap: 28px; padding-top: 36px; }
    .sv-step { position: static; }
    .sv-step .panel { display: none; }
    .sv-hero h1 { font-size: 34px; }
  }
  @media (max-width: 560px) {
    .sv-grid { grid-template-columns: 1fr; }
  }
</style>
@endpush

@section('content')

@include('partials.services-hero')

@include('partials.services-body')

@endsection
