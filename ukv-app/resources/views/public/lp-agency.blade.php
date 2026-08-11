{{-- Ported from storage/previews/preview-agents-lp-bp-theme.html; standalone LP, brand-themed. --}}
@extends('layouts.public')

@section('title', 'Schengen Visa Agency UK | Beyond Passports')
@section('description', 'Trusted Schengen visa agents in the UK. 93% approval rate. We find appointments, prepare documents, and handle your application. 100% refund if refused. Free case check, reply in 30 minutes.')

@push('head')
<style>
/* Beyond Passports theme tokens (lp-bold house style): petrol #155E7A primary, stamp teal #2E9A8C,
   WhatsApp green CTAs, paper #F4F6FA, ink #16222E. Names kept from source; values reskinned.
   All tokens + selectors scoped under .sva so they never clash with the global stylesheet. */
.sva {
  --navy: #16222e;
  --navy-mid: #0f2028;
  --teal: #155e7a;               /* petrol - primary accent */
  --teal-bright: #2e9a8c;        /* stamp teal - secondary */
  --teal-light: #e7f4ef;
  --teal-glow: rgba(21,94,122,0.12);
  --cta: #25d366;                /* WhatsApp green (unchanged) */
  --cta-hover: #1fc45a;
  --cta-glow: rgba(37,211,102,0.25);
  --bg: #f4f6fa;                 /* paper */
  --bg-warm: #fafbfc;
  --white: #ffffff;
  --card: #ffffff;
  --border: #dde3ec;
  --border-hover: rgba(21,94,122,0.30);
  --grey-50: #f7f9fb;
  --grey-100: #eef2f6;
  --grey-200: #dde3ec;
  --grey-300: #c4cdd8;
  --grey-500: #8a959d;
  --grey-600: #5d6b76;
  --grey-700: #3d4a56;
  --amber: #b5791f;              /* limited band */
  --amber-bg: rgba(181,121,31,0.10);
  --red: #c0492f;               /* urgent band */
  --red-bg: rgba(192,73,47,0.08);
  --green-bg: rgba(37,211,102,0.08);
  --font: 'Outfit', system-ui, -apple-system, sans-serif;
  --font-display: 'Outfit', system-ui, sans-serif;
  --max-w: 1120px;
  --section-py: clamp(3.5rem, 7vw, 6rem);
  --radius: 14px;
  --radius-sm: 8px;
  --shadow-sm: 0 1px 3px rgba(15,25,35,0.04), 0 1px 2px rgba(15,25,35,0.06);
  --shadow-md: 0 4px 20px rgba(15,25,35,0.06), 0 1px 3px rgba(15,25,35,0.04);
  --shadow-lg: 0 8px 40px rgba(15,25,35,0.08), 0 2px 6px rgba(15,25,35,0.04);
  --shadow-glow: 0 8px 32px var(--cta-glow);
}

.sva, .sva *, .sva *::before, .sva *::after { box-sizing: border-box; margin: 0; padding: 0; }

.sva {
  font-family: var(--font);
  color: var(--navy);
  background: var(--bg);
  line-height: 1.65;
  font-size: 16px;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* ── ANIMATIONS ── */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(24px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes pulse {
  0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(37,211,102,0.4); }
  50% { opacity: 0.8; box-shadow: 0 0 0 8px rgba(37,211,102,0); }
}
@keyframes slideInRight {
  from { opacity: 0; transform: translateX(30px); }
  to { opacity: 1; transform: translateX(0); }
}
@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

.sva .reveal {
  opacity: 0;
  transform: translateY(24px);
  transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.sva .reveal.visible {
  opacity: 1;
  transform: translateY(0);
}

/* ── HERO ── */
.sva .hero {
  background: var(--white);
  padding: clamp(2.5rem, 5vw, 4rem) 1.5rem clamp(3rem, 6vw, 5rem);
  position: relative;
  overflow: hidden;
}
.sva .hero::before {
  content: '';
  position: absolute;
  top: -50%; right: -20%;
  width: 70%; height: 200%;
  background: radial-gradient(ellipse at center, var(--teal-glow) 0%, transparent 70%);
  pointer-events: none;
}
.sva .hero-landmarks {
  position: absolute;
  bottom: 0; right: 0;
  width: 55%;
  max-width: 500px;
  height: 180px;
  opacity: 0.04;
  pointer-events: none;
  z-index: 0;
}
@media (max-width: 767px) {
  .sva .hero-landmarks { width: 80%; height: 120px; opacity: 0.03; }
}
.sva .hero::after {
  content: '';
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--border), transparent);
}
.sva .hero-inner {
  max-width: var(--max-w);
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr;
  gap: 2rem;
  align-items: center;
  position: relative;
}
@media (min-width: 768px) {
  .sva .hero-inner { grid-template-columns: 1.15fr 0.85fr; gap: 3.5rem; }
}

.sva .hero-copy {
  position: relative;
  z-index: 1;
  animation: fadeUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.sva .refund-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  background: var(--teal-light);
  border: 1px solid rgba(26,122,109,0.12);
  border-radius: 100px;
  padding: 0.35rem 0.85rem;
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--teal);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 1.25rem;
}
.sva .refund-badge svg { flex-shrink: 0; }

.sva .eyebrow {
  font-family: var(--font);
  font-size: 0.78rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--teal);
  margin-bottom: 0.85rem;
  opacity: 0.85;
}

.sva .hero h1 {
  font-family: var(--font-display);
  font-size: clamp(1.65rem, 3.8vw, 2.6rem);
  font-weight: 800;
  line-height: 1.15;
  color: var(--navy);
  text-wrap: balance;
  margin-bottom: 1.15rem;
  letter-spacing: -0.025em;
}

.sva .hero-sub {
  font-size: 0.98rem;
  color: var(--grey-600);
  line-height: 1.7;
  max-width: 500px;
  margin-bottom: 1.5rem;
}
.sva .hero-sub strong { color: var(--navy); font-weight: 600; }

.sva .proof-strip {
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem 1.15rem;
}
.sva .proof-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  font-weight: 500;
  color: var(--grey-700);
}
.sva .proof-check {
  width: 18px; height: 18px;
  background: var(--teal-light);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.sva .proof-check svg { color: var(--teal); }

/* ── FORM CARD ── */
.sva .form-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.75rem;
  box-shadow: var(--shadow-lg);
  position: relative;
  z-index: 1;
  animation: slideInRight 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
}
.sva .form-card::before {
  content: '';
  position: absolute;
  inset: -1px;
  border-radius: var(--radius);
  padding: 1px;
  background: linear-gradient(135deg, rgba(26,122,109,0.2), transparent 50%, rgba(37,211,102,0.15));
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
}

.sva .form-header {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin-bottom: 1.25rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--grey-100);
}
.sva .form-live-dot {
  width: 8px; height: 8px;
  background: var(--cta);
  border-radius: 50%;
  animation: pulse 2s ease-in-out infinite;
}
.sva .form-header-text {
  font-size: 0.82rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--navy);
}
.sva .form-header-free {
  font-size: 0.65rem;
  font-weight: 700;
  background: var(--green-bg);
  color: #16a34a;
  padding: 0.2rem 0.6rem;
  border-radius: 100px;
  margin-left: auto;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.sva .form-field { margin-bottom: 0.85rem; }
.sva .form-field label {
  display: block;
  font-size: 0.72rem;
  font-weight: 600;
  color: var(--grey-500);
  margin-bottom: 0.35rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}
.sva .form-field input,
.sva .form-field select {
  width: 100%;
  padding: 0.72rem 0.85rem;
  border: 1.5px solid var(--grey-200);
  border-radius: var(--radius-sm);
  font-size: 0.92rem;
  font-family: var(--font);
  color: var(--navy);
  background: var(--grey-50);
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
  outline: none;
}
.sva .form-field input:focus,
.sva .form-field select:focus {
  border-color: var(--teal);
  background: var(--white);
  box-shadow: 0 0 0 3px var(--teal-glow);
}

.sva .cta-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.85rem 1.5rem;
  background: linear-gradient(135deg, var(--cta) 0%, var(--cta-hover) 100%);
  color: #fff;
  font-size: 0.98rem;
  font-weight: 700;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.2s;
  font-family: var(--font);
  letter-spacing: 0.01em;
  box-shadow: var(--shadow-glow);
  text-decoration: none;
  position: relative;
  overflow: hidden;
}
.sva .cta-btn::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
  background-size: 200% 100%;
  animation: shimmer 3s ease-in-out infinite;
}
.sva .cta-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 12px 40px var(--cta-glow);
}
.sva .cta-btn:active { transform: translateY(0); }

.sva .click-triggers {
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem;
  margin-top: 0.85rem;
  font-size: 0.72rem;
  color: var(--grey-500);
  font-weight: 500;
}
.sva .click-triggers span {
  display: flex;
  align-items: center;
  gap: 0.3rem;
}
.sva .click-triggers svg { color: var(--teal); }

/* ── SECTIONS ── */
.sva .section { padding: var(--section-py) 1.5rem; }
.sva .section-inner { max-width: var(--max-w); margin: 0 auto; }
.sva .section--white { background: var(--white); }
.sva .section--grey { background: var(--bg); }
.sva .section--navy {
  background: var(--navy);
  color: #fff;
  position: relative;
}
.sva .section--navy::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at 30% 50%, rgba(26,122,109,0.08) 0%, transparent 60%);
  pointer-events: none;
}

.sva .section-eyebrow {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: var(--teal);
  margin-bottom: 0.6rem;
}
.sva .section--navy .section-eyebrow { color: var(--teal-bright); opacity: 0.7; }

