# Build tricks — developer guide (conventions + gotchas)

For anyone extending the UKV WordPress build. The hard-won rules that keep changes safe. See also the
`ukv-parallel-build` skill (bundled data) and memory `ukv-meta-key-conventions`.

## Environment
- WordPress: `C:\xampp\htdocs\ukvisa`. All custom logic = **mu-plugins** (`wp-content/mu-plugins/`, auto-active).
- **Mirror** every finished mu-plugin to the repo `wordpress/mu-plugins/` and scripts/tests to `automation/` — the repo is the source of truth, htdocs is the live runtime.
- PHP CLI: `C:\xampp\php\php.exe`. Lint: `php -l`. wp-cli runs via Git Bash; **cwd resets each call** so `cd` every time.
- **Always pass `-d memory_limit=512M`** to wp-cli — the CLI OOMs (`VirtualAlloc`/"paging file") without it on this box. A transient OOM = just rerun.
- MySQL + Apache run in the background; if the site/db is down, restart `mysqld.exe` and `httpd.exe` (background).

## The #1 gotcha — meta-key prefixes
- **Order** post-meta is `ukv_`-prefixed: `ukv_order_ref`, `ukv_destination` (a DISPLAY name like "Egypt", NOT a slug), `ukv_status`, `ukv_documents`, etc.
- **Barrier** post-meta is **bare**: `nature`, `scope`, `destination` (a SLUG), `order_ref`, `guidance`, `status`.
- Reading an order with a bare key (or passing the display-name where a slug is expected) returns empty and **fails silently**. Normalise with `ukv_dest_slug()`. This bit us twice (P11 fan-out, doc-review validity).

## Testing discipline
- Every plugin ships `automation/test-<name>.php` run via `wp eval-file` (NOT standalone php — rely on WP being loaded; do **not** `require` plugin files, that redeclares and fatals).
- Tests **seed their own data and force-delete it**. Assert with `PASS —`/`FAIL —` lines + a final `RESULT: ALL PASS`.
- **Isolate from ambient data:** the demo seed (#48) leaves open Egypt orders/barriers. Count-based tests must use a **synthetic destination** (e.g. `Testlandia`/`Prodland`) so demo/real data can't pollute them.
- **Blank live tokens in tests:** a real `ukv_hubspot_token` may sit in the DB. Save+blank `ukv_hubspot_token`/`ukv_anthropic_key` before any code path that could call out, restore after — or no real CRM/API call fires.
- For AI, inject a mock via the `ukv_ai_pre_response` filter — no real HTTP.
- A **blank** test result usually means the file is in the repo `automation/` dir, not htdocs — rerun with the repo path.

## Safety rails (never weaken these)
- **Content engines are draft-only** + abort on `ukv_story_has_leak` (PII + competitor tokens). Never publish programmatically.
- **AI is key-gated:** `ukv_ai()` returns null without a key; every caller falls back to rules. AI output for public content is re-gated.
- **Redact staff free-text** (barrier `guidance`) before any public sink (tracker), external sink (AI), or broadcast (client emails).
- Every admin/POST handler verifies a **nonce + `current_user_can`**. `save_post` hooks guard autosave.

## Hook ordering on `save_post_ukv_order` (important)
Multiple plugins hook it; priority matters:
- 7 = required-count sync · 8 = QA sign-off save · 9 = **QA submit gate** (reverts bad submit) · 10 = **stage gates** (reverts other bad transitions) · 11 = journey save · 12 = **email status-change** (updates `ukv_status_last`, fires stage emails) · 13 = Zapier · 14 = retention close-stamp.
Because emails update `ukv_status_last` at 12 (after the gates at 9/10), a reverted status never fires a stage email. Keep new gates ≤10, new comms ≥12.

## Reuse, don't reimplement
Helpers exist for almost everything (`ukv_create_order`, `ukv_dest_slug`, `ukv_dest_value`, `ukv_redact_pii`, `ukv_story_has_leak`, `ukv_order_sla_hours`, `ukv_barrier_create`, `ukv_qa_can_submit`, `ukv_stage_can_enter`, …). Guard with `function_exists`/`defined` so a plugin degrades gracefully if another isn't loaded.

## Determinism
No `rand()`/`Date.now()` for ids — derive deterministically (e.g. `md5` of inputs) so reruns/resumes are stable. Group ids, discount codes, supply-chain ids all follow this.

## Parallel builds
Independent units (separate files) → fan out to subagents via the `ukv-parallel-build` skill: each writes test-first, mirrors, reports (does NOT commit). The parent re-lints, re-tests, and commits per unit. Same-file edits + security/privacy changes = do yourself (single-writer).
