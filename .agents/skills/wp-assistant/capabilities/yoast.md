# Yoast SEO Capability

All Yoast SEO operations via Playwright (dashboard). Covers per-post settings, global settings, and SEO suggestions (Yoast analysis + Claude recommendations).

---

## Per-Post / Per-Page Settings

> **Important: Gutenberg vs Classic Editor**
> WordPress defaults to Gutenberg (block editor). In Gutenberg, the Yoast SEO panel lives in the **right sidebar** — not below the editor. In Classic Editor, it appears as a metabox below the content area. Determine which editor is active before proceeding.

### Detect Editor Type

After opening any post or page edit screen:
- If you see `.block-editor-page` in the page body → **Gutenberg** is active → follow Gutenberg steps
- If you see `#content` textarea and `#postdivrich` → **Classic Editor** is active → follow Classic steps

### Gutenberg: Access Yoast SEO Panel

1. Look for the Yoast SEO panel in the right sidebar (Document settings panel)
2. If not visible: click the gear/settings icon (`button[aria-label="Settings"]`) in the top-right toolbar to show the sidebar
3. Scroll the sidebar to find the "Yoast SEO" section heading, or click the Yoast SEO icon in the post editor toolbar
4. The panel shows tabs for: SEO | Readability | Social | Schema | Advanced

### Classic Editor: Access Yoast SEO Metabox

1. Scroll below the content editor
2. Find the Yoast SEO metabox (heading: "Yoast SEO")
3. If collapsed: click the metabox title bar to expand
4. Tabs available: SEO | Readability | Social | Schema | Advanced

### Set SEO Title

**Gutenberg (right sidebar):**
1. Click the "SEO" tab in the Yoast panel
2. Click the SEO title field: `input.yoast-seo-title-input` or `input[id*="yoast-google-preview-title"]`
3. Click inside the field to focus it
4. Press Ctrl+A to select all text within the field (do NOT use Ctrl+A without focus — in Gutenberg this selects all blocks)
5. Type new title
6. Click "Update" or "Publish" to save — verify "Post updated" notice appears

**Classic Editor:**
1. Click the "SEO" tab in the Yoast metabox
2. Click `input#yoast_wpseo_title`
3. Select all (Ctrl+A) → Delete
4. Type new title
5. Click "Update" → verify "Post updated." notice

### Set Meta Description

**Gutenberg:**
1. Click "SEO" tab in Yoast panel
2. Click `textarea.yoast-meta-description-input` or `textarea[id*="yoast-google-preview-description"]`
3. Select all → Delete
4. Type description (optimal: 150-160 characters)
5. Save post → verify update notice

**Classic Editor:**
1. Click "SEO" tab in metabox
2. Click `textarea#yoast_wpseo_metadesc`
3. Select all → Delete
4. Type description
5. Click "Update" → verify notice

### Set Focus Keyphrase

**Gutenberg:**
1. Click "SEO" tab in Yoast panel
2. Click `input.yoast-keyword-input` or `input[id*="yoast-keyword-input"]`
3. Clear → type keyphrase
4. Wait for Yoast analysis to re-run: traffic light indicators update (allow 2-3 seconds)
5. Save post

**Classic Editor:**
1. Click "SEO" tab in metabox
2. Click `input#yoast_wpseo_focuskw`
3. Clear → type keyphrase
4. Wait for analysis update
5. Save post

### Toggle Cornerstone Content

**Gutenberg:**
1. Click "SEO" tab in Yoast panel
2. Find toggle labelled "Cornerstone content" — click `input[id*="cornerstone"]` or the toggle switch element
3. Verify toggle state changed
4. Save post

**Classic Editor:**
1. Click "SEO" tab
2. Find `input#yoast_wpseo_is_cornerstone` toggle
3. Click to enable or disable
4. Save post

### Robots Meta (Advanced Tab)

**Gutenberg:**
1. Click "Advanced" tab in Yoast panel (look for tab button with text "Advanced")
2. "Allow search engines to show this post" dropdown → select "Yes" (index) or "No" (noindex)
3. "Should search engines follow links" dropdown → select "Yes" (follow) or "No" (nofollow)
4. "Canonical URL" field → enter URL only if overriding Yoast default
5. Save post

**Classic Editor:**
1. Click "Advanced" tab in Yoast metabox: `a[href="#wpseo-meta-section-advanced"]` or tab text "Advanced"
2. Same dropdowns as Gutenberg above
3. Save post

### OG / Social Settings (Social Tab)

1. Click "Social" tab in Yoast metabox
2. Facebook section:
   - "Facebook image" → click "Select image" → choose from media library or upload
   - "Facebook title" → enter OG title (leave blank to use SEO title)
   - "Facebook description" → enter OG description (leave blank to use meta description)
3. Twitter section: same fields
4. Save post

> **Verification:** After every save, confirm success by waiting for the "Post updated" admin notice (`.notice-success` or text containing "updated"). If no notice appears within 5 seconds, report the save as failed.