.sva .section h2 {
  font-family: var(--font-display);
  font-size: clamp(1.4rem, 3.2vw, 2.1rem);
  font-weight: 800;
  line-height: 1.2;
  text-wrap: balance;
  margin-bottom: 0.6rem;
  letter-spacing: -0.02em;
}
.sva .section--navy h2 { color: #fff; }

.sva .section-sub {
  font-size: 0.95rem;
  color: var(--grey-600);
  max-width: 540px;
  margin-bottom: 2.5rem;
  line-height: 1.7;
}
.sva .section--navy .section-sub { color: rgba(255,255,255,0.55); }

/* ── DIVIDER ── */
.sva .section-divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--border), transparent);
}

/* ── PATH CARDS ── */
.sva .path-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}
@media (min-width: 768px) {
  .sva .path-grid { grid-template-columns: repeat(3, 1fr); }
}

/* Boarding-pass stub cards: teal stub (icon + vertical code) + perforation + body */
.sva .path-card {
  background: var(--card);
  border: 1.5px solid var(--border);
  border-radius: 16px;
  overflow: hidden;
  transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s;
  display: flex;
  position: relative;
  box-shadow: 0 10px 34px -22px rgba(15,25,35,0.35);
}
.sva .path-card:hover {
  box-shadow: var(--shadow-lg);
  transform: translateY(-5px);
}
.sva .pc-stub {
  flex: none;
  width: 74px;
  background: linear-gradient(180deg, var(--teal), var(--teal-d, #0f4a61));
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  padding: 1.1rem 0;
}
.sva .pc-ic {
  width: 38px; height: 38px;
  border-radius: 10px;
  background: rgba(255,255,255,0.16);
  display: grid;
  place-items: center;
  flex: none;
}
.sva .pc-ic svg { width: 20px; height: 20px; fill: none; stroke: #fff; stroke-width: 1.9; stroke-linecap: round; stroke-linejoin: round; }
.sva .pc-code {
  writing-mode: vertical-rl;
  transform: rotate(180deg);
  font-size: 0.6rem;
  font-weight: 800;
  letter-spacing: 0.28em;
  text-transform: uppercase;
  color: rgba(255,255,255,0.85);
}
.sva .pc-perf {
  flex: none;
  width: 2px;
  background: repeating-linear-gradient(180deg, var(--border) 0 6px, transparent 6px 12px);
  position: relative;
}
.sva .pc-perf::before,
.sva .pc-perf::after {
  content: "";
  position: absolute;
  left: -6px;
  width: 14px; height: 14px;
  border-radius: 50%;
  background: var(--bg);
  border: 1px solid var(--border);
}
.sva .pc-perf::before { top: -8px; }
.sva .pc-perf::after { bottom: -8px; }
.sva .pc-body {
  padding: 1.4rem 1.4rem 1.25rem;
  display: flex;
  flex-direction: column;
}
.sva .path-card p.path-card-quote {
  font-family: var(--font-display);
  font-size: 0.8rem;
  font-weight: 600;
  font-style: italic;
  color: #1f6e63;
  background: rgba(46,154,140,0.09);
  align-self: flex-start;
  flex: 0 0 auto;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  margin-bottom: 0.7rem;
}
.sva .path-card h3 {
  font-family: var(--font-display);
  font-size: 1.05rem;
  font-weight: 700;
  margin-bottom: 0.55rem;
  color: var(--navy);
  line-height: 1.3;
}
.sva .path-card p {
  font-size: 0.85rem;
  color: var(--grey-600);
  line-height: 1.6;
  flex: 1;
  margin-bottom: 1rem;
}
.sva .path-card-cta {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  margin-top: auto;
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--teal);
  text-decoration: none;
  transition: gap 0.2s, color 0.2s;
}
.sva .path-card-cta:hover { gap: 0.65rem; color: var(--teal-bright); }
/* Boarding-pass cards — small-screen tightening */
@media (max-width: 560px) {
  .sva .pc-stub { width: 60px; padding: 0.9rem 0; }
  .sva .pc-ic { width: 34px; height: 34px; }
  .sva .pc-ic svg { width: 18px; height: 18px; }
  .sva .pc-body { padding: 1rem 1rem 0.9rem; }
  .sva .path-card h3 { font-size: 0.98rem; }
}

/* ── APPOINTMENT TABLE ── */
.sva .appt-table-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  border: 1.5px solid var(--border);
  border-radius: var(--radius);
  background: var(--card);
  box-shadow: var(--shadow-sm);
}
.sva .appt-table {
  width: 100%;
  border-collapse: collapse;
  font-variant-numeric: tabular-nums;
  min-width: 500px;
}
.sva .appt-table th {
  text-align: left;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--grey-500);
  padding: 0.85rem 1.15rem;
  border-bottom: 1.5px solid var(--grey-100);
  background: var(--grey-50);
}
.sva .appt-table td {
  padding: 0.85rem 1.15rem;
  border-bottom: 1px solid var(--grey-100);
  font-size: 0.9rem;
}
.sva .appt-table tr:last-child td { border-bottom: none; }
.sva .appt-table tr {
  transition: background 0.15s;
}
.sva .appt-table tbody tr:hover td { background: var(--teal-light); }

.sva .appt-country {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-weight: 600;
}
.sva .appt-country .flag { font-size: 1.2rem; }

.sva .status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.25rem 0.65rem;
  border-radius: 100px;
}
.sva .status-badge--green { background: var(--green-bg); color: #16a34a; }
.sva .status-badge--amber { background: var(--amber-bg); color: #b45309; }
.sva .status-badge--red { background: var(--red-bg); color: var(--red); }
.sva .status-badge--grey { background: var(--grey-100); color: var(--grey-600); }
.sva .status-dot {
  width: 6px; height: 6px;
  border-radius: 50%;
  display: inline-block;
}
.sva .status-dot--green { background: #16a34a; }
.sva .status-dot--amber { background: var(--amber); }
.sva .status-dot--red { background: var(--red); }
.sva .status-dot--grey { background: var(--grey-500); }

.sva .appt-cta {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--teal);
  text-decoration: none;
  white-space: nowrap;
  transition: color 0.15s;
}
.sva .appt-cta:hover { color: var(--teal-bright); }

.sva .urgency-bar {
  margin-top: 1.5rem;
  background: var(--navy);
  border-radius: var(--radius);
  padding: 1.25rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  position: relative;
  overflow: hidden;
}
.sva .urgency-bar::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at right, rgba(37,211,102,0.08), transparent 60%);
  pointer-events: none;
}
.sva .urgency-bar-text {
  color: rgba(255,255,255,0.8);
  font-size: 0.9rem;
  font-weight: 500;
  position: relative;
}
.sva .urgency-bar-text strong { color: #fff; font-weight: 700; }
.sva .urgency-bar .cta-btn {
  width: auto;
  padding: 0.65rem 1.25rem;
  font-size: 0.85rem;
  flex-shrink: 0;
  position: relative;
}

.sva .appt-disclaimer {
  margin-top: 1rem;
  font-size: 0.72rem;
  color: var(--grey-500);
  line-height: 1.6;
}

/* ── TESTIMONIALS ── */
.sva .testimonial-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}
@media (min-width: 640px) {
  .sva .testimonial-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 960px) {
  .sva .testimonial-grid { grid-template-columns: repeat(3, 1fr); }
}

.sva .testimonial-card {
  background: var(--card);
  border: 1.5px solid var(--border);
  border-radius: var(--radius);
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.sva .testimonial-card:hover {
  border-color: var(--border-hover);
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}
.sva .testimonial-stars {
  color: var(--amber);
  font-size: 0.85rem;
  letter-spacing: 0.12em;
  margin-bottom: 0.75rem;
}
.sva .testimonial-quote {
  font-size: 0.9rem;
  line-height: 1.7;
  color: var(--grey-700);
  flex: 1;
  margin-bottom: 1.15rem;
  font-style: italic;
}
.sva .testimonial-meta {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding-top: 1rem;
  border-top: 1px solid var(--grey-100);
}
.sva .testimonial-avatar {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--teal), var(--teal-bright));
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.75rem;
  flex-shrink: 0;
  letter-spacing: 0.02em;
}
.sva .testimonial-name {
  font-weight: 600;
  font-size: 0.82rem;
  color: var(--navy);
}
.sva .testimonial-detail {
  font-size: 0.72rem;
  color: var(--grey-500);
}

/* ── TIMELINE ── */
.sva .timeline {
  position: relative;
  padding-left: 2.5rem;
  max-width: 600px;
}
.sva .timeline::before {
  content: '';
  position: absolute;
  left: 7px;
  top: 8px;
  bottom: 8px;
  width: 2px;
  background: linear-gradient(180deg, var(--teal) 0%, var(--red) 100%);
  border-radius: 2px;
}
.sva .timeline-step {
  position: relative;
  padding-bottom: 2.25rem;
}
.sva .timeline-step:last-child { padding-bottom: 0; }
.sva .timeline-dot {
  position: absolute;
  left: -2.15rem;
  top: 4px;
  width: 16px; height: 16px;
  border-radius: 50%;
  border: 3px solid var(--teal);
  background: var(--white);
  box-shadow: 0 0 0 4px var(--teal-glow);
  transition: all 0.2s;
}
.sva .timeline-step:nth-child(3) .timeline-dot,
.sva .timeline-step:nth-child(4) .timeline-dot {
  border-color: var(--red);
  box-shadow: 0 0 0 4px var(--red-bg);
}
.sva .timeline-step h3 {
  font-family: var(--font-display);
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--navy);
  margin-bottom: 0.35rem;
}
.sva .timeline-step p {
  font-size: 0.88rem;
  color: var(--grey-600);
  line-height: 1.65;
}
.sva .timeline-step strong { color: var(--navy); }

