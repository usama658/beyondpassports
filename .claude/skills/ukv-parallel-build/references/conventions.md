# UKV build conventions

## Environment
- WordPress: `C:\xampp\htdocs\ukvisa`. Custom logic = mu-plugins at `...\wp-content\mu-plugins\` (auto-active).
- Repo mirror: copy every finished mu-plugin to `<repo>\wordpress\mu-plugins\` and every test/script to `<repo>\automation\`.
- PHP CLI: `C:\xampp\php\php.exe`. Lint: `C:\xampp\php\php.exe -l <file>`.
- wp-cli via Bash (Git Bash; cwd resets each call), ALWAYS pass `-d memory_limit=512M`:
  `cd /c/xampp/htdocs/ukvisa && /c/xampp/php/php.exe -d memory_limit=512M wp-cli.phar eval-file "<ABS windows path>" 2>&1 | tail -30`
- MySQL + Apache run in the background already.

## Code style (match existing mu-plugins)
`<?php` then `/** Plugin Name: ... */` then `defined('ABSPATH')||exit;`. Arrow closures, tabs. Escape ALL output
(`esc_html`/`esc_attr`/`esc_url`/`wp_kses_post`); sanitize ALL input (`sanitize_text_field`/`sanitize_textarea_field`/`wp_unslash`).
Every admin/POST handler verifies a nonce AND `current_user_can(...)`. `save_post` hooks guard autosave + nonce.

## Test-first
Write `automation/test-<unit>.php` that seeds its own isolated data, asserts (`PASS — ...`/`FAIL — ...`), prints a
final `RESULT: ALL PASS` / `RESULT: FAILURES PRESENT`, and force-deletes everything it created. Iterate until green.
A blank result when running usually means the test file is in the repo `automation/` dir, not htdocs — re-run with
the repo path. Transient `Out of memory`/`VirtualAlloc` = CLI memory pressure; rerun (already at 512M).

## Privacy + safety (non-negotiable)
- **Content/testimonial engines are DRAFT-ONLY.** `ukv_generate_story_draft`/`ukv_generate_testimonial_draft` run
  `ukv_redact_pii` then `ukv_redact_competitor`, then ABORT (return 0, create nothing) on any `ukv_story_has_leak`
  finding, and insert `post_status='draft'`. Never publish programmatically.
- **Redact staff free-text** (`barrier guidance`) before it reaches any public sink (tracker), external sink (AI),
  or broadcast sink (client emails).
- **AI is key-gated:** `ukv_ai()` returns null without `ukv_anthropic_key`; every caller falls back to a rules
  template. AI output for public content is re-checked by the leak gate.
- **Token safety in tests:** a LIVE `ukv_hubspot_token` may be in the DB. Save+blank it (and `ukv_anthropic_key`)
  at the start of any test that could trigger an outbound call, and restore at the end:
  `$tk=get_option('ukv_hubspot_token',''); update_option('ukv_hubspot_token',''); /* ... */ update_option('ukv_hubspot_token',$tk);`
  For mockable AI, inject via the `ukv_ai_pre_response` filter — no real HTTP.

## Responsiveness (customer-facing work)
Design must hold at desktop / tablet / mobile. Prefer kit-native Elementor sections (inherit the kit's responsive
system + per-breakpoint controls) over bespoke inline-CSS. For any HTML tables, ensure mobile overflow handling.
The admin screens (cockpit/reports/meta boxes) are staff-only desktop — responsiveness not required there.

## Commit discipline (parent only)
Subagents report; they do NOT commit. The parent re-runs lint + test, then commits each unit with a clear message:
`feat(<area> #<task>): <what> — tests pass`. Mirror to repo before committing. End commit messages with the
standard Co-Authored-By line.
