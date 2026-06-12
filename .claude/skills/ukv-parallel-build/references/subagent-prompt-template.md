# Subagent prompt template (one per independent unit)

Fill the brackets and dispatch one `general-purpose` subagent per unit, all in a single turn. Keep each unit's
files non-overlapping. The subagent has zero project context — everything it needs must be in the prompt.

```
Build ONE [WordPress mu-plugin | Elementor kit section | content batch] for the UK Visa site: [unit name].
Zero prior context — all below. Work TEST-FIRST. Do NOT git commit (parent will).

## Environment
- WP `C:\xampp\htdocs\ukvisa`; mu-plugins `...\wp-content\mu-plugins\`. Mirror finished file(s) to repo
  `<repo>\wordpress\mu-plugins\`; tests to `<repo>\automation\` (and run from `C:\xampp\htdocs\ukvisa\automation\`).
- PHP `C:\xampp\php\php.exe`; lint `-l`. wp-cli via Bash (cwd resets each call), ALWAYS `-d memory_limit=512M`:
  `cd /c/xampp/htdocs/ukvisa && /c/xampp/php/php.exe -d memory_limit=512M wp-cli.phar eval-file "<ABS>" 2>&1 | tail -30`
- Conventions: `<?php` + `/** Plugin Name */` + `defined('ABSPATH')||exit;`; arrow closures; esc_/sanitize_;
  nonce + `current_user_can` on every state-changing handler; tabs.

## Data model (use EXACT keys)
[Paste the relevant slice of references/data-model.md — order meta is ukv_-prefixed, barrier meta bare,
ukv_destination is a display name (slugify with ukv_dest_slug), status vocab, the helper functions you may reuse.]

## Reuse (loaded — do NOT reimplement)
[List the exact helper signatures this unit calls, e.g. ukv_create_order, ukv_redact_pii, ukv_story_has_leak, ...]

## Build: file `[ukv-<unit>.php | build-<unit>.php]`
[Precise spec: functions + signatures, admin surfaces, triggers. For content engines: draft-only + leak-gate.
For AI: key-gated + rules fallback + leak re-gate. For kit sections: global colour refs + shortcode widget.]

## Test: file `automation/test-<unit>.php`
[Assertions, each PASS/FAIL + a final RESULT line. Seed isolated data; force-delete it after. If the unit can
trigger HubSpot/AI, SAVE+BLANK+RESTORE `ukv_hubspot_token` / `ukv_anthropic_key` so no real call fires.]
Lint, run (`-d memory_limit=512M`), iterate until `RESULT: ALL PASS`.

## Report back
Files created, final test output (must be ALL PASS), exact public function signatures, and confirm the
guardrails relevant to this unit (draft-only / null-safe-without-key / no real HTTP in tests / responsive).
Do not commit.
```

## After agents return (parent)
1. Re-run `php -l` + the test for each unit yourself (don't trust reports). Blank result → run from the repo
   `automation/` path. OOM → rerun (already 512M).
2. On green, mirror if needed, then commit per unit: `feat(<area> #<task>): <what> — tests pass`.
3. Run the affected/full `automation/test-*.php` once more to catch cross-plugin regressions.
4. If a unit failed, fix it yourself (single-writer) — don't re-dispatch onto the same file.