.sva .bridge-box {
  margin-top: 2.5rem;
  background: var(--teal-light);
  border-left: 4px solid var(--teal);
  border-radius: 0 var(--radius) var(--radius) 0;
  padding: 1.5rem 1.75rem;
  max-width: 600px;
}
.sva .bridge-box p {
  font-size: 0.9rem;
  color: var(--grey-600);
  line-height: 1.7;
  margin-bottom: 0.5rem;
}
.sva .bridge-box p:last-child { margin-bottom: 0; }
.sva .bridge-box strong { color: var(--navy); }

/* ── PROCESS ── */
.sva .process-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}
@media (min-width: 768px) {
  .sva .process-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 960px) {
  .sva .process-grid { grid-template-columns: repeat(4, 1fr); }
}

.sva .process-card {
  background: var(--card);
  border: 1.5px solid var(--border);
  border-radius: var(--radius);
  padding: 1.5rem;
  position: relative;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.sva .process-card:hover {
  border-color: var(--border-hover);
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}
.sva .process-number {
  font-family: var(--font-display);
  font-size: 2.8rem;
  font-weight: 800;
  background: linear-gradient(135deg, var(--teal-light) 30%, rgba(26,122,109,0.08));
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  line-height: 1;
  margin-bottom: 0.65rem;
}
.sva .process-card h3 {
  font-family: var(--font-display);
  font-size: 0.92rem;
  font-weight: 700;
  color: var(--navy);
  margin-bottom: 0.45rem;
}
.sva .process-card p {
  font-size: 0.84rem;
  color: var(--grey-600);
  line-height: 1.65;
}

/* ── GUARANTEE ── */
.sva .guarantee-box {
  background: var(--teal-light);
  border: 1.5px solid rgba(26,122,109,0.1);
  border-radius: 20px;
  padding: clamp(2rem, 4vw, 3rem);
  text-align: center;
  max-width: 660px;
  margin: 0 auto;
  position: relative;
  overflow: hidden;
}
.sva .guarantee-box::before {
  content: '';
  position: absolute;
  top: -40%; left: -30%;
  width: 160%; height: 180%;
  background: radial-gradient(ellipse at center, rgba(26,122,109,0.06) 0%, transparent 70%);
  pointer-events: none;
}
.sva .guarantee-icon {
  width: 56px; height: 56px;
  background: var(--white);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.25rem;
  box-shadow: var(--shadow-md);
  position: relative;
}
.sva .guarantee-box h2 { margin-bottom: 0.85rem; position: relative; }
.sva .guarantee-box p {
  font-size: 0.92rem;
  color: var(--grey-600);
  line-height: 1.7;
  margin-bottom: 0.6rem;
  position: relative;
}
.sva .guarantee-checks {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  margin: 1.25rem auto;
  text-align: left;
  max-width: 380px;
  position: relative;
}
.sva .guarantee-checks li {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.88rem;
  font-weight: 500;
  color: var(--navy);
}
.sva .guarantee-checks svg { color: var(--teal); flex-shrink: 0; }
.sva .guarantee-honesty {
  font-size: 0.85rem;
  color: var(--grey-600);
  font-style: italic;
  margin-top: 1.25rem;
  padding-top: 1.25rem;
  border-top: 1px solid rgba(26,122,109,0.1);
  position: relative;
}

/* ── TEAM ── */
.sva .team-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}
@media (min-width: 768px) {
  .sva .team-grid { grid-template-columns: repeat(3, 1fr); }
}

.sva .team-card {
  background: var(--card);
  border: 1.5px solid var(--border);
  border-radius: var(--radius);
  padding: 1.75rem;
  text-align: center;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.sva .team-card:hover {
  border-color: var(--border-hover);
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}
.sva .team-avatar {
  width: 64px; height: 64px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--navy) 0%, var(--teal) 100%);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.15rem;
  margin: 0 auto 1rem;
  letter-spacing: 0.02em;
  box-shadow: 0 4px 12px rgba(15,25,35,0.12);
}
.sva .team-card h3 {
  font-family: var(--font-display);
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--navy);
  margin-bottom: 0.2rem;
}
.sva .team-role {
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--teal);
  margin-bottom: 0.85rem;
}
.sva .team-quote {
  font-size: 0.86rem;
  color: var(--grey-600);
  line-height: 1.65;
  font-style: italic;
}

/* ── CLOSE SECTION ── */
.sva .close-section { text-align: center; position: relative; }
.sva .close-section h2 { color: #fff; margin-bottom: 0.5rem; }
.sva .close-section .section-sub {
  margin-left: auto;
  margin-right: auto;
  margin-bottom: 2rem;
}
.sva .close-form {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-width: 400px;
  margin: 0 auto;
  position: relative;
}
@media (min-width: 640px) {
  .sva .close-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
  }
}
.sva .close-form input,
.sva .close-form select {
  width: 100%;
  padding: 0.75rem 0.85rem;
  border: 1.5px solid rgba(255,255,255,0.12);
  border-radius: var(--radius-sm);
  font-size: 0.92rem;
  font-family: var(--font);
  color: #fff;
  background: rgba(255,255,255,0.06);
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
  backdrop-filter: blur(4px);
}
.sva .close-form input::placeholder { color: rgba(255,255,255,0.35); }
.sva .close-form select { color: rgba(255,255,255,0.5); }
.sva .close-form select option { color: var(--navy); background: var(--white); }
.sva .close-form input:focus,
.sva .close-form select:focus {
  border-color: var(--cta);
  background: rgba(255,255,255,0.1);
  box-shadow: 0 0 0 3px rgba(37,211,102,0.12);
}

.sva .close-trust {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 0.85rem;
  margin-top: 1.25rem;
  position: relative;
}
.sva .close-trust span {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.75rem;
  color: rgba(255,255,255,0.5);
  font-weight: 500;
}
.sva .close-trust svg { color: var(--cta); }

