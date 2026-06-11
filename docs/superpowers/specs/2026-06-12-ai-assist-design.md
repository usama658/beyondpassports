# AI Assist (P15) — Design Spec

**Date:** 2026-06-12 · **Parent:** Smart Stories (`2026-06-12-smart-stories-design.md`) · **Depends on:** ukv-barriers.php, ukv-stories-content.php (`ukv_story_has_leak`), ukv-orders.php (journey).

## Goal
A thin, optional Claude layer that polishes copy and proposes next-best-actions. Advisory only; every caller falls back to existing rules templates when no key. Never auto-publishes; AI output for public content is re-checked by the leak gate.

## Architecture
- **One gateway:** `ukv_ai( string $system, string $user, array $opts = [] ): ?string`.
  - Reads key from option `ukv_anthropic_key`. No key → return `null`.
  - POST `https://api.anthropic.com/v1/messages` via `wp_remote_post`, headers `x-api-key`, `anthropic-version: 2023-06-01`. Model default `claude-haiku-4-5`; `max_tokens` default 400 (override via `$opts`). Timeout 30s.
  - Parse `content[0].text`; on any error/non-200 → return `null` (never throw).
- **On-demand only** — called from admin buttons, never bulk/cron. Keeps cost to pennies.

## Three use-cases
1. `ukv_ai_polish_guidance( int $barrier_id ): ?string` — rewrite barrier `guidance` into plain, on-brand client-facing prose. Caller keeps the original if `null`.
2. `ukv_ai_polish_content( string $anonymised_text, string $type ): ?string` — improve a story/testimonial draft. **Input must already be anonymised** (post-redaction). After AI returns, caller MUST re-run `ukv_story_has_leak()` on the result; any finding → discard AI output, keep the rules draft.
3. `ukv_ai_next_best_action( int $order_id ): ?string` — read the order journey + a digest of similar past cases (same destination/tier + outcome) → a short recommendation string shown on the order. Advisory.

## Safety / compliance
- AI never receives PII: callers pass already-redacted text (use-case 2) or non-PII summaries (use-case 3 sends destination/tier/status/journey-note text — staff are told not to put client PII in journey notes; still passed through `ukv_redact_pii` before sending).
- AI output is advisory; a human approves before any send/publish.
- Brand/honesty constraints in the system prompt: independent service, not a government website; express speeds handling not the government decision; no guarantees of approval.

## Build phases
1. `ukv_ai` gateway + key option + admin settings field. Free to build; needs key to run.
2. The three use-case functions + admin buttons (barrier polish, content polish, next-best-action on the order).
3. Tests (mocked): gateway returns `null` without key; callers fall back; leak gate still guards use-case 2.

## Acceptance
- Without a key, every function returns `null` and callers use rules templates (no fatal, no broken UI).
- Use-case 2 output is re-scanned by `ukv_story_has_leak`; a planted leak in mocked AI output is rejected.
- With a key, buttons return polished text; nothing publishes without human approval.
