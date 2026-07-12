{{--
    Lead-checklist BAND — a compact conversion strip promoting the free /document-checklist tool.
    Value-first lead magnet: drop it on TOFU/MOFU pages to capture intent mid-scroll.

    PURE PRESENTATIONAL. Self-contained, scoped CSS (literal brand colours, self-hosted Outfit) so it
    is safe on any surface — pages that load ukv.css and standalone documents alike.

    Params (all optional, cb-prefixed to avoid clashing with parent-scope vars):
      $cbTitle        headline (default generic; pass a per-country line on destination pages)
      $cbSub          supporting line
      $cbCta          button label
      $cbDestination  country NAME (e.g. "Germany") — deep-links the tool with it preselected and
                      personalises the copy. Omit for the generic band.

    Include with:
      @@include('partials.checklist-band')
      @@include('partials.checklist-band', ['cbDestination' => $destination->name])
--}}
@php
  $cbDestination = ($cbDestination ?? null) ?: null;
  $cbTitle = ($cbTitle ?? null) ?: ($cbDestination
      ? "Know exactly what {$cbDestination} needs — before you apply"
      : 'Get your free personalised document checklist');
  $cbSub = ($cbSub ?? null) ?: 'Answer a few questions about your trip and situation. We build the list around you — not a generic one — in under two minutes. Free, no account.';
  $cbCta = ($cbCta ?? null) ?: 'Build my checklist';
  // Compact = a slim single-row inline strip for mid-content scroll-capture (drops the eyebrow,
  // supporting line and reassurance note; keeps the title + button).
  $cbCompact = ($cbCompact ?? false) ? true : false;
  // Deep-link the tool with the destination preselected (matched on country name in the tool form).
  $cbUrl = $cbDestination
      ? route('checklist.tool').'?destination='.rawurlencode($cbDestination)
      : route('checklist.tool');
@endphp

<div class="cl-band{{ $cbCompact ? ' cl-band--compact' : '' }}" role="region" aria-label="Free document checklist">
  <style>
    /* checklist-band — scoped, literal colours (ink #16222E / petrol #155E7A / teal #1F6E63). */
    .cl-band{font-family:"Outfit",system-ui,sans-serif;color:#16222E;background:linear-gradient(135deg,#F4F6FA,#eaf1f4);
      border:1px solid #dde3ec;border-left:4px solid #155E7A;border-radius:16px;padding:24px 28px;
      display:flex;gap:24px;align-items:center;justify-content:space-between;flex-wrap:wrap;
      box-shadow:0 16px 40px -32px rgba(40,50,70,.5)}
    .cl-band .cl-copy{flex:1 1 320px;min-width:0}
    .cl-band .cl-eyebrow{font-weight:700;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#155E7A;margin:0 0 6px}
    .cl-band .cl-title{font-size:clamp(20px,2.4vw,26px);font-weight:700;letter-spacing:-.02em;line-height:1.15;margin:0 0 6px;color:#16222E}
    .cl-band .cl-sub{font-size:14.5px;color:#697079;margin:0;line-height:1.55;max-width:60ch}
    .cl-band .cl-cta{flex:0 0 auto}
    .cl-band .cl-btn{display:inline-flex;align-items:center;gap:9px;background:#155E7A;color:#fff;
      font-family:"Outfit",system-ui,sans-serif;font-weight:600;font-size:16px;text-decoration:none;
      padding:13px 24px;border-radius:11px;transition:background .18s ease,transform .18s ease,box-shadow .18s ease}
    .cl-band .cl-btn:hover{background:#124f66;transform:translateY(-2px);box-shadow:0 14px 28px -14px rgba(21,94,122,.8)}
    .cl-band .cl-btn:focus-visible{outline:2px solid #1F6E63;outline-offset:3px}
    .cl-band .cl-btn svg{width:18px;height:18px;flex:none}
    .cl-band .cl-free{display:block;font-size:12px;color:#697079;margin-top:8px;text-align:center}
    /* Compact: slim inline strip. Hide the eyebrow/subline/reassurance, tighten padding + title. */
    .cl-band--compact{padding:16px 20px;gap:16px;border-radius:12px}
    .cl-band--compact .cl-eyebrow,.cl-band--compact .cl-sub,.cl-band--compact .cl-free{display:none}
    .cl-band--compact .cl-title{font-size:clamp(17px,2vw,20px);margin:0}
    .cl-band--compact .cl-btn{padding:11px 20px;font-size:15px}
    @media (max-width:640px){.cl-band{padding:20px}.cl-band .cl-cta{flex:1 1 100%}.cl-band .cl-btn{width:100%;justify-content:center}}
  </style>

  <div class="cl-copy">
    <p class="cl-eyebrow">Free tool</p>
    <h2 class="cl-title">{{ $cbTitle }}</h2>
    <p class="cl-sub">{{ $cbSub }}</p>
  </div>
  <div class="cl-cta">
    <a class="cl-btn" href="{{ $cbUrl }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3 8-8"/><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9"/></svg>
      {{ $cbCta }}
    </a>
    <span class="cl-free">Takes ~2 minutes · No account needed</span>
  </div>
</div>