---

## Global Yoast Settings

### General
1. Navigate to `{WP_URL}/wp-admin/admin.php?page=wpseo_dashboard`
2. Configure company name, logo, person info if applicable
3. Set title separator character
4. Click "Save changes"

### Search Appearance — Content Types
1. Navigate to `{WP_URL}/wp-admin/admin.php?page=wpseo_titles`
2. Click "Content types" tab
3. For each post type (Posts, Pages, custom types):
   - Set SEO title template e.g. `%%title%% %%sep%% %%sitename%%`
   - Set meta description template
   - Toggle "Show [post type] in search results" (noindex control)
4. Click "Save changes"

### Search Appearance — Taxonomies
1. On same page click "Taxonomies" tab
2. For each taxonomy (Categories, Tags, custom):
   - Set title template
   - Toggle show in search results
3. Click "Save changes"

### Search Appearance — Archives
1. Click "Archives" tab
2. Configure author archives (recommend disable if single-author site)
3. Configure date archives
4. Click "Save changes"

### Social Settings
1. Navigate to `{WP_URL}/wp-admin/admin.php?page=wpseo_social`
2. Facebook tab: enter Facebook page URL, enable Open Graph, set default OG image
3. Twitter tab: enter Twitter/X username, set default card type to "Summary with large image"
4. Click "Save changes"

### XML Sitemaps
1. Navigate to `{WP_URL}/wp-admin/admin.php?page=wpseo_dashboard`
2. Click "Features" tab
3. Verify "XML sitemaps" toggle is ON
4. To check sitemap: navigate to `{WP_URL}/sitemap_index.xml` — must return XML
5. To exclude post type from sitemap: Search Appearance → Content Types → disable "Include in sitemap" for that type

### Redirects (Yoast Premium only)
1. Navigate to `{WP_URL}/wp-admin/admin.php?page=wpseo_redirects`
2. Click "Add new redirect"
3. Fill "Old URL" (origin path e.g. `/old-page/`)
4. Fill "New URL" (destination e.g. `/new-page/`)
5. Select redirect type: 301 (permanent), 302 (temporary), 307, 410 (gone), 451
6. Click "Add Redirect"
7. Verify new row appears in redirect list

---

## Yoast SEO Suggestions

Run both tracks and combine output.

### Track A: Read Yoast Analysis from Dashboard

1. Open post or page edit screen
2. Scroll to Yoast SEO metabox
3. Click "SEO" tab — read every bullet in the SEO analysis list with its traffic light color:
   - Green circle = passing
   - Orange circle = needs improvement
   - Red circle = failing
   To determine color programmatically in Playwright:
   - Each analysis result item has a class indicating status: look for `li` elements with `.yoast-seo-analysis-upsell` (upsell/premium), `.yoast-assessment-result--green` / `--orange` / `--red`
   - Or check the SVG circle element: `circle.yoast-icon--svg` — inspect the `fill` or `stroke` CSS value
   - Or read the `aria-label` attribute on the score icon if present
   - In practice: read the visible text of each bullet and its parent element's class suffix (`good`/`ok`/`bad`) to determine color
4. Note the overall SEO score label and color
5. Click "Readability" tab — read every bullet in readability analysis with its color
6. Note overall readability score label and color

Report in this format:

---
Yoast SEO Analysis — [Post Title]

SEO Score: [color] [label]
[color] [check description]
[color] [check description]
...

Readability Score: [color] [label]
[color] [check description]
...
---

### Track B: Claude SEO Recommendations

Fetch post content via REST API:
GET `{WP_URL}/wp-json/wp/v2/posts/{id}?_fields=title,content,excerpt,slug,yoast_head_json`
Header: `Authorization: Basic [base64(WP_USER:WP_APP_PASSWORD)]`

Analyse:

**SEO title:**
- Length: 50-60 chars is optimal. Report actual char count.
- Keyphrase position: should appear in first 3 words ideally. Report position.

**Meta description:**
- Length: 150-160 chars optimal. Flag if under 120 (too short) or over 160 (truncated in SERPs).
- Keyphrase presence: flag if missing.

**Content:**
- Keyphrase density: count occurrences / total word count. Optimal 0.5%-2.5%. Flag if outside range.
- Heading structure: H1 must exist and contain keyphrase. H2s recommended every 300 words for posts over 600 words.
- Internal links: flag zero internal links in posts over 500 words.
- Image alt text: flag images missing alt text containing keyphrase.
- Word count: flag if under 300 words for posts intended to rank organically.

### Combined Output Format

Deliver both tracks together:

---
SEO Report — [Post Title]

Yoast Score: [color emoji] [label] ([score if visible])
Readability: [color emoji] [label]

Yoast flags:
- [flag 1]
- [flag 2]

Claude recommendations:
- [recommendation 1]
- [recommendation 2]
---

---