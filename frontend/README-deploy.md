# Deploying the UKVisaCo front-end to Netlify

This folder (`frontend/`) is a self-contained static site: 16 HTML pages plus
`assets/ukv.css` and `assets/ukv-illustrations.js`. It has **no build step**.
It deploys independently of the Laravel backend (`ukv-app/`) — nothing here
depends on or touches that directory.

## Configuration files in this folder

| File            | Purpose                                                        |
| --------------- | -------------------------------------------------------------- |
| `netlify.toml`  | Publish dir, security headers, CSP, caching rules              |
| `_redirects`    | 404 fallback to `/404.html`; clean-URL notes                   |
| `robots.txt`    | Allow all crawlers; points to the sitemap                      |
| `sitemap.xml`   | Public pages (excludes `confirmation.html` and `404.html`)     |

Before going live, replace every `https://YOUR-SITE.netlify.app` placeholder in
`robots.txt` and `sitemap.xml` with the real production domain.

## Deploy options

### 1. Drag-and-drop (quickest)
Go to https://app.netlify.com → "Add new site" → "Deploy manually", then drag
**this `frontend/` folder** onto the drop zone. Netlify serves it as the site root.

### 2. Netlify CLI
```bash
# from the repository root
npm install -g netlify-cli      # once
netlify login                   # once

# preview deploy
netlify deploy --dir=frontend

# production deploy
netlify deploy --dir=frontend --prod
```

### 3. Git-connected (continuous deploy)
Connect the repo in the Netlify UI and set:
- **Base directory:** `frontend`
- **Publish directory:** `frontend` (or `.` relative to base)
- **Build command:** *(leave empty — static site)*

`netlify.toml` lives inside `frontend/`, so with the base directory set to
`frontend` its `publish = "."` resolves to this folder correctly.

## Notes

- **CSP / Google Fonts:** the CSP in `netlify.toml` allows Google Fonts
  (`fonts.googleapis.com` for CSS, `fonts.gstatic.com` for font files) and
  permits inline `<style>`/`<script>`/`onclick`/JSON-LD via `'unsafe-inline'`,
  which the existing pages rely on. See the comment block in `netlify.toml` for
  the full trade-off and how to harden it later.
- **Caching:** `assets/*` is cached for a year (`immutable`); HTML is
  `must-revalidate` so content updates appear immediately.
- **Backend hand-off (UKV_API_BASE):** the apply and track flows are wired to the
  Laravel app via `assets/ukv-config.js`, which sets
  `window.UKV_API_BASE`. For production, edit that one line to your hosted Laravel
  origin with **no trailing slash** (e.g. `https://api.your-laravel-host.example`);
  it ships pointing at `http://localhost:8000` for local dev. `apply.html` POSTs the
  form to `${UKV_API_BASE}/apply`; on a `standard` lane it then navigates the browser
  to `${UKV_API_BASE}/checkout/{order_ref}` (which redirects to Stripe), and on a
  `manual_review` lane it shows the callback panel. `track.html` submits the
  reference (full page) to `${UKV_API_BASE}/track/lookup`. If `UKV_API_BASE` is empty
  or the API is unreachable, both pages fall back to the original client-side demo.
- **CORS (Laravel side must allow this site):** because the static site and the
  Laravel app are on **different origins**, the app must return CORS headers for the
  `/apply` fetch. In `ukv-app/config/cors.php` set `paths` to include `apply` (and
  `track/lookup` if you switch it to fetch), `allowed_origins` to this Netlify
  domain (e.g. `https://YOUR-SITE.netlify.app`, plus `http://localhost:8888`/your
  local static host for dev), `allowed_methods` to `['POST']`, and
  `allowed_headers` to include `Content-Type` and `Accept`. The browser will send a
  preflight `OPTIONS` for the JSON POST, so the response needs
  `Access-Control-Allow-Origin: <this site>`, `Access-Control-Allow-Methods: POST,
  OPTIONS`, and `Access-Control-Allow-Headers: Content-Type, Accept`. The
  `/checkout/{order_ref}` and `/track/lookup` full-page navigations are top-level
  browser navigations, not XHR, so they do **not** need CORS — only the Netlify CSP
  `connect-src` / `form-action` (already widened in `netlify.toml`) must permit the
  API origin.
