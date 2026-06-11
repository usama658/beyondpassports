# Elementor Capability

Edit pages built with the Elementor page builder. Elementor stores content in `_elementor_data` postmeta as JSON widget trees — NOT in `post_content`. The WordPress REST API and the Gutenberg/Classic editor canNOT edit Elementor content. Use the Elementor editor's JavaScript API via Playwright `browser_evaluate`.

> **Detect Elementor:** On the live page or in the WP admin bar, an "Edit with Elementor" link appears. The admin bar link format is `{WP_URL}/wp-admin/post.php?post={ID}&action=elementor`. The post ID is in that link.

---

## Why JS API, not canvas clicking

Driving the Elementor canvas (double-click widget → inline TinyMCE → type) is slow and brittle. Instead, read and write widget settings directly through Elementor's command API. It records undo history, marks the document dirty, and saves cleanly — surgical and fast.

---

## Open the Elementor Editor

1. Get the post ID from the "Edit with Elementor" admin bar link, or from `?post={ID}` on any edit screen.
2. Navigate to `{WP_URL}/wp-admin/post.php?post={ID}&action=elementor`
3. **Elementor is slow to load.** `browser_navigate` will likely time out (60s) — this is expected. After the timeout:
   - Wait 25-30s (`browser_wait_for` time:30)
   - Poll readiness with `browser_evaluate`:
     ```js
     () => {
       const l = document.querySelector('#elementor-loading');
       return {
         ready: !!document.querySelector('#elementor-preview-iframe')
                && (!l || getComputedStyle(l).display === 'none')
                && document.body.classList.contains('elementor-editor-active'),
         loaderDisplay: l ? getComputedStyle(l).display : 'gone'
       };
     }
     ```
   - Repeat the wait until `ready: true`. May take 60-120s total on a sluggish site.
4. If a "Can't Edit?" / Safe Mode message persists and `ready` never becomes true after ~3 minutes: report to user, suggest they check Elementor Safe Mode or server load.

---

## Locate the Target Text and Its Widget

The live front-end markup is inside the preview iframe (`#elementor-preview-iframe`). Find the element and walk up to its `.elementor-widget` ancestor to get the widget id and type.

```js
() => {
  const doc = document.querySelector('#elementor-preview-iframe').contentDocument;
  if (!doc) return { err: 'no-doc' };
  const SEARCH = 'We have sold';            // <-- a unique phrase from the target text
  const all = [...doc.querySelectorAll('p, div, span, h1, h2, h3, li, a')];
  const matches = all.filter(el => el.textContent.includes(SEARCH));
  matches.sort((a, b) => a.textContent.length - b.textContent.length); // narrowest first
  return matches.slice(0, 4).map(el => {
    const w = el.closest('.elementor-widget');
    return {
      tag: el.tagName, cls: el.className, html: el.innerHTML.slice(0, 200),
      widgetId: w ? w.dataset.id : null,
      widgetType: w ? (w.dataset.widget_type || w.dataset.element_type) : null
    };
  });
}
```

The narrowest match's `widgetId` is your target. Note the `widgetType`:
- `html.default` — raw HTML widget (whole sections often live in ONE html widget; edit the `html` setting)
- `text-editor.default` — WYSIWYG text (edit the `editor` setting)
- `heading.default` — heading (edit the `title` setting)
- `button.default` — button (edit the `text` setting, link in `link.url`)

---

## Read a Widget Setting

Replace `SETTING_KEY` per the widget type table above (`html`, `editor`, `title`, `text`, ...).

```js
() => {
  try {
    const c = window.elementor.getContainer('WIDGET_ID');
    const val = c.settings.get('SETTING_KEY');
    return { len: val.length, snippet: val.slice(0, 300) };
  } catch (e) { return { err: e.message }; }
}
```

**Before editing, confirm your old-string anchor is UNIQUE** within the setting value (count === 1). A non-unique anchor risks replacing the wrong instance. Pick a longer surrounding phrase if count > 1.

```js
() => {
  const c = window.elementor.getContainer('WIDGET_ID');
  const v = c.settings.get('SETTING_KEY');
  const anchor = 'OLD STRING';
  return { count: (v.split(anchor).length - 1) };
}
```

---

## Apply the Edit

Use the command API so history + dirty-state register correctly. Do NOT use `c.settings.set` alone — it skips history and may not persist.

```js
() => {
  try {
    const c = window.elementor.getContainer('WIDGET_ID');
    const val = c.settings.get('SETTING_KEY');
    const oldStr = 'OLD STRING';
    const newStr = 'NEW STRING';
    if (!val.includes(oldStr)) return { err: 'anchor-not-found' };
    const updated = val.replace(oldStr, newStr);  // replaces FIRST occurrence only
    window.$e.run('document/elements/settings', { container: c, settings: { 'SETTING_KEY': updated } });
    const after = window.elementor.getContainer('WIDGET_ID').settings.get('SETTING_KEY');
    return { applied: after.includes(newStr) };
  } catch (e) { return { err: e.message }; }
}
```

`.replace(str, str)` swaps only the first match — safe when anchor is unique. For a global swap use a regex with the `g` flag, but only after confirming every match should change.

---

## Save the Document

```js
() => new Promise((resolve) => {
  try {
    window.$e.run('document/save/update')
      .then(() => resolve({ saved: true, isDirty: window.elementor.saver.isEditorChanged() }))
      .catch(err => resolve({ saveErr: String(err) }));
  } catch (e) { resolve({ err: e.message }); }
})
```

Success = `{ saved: true, isDirty: false }`.

---

## Verify on Live Front-End

Navigate to the live page (not the preview URL) and read the element back:

```js
() => {
  const el = document.querySelector('SELECTOR');  // e.g. '.hero__lede'
  return { text: el ? el.textContent.trim().replace(/\s+/g, ' ') : 'not-found' };
}
```

Confirm the new string is present before reporting done.

---

## Notes & Gotchas

- **Whole-page HTML widget:** Some Elementor pages place an entire custom layout inside a single `html.default` widget (100KB+). All text edits for such a page go through that one widget's `html` setting. The widget id is the same for every section.
- **Slowness:** This site's Elementor editor takes 60-120s to load and frequently times out `browser_navigate`. Always treat the first navigation timeout as normal and poll for readiness.
- **Don't reload mid-edit** before saving — unsaved `$e.run` settings changes are lost on navigation.
- **Global widgets / templates:** If a widget id resolves but edits don't appear on the front end, the section may be a global widget or a Theme Builder template (header/footer). Edit the corresponding template instead (`{WP_URL}/wp-admin/edit.php?post_type=elementor_library`).
- **Caching:** If the live page still shows old text after save, a page cache may be serving stale HTML. Hard-reload, or clear the site/CDN cache.