/* ── FAQ ── */
.sva .faq-list {
  max-width: 680px;
  margin: 0 auto;
}
.sva .faq-item {
  border-bottom: 1px solid var(--grey-200);
}
.sva .faq-item:first-child { border-top: 1px solid var(--grey-200); }
.sva .faq-q {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.15rem 0;
  background: none;
  border: none;
  cursor: pointer;
  font-family: var(--font);
  font-size: 0.92rem;
  font-weight: 600;
  color: var(--navy);
  text-align: left;
  line-height: 1.4;
  transition: color 0.15s;
}
.sva .faq-q:hover { color: var(--teal); }
.sva .faq-q svg {
  flex-shrink: 0;
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  color: var(--grey-300);
}
.sva .faq-item.open .faq-q svg { transform: rotate(45deg); color: var(--teal); }
.sva .faq-a {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.sva .faq-item.open .faq-a { grid-template-rows: 1fr; }
.sva .faq-a-inner {
  overflow: hidden;
  padding: 0;
  font-size: 0.88rem;
  color: var(--grey-600);
  line-height: 1.7;
}
.sva .faq-item.open .faq-a-inner { padding-bottom: 1.25rem; }

/* ── UTILITY ── */
.sva .sr-only {
  position: absolute; width: 1px; height: 1px;
  padding: 0; margin: -1px; overflow: hidden;
  clip: rect(0,0,0,0); border: 0;
}

.sva .inline-cta {
  display: inline-flex;
  max-width: 300px;
}

/* ── CONSENT ── */
.sva .consent-check {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  font-size: 0.72rem;
  color: var(--grey-500);
  line-height: 1.5;
  margin-bottom: 0.65rem;
  cursor: pointer;
}
.sva .consent-check input[type="checkbox"] {
  margin-top: 2px;
  accent-color: var(--teal);
  flex-shrink: 0;
}
.sva .consent-check--dark { color: rgba(255,255,255,0.4); }

@media (prefers-reduced-motion: reduce) {
  .sva *, .sva *::before, .sva *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
  .sva .reveal { opacity: 1; transform: none; }
}

/* Default site trust bar (.tbar-f) — core desktop styling. The global ukv.css only
   ships the mobile carousel; the navy band + flex row live in each page's own CSS
   (home.blade.php has its own copy). Scoped to .sva and given explicit padding
   because .sva *{padding:0} zeroes the shared .wrap. */
.sva .tbar-f { padding: 0; background:
    radial-gradient(520px 200px at 12% 0%, rgba(21,94,122,.45), transparent 60%),
    radial-gradient(520px 200px at 92% 100%, rgba(46,154,140,.42), transparent 60%),
    var(--navy); color:#fff; }
.sva .tbar-f .wrap { max-width: var(--max-w); margin: 0 auto; padding: 0 24px; }
.sva .tbar-f .row { display:flex; justify-content:center; gap:30px; flex-wrap:wrap; padding:16px 0; }
.sva .tbar-f .ti { display:flex; align-items:center; gap:9px; font:600 14px var(--font-display); color:#fff; white-space:nowrap; text-decoration:none; }
.sva .tbar-f .ti svg { width:20px; height:20px; color:#7fd1b4; flex:none; }
.sva .tbar-f .ti b { color:#7fd1b4; font-weight:800; }
@media (max-width:760px) {
  .sva .tbar-f .wrap { padding:0; }
}

/* ── Live appointment board — same neon-glass design as /schengen-visa-consultancy.
   Scoped under a nested .lpb wrapper so we reuse the consultancy board CSS verbatim
   without colliding with the .sva page tokens. ── */
.sva .lpb{--cta:#155E7A;--cta-d:#0F4A61;--stamp:#2E9A8C;--soft:#A9CCDA;--stamp-text:#1F6E63;--muted:#5d6b76;--edge:#dde3ec;--wa:#25D366}
.sva .lpb .open{--c:#1F6E63;--cd:#155248;--cbg:#e7f4ef;--cbg2:#f4fbf8;--cbd:#bfe3d8;--soft:#f5faf8}
.sva .lpb .tight{--c:#b5791f;--cd:#9a6413;--cbg:#faeed6;--cbg2:#fffaf0;--cbd:#ecce9a;--soft:#fdf9ef}
.sva .lpb .none{--c:#c0392b;--cd:#992a1f;--cbg:#fbe4e0;--cbg2:#fff3f0;--cbd:#eeb4a8;--soft:#fef4f2}
.sva .lpb .ngstage{background:transparent;border:0;padding:0;margin-top:6px}
.sva .lpb .bfresh{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:0 0 14px}
.sva .lpb .bfresh .fchip{display:inline-flex;align-items:center;gap:7px;font-size:12px;font-weight:700;color:var(--stamp-text);background:rgba(46,154,140,.1);border:1px solid rgba(46,154,140,.28);border-radius:100px;padding:6px 12px}
.sva .lpb .bfresh .fdot{width:8px;height:8px;border-radius:50%;background:var(--wa);box-shadow:0 0 8px var(--wa)}
.sva .lpb .bfresh .fcad{font-size:12px;color:#8a959d}
.sva .lpb .bfresh .fmsg{font-size:12.5px;color:var(--cta);font-weight:700;text-decoration:none}
.sva .lpb .ngsearch{position:relative;margin:0 0 14px}
.sva .lpb .ngsearch>svg{position:absolute;left:14px;top:50%;transform:translateY(-50%);width:16px;height:16px;fill:#8a99a2;pointer-events:none}
.sva .lpb .ngsearch input{width:100%;font:600 14px "Outfit",system-ui,sans-serif;color:#16222E;padding:12px 40px;border:1px solid var(--edge);border-radius:12px;background:#f7f9fb;outline:none;transition:border-color .15s,box-shadow .15s,background .15s}
.sva .lpb .ngsearch input:focus{border-color:#155E7A;box-shadow:0 0 0 3px rgba(21,94,122,.12);background:#fff}
.sva .lpb .ngsearch .clr{position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:#eef2f6;color:#5d6b76;width:26px;height:26px;border-radius:8px;cursor:pointer;font-size:16px;line-height:1;display:grid;place-items:center}
.sva .lpb .ngsearch .clr[hidden]{display:none}
.sva .lpb .ngsug{position:absolute;z-index:30;left:0;right:0;top:calc(100% + 6px);margin:0;padding:6px;list-style:none;background:#fff;border:1px solid var(--edge);border-radius:12px;box-shadow:0 24px 50px -24px rgba(20,34,46,.5);max-height:264px;overflow:auto}
.sva .lpb .ngsug[hidden]{display:none}
.sva .lpb .ngsug li{display:flex;align-items:center;gap:10px;padding:9px 11px;border-radius:8px;font-size:14px;font-weight:600;color:#16222E;cursor:pointer}
.sva .lpb .ngsug li .fl{width:24px;flex:none;display:flex;align-items:center}
.sva .lpb .ngsug .flimg{width:24px;height:16px;object-fit:cover;border-radius:2px;box-shadow:0 0 0 1px rgba(0,0,0,.1);display:block}
.sva .lpb .ngsug li .zt{margin-left:auto;font-size:10px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:#8a99a2}
.sva .lpb .ngsug li:hover,.sva .lpb .ngsug li.on{background:#eef4f6;color:#155E7A}
.sva .lpb .ngempty{grid-column:1/-1;text-align:center;color:#5d6b76;font-size:13px;padding:20px 8px;line-height:1.5}
.sva .lpb .ngempty b{color:#16222E}.sva .lpb .ngempty a{color:#155E7A;font-weight:800}
.sva .lpb .nghd{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:2px 4px 14px}
.sva .lpb .ngleg{display:flex;gap:14px;flex-wrap:wrap;font-size:11px;color:#5d6b76}
.sva .lpb .ngleg>span{display:inline-flex;align-items:center;gap:6px}
.sva .lpb .nghd .d{width:8px;height:8px;border-radius:50%}
.sva .lpb .nghd .d.open{background:#1F6E63}.sva .lpb .nghd .d.tight{background:#b5791f}.sva .lpb .nghd .d.none{background:#c0392b}
.sva .lpb .ngtick{font-size:12px;font-weight:700;color:#1F6E63;display:inline-flex;align-items:center;gap:7px}
.sva .lpb .ngdot{width:8px;height:8px;border-radius:50%;background:#25D366;box-shadow:0 0 8px #25D366;animation:brpulse 2s ease-in-out infinite}
@keyframes brpulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.35;transform:scale(.65)}}
.sva .lpb .nggrid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.sva .lpb .ngcard{position:relative;overflow:hidden;display:flex;align-items:center;gap:15px;background:var(--soft);border:1px solid var(--edge);border-radius:16px;padding:16px;text-decoration:none;color:#16222E;box-shadow:0 14px 30px -24px rgba(20,34,46,.5);transition:transform .15s,box-shadow .15s}
@media(hover:hover){.sva .lpb .ngcard:hover{transform:translateY(-2px);box-shadow:0 22px 44px -28px rgba(20,45,50,.5)}}
.sva .lpb .ngcard[hidden]{display:none}
.sva .lpb .ngcard::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--c);box-shadow:0 0 14px var(--c)}
.sva .lpb .ngn{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:40px;font-weight:900;line-height:1;letter-spacing:-.05em;color:var(--c);flex:none;font-variant-numeric:tabular-nums}
.sva .lpb .nginfo{flex:1;min-width:0}
.sva .lpb .ngcn{font-weight:800;font-size:16px;color:#16222E}
.sva .lpb .ngnx{font-size:11.5px;color:#5d6b76;margin-top:1px}.sva .lpb .ngnx b{color:#1F6E63}
.sva .lpb .ngstp{display:inline-block;margin-top:6px;font-size:9px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;padding:3px 9px;border-radius:100px;color:var(--c);background:var(--cbg)}
.sva .lpb .ngcta{flex:none;display:inline-flex;align-items:center;gap:6px;background:var(--c);color:#fff;font:800 11.5px "Outfit",system-ui,sans-serif;padding:9px 13px;border-radius:9px;white-space:nowrap;box-shadow:0 8px 18px -12px var(--c)}
.sva .lpb .ngcta svg{width:14px;height:14px;fill:#fff;flex:none}
@media(hover:hover){.sva .lpb .ngcard:hover .ngcta{filter:brightness(.92)}}
.sva .lpb .ngfoot{position:relative;overflow:hidden;display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-top:14px;padding:14px 16px;border-radius:14px;font-size:13px;color:#e6c9c6;background:radial-gradient(600px 170px at 12% 0,rgba(224,52,43,.55),transparent),#0f2028}
.sva .lpb .ngfoot .ngup{font-size:11px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:#fff;background:#c0392b;border-radius:6px;padding:4px 9px;white-space:nowrap}
.sva .lpb .ngfoot .ngmsg{flex:1;min-width:180px}.sva .lpb .ngfoot .ngmsg b{color:#fff}
.sva .lpb .ngfoot .ngwa{margin-left:auto;display:inline-flex;align-items:center;gap:7px;background:#fff;color:#c0392b;font:800 12.5px "Outfit",system-ui,sans-serif;padding:10px 16px;border-radius:10px;white-space:nowrap;text-decoration:none}
.sva .lpb .ngfoot .ngwa svg{width:15px;height:15px;fill:#c0392b;flex:none}
.sva .lpb .brempty{text-align:center;color:#5d6b76;padding:22px}
@media(max-width:560px){
  .sva .lpb .nggrid{grid-template-columns:1fr}
  .sva .lpb .ngcard{flex-wrap:wrap;gap:12px}
  .sva .lpb .ngn{font-size:34px}
  .sva .lpb .ngcta{flex:1 0 100%;justify-content:center;margin-top:2px}
  .sva .lpb .ngfoot .ngwa{flex:1 0 100%;justify-content:center;margin-left:0;margin-top:2px}
}

/* ── HERO · Direction B (light editorial) polish ── */
.sva .hero-spine { position:absolute; left:0; top:0; bottom:0; width:5px;
  background:linear-gradient(180deg, var(--teal-bright), var(--teal)); z-index:2; }
.sva .eyebrow { display:inline-flex; align-items:center; gap:.55rem; }
.sva .eyebrow::before { content:''; width:6px; height:6px; border-radius:50%;
  background:var(--teal-bright); box-shadow:0 0 0 4px rgba(46,154,140,.18); flex:none; }
.sva .hero h1 .hl { color:var(--teal); }
.sva .hero h1 .u { background:linear-gradient(180deg, transparent 62%, rgba(46,154,140,.28) 0); padding:0 2px; }
.sva .proof-item { background:var(--white); border:1px solid var(--border);
  box-shadow:0 2px 8px -4px rgba(15,25,35,.15); border-radius:999px; padding:.5rem .8rem; }
</style>
@endpush

@section('content')
<div class="sva">

<!-- HERO -->
<section class="hero">
  <span class="hero-spine" aria-hidden="true"></span>
  <svg class="hero-landmarks" viewBox="0 0 600 180" fill="var(--navy)" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <!-- Eiffel Tower silhouette -->
    <path d="M80 180h-8l12-70 8-50h-6l-2-15h4l2-25h-2l2-20 2 20h-2l2 25h4l-2 15h-6l8 50 12 70h-8l-4-25h-12z"/>
    <!-- Colosseum silhouette -->
    <path d="M160 180v-55c0-8 4-14 10-18 6-4 14-6 22-6s16 2 22 6c6 4 10 10 10 18v55h-4v-50c0-6-3-10-8-14-5-3-12-5-20-5s-15 2-20 5c-5 4-8 8-8 14v50z M166 140h52v4h-52z M166 155h52v4h-52z M166 170h52v4h-52z"/>
    <!-- Big Ben / Clock Tower -->
    <path d="M290 180h-4v-120h-3v-10h3v-8h-2v-12l4-10 4 10v12h-2v8h3v10h-3v120h-4z M282 60h16v16h-16z"/>
    <!-- Windmill -->
    <path d="M380 180h-8v-80h16v80h-8z M376 100l-30-20 4-6 26 18v-18l-26-18 4-6 30 20-4 6v24z M376 100l30-20-4-6-26 18v-18l26-18-4-6-30 20 4 6v24z"/>
    <!-- Sagrada Familia spires -->
    <path d="M470 180h-3v-100l-4-30 7-30 7 30-4 30v100h-3z M455 180h-3v-85l-3-25 6-25 6 25-3 25v85h-3z M485 180h-3v-85l-3-25 6-25 6 25-3 25v85h-3z"/>
    <!-- Greek Parthenon -->
    <path d="M540 180v-50h4v50h-4z M550 180v-50h4v50h-4z M560 180v-50h4v50h-4z M570 180v-50h4v50h-4z M580 180v-50h4v50h-4z M536 130h52v6h-52z M538 124h48l-4-10h-40z"/>
  </svg>
  <div class="hero-inner">
    <div class="hero-copy">
      <div class="refund-badge">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M8 1l1.8 3.6L14 5.3l-3 2.9.7 4.1L8 10.5 4.3 12.3l.7-4.1-3-2.9 4.2-.7L8 1z" fill="currentColor"/></svg>
        100% refund if refused
      </div>

      <p class="eyebrow">Trusted Schengen Visa Agency, UK-Based Agents</p>

      <h1>Our Schengen Visa Agents Found <span class="hl u">62 Appointments</span> This Week. <span class="hl">Zero</span> Were on Public Pages.</h1>

      <p class="hero-sub">The UK's specialist Schengen visa agents. We track appointment availability across 29 countries daily, prepare thorough applications, and refund our service fee if the consulate refuses. <strong>93% approval rate</strong> across 2,000+ cases.</p>

      <div class="proof-strip">
        <span class="proof-item">
          <span class="proof-check"><svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M13.3 4L6 11.3 2.7 8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
          93% approval rate
        </span>
        <span class="proof-item">
          <span class="proof-check"><svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M13.3 4L6 11.3 2.7 8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
          100% refund if refused
        </span>
        <span class="proof-item">
          <span class="proof-check"><svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M13.3 4L6 11.3 2.7 8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
          Only 15 new cases per month
        </span>
        <span class="proof-item">
          <span class="proof-check"><svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M13.3 4L6 11.3 2.7 8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
          UK-registered visa agents
        </span>
      </div>
    </div>

    <div class="form-card">
      <div class="form-header">
        <span class="form-live-dot"></span>
        <span class="form-header-text">Case check</span>
        <span class="form-header-free">Free</span>
      </div>

      <div class="form-field">
        <label for="hero-name">Your name</label>
        <input type="text" id="hero-name" placeholder="Full name">
      </div>
      <div class="form-field">
        <label for="hero-phone">Phone (WhatsApp)</label>
        <input type="tel" id="hero-phone" placeholder="+44" value="+44 ">
      </div>
      <div class="form-field">
        <label for="hero-country">Where are you going?</label>
        <select id="hero-country">
          <option value="" disabled selected>Select a country</option>
          <option>France</option><option>Netherlands</option><option>Italy</option>
          <option>Spain</option><option>Germany</option><option>Switzerland</option>
          <option>Belgium</option><option>Austria</option><option>Portugal</option>
          <option>Greece</option><option>Poland</option><option>Hungary</option>
          <option>Czech Republic</option><option>Norway</option><option>Sweden</option>
          <option>Denmark</option><option>Finland</option><option>Other Schengen</option>
        </select>
      </div>

      <label class="consent-check"><input type="checkbox" checked> I agree to be contacted about my enquiry. We never share your details. <a href="#" style="color:var(--teal);text-decoration:underline">Privacy</a></label>

      <a class="cta-btn" href="{{ 'https://wa.me/'.config('ukv.whatsapp').'?text='.rawurlencode('Hi Beyond Passports, I would like a free Schengen visa case check.') }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="position:relative;z-index:1"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
        <span style="position:relative;z-index:1">Check my case free →</span>
      </a>

      <div class="click-triggers">
        <span><svg width="12" height="12" viewBox="0 0 16 16" fill="none"><path d="M13.3 4L6 11.3 2.7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> No payment to check</span>
        <span><svg width="12" height="12" viewBox="0 0 16 16" fill="none"><path d="M13.3 4L6 11.3 2.7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> We reply on WhatsApp</span>
        <span><svg width="12" height="12" viewBox="0 0 16 16" fill="none"><path d="M13.3 4L6 11.3 2.7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Takes 30 seconds</span>
      </div>
    </div>
  </div>
</section>

{{-- Default site trust bar (global ukv.css .tbar-f), under the hero --}}
<section class="tbar-f"><div class="wrap"><div class="row">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m9 12 2 2 4-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Schengen visa</b> experts</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v10M9.5 9.2c0-1 1.1-1.7 2.5-1.7s2.5.7 2.5 1.7-1.1 1.6-2.5 1.6-2.5.7-2.5 1.7 1.1 1.7 2.5 1.7 2.5-.7 2.5-1.7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg><span><b>No hidden</b> fees</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>7-day</b> support</span></span>
  <x-reg-verify class="ti">@include('partials.uk-eu-flags',['size'=>15])<span>Registered in <b>England and Wales</b></span></x-reg-verify>
</div></div></section>

<!-- ENGAGE -->
<section class="section section--grey">
  <div class="section-inner">
    <div class="reveal">
      <p class="section-eyebrow">Your situation</p>
      <h2>Your Schengen Visa Agency: Which Path Fits You?</h2>
      <p class="section-sub">Schengen visa agents near you in the UK: London, Manchester, Birmingham, Edinburgh. Pick your situation below.</p>
    </div>

    <div class="path-grid">
      <div class="path-card reveal">
        <div class="pc-stub">
          <span class="pc-ic"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4M8 14l2.5 2.5L15 12"/></svg></span>
          <span class="pc-code">Appointment</span>
        </div>
        <span class="pc-perf" aria-hidden="true"></span>
        <div class="pc-body">
          <p class="path-card-quote">"Every slot is gone"</p>
          <h3>Need a Schengen visa appointment?</h3>
          <p>Our Schengen visa agents monitor appointment slots at official visa centres near you: London, Manchester, Edinburgh, and across the UK. Rechecked every 3 hours. We regularly secure slots that never appear on public pages.</p>
          <a href="#hero-name" class="path-card-cta">Check appointment availability <span>→</span></a>
        </div>
      </div>

      <div class="path-card reveal">
        <div class="pc-stub">
          <span class="pc-ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"/><path d="M9 13l2 2 4-4"/></svg></span>
          <span class="pc-code">Full service</span>
        </div>
        <span class="pc-perf" aria-hidden="true"></span>
        <div class="pc-body">
          <p class="path-card-quote">"Just handle it for me"</p>
          <h3>Looking for a Schengen visa travel agent?</h3>
          <p>We prepare your documents, write your cover letter, book your appointment, and brief you for the embassy visit. A dedicated agency for Schengen visa applications. You attend, we make sure everything is right before you do.</p>
          <a href="#hero-name" class="path-card-cta">Check my case <span>→</span></a>
        </div>
      </div>

      <div class="path-card reveal">
        <div class="pc-stub">
          <span class="pc-ic"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M17 11a3 3 0 1 0-2-5.2"/><path d="M3 20v-1a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v1"/><path d="M17 14a5 5 0 0 1 4 5v1"/></svg></span>
          <span class="pc-code">Group</span>
        </div>
        <span class="pc-perf" aria-hidden="true"></span>
        <div class="pc-body">
          <p class="path-card-quote">"We're applying together"</p>
          <h3>Couple or family application?</h3>
          <p>We prepare every file together so no weak case drags the group down. One fee per person, one coordinator from our Schengen visa agency, one timeline.</p>
          <a href="#hero-name" class="path-card-cta">Check our case <span>→</span></a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- APPOINTMENT TABLE -->
<div class="section-divider"></div>
<section class="section section--white">
  <div class="section-inner">
    <div class="reveal">
      <p class="section-eyebrow">Live availability</p>
      <h2>Schengen Visa Appointments: Found by Our Agents This Week</h2>
      <p class="section-sub">Rechecked every 3 hours from official visa centres across all 29 Schengen countries. Most applicants never see these slots. Our Schengen visa agents in the UK find them.</p>
    </div>

    @php $wa = 'https://wa.me/'.config('ukv.whatsapp'); @endphp
    {{-- Live board — same neon-glass design as /schengen-visa-consultancy, fed by the shared
         $apptCards composer. Scoped under .lpb so the consultancy board CSS applies verbatim. --}}
    <div class="lpb reveal">
      @if(config('ukv.slots.last_checked'))
      <div class="bfresh"><span class="fchip"><span class="fdot"></span>Last checked {{ config('ukv.slots.last_checked') }}</span><span class="fcad">Rechecked every 3 hours</span><a class="fmsg" href="{{ $wa }}?text={{ rawurlencode('Hi Beyond Passports, please send today\'s live Schengen appointment numbers.') }}">Message us for today's numbers →</a></div>
      @endif
      @php
        $apptShown = collect($apptCards ?? []);
        $apptTotal = $apptShown->sum('slots');
        $schengenAll = ['Austria','Belgium','Bulgaria','Croatia','Czechia','Denmark','Estonia','Finland','France','Germany','Greece','Hungary','Iceland','Italy','Latvia','Liechtenstein','Lithuania','Luxembourg','Malta','Netherlands','Norway','Poland','Portugal','Romania','Slovakia','Slovenia','Spain','Sweden','Switzerland'];
        $apptHave = $apptShown->pluck('name')->map(fn ($n) => \Illuminate\Support\Str::lower($n))->all();
        foreach ($schengenAll as $sn) {
          if (!in_array(\Illuminate\Support\Str::lower($sn), $apptHave, true)) {
            $apptShown->push(['name' => $sn, 'cls' => 'none', 'label' => 'Very limited', 'slots' => 0]);
          }
        }
      @endphp
      <div class="ngstage" data-slotboard>
        <div class="nghd">
          <span class="ngleg"><span><i class="d open"></i>Available</span><span><i class="d tight"></i>Limited</span><span><i class="d none"></i>Very limited</span></span>
          @if($apptTotal > 0)<span class="ngtick"><span class="ngdot"></span><span data-slotcount>{{ $apptTotal }} slots open now</span></span>@endif
        </div>
        @if($apptShown->count() > 4)
        <div class="ngsearch">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.7.7l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0A4.5 4.5 0 1 1 14 9.5 4.49 4.49 0 0 1 9.5 14z"/></svg>
          <input data-slotsearch type="text" placeholder="Search a Schengen country…" autocomplete="off" role="combobox" aria-expanded="false" aria-autocomplete="list" aria-label="Search Schengen country">
          <button type="button" class="clr" data-slotclear aria-label="Clear search">&times;</button>
          <ul class="ngsug" data-slotsug role="listbox" hidden></ul>
        </div>
        @endif
        <div class="nggrid">
          @forelse($apptShown as $c)
          @php $rn = (int) ($c['slots'] ?? 0); @endphp
          <a class="ngcard {{ $c['cls'] }}{{ $rn === 0 ? ' zero' : '' }}" data-slotname="{{ \Illuminate\Support\Str::lower($c['name']) }}" data-slotzero="{{ $rn === 0 ? '1' : '0' }}" href="{{ $wa }}?text={{ rawurlencode($rn === 0 ? 'Hi Beyond Passports, I would like a '.$c['name'].' Schengen appointment. There are no live slots showing right now, please put me on the watch and grab the next one.' : 'Hi Beyond Passports, I would like a '.$c['name'].' Schengen appointment. Please check the soonest live slot and secure it for me.') }}" aria-label="{{ $rn === 0 ? 'Ask about a '.$c['name'].' appointment' : 'Secure a '.$c['name'].' appointment slot' }}"@if($rn === 0) hidden @endif>
            <div class="ngn">{{ $rn }}</div>
            <div class="nginfo"><div class="ngcn">{{ $c['name'] }}</div>@if($rn === 0)<div class="ngnx">no live slots <b>right now</b></div><span class="ngstp">Ask us to watch</span>@else<div class="ngnx">slot{{ $rn === 1 ? '' : 's' }} <b>in the next 30 days</b></div><span class="ngstp">{{ $c['label'] }} · {{ $rn }} slot{{ $rn === 1 ? '' : 's' }}</span>@endif</div>
            <span class="ngcta">@include('partials.wa-glyph'){{ $rn === 0 ? 'Find me '.$c['name'].' slots' : 'Secure' }}</span>
          </a>
          @empty
          <p class="brempty" style="grid-column:1/-1">Live availability is confirmed with each centre before you pay. Tell us your dates and we'll check today.</p>
          @endforelse
          <p class="ngempty" data-slotempty hidden>No live slots match “<b data-slotterm></b>”. <a href="{{ $wa }}?text=Hi%2C%20I%27d%20like%20to%20check%20Schengen%20appointment%20availability.%20My%20travel%20dates%20are%3A%20">Message us</a> and we'll check any Schengen country.</p>
        </div>
        <div class="ngfoot"><span class="ngup">⏱ Urgent</span><span class="ngmsg">Travelling within 3 weeks? <b>These slots won't wait.</b></span><a class="ngwa" href="{{ $wa }}?text=Hi%2C%20I%20need%20a%20Schengen%20appointment.%20My%20travel%20dates%20are%3A%20">@include('partials.wa-glyph')Message us now</a></div>
        @php $apptTotalTxt = $apptTotal.' slot'.($apptTotal === 1 ? '' : 's').' open now'; @endphp
        <script>(function(){var b=document.querySelector('[data-slotboard]');if(!b)return;var wrap=b.querySelector('.ngsearch'),q=b.querySelector('[data-slotsearch]'),clr=b.querySelector('[data-slotclear]'),sug=b.querySelector('[data-slotsug]'),cards=b.querySelectorAll('.ngcard'),empty=b.querySelector('[data-slotempty]'),term=b.querySelector('[data-slotterm]'),cnt=b.querySelector('[data-slotcount]');if(!q)return;var base=@json($apptTotalTxt);var SCH=[["Austria","AT"],["Belgium","BE"],["Bulgaria","BG"],["Croatia","HR"],["Czechia","CZ"],["Denmark","DK"],["Estonia","EE"],["Finland","FI"],["France","FR"],["Germany","DE"],["Greece","GR"],["Hungary","HU"],["Iceland","IS"],["Italy","IT"],["Latvia","LV"],["Liechtenstein","LI"],["Lithuania","LT"],["Luxembourg","LU"],["Malta","MT"],["Netherlands","NL"],["Norway","NO"],["Poland","PL"],["Portugal","PT"],["Romania","RO"],["Slovakia","SK"],["Slovenia","SI"],["Spain","ES"],["Sweden","SE"],["Switzerland","CH"]];var LIVE={};cards.forEach(function(c){if(c.getAttribute('data-slotzero')!=='1')LIVE[c.getAttribute('data-slotname')]=1;});function flag(cc){return '<img class="flimg" src="https://flagcdn.com/'+cc.toLowerCase()+'.svg" width="24" height="16" alt="" loading="lazy">';}function f(){var v=q.value.trim().toLowerCase(),active=v.length>0,shown=0,sum=0;cards.forEach(function(c){var hit=!v||c.getAttribute('data-slotname').indexOf(v)>-1;var zero=c.getAttribute('data-slotzero')==='1';var vis=hit&&(active||!zero);c.hidden=!vis;if(vis){shown++;sum+=parseInt((c.querySelector('.ngn')||{}).textContent,10)||0;}});if(clr)clr.hidden=!active;if(empty)empty.hidden=shown>0;if(term)term.textContent=q.value.trim();if(cnt)cnt.textContent=active?(shown+(shown===1?' country':' countries')+' · '+sum+' slot'+(sum===1?'':'s')):base;}function open(s){if(!sug)return;sug.hidden=!s;q.setAttribute('aria-expanded',s?'true':'false');}function renderSug(){if(!sug)return;var v=q.value.trim().toLowerCase();var list=SCH.filter(function(c){return !v||c[0].toLowerCase().indexOf(v)>-1;}).slice(0,8);if(!list.length){open(false);return;}sug.innerHTML=list.map(function(c){var live=LIVE[c[0].toLowerCase()];return '<li role="option" data-c="'+c[0]+'"><span class="fl">'+flag(c[1])+'</span>'+c[0]+(live?'':'<span class="zt">no live slots</span>')+'</li>';}).join('');open(true);}f();q.addEventListener('input',function(){f();renderSug();});q.addEventListener('focus',renderSug);if(sug)sug.addEventListener('mousedown',function(e){var li=e.target.closest('li[data-c]');if(!li)return;e.preventDefault();q.value=li.getAttribute('data-c');open(false);f();});if(clr)clr.addEventListener('click',function(){q.value='';q.focus();f();renderSug();});q.addEventListener('keydown',function(e){if(e.key==='Escape')open(false);});document.addEventListener('click',function(e){if(wrap&&!wrap.contains(e.target))open(false);});})();</script>
      </div>
      @include('partials.disclaimer-strip', ['text' => 'Appointment availability shown here is indicative, not a live booking system. Slot dates are updated daily from public appointment centres and can change at any moment; the exact slot is confirmed with the centre before you pay. Beyond Passports prepares your documents and assists with appointment booking. We do not control or guarantee appointment availability, and every visa decision rests with the relevant authorities.'])
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="section section--grey">
  <div class="section-inner">
    <div class="reveal">
      <p class="section-eyebrow">Client results</p>
      <h2>2,000+ UK Applicants. 93% Approved. Here's What They Say About Our Schengen Visa Agency.</h2>
      <p class="section-sub">Real clients from our community. Every reference is verifiable on request.</p>
    </div>

    <div class="testimonial-grid">
      <div class="testimonial-card reveal"><div class="testimonial-stars">★★★★★</div><p class="testimonial-quote">"Had a refusal last year and was terrified of applying again. They went through what went wrong, fixed it, and my Netherlands visa came through. Straight with me the whole way."</p><div class="testimonial-meta"><div class="testimonial-avatar">PS</div><div><div class="testimonial-name">Priya Sharma · Birmingham</div><div class="testimonial-detail">🇳🇱 Netherlands visa · June 2026 · Ref: BP-2026-100227</div></div></div></div>

      <div class="testimonial-card reveal"><div class="testimonial-stars">★★★★★</div><p class="testimonial-quote">"Could not find a single appointment for France anywhere. These lot found one within 3 days. Didn't believe it until I saw the confirmation email. Proper service."</p><div class="testimonial-meta"><div class="testimonial-avatar">FH</div><div><div class="testimonial-name">Fatima Hussain · Manchester</div><div class="testimonial-detail">🇫🇷 France visa · May 2026 · Ref: BP-2026-100184</div></div></div></div>

      <div class="testimonial-card reveal"><div class="testimonial-stars">★★★★★</div><p class="testimonial-quote">"Handled everything for me and my wife. Two separate applications, both approved first time. The cover letters they wrote were brilliant, really specific to our situation."</p><div class="testimonial-meta"><div class="testimonial-avatar">DO</div><div><div class="testimonial-name">Daniel O'Brien · London</div><div class="testimonial-detail">🇪🇸 Spain visa · April 2026 · Ref: BP-2026-100142</div></div></div></div>

      <div class="testimonial-card reveal"><div class="testimonial-stars">★★★★★</div><p class="testimonial-quote">"First time applying for Schengen, had no idea where to start. They walked me through every document on WhatsApp. Got my Italy visa in 8 working days."</p><div class="testimonial-meta"><div class="testimonial-avatar">MS</div><div><div class="testimonial-name">Maria Santos · Leicester</div><div class="testimonial-detail">🇮🇹 Italy visa · July 2026 · Ref: BP-2026-100291</div></div></div></div>

      <div class="testimonial-card reveal"><div class="testimonial-stars">★★★★★</div><p class="testimonial-quote">"Family of four, all approved. They coordinated everything so we submitted together. The embassy briefing was the best part, knew exactly what to expect."</p><div class="testimonial-meta"><div class="testimonial-avatar">AR</div><div><div class="testimonial-name">Ahmed Rahman · Bradford</div><div class="testimonial-detail">🇧🇪 Belgium visa · June 2026 · Ref: BP-2026-100256</div></div></div></div>

      <div class="testimonial-card reveal"><div class="testimonial-stars">★★★★★</div><p class="testimonial-quote">"Was sceptical because I'd been scammed before by another 'agent'. These guys showed me their ICO registration, sent everything in writing. Completely different experience."</p><div class="testimonial-meta"><div class="testimonial-avatar">SB</div><div><div class="testimonial-name">Sophie Bennett · Edinburgh</div><div class="testimonial-detail">🇳🇱 Netherlands visa · March 2026 · Ref: BP-2026-100098</div></div></div></div>
    </div>
  </div>
</section>

<!-- AGITATE -->
<div class="section-divider"></div>
<section class="section section--white">
  <div class="section-inner">
    <div class="reveal">
      <p class="section-eyebrow">What's at stake</p>
      <h2>What Happens When a Schengen Visa Gets Refused</h2>
      <p class="section-sub">Not "if", when. It happens. The question is whether it happens to you.</p>
    </div>

    <div class="timeline reveal">
      <div class="timeline-step"><div class="timeline-dot"></div><h3>You get refused</h3><p>Your application is rejected. That refusal is logged in <strong>VIS, the shared EU database</strong>.</p></div>
      <div class="timeline-step"><div class="timeline-dot"></div><h3>29 countries see it</h3><p>Not just the country you applied to. Every Schengen country, <strong>all 29</strong>, can see your refusal on file.</p></div>
      <div class="timeline-step"><div class="timeline-dot"></div><h3>It stays for 5 years</h3><p>Not 1 year. Not 2. <strong>Five years</strong> on a shared record that follows every future application you make.</p></div>
      <div class="timeline-step"><div class="timeline-dot"></div><h3>You start at minus one</h3><p>Your next application doesn't start at zero. <strong>The burden of proof flips.</strong> You must prove you're not a risk.</p></div>
    </div>

    <div class="bridge-box reveal">
      <p>Most refusals we review describe something <strong>preventable</strong>.</p>
      <p>Wrong bank statements. Missing employer letters. Itineraries that didn't add up.</p>
      <p>Each refusal costs you <strong>£180+ in embassy fees</strong>, months of waiting, and a 5-year mark on your record. That's £500+ in real cost, for a mistake a 30-minute document review catches every time.</p>
      <p><strong>Our Schengen visa agents check everything before you pay a penny to the embassy.</strong></p>
    </div>

    <div style="margin-top: 1.75rem;">
      <a href="#hero-name" class="cta-btn inline-cta">Check my documents free →</a>
    </div>
  </div>
</section>

<!-- PROCESS -->
<section class="section section--grey">
  <div class="section-inner">
    <div class="reveal">
      <p class="section-eyebrow">How our Schengen visa agency works</p>
      <h2>Four Steps. No Surprises. No Payment Until You're Ready.</h2>
      <p class="section-sub">Same process every time. No account needed. No booking system. No login. We help our community navigate the Schengen visa process, properly.</p>
    </div>

    <div class="process-grid">
      <div class="process-card reveal"><div class="process-number">01</div><h3>You message us</h3><p>WhatsApp or phone. Your own words. Tell us where you're going and when. That's all we need to start.</p></div>
      <div class="process-card reveal"><div class="process-number">02</div><h3>We check your case</h3><p>Within 30 minutes. Free. We confirm whether we can actually help. If we can't, we tell you honestly.</p></div>
      <div class="process-card reveal"><div class="process-number">03</div><h3>We prepare everything</h3><p>Documents. Cover letter. Appointment booking. Full embassy briefing. Every detail checked before submission.</p></div>
      <div class="process-card reveal"><div class="process-number">04</div><h3>You attend with confidence</h3><p>Every document right. Every question anticipated. You walk into the appointment knowing it's sorted.</p></div>
    </div>

    <div style="margin-top: 2rem; text-align: center;">
      <a href="{{ 'https://wa.me/'.config('ukv.whatsapp').'?text='.rawurlencode('Hi Beyond Passports, I would like a free Schengen visa case check.') }}" class="cta-btn" style="max-width: 300px; margin: 0 auto;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="position:relative;z-index:1"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
        <span style="position:relative;z-index:1">Start step 1 now →</span>
      </a>
    </div>
  </div>
</section>

<!-- GUARANTEE -->
<div class="section-divider"></div>
<section class="section section--white">
  <div class="section-inner">
    <div class="guarantee-box reveal">
      <div class="guarantee-icon">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#155E7A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
      </div>
      <h2>Refused? Your Service Fee Back. That's Our Schengen Visa Agency Promise.</h2>
      <p>Refused? <strong>100% of your service fee back.</strong></p>
      <ul class="guarantee-checks">
        <li><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4L6 11.3 2.7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Refunded to your original payment method</li>
        <li><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4L6 11.3 2.7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Clear, written terms before you pay</li>
        <li><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4L6 11.3 2.7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> No questions, no hoops, no delays</li>
      </ul>
      <p class="guarantee-honesty">We don't guarantee the embassy's decision. Nobody honestly can. We guarantee our work. If our preparation fails you, we refund our service fee. Check your case free today. You only pay when you're ready to proceed.</p>
      <div style="margin-top: 1.5rem;">
        <a href="{{ 'https://wa.me/'.config('ukv.whatsapp').'?text='.rawurlencode('Hi Beyond Passports, I would like a free Schengen visa case check.') }}" class="cta-btn" style="max-width: 260px; margin: 0 auto;">Check my case now →</a>
      </div>
    </div>
  </div>
</section>

<!-- TEAM -->
<section class="section section--grey">
  <div class="section-inner">
    <div class="reveal">
      <p class="section-eyebrow">Your Schengen visa agents</p>
      <h2>The People On Your Case</h2>
      <p class="section-sub">Named Schengen visa agents in the UK. Not a call centre. Not an offshore operation.</p>
    </div>

    <div class="team-grid">
      <div class="team-card reveal"><div class="team-avatar">S</div><h3>Sarah</h3><p class="team-role">Lead Visa Consultant</p><p class="team-quote">"I review every refusal letter personally. Most describe something a 30-minute check would have caught."</p></div>
      <div class="team-card reveal"><div class="team-avatar">J</div><h3>James</h3><p class="team-role">Refusal-Recovery Specialist</p><p class="team-quote">"The refusal letter never tells you the real reason. I find what actually triggered it."</p></div>
      <div class="team-card reveal"><div class="team-avatar">C</div><h3>Chloe</h3><p class="team-role">Appointments & Client Coordinator</p><p class="team-quote">"I monitor appointment systems daily. Most slots I find never appear on public booking pages."</p></div>
    </div>
  </div>
</section>

<!-- CLOSE -->
<section class="section section--navy close-section" id="close-cta">
  <div class="section-inner">
    <div class="reveal">
      <p class="section-eyebrow">Get started with your Schengen visa agents</p>
      <h2>Your Schengen Visa Starts With a 30-Second Message</h2>
      <p class="section-sub">Tell us where you're going. Your dedicated Schengen visa agent replies in 30 minutes. Honestly.</p>
    </div>

    <div class="close-form reveal">
      <div class="close-form-row">
        <input type="text" placeholder="Your name">
        <input type="tel" placeholder="+44" value="+44 ">
      </div>
      <select><option value="" disabled selected>Where are you going?</option><option>France</option><option>Netherlands</option><option>Italy</option><option>Spain</option><option>Germany</option><option>Switzerland</option><option>Belgium</option><option>Austria</option><option>Portugal</option><option>Greece</option><option>Poland</option><option>Hungary</option><option>Czech Republic</option><option>Norway</option><option>Sweden</option><option>Denmark</option><option>Finland</option><option>Other Schengen</option></select>
      <label class="consent-check consent-check--dark"><input type="checkbox" checked> I agree to be contacted about my enquiry. <a href="#" style="color:var(--cta);text-decoration:underline">Privacy</a></label>
      <a class="cta-btn" href="{{ 'https://wa.me/'.config('ukv.whatsapp').'?text='.rawurlencode('Hi Beyond Passports, I would like a free Schengen visa case check.') }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="position:relative;z-index:1"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
        <span style="position:relative;z-index:1">Check my case free →</span>
      </a>
    </div>

    <div class="close-trust reveal">
      <span><svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M13.3 4L6 11.3 2.7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> No payment needed</span>
      <span><svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M13.3 4L6 11.3 2.7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> 93% approval rate</span>
      <span><svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M13.3 4L6 11.3 2.7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> 100% refund if refused</span>
      <span><svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M13.3 4L6 11.3 2.7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> UK-registered Schengen visa agents</span>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="section section--white">
  <div class="section-inner">
    <div class="reveal">
      <p class="section-eyebrow">Common questions</p>
      <h2>Schengen Visa Agents UK: Your Questions, Straight Answers</h2>
      <p class="section-sub">What UK applicants ask our agency for Schengen visa help every day. No jargon, no runaround.</p>
    </div>

    <div class="faq-list reveal">
      <div class="faq-item"><button class="faq-q" onclick="this.parentElement.classList.toggle('open')">What does a Schengen visa agent do?<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 3.75v10.5M3.75 9h10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button><div class="faq-a"><div class="faq-a-inner">A Schengen visa agent prepares your complete application: documents, cover letter, appointment booking, and a briefing for the embassy visit. You still attend the appointment in person. The agent's job is making sure every document is right before you do. Anyone claiming they can attend on your behalf or guarantee approval is misleading you.</div></div></div>

      <div class="faq-item"><button class="faq-q" onclick="this.parentElement.classList.toggle('open')">How much does a Schengen visa agency in the UK charge?<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 3.75v10.5M3.75 9h10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button><div class="faq-a"><div class="faq-a-inner">Our service starts from £149 per person. This includes document preparation, cover letter, appointment booking, and our 100% refund promise if your visa is refused. No hidden fees. You see the full price before you pay anything.</div></div></div>

      <div class="faq-item"><button class="faq-q" onclick="this.parentElement.classList.toggle('open')">Can you get me a Schengen visa appointment when there are no slots?<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 3.75v10.5M3.75 9h10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button><div class="faq-a"><div class="faq-a-inner">We monitor appointment availability at official visa centres across all 29 Schengen countries, rechecked every 3 hours. We regularly secure slots that don't appear on public booking pages. Check current availability in the appointment table above.</div></div></div>

      <div class="faq-item"><button class="faq-q" onclick="this.parentElement.classList.toggle('open')">I was refused before. Can you help?<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 3.75v10.5M3.75 9h10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button><div class="faq-a"><div class="faq-a-inner">Yes. Refusal recovery is one of our core services. We review your refusal letter, identify what actually triggered it (the letter rarely tells you the real reason), and rebuild your case. If recovery isn't realistic, we tell you honestly rather than take your money.</div></div></div>

      <div class="faq-item"><button class="faq-q" onclick="this.parentElement.classList.toggle('open')">Are you a real, registered UK company?<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 3.75v10.5M3.75 9h10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button><div class="faq-a"><div class="faq-a-inner">Beyond Passports is registered in England and Wales. ICO registered: ZC197159. UK phone number: +44 7882 747584. Named team members who respond personally. We're not a WhatsApp-only operation running from abroad. Check our registration yourself.</div></div></div>

      <div class="faq-item"><button class="faq-q" onclick="this.parentElement.classList.toggle('open')">Do I still need to go to the embassy myself?<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 3.75v10.5M3.75 9h10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button><div class="faq-a"><div class="faq-a-inner">Yes. No agent in the UK can attend a visa appointment on your behalf. If someone tells you otherwise, they are either lying or operating illegally. Our job is to prepare everything so your visit is smooth and your documents are correct.</div></div></div>

      <div class="faq-item"><button class="faq-q" onclick="this.parentElement.classList.toggle('open')">Is there a Schengen visa agent near me?<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 3.75v10.5M3.75 9h10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button><div class="faq-a"><div class="faq-a-inner">Beyond Passports is a Schengen visa agency near you wherever you are in the UK: London, Manchester, Birmingham, Edinburgh, Bradford, Leicester, and everywhere in between. Everything is handled over WhatsApp and phone, so you get dedicated Schengen visa agents in the UK working on your case regardless of location. No office visit needed.</div></div></div>

      <div class="faq-item"><button class="faq-q" onclick="this.parentElement.classList.toggle('open')">What is the difference between a Schengen visa agent and a travel agent?<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 3.75v10.5M3.75 9h10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button><div class="faq-a"><div class="faq-a-inner">A Schengen visa travel agent typically only books flights and hotels. A specialist Schengen visa agent like Beyond Passports handles the full visa application: document preparation, cover letter, appointment booking, and embassy briefing. Some travel agents offer visa assistance as an add-on, but it's rarely their specialism. We do nothing else. Visas are all we do.</div></div></div>

      <div class="faq-item"><button class="faq-q" onclick="this.parentElement.classList.toggle('open')">How do I find a trustworthy agency for Schengen visa in the UK?<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 3.75v10.5M3.75 9h10.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button><div class="faq-a"><div class="faq-a-inner">Look for three things: UK company registration (check Companies House), ICO registration for data protection, and a clear refund policy in writing. Avoid any agency for Schengen visa that guarantees approval. No honest agent can promise that. Beyond Passports is registered in England & Wales, ICO registered (ZC197159), and offers a written 100% service fee refund if your visa is refused.</div></div></div>
    </div>
  </div>
</section>

<!-- FAQ SCHEMA -->
@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {"@type": "Question", "name": "What does a Schengen visa agent do?", "acceptedAnswer": {"@type": "Answer", "text": "A Schengen visa agent prepares your complete application: documents, cover letter, appointment booking, and a briefing for the embassy visit. You still attend the appointment in person. The agent's job is making sure every document is right before you do."}},
    {"@type": "Question", "name": "How much does a Schengen visa agency in the UK charge?", "acceptedAnswer": {"@type": "Answer", "text": "Our service starts from £149 per person. This includes document preparation, cover letter, appointment booking, and our 100% refund promise if your visa is refused. No hidden fees."}},
    {"@type": "Question", "name": "Can you get me a Schengen visa appointment when there are no slots?", "acceptedAnswer": {"@type": "Answer", "text": "We monitor appointment availability at official visa centres across all 29 Schengen countries, rechecked every 3 hours. We regularly secure slots that don't appear on public booking pages."}},
    {"@type": "Question", "name": "I was refused before. Can you help?", "acceptedAnswer": {"@type": "Answer", "text": "Yes. Refusal recovery is one of our core services. We review your refusal letter, identify what actually triggered it, and rebuild your case. If recovery isn't realistic, we tell you honestly rather than take your money."}},
    {"@type": "Question", "name": "Are you a real, registered UK company?", "acceptedAnswer": {"@type": "Answer", "text": "Beyond Passports is registered in England and Wales. ICO registered: ZC197159. UK phone number: +44 7882 747584. Named team members who respond personally."}},
    {"@type": "Question", "name": "Do I still need to go to the embassy myself?", "acceptedAnswer": {"@type": "Answer", "text": "Yes. No agent in the UK can attend a visa appointment on your behalf. If someone tells you otherwise, they are either lying or operating illegally. Our job is to prepare everything so your visit is smooth and your documents are correct."}},
    {"@type": "Question", "name": "Is there a Schengen visa agent near me?", "acceptedAnswer": {"@type": "Answer", "text": "Beyond Passports is a Schengen visa agency near you wherever you are in the UK: London, Manchester, Birmingham, Edinburgh, Bradford, Leicester, and everywhere in between. Everything is handled over WhatsApp and phone, so you get dedicated Schengen visa agents in the UK working on your case regardless of location."}},
    {"@type": "Question", "name": "What is the difference between a Schengen visa agent and a travel agent?", "acceptedAnswer": {"@type": "Answer", "text": "A Schengen visa travel agent typically only books flights and hotels. A specialist Schengen visa agent like Beyond Passports handles the full visa application: document preparation, cover letter, appointment booking, and embassy briefing."}},
    {"@type": "Question", "name": "How do I find a trustworthy agency for Schengen visa in the UK?", "acceptedAnswer": {"@type": "Answer", "text": "Look for three things: UK company registration, ICO registration for data protection, and a clear refund policy in writing. Avoid any agency for Schengen visa that guarantees approval. No honest agent can promise that."}}
  ]
}
</script>
@endverbatim

<!-- SCROLL REVEAL -->
<script>
(function(){
  var els = document.querySelectorAll('.sva .reveal');
  if (!window.IntersectionObserver) {
    els.forEach(function(el) { el.classList.add('visible'); });
    return;
  }
  var obs = new IntersectionObserver(function(entries) {
    entries.forEach(function(e) {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        obs.unobserve(e.target);
      }
    });
  }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
  els.forEach(function(el, i) {
    el.style.transitionDelay = (i % 3) * 0.08 + 's';
    obs.observe(el);
  });
})();
</script>

</div>
@endsection
