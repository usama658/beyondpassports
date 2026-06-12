---
name: ukv-parallel-build
description: >-
  Build or convert features and page sections for the UK Visa WordPress site (the local XAMPP build at
  C:\xampp\htdocs\ukvisa, mirrored to the repo's wordpress/ dir). Use this skill WHENEVER the user asks to
  build, add, convert, or restyle anything on the UKV site — a mu-plugin, an Elementor/kit section, a content
  batch, an admin screen, a checker/funnel tweak — even if they don't name the skill. It defaults to PARALLEL
  SUBAGENTS for speed and carries the project's data model + kit patterns + conventions so every build stays
  consistent and accurate. Prefer this skill's subagent-first method over building sequentially yourself.
---

# UKV Parallel Build

The UK Visa site is a plugin-first WordPress build: one Pods `destination` CPT drives money pages; all custom
logic lives in **mu-plugins** (`C:\xampp\htdocs\ukvisa\wp-content\mu-plugins\`), mirrored to the repo at
`<repo>\wordpress\mu-plugins\`. Work is verified by `automation/test-*.php` scripts run through wp-cli. We move
fast by fanning out **independent units to parallel subagents**, then verifying and committing centrally.

**Why subagents first:** most build requests are several independent files (plugins, sections, content batches)
that don't share state. Building them sequentially wastes wall-clock; fanning out gets a feature done in one
pass. Use subagents *before* reaching for a sequential approach — unless the work is a single small edit.

## The data is the point

Subagents make consistent, correct design decisions only when handed accurate project facts. Before dispatching,
make sure each subagent's prompt either inlines or points at the right reference. Read these as needed:

- `references/data-model.md` — CPTs, **post-meta keys** (the `ukv_` vs bare gotcha), options, status vocabulary.
- `references/kit-patterns.md` — active kit #156 global colours/typography, the Jeg-Kit/core widgets, the
  kit-native section recipe (Elementor JSON + global refs + `shortcode` widget for live data).
- `references/conventions.md` — test-first, wp-cli invocation, mirroring, draft-only/leak-gate, key-gating,
  token safety, responsiveness.
- `references/shortcodes.md` — the `[ukv_*]` shortcodes available to embed live data.

## Workflow

### 1. Decompose into independent units
Split the request so **files never collide**: one mu-plugin OR one Elementor section template OR one content
batch per unit. If units share a dependency (e.g. a new helper others call), build the dependency FIRST
yourself, verify it, then fan out the dependents against its now-known interface. (This is why the barrier
register was built before the client-updates/content/consent units that consumed it.)

### 2. Fan out parallel subagents (test-first)
Dispatch one subagent per unit, in a single turn, each with a self-contained prompt. Use the template in
`references/subagent-prompt-template.md`. Each subagent must:
- Build the mu-plugin / section / batch following the conventions.
- Write `automation/test-<unit>.php` and run it:
  `cd /c/xampp/htdocs/ukvisa && /c/xampp/php/php.exe -d memory_limit=512M wp-cli.phar eval-file "<ABS path>"`
- Mirror the finished file(s) to the repo (`wordpress/mu-plugins/` and/or `automation/`).
- Iterate until its test prints `RESULT: ALL PASS`. **Report — do NOT git commit.**

### 3. Parent verifies + commits per unit
Don't trust the reports. For each returned unit: re-run `php -l` and the test yourself; on green, mirror if
needed and commit that unit with a clear `feat(...)`/`fix(...)` message. If a unit fails, read the actual code
and fix it yourself (parallel agents editing the same file collide — fixes are a single-writer job).

### 4. Regression
After a batch, run the affected `automation/test-*.php` (or the full suite) to confirm new plugins loading
didn't break anything. A blank result usually means the test file lives in the repo `automation/` dir, not
htdocs — re-run with the repo path. A transient `Out of memory`/`VirtualAlloc` is CLI memory pressure: rerun
with `-d memory_limit=512M`.

## Guardrails (load `references/conventions.md` for the full list)
- **Meta keys:** order meta is `ukv_`-prefixed; barrier meta is bare; `ukv_destination` is a DISPLAY name —
  slugify with `ukv_dest_slug()` before matching. Getting this wrong fails silently (it has bitten us twice).
- **Privacy:** content/testimonial engines are **draft-only** and abort on `ukv_story_has_leak`; redact staff
  free-text before any public/AI/email sink.
- **Token safety in tests:** a LIVE `ukv_hubspot_token` may sit in the DB. Tests MUST blank+restore it (and
  `ukv_anthropic_key`) so no real CRM/API call fires.
- **Responsiveness:** any customer-facing design must work at desktop/tablet/mobile; kit-native sections inherit
  the kit's responsive system — prefer them over bespoke inline-CSS for new visual surfaces.

## When NOT to fan out
A single small edit, a one-file fix, or a privacy/security-sensitive change (redaction, auth) — do those
yourself. Fan-out is for several genuinely independent units.
