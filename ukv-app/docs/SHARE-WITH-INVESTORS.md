# Share a live demo with investors (free, ~2 minutes, no hosting)

Expose your local app on a temporary public `https://` URL via a Cloudflare quick tunnel. Free, no account, no card. Tear it down when done. Use this to show investors *before* committing to paid hosting.

> This is a **demo**, not production: it runs off your machine (keep it on during the session), the URL changes each run, payments are Stripe **test mode** (never take real money here), and integrations (HubSpot/AI/WhatsApp/email) stay dormant unless you add keys. Don't enter real personal data.

## One-time install
```bash
# Windows
winget install --id Cloudflare.cloudflared
# (mac: brew install cloudflared)
```
Alternative: **ngrok** (`ngrok http 8000`) — also free, but needs a free account/authtoken and shows an interstitial page.

## 1. Start + populate the app
```bash
cd ukv-app
php artisan migrate --force
php artisan db:seed --class=DestinationSeeder --force
php artisan db:seed --class=DocumentRequirementSeeder --force
php artisan db:seed --class=FinderDemoSeeder --force
php artisan db:seed --class="Database\\Seeders\\TurkeyGoldGuidesSeeder" --force
php artisan serve            # serves http://127.0.0.1:8000
```
(Optional, for clean absolute links/sitemap during the demo: set `APP_URL` + `UKV_BASE_URL` to the tunnel URL in `.env`, then `php artisan config:clear`.)

## 2. Expose it (second terminal)
```bash
cloudflared tunnel --url http://localhost:8000
```
Copy the printed `https://<random>.trycloudflare.com` — **that is the investor link.**

## 3. Stop
`Ctrl+C` in the tunnel terminal kills the public URL instantly. `Ctrl+C` the server too when finished.

---

## Suggested 5-minute demo script (what to click)
1. **Home (`/`)** — positioning + the live "appointments available" band + trust framing.
2. **Tools → `/document-checklist`** — pick Turkey, answer 2–3 questions → instant tailored document list; show the multi-channel "send me this" + the sticky action bar.
3. **Money page `/visa/turkey`** — the destination hub; scroll to the auto-listed guides cluster.
4. **A guide `/visa/turkey/do-i-need-a-visa`** — SEO content depth, FAQ, "reviewed by" trust line, cited-source strip (the AI-assisted content engine).
5. **`/find-a-centre`** — type a postcode (or "use my location") → nearest centre + "we book here" + available appointment slots.
6. **Apply journey `/apply`** — fill the eligibility form → routes to Stripe **test** checkout (use card `4242 4242 4242 4242`, any future expiry/CVC) → confirmation → `/track` a status.
7. **Admin `/admin`** (have a seeded login ready) — orders hub, production-line board, document-requirements + guide editors, centre/slot management, data-change inbox. *This is the operations engine investors don't expect to already exist.*

**Talking points:** code-complete (141 automated tests), full ops back-office, AI content + change-detection with a no-invention guardrail, compliance baked in (independent service, fee transparency, CCRs 14-day, GDPR purge). Remaining to launch = hosting + keys + legal sign-offs, not engineering.

## After the raise / before real launch
Move to a real host with always-on workers + cron + SSL — see `DEPLOY-LARAVEL-CLOUD.md` (fastest) or `GO-LIVE-RUNBOOK.md` (any host). A tunnel/demo must NOT be used for real customers or payments.
