# Plugins & Themes Capability

All operations via Playwright. No equivalent REST API endpoints.

---

## Plugins

### Install Plugin from WordPress.org

1. Navigate to `{WP_URL}/wp-admin/plugin-install.php`
2. Click search input → type plugin name
3. Locate correct plugin card
4. Click "Install Now" button on that card
5. Wait: button text changes to "Activate"
6. Click "Activate"
7. Verify: plugin listed at `{WP_URL}/wp-admin/plugins.php` with "Deactivate" link visible

### Install Plugin from ZIP File

1. Navigate to `{WP_URL}/wp-admin/plugin-install.php`
2. Click "Upload Plugin" button at top
3. Click "Choose File" → select ZIP file
4. Click "Install Now"
5. On confirmation screen click "Activate Plugin"
6. Verify: plugin listed at `{WP_URL}/wp-admin/plugins.php` with "Deactivate" link visible in its row

### Activate Plugin

1. Navigate to `{WP_URL}/wp-admin/plugins.php`
2. Find plugin row by name
3. Click "Activate" link under plugin name
4. Verify: "Deactivate" link now shows in that row, success admin notice appears

### Deactivate Plugin

1. Navigate to `{WP_URL}/wp-admin/plugins.php`
2. Find plugin row
3. Click "Deactivate" link
4. Verify: "Activate" link now shows

### Delete Plugin

1. Deactivate plugin first (see above)
2. In plugin row: click "Delete" link (only visible when deactivated)
3. Confirm deletion in the confirmation dialog
4. Verify: plugin row no longer exists on plugins page

### Update Single Plugin

1. Navigate to `{WP_URL}/wp-admin/update-core.php`
2. Find plugin in "Plugins" section
3. Check its checkbox
4. Click "Update Plugins"
5. Wait for progress to complete
6. Verify: plugin shows updated version number

### Update All Plugins

1. Navigate to `{WP_URL}/wp-admin/update-core.php`
2. In "Plugins" section: check the "Select All" checkbox
3. Click "Update Plugins"
4. Wait for all updates to complete
5. Verify no "update available" notices remain

---

## Themes

### Install Theme from WordPress.org

1. Navigate to `{WP_URL}/wp-admin/theme-install.php`
2. Type theme name in search field
3. Hover over theme card → click "Install"
4. Click "Activate" after installation completes
5. Verify: theme listed as active at `{WP_URL}/wp-admin/themes.php`

### Install Theme from ZIP File

1. Navigate to `{WP_URL}/wp-admin/theme-install.php`
2. Click "Upload Theme"
3. Click "Choose File" → select ZIP
4. Click "Install Now"
5. Click "Activate" on confirmation screen
6. Verify activation

### Switch Active Theme

1. Navigate to `{WP_URL}/wp-admin/themes.php`
2. Hover over desired theme card
3. Click "Activate" button
4. Verify: theme card shows "Active" label

### Delete Theme

1. Ensure theme is not currently active — switch to another theme first
2. Navigate to `{WP_URL}/wp-admin/themes.php`
3. Click theme thumbnail to open detail panel
4. Click "Delete" link (bottom right of panel)
5. Confirm in dialog
6. Verify: theme no longer appears

### Update Themes

1. Navigate to `{WP_URL}/wp-admin/update-core.php`
2. In "Themes" section: check themes to update
3. Click "Update Themes"
4. Wait for completion

---

## WordPress Core Update

1. Navigate to `{WP_URL}/wp-admin/update-core.php`
2. Under "An updated version of WordPress is available": click "Update Now"
3. Wait — page streams update log output
4. Verify: redirected to dashboard with "Welcome to WordPress [version]" notice
5. Confirm new version at `{WP_URL}/wp-admin/about.php`

---
