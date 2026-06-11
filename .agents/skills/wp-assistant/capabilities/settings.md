# Settings Capability

WordPress core settings via Playwright. User management via REST API (primary) and Playwright (fallback).

---

## General Settings

1. Navigate to `{WP_URL}/wp-admin/options-general.php`
2. Available fields and their selectors:
   - Site Title: `#blogname`
   - Tagline: `#blogdescription`
   - Admin Email: `#new_admin_email`
   - Membership (allow registration): `#users_can_register` checkbox
   - Default Role: `#default_role` select
   - Timezone: `#timezone_string` select
   - Date Format: `input[name="date_format"]` radio or custom `#date_format_custom`
   - Time Format: `input[name="time_format"]` radio or custom `#time_format_custom`
3. Make changes
4. Click "Save Changes"
5. Verify: "Settings saved." admin notice

## Reading Settings

1. Navigate to `{WP_URL}/wp-admin/options-reading.php`
2. Available fields:
   - Homepage displays: use `input[name="show_on_front"]` radio buttons — value `posts` for latest posts, value `page` for static page
   - When static page selected: `#page_on_front` selects the homepage, `#page_for_posts` selects the posts page
   - Blog pages show at most: `#posts_per_page` input
   - Search engine visibility: `#blog_public` checkbox — UNCHECKED = noindex entire site
3. Click "Save Changes"

## Discussion Settings

1. Navigate to `{WP_URL}/wp-admin/options-discussion.php`
2. Configure: comment settings, moderation queue, blacklist, avatars
3. Click "Save Changes"

> Note: Discussion settings are highly site-specific. Common fields include `#default_comment_status` (open/closed), `#require_name_email` checkbox, `#comment_moderation` checkbox, and `#moderation_notify` checkbox. Identify the specific setting by its label text and use the adjacent input's `name` attribute as the selector.

## Permalinks

1. Navigate to `{WP_URL}/wp-admin/options-permalink.php`
2. Select structure radio: Plain, Day and name, Month and name, Numeric, Post name, or Custom
3. For custom: enter pattern in `#permalink_structure` e.g. `/%postname%/`
4. Click "Save Changes"
5. Verify: "Permalink structure updated" notice (or rewrite rules notice if .htaccess not writable)

## Media Settings

1. Navigate to `{WP_URL}/wp-admin/options-media.php`
2. Set image dimensions:
   - Thumbnail: width `#thumbnail_size_w`, height `#thumbnail_size_h`
   - Medium: width `#medium_size_w`, height `#medium_size_h`
   - Large: width `#large_size_w`, height `#large_size_h`
3. Toggle "Organize uploads into month/year folders": `#uploads_use_yearmonth_folders`
4. Click "Save Changes"

---

## User Management

### List Users (REST API)

GET `{WP_URL}/wp-json/wp/v2/users?per_page=100`
Header: `Authorization: Basic [base64(WP_USER:WP_APP_PASSWORD)]`

### Create User (REST API)

POST `{WP_URL}/wp-json/wp/v2/users`
Headers: `Authorization: Basic [...]`, `Content-Type: application/json`
Body:
{
  "username": "newuser",
  "name": "Display Name",
  "email": "user@example.com",
  "password": "StrongPassword123!",
  "roles": ["editor"]
}

Valid roles: `administrator`, `editor`, `author`, `contributor`, `subscriber`

### Update User (REST API)

PATCH `{WP_URL}/wp-json/wp/v2/users/{id}`
Header: `Authorization: Basic [...]`, `Content-Type: application/json`
Body: `{ "roles": ["administrator"] }` or any other user field

### Delete User (REST API)

DELETE `{WP_URL}/wp-json/wp/v2/users/{id}?force=true&reassign={other_user_id}`
Header: `Authorization: Basic [...]`
Note: `reassign` parameter is required — provide the ID of another user to receive the deleted user's posts.

### Create User (Playwright fallback)

1. Navigate to `{WP_URL}/wp-admin/user-new.php`
2. Fill `#user_login` with username
3. Fill `#email` with email address
4. Fill `#first_name` and `#last_name`
5. Set password: click "Show password" button → clear field → type password
6. Set role: `#role` select
7. Uncheck "Send User Notification" if email notification not wanted
8. Click "Add New User"
9. Verify: "New user created" admin notice

### Delete User (Playwright fallback)

1. Navigate to `{WP_URL}/wp-admin/users.php`
2. Hover over user row → click "Delete"
3. On confirmation page: select `input[value="reassign"]` radio → then select the target user from `#reassign_user` dropdown
4. Click "Confirm Deletion"
5. Verify: user no longer in list

---
