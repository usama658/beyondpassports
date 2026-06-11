# Content Capability

Posts, pages, media, menus, users, and taxonomies via REST API (primary) and Playwright (fallback + full capability).

---

## REST API — Posts

> **All REST API requests require the Authorization header:** `Authorization: Basic [base64(WP_USER:WP_APP_PASSWORD)]`
> This applies to every GET, POST, PATCH, and DELETE request below. Only shown explicitly on the first example per section.

**List posts:**
GET `{WP_URL}/wp-json/wp/v2/posts?per_page=20&status=any`
Header: `Authorization: Basic [base64(WP_USER:WP_APP_PASSWORD)]`

**Get single post by ID:**
GET `{WP_URL}/wp-json/wp/v2/posts/{id}`
Header: `Authorization: Basic [...]`

**Create post:**
POST `{WP_URL}/wp-json/wp/v2/posts`
Headers: `Authorization: Basic [...]`, `Content-Type: application/json`
Body:
{
  "title": "Post Title",
  "content": "Post body HTML",
  "status": "publish",
  "excerpt": "Optional excerpt",
  "categories": [1, 2],
  "tags": [3],
  "slug": "post-slug"
}

Valid status values: `publish`, `draft`, `pending`, `private`

**Edit post:**
PATCH `{WP_URL}/wp-json/wp/v2/posts/{id}`
Headers: `Authorization: Basic [...]`, `Content-Type: application/json`
Body: only the fields to change e.g. `{ "title": "New Title" }`

**Delete post (move to trash):**
DELETE `{WP_URL}/wp-json/wp/v2/posts/{id}`
Header: `Authorization: Basic [...]`

**Delete post permanently:**
DELETE `{WP_URL}/wp-json/wp/v2/posts/{id}?force=true`
Header: `Authorization: Basic [...]`

---

## REST API — Pages

Same structure as Posts but use `/pages` endpoint. Pages have no `categories` or `tags` fields.

**List pages:**
GET `{WP_URL}/wp-json/wp/v2/pages?per_page=20&status=any`
Header: `Authorization: Basic [...]`

**Create page:**
POST `{WP_URL}/wp-json/wp/v2/pages`
Headers: `Authorization: Basic [...]`, `Content-Type: application/json`
Body:
{
  "title": "Page Title",
  "content": "Page body HTML",
  "status": "publish",
  "slug": "page-slug",
  "parent": 0
}

**Edit page:**
PATCH `{WP_URL}/wp-json/wp/v2/pages/{id}`
Headers: `Authorization: Basic [...]`, `Content-Type: application/json`
Body: only fields to change e.g. `{ "title": "New Title" }`

**Delete page permanently:**
DELETE `{WP_URL}/wp-json/wp/v2/pages/{id}?force=true`
Header: `Authorization: Basic [...]`

---

## REST API — Media

**Upload image:**
POST `{WP_URL}/wp-json/wp/v2/media`
Headers: `Authorization: Basic [...]`, `Content-Disposition: attachment; filename="image.jpg"`, `Content-Type: image/jpeg`
Body: binary file content

**List media:**
GET `{WP_URL}/wp-json/wp/v2/media?per_page=20`

**Update media metadata:**
PATCH `{WP_URL}/wp-json/wp/v2/media/{id}`
Body: `{ "title": "Image Title", "alt_text": "Alt text", "caption": "Caption" }`

**Delete media:**
DELETE `{WP_URL}/wp-json/wp/v2/media/{id}?force=true`

---

## REST API — Categories & Tags

**List categories:**
GET `{WP_URL}/wp-json/wp/v2/categories`

**Create category:**
POST `{WP_URL}/wp-json/wp/v2/categories`
Body: `{ "name": "Category Name", "slug": "category-slug", "parent": 0 }`

**Delete category:**
DELETE `{WP_URL}/wp-json/wp/v2/categories/{id}?force=true`

Same pattern for tags: replace `/categories` with `/tags`.

---

## REST API — Users

**List users:**
GET `{WP_URL}/wp-json/wp/v2/users`

**Create user:**
POST `{WP_URL}/wp-json/wp/v2/users`
Body:
{
  "username": "newuser",
  "email": "user@example.com",
  "password": "StrongPassword123!",
  "roles": ["editor"]
}
Valid roles: `administrator`, `editor`, `author`, `contributor`, `subscriber`

**Update user:**
PATCH `{WP_URL}/wp-json/wp/v2/users/{id}`
Body: `{ "roles": ["administrator"] }`

**Delete user:**
DELETE `{WP_URL}/wp-json/wp/v2/users/{id}?force=true&reassign={other_user_id}`
Note: `reassign` is required — provide ID of user to receive deleted user's posts.

---

## Playwright — Create Post via Dashboard

Use when REST fails or user requests browser.

1. Navigate to `{WP_URL}/wp-admin/post-new.php`
2. Wait for Gutenberg to load: selector `.editor-post-title__input` must be visible
3. Click `.editor-post-title__input` → type post title
4. Click `.block-editor-default-block-appender__content` → type content
5. Set status: click `button[aria-label="Status & visibility"]` to expand panel → select target status from `select#post-visibility-selector` or click the radio buttons
6. Set categories: click `button[aria-label="Categories"]` to expand panel → check category checkboxes
7. Click `.editor-post-publish-button__button` to publish (or `.editor-post-save-draft__button` to save as draft)
8. Verify: wait for `.components-notice__content` containing "published" or "saved"

---

## Playwright — Edit Post via Dashboard

1. Navigate to `{WP_URL}/wp-admin/edit.php`
2. If searching: fill `input[name="s"]` → press Enter
3. Click `.row-title a` link in the post row to open editor
4. Make changes to title and/or content
5. Click "Update" button
6. Verify: notice containing "updated" appears

---

## Playwright — Delete Post via Dashboard

1. Navigate to `{WP_URL}/wp-admin/edit.php`
2. Hover over post row → click `.submitdelete` link (shows as "Trash" on hover)
3. Verify: post moves to Trash, success notice appears

---

## Playwright — Upload Media via Dashboard

1. Navigate to `{WP_URL}/wp-admin/media-new.php`
2. Set file on input `#async-upload`
3. Wait for upload complete: progress bar disappears, filename listed
4. Verify media appears at `{WP_URL}/wp-admin/upload.php`

---

## Playwright — Navigation Menus

**Create menu:**
1. Navigate to `{WP_URL}/wp-admin/nav-menus.php`
2. Click "create a new menu" link
3. Enter menu name in `#menu-name` → click "Create Menu"

**Add items to menu:**
1. Expand desired panel (Pages, Posts, Custom Links, Categories) on left sidebar
2. Check items to add → click "Add to Menu"
3. Reorder by dragging menu item rows

**Assign menu to theme location:**
1. Click "Manage Locations" tab
2. Select menu from dropdown for target location
3. Click "Save Changes"

**Save menu:**
Click "Save Menu" button (`#save_menu_header` or `#save_menu_footer`)

---
