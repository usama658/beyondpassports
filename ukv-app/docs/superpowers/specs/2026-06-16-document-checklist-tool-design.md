# Document-Checklist Tool (multi-channel) — design spec

**Goal:** a public self-serve tool where anyone checks exactly which documents they need for their trip, gets the tailored list LIVE on-screen for free, then optionally has it delivered + keeps it. Top-of-funnel SEO entry + lead magnet feeding /apply. Extends #235.

**Stance (locked):** value-first — show the full tailored checklist on-screen with NO contact required, THEN offer delivery + a saved copy. Maximises reach + trust for a YMYL audience; captures motivated leads only.

**Channels (locked):** on-screen + Email + WhatsApp (opt-in) + instant PDF + shareable saved link + .ics calendar reminder. (Wallet pass / SMS deferred.)

## Approach (chosen)
Server-rendered progressive wizard (works no-JS) → on completion persist a `checklist_request` record (token + inputs + computed snapshot) → render the result at `/checklist/{token}` (= the shareable link) → dispatch chosen delivery via queued jobs. Reuses RequirementService, EmailService, WhatsAppService (#25). Rejected: pure client-side SPA (no shareable/PDF/lead foundation); extending /tools checker (doesn't cleanly yield link/PDF/calendar/lead pipeline).

## Components

### Data
- `checklist_requests` table: `token` (uuid, public), `destination_id`, `inputs` (json: trip_purpose, is_minor, residency_status, employment_status, accommodation_type, funding_source, travel_date, return_date, visa_entries, prior_refusal), `items` (json snapshot of computed checklist), `email` (nullable), `phone` (nullable), `channels` (json: which delivery requested), `marketing_consent` (bool, default false), `created_at`, `ip`.
- GDPR: holds contact + travel intent → retention policy, auto-purge after N days (reuse the document-retention purge pattern). Transactional send (the checklist they asked for) needs no marketing consent; nurture/marketing does → separate `marketing_consent` checkbox.

### Service
- `ChecklistService::build(Destination, array $inputs): array` — wraps `RequirementService::preview($destination, $ctx)`; returns the engine item shape. Snapshot stored on the request so the saved link + PDF + email are stable even if rules later change.

### URLs / silo
- `/document-checklist` — the tool (under /tools); indexed; SEO entry + lead magnet.
- `/checklist/{token}` — saved result (shareable; `noindex` — per-user/thin).
- Entry points: home, /tools, every /visa/{country} hub ("Check your documents →"), guides. Tool CTAs route to /apply?destination={slug} + the money page.

### Delivery (queued jobs, all guarded/idempotent)
- Email: new `ChecklistMail` via EmailService — renders the email doc-checklist partial + the saved link + apply CTA.
- WhatsApp: WhatsAppService (#25) — opt-in only; Meta-approved template; sends a summary + saved link (full list lives at the link to fit template limits).
- PDF: branded PDF of the snapshot. **Open decision:** add a PDF lib (dompdf) vs a print-optimised result page + browser "Save as PDF". Default: dompdf if acceptable, else print-CSS fallback.
- Shareable link: the `/checklist/{token}` page itself (no send needed).
- Calendar: generate an `.ics` (no lib) — events: "Start your {destination} application by {date}" (computed: travel_date − processing_time − buffer) + "Check passport validity". Attach to email / offer download.

### Lead capture
- On any contact submission: store on the request + optionally sync to HubSpot (HubSpotService) as a lead/contact, tagged source=document-checklist, with `marketing_consent`. Nurture sequence (#23) only fires when marketing_consent = true.

### Compliance
- Tool + result carry the standard strip: independent service / not a government site / service fee separate / no approval guarantee.
- Doc list links official sources (ties to guide-engine Module A) so users self-verify.
- WhatsApp/email opt-in wording explicit; marketing consent separate from transactional delivery.

## Build waves
1. Core: migration + ChecklistRequest model + ChecklistService + wizard form + result page + on-screen render. Reuse RequirementService + doc-checklist partial.
2. Delivery: Email + WhatsApp + .ics + PDF jobs + the "send me this" UI + saved-link.
3. Lead + compliance: lead store + HubSpot sync + consent split + retention purge + nav/silo entry points + sitemap (tool in, token out).

## Testing
- Wizard completes no-JS → result page renders correct items for the case.
- Snapshot is stable (rule change after creation doesn't alter a saved request).
- `/checklist/{token}` renders; unknown token 404; token page noindex.
- Email send queues once + carries the list + link; WhatsApp guarded no-op without opt-in/creds; .ics validates; PDF (or print page) renders.
- Lead stored; HubSpot sync guarded; nurture only with marketing_consent.
- Retention purge removes old requests.

## Open decisions for owner
- PDF: dompdf dependency vs print-CSS only.
- WhatsApp template copy (needs Meta approval before live).
- Lead retention window (days) for checklist_requests.
