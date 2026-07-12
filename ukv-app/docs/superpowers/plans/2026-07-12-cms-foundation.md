# CMS Foundation Implementation Plan (Phase 1 of 5)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the theme-safe CMS infrastructure — a page/block data model, block registry, an `UKV_CMS_ENABLED` + per-page `mode` resolver that renders CMS blocks *or* falls back to the existing coded Blade, an Editor role, and a Filament Pages resource — while changing **nothing** on the live site (flag ships off; every public page still renders its coded view).

**Architecture:** A `Page` holds a JSON stack of blocks. A `BlockRegistry` maps `type => [Filament schema, Blade partial]`. `CmsController::show()` resolves a slug: if the global flag is on AND the page `mode=cms` AND it has published blocks, it loops the registry and `@include`s each partial inside the existing site shell; otherwise it renders the existing coded view (the shared-partial fallback). Publishing writes a `PageRevision` snapshot and busts a full-page cache. All CMS admin lives in a new "Content" Filament nav group gated to the Editor role.

**Tech Stack:** Laravel 11, Filament v3 (native `Builder` field — no package), MySQL, Blade + `ukv.css`, PHPUnit (Feature tests, `RefreshDatabase`). No new front-end dependencies.

## Global Constraints

- No new front-end/composer dependencies for this phase. Native Filament `Builder` only.
- Public output must be byte-identical to today while the flag is off (`UKV_CMS_ENABLED` default **false**).
- No changes to existing routes, controllers, payment/apply/track code.
- No em-dashes in any user-facing copy (project rule). Use commas or restructure.
- PHP: `declare(strict_types=1);` at top of every new PHP file. Tests extend `Tests\TestCase`, use `RefreshDatabase`, `test_*` method names (match existing style).
- Filament resources register automatically from `app/Filament/Resources`; follow the existing resource pattern (see `app/Filament/Resources/GuideResource.php`).
- Config flag pattern mirrors `config/ukv.php` `trustpilot.enabled` (env-driven, default false).

---

## File Structure

- `config/ukv.php` — add `cms` block (flag).
- `app/Enums/UserRole.php` — add `Editor` case.
- `database/migrations/*_create_pages_table.php` — pages.
- `database/migrations/*_create_page_revisions_table.php` — revisions.
- `app/Models/Page.php`, `app/Models/PageRevision.php` — models.
- `app/Cms/BlockRegistry.php` — type → schema + partial map (the extension point).
- `app/Cms/Blocks/RichTextBlock.php` — first block (proves the pattern).
- `resources/views/cms/blocks/rich-text.blade.php` — its partial.
- `resources/views/cms/page.blade.php` — CMS shell that loops blocks.
- `app/Http/Controllers/CmsController.php` — resolver + fallback + cache.
- `app/Services/PageRenderer.php` — pure block-loop → HTML (testable, no HTTP).
- `app/Filament/Resources/PageResource.php` (+ `Pages/`) — admin CRUD with Builder field.
- `app/Policies/PagePolicy.php` — Editor/Admin access; deny others.
- `routes/web.php` — register the CMS catch route LAST (after all real routes).
- `tests/Feature/Cms/*` — tests.

---

### Task 1: Config flag + Editor role

**Files:**
- Modify: `config/ukv.php` (add `cms` array near other feature blocks)
- Modify: `app/Enums/UserRole.php`
- Test: `tests/Feature/Cms/CmsFlagTest.php`

**Interfaces:**
- Produces: `config('ukv.cms.enabled')` (bool, default false); `App\Enums\UserRole::Editor` (value `'editor'`).

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Enums\UserRole;
use Tests\TestCase;

final class CmsFlagTest extends TestCase
{
    public function test_cms_flag_defaults_off(): void
    {
        $this->assertFalse(config('ukv.cms.enabled'));
    }

    public function test_editor_role_exists(): void
    {
        $this->assertSame('editor', UserRole::Editor->value);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CmsFlagTest`
Expected: FAIL (`config('ukv.cms.enabled')` null; `UserRole::Editor` undefined).

- [ ] **Step 3: Add the config block**

In `config/ukv.php`, add near the other feature blocks (e.g. after `trustpilot`):

```php
    // Content CMS (theme-safe block builder). OFF by default: public pages render their
    // existing coded Blade until a page is explicitly switched to cms mode AND published.
    'cms' => [
        'enabled' => env('UKV_CMS_ENABLED', false),
    ],
```

- [ ] **Step 4: Add the Editor role**

In `app/Enums/UserRole.php`, add the case:

```php
    case Editor = 'editor';
```

Leave `canAccessPanel()` (or equivalent) as-is for now; the Editor gets panel access via the `in_array(...)` gate — add `UserRole::Editor` to that array in `app/Models/User.php`:

```php
        return in_array($this->role, [UserRole::Admin, UserRole::Agent, UserRole::Viewer, UserRole::Editor], true);
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=CmsFlagTest`
Expected: PASS (2 tests).

- [ ] **Step 6: Commit**

```bash
git add config/ukv.php app/Enums/UserRole.php app/Models/User.php tests/Feature/Cms/CmsFlagTest.php
git commit -m "feat(cms): add UKV_CMS_ENABLED flag + Editor role"
```

---

### Task 2: pages + page_revisions tables and models

**Files:**
- Create: `database/migrations/2026_07_12_000001_create_pages_table.php`
- Create: `database/migrations/2026_07_12_000002_create_page_revisions_table.php`
- Create: `app/Models/Page.php`
- Create: `app/Models/PageRevision.php`
- Test: `tests/Feature/Cms/PageModelTest.php`

**Interfaces:**
- Produces: `App\Models\Page` with fillable `slug,title,mode,status,blocks,seo_title,seo_description,og_image,noindex,in_sitemap,published_at`; casts `blocks=>array,noindex=>bool,in_sitemap=>bool,published_at=>datetime`. `Page::isPublishedCms(): bool` returns `mode==='cms' && status==='published' && !empty(blocks)`. `Page::revisions()` hasMany `PageRevision`. `PageRevision` fillable `page_id,title,blocks,seo_title,seo_description,og_image,editor_id`, casts `blocks=>array`.

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PageModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_cms_requires_mode_status_and_blocks(): void
    {
        $draft = Page::create(['slug' => 'home', 'title' => 'Home', 'mode' => 'cms', 'status' => 'draft', 'blocks' => [['type' => 'rich-text', 'data' => ['body' => 'x']]]]);
        $this->assertFalse($draft->isPublishedCms());

        $coded = Page::create(['slug' => 'about', 'title' => 'About', 'mode' => 'coded', 'status' => 'published', 'blocks' => [['type' => 'rich-text', 'data' => ['body' => 'x']]]]);
        $this->assertFalse($coded->isPublishedCms());

        $empty = Page::create(['slug' => 'svc', 'title' => 'Svc', 'mode' => 'cms', 'status' => 'published', 'blocks' => []]);
        $this->assertFalse($empty->isPublishedCms());

        $live = Page::create(['slug' => 'live', 'title' => 'Live', 'mode' => 'cms', 'status' => 'published', 'blocks' => [['type' => 'rich-text', 'data' => ['body' => 'x']]]]);
        $this->assertTrue($live->isPublishedCms());
    }

    public function test_blocks_cast_to_array(): void
    {
        $p = Page::create(['slug' => 'a', 'title' => 'A', 'mode' => 'cms', 'status' => 'draft', 'blocks' => [['type' => 'rich-text', 'data' => ['body' => 'hi']]]]);
        $this->assertSame('hi', $p->fresh()->blocks[0]['data']['body']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PageModelTest`
Expected: FAIL (no `pages` table / `Page` model).

- [ ] **Step 3: Write the pages migration**

`database/migrations/2026_07_12_000001_create_pages_table.php`:

```php
<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('mode')->default('coded');       // coded | cms
            $table->string('status')->default('draft');       // draft | published
            $table->json('blocks')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->string('og_image')->nullable();
            $table->boolean('noindex')->default(false);
            $table->boolean('in_sitemap')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
```

- [ ] **Step 4: Write the page_revisions migration**

`database/migrations/2026_07_12_000002_create_page_revisions_table.php`:

```php
<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->json('blocks')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->string('og_image')->nullable();
            $table->foreignId('editor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_revisions');
    }
};
```

- [ ] **Step 5: Write the models**

`app/Models/Page.php`:

```php
<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'slug', 'title', 'mode', 'status', 'blocks',
        'seo_title', 'seo_description', 'og_image', 'noindex', 'in_sitemap', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'noindex' => 'boolean',
            'in_sitemap' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->latest();
    }

    /** The single source of truth for "render CMS blocks instead of coded Blade". */
    public function isPublishedCms(): bool
    {
        return $this->mode === 'cms'
            && $this->status === 'published'
            && ! empty($this->blocks);
    }
}
```

`app/Models/PageRevision.php`:

```php
<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageRevision extends Model
{
    protected $fillable = ['page_id', 'title', 'blocks', 'seo_title', 'seo_description', 'og_image', 'editor_id'];

    protected function casts(): array
    {
        return ['blocks' => 'array'];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=PageModelTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_07_12_00000*_*.php app/Models/Page.php app/Models/PageRevision.php tests/Feature/Cms/PageModelTest.php
git commit -m "feat(cms): pages + page_revisions tables and models"
```

---

### Task 3: BlockRegistry + first block (rich-text) + partial

**Files:**
- Create: `app/Cms/BlockRegistry.php`
- Create: `app/Cms/Blocks/BlockType.php` (interface)
- Create: `app/Cms/Blocks/RichTextBlock.php`
- Create: `resources/views/cms/blocks/rich-text.blade.php`
- Test: `tests/Feature/Cms/BlockRegistryTest.php`

**Interfaces:**
- Produces: `App\Cms\Blocks\BlockType` interface with static `key(): string`, static `label(): string`, static `schema(): array` (Filament components), static `view(): string` (partial name). `App\Cms\BlockRegistry` with `all(): array<string,class-string>`, `view(string $type): ?string`, `builderBlocks(): array` (Filament `Builder\Block[]`).

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Cms\BlockRegistry;
use Tests\TestCase;

final class BlockRegistryTest extends TestCase
{
    public function test_rich_text_is_registered(): void
    {
        $reg = app(BlockRegistry::class);
        $this->assertArrayHasKey('rich-text', $reg->all());
        $this->assertSame('cms.blocks.rich-text', $reg->view('rich-text'));
        $this->assertNull($reg->view('does-not-exist'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=BlockRegistryTest`
Expected: FAIL (no `BlockRegistry`).

- [ ] **Step 3: Write the BlockType interface**

`app/Cms/Blocks/BlockType.php`:

```php
<?php
declare(strict_types=1);

namespace App\Cms\Blocks;

interface BlockType
{
    /** Stable machine key stored in pages.blocks[].type. Never rename after data exists. */
    public static function key(): string;

    /** Human label in the builder palette. */
    public static function label(): string;

    /** Filament form components for this block's fields. */
    public static function schema(): array;

    /** Blade partial name (dot notation) that renders this block on the public site. */
    public static function view(): string;
}
```

- [ ] **Step 4: Write the RichTextBlock**

`app/Cms/Blocks/RichTextBlock.php`:

```php
<?php
declare(strict_types=1);

namespace App\Cms\Blocks;

use Filament\Forms\Components\RichEditor;

class RichTextBlock implements BlockType
{
    public static function key(): string
    {
        return 'rich-text';
    }

    public static function label(): string
    {
        return 'Rich text';
    }

    public static function schema(): array
    {
        return [
            RichEditor::make('body')
                ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList', 'h2', 'h3'])
                ->required(),
        ];
    }

    public static function view(): string
    {
        return 'cms.blocks.rich-text';
    }
}
```

- [ ] **Step 5: Write the BlockRegistry**

`app/Cms/BlockRegistry.php`:

```php
<?php
declare(strict_types=1);

namespace App\Cms;

use App\Cms\Blocks\BlockType;
use App\Cms\Blocks\RichTextBlock;
use Filament\Forms\Components\Builder\Block;

class BlockRegistry
{
    /**
     * Ordered map of block key => class. Add a new section by appending one line here
     * (plus its BlockType class + Blade partial). Never touches existing blocks.
     *
     * @var array<int, class-string<BlockType>>
     */
    private array $types = [
        RichTextBlock::class,
    ];

    /** @return array<string, class-string<BlockType>> */
    public function all(): array
    {
        $out = [];
        foreach ($this->types as $class) {
            $out[$class::key()] = $class;
        }

        return $out;
    }

    public function view(string $type): ?string
    {
        $class = $this->all()[$type] ?? null;

        return $class ? $class::view() : null;
    }

    /** @return array<int, Block> Filament Builder blocks for the admin form. */
    public function builderBlocks(): array
    {
        return array_map(
            fn (string $class) => Block::make($class::key())->label($class::label())->schema($class::schema()),
            array_values($this->all()),
        );
    }
}
```

- [ ] **Step 6: Write the partial**

`resources/views/cms/blocks/rich-text.blade.php`:

```blade
{{-- Rich-text CMS block. Renders trusted editor HTML inside the site's prose scope. --}}
<div class="wrap cms-prose">
    {!! $data['body'] ?? '' !!}
</div>
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --filter=BlockRegistryTest`
Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add app/Cms tests/Feature/Cms/BlockRegistryTest.php resources/views/cms/blocks/rich-text.blade.php
git commit -m "feat(cms): block registry + rich-text block (extension point)"
```

---

### Task 4: PageRenderer + CmsController resolver + coded fallback

**Files:**
- Create: `app/Services/PageRenderer.php`
- Create: `resources/views/cms/page.blade.php`
- Create: `app/Http/Controllers/CmsController.php`
- Modify: `routes/web.php` (register the catch route LAST)
- Test: `tests/Feature/Cms/CmsResolverTest.php`

**Interfaces:**
- Consumes: `Page::isPublishedCms()`, `BlockRegistry::view()`, `config('ukv.cms.enabled')`.
- Produces: `PageRenderer::render(Page $page): string` (block loop → HTML). `CmsController::show(string $slug)` returns a Response: CMS view when flag on + `isPublishedCms()`, else `abort(404)` so Laravel's existing coded route can own the slug.

**Note on routing:** the CMS route is a **catch-all registered last**. Real coded routes (`/`, `/about`, `/schengen-visa`, etc.) are matched first by Laravel. The catch route only fires for slugs no coded route claimed. In Phase 2 (Home pilot) we invert one specific route; for Foundation the resolver only serves brand-new CMS-only slugs, guaranteeing zero impact on existing pages.

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CmsResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_flag_off_hides_cms_page(): void
    {
        config(['ukv.cms.enabled' => false]);
        Page::create(['slug' => 'promo', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published', 'blocks' => [['type' => 'rich-text', 'data' => ['body' => '<p>Live promo</p>']]]]);

        $this->get('/promo')->assertNotFound();
    }

    public function test_flag_on_renders_published_cms_page(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create(['slug' => 'promo', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published', 'blocks' => [['type' => 'rich-text', 'data' => ['body' => '<p>Live promo</p>']]]]);

        $this->get('/promo')->assertOk()->assertSee('Live promo', false);
    }

    public function test_flag_on_but_draft_falls_back_to_404(): void
    {
        config(['ukv.cms.enabled' => true]);
        Page::create(['slug' => 'promo', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'draft', 'blocks' => [['type' => 'rich-text', 'data' => ['body' => '<p>x</p>']]]]);

        $this->get('/promo')->assertNotFound();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CmsResolverTest`
Expected: FAIL (no route / controller).

- [ ] **Step 3: Write the PageRenderer**

`app/Services/PageRenderer.php`:

```php
<?php
declare(strict_types=1);

namespace App\Services;

use App\Cms\BlockRegistry;
use App\Models\Page;
use Illuminate\Support\Facades\View;

class PageRenderer
{
    public function __construct(private readonly BlockRegistry $registry) {}

    /** Render a page's block stack to HTML. Unknown block types are skipped, not fatal. */
    public function render(Page $page): string
    {
        $html = '';
        foreach ($page->blocks ?? [] as $block) {
            $view = $this->registry->view($block['type'] ?? '');
            if ($view === null) {
                continue;
            }
            $html .= View::make($view, ['data' => $block['data'] ?? []])->render();
        }

        return $html;
    }
}
```

- [ ] **Step 4: Write the CMS page shell view**

`resources/views/cms/page.blade.php`:

```blade
{{-- CMS page shell. Wraps rendered blocks in the SAME site chrome coded pages use. --}}
@extends('layouts.app', ['title' => $page->seo_title ?: $page->title, 'metaDescription' => $page->seo_description])

@section('content')
    {!! $rendered !!}
@endsection
```

> If the coded pages do not use `layouts.app`, match whatever master layout `resources/views/public/home.blade.php` extends (open it and copy the `@extends(...)` line + section name). The block HTML must sit inside the identical header/footer shell.

- [ ] **Step 5: Write the CmsController**

`app/Http/Controllers/CmsController.php`:

```php
<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageRenderer;
use Illuminate\Http\Response;

class CmsController extends Controller
{
    public function show(string $slug, PageRenderer $renderer): Response
    {
        // Global kill switch: when off, the CMS never owns any slug.
        abort_unless((bool) config('ukv.cms.enabled'), 404);

        $page = Page::where('slug', $slug)->first();
        abort_unless($page && $page->isPublishedCms(), 404);

        return response()->view('cms.page', [
            'page' => $page,
            'rendered' => $renderer->render($page),
        ]);
    }
}
```

- [ ] **Step 6: Register the catch route LAST in `routes/web.php`**

At the very end of `routes/web.php` (after every existing route, including the guides `{destination}/{topic}` routes and the tracker), add:

```php
// --- CMS catch-all (LAST). Only fires for slugs no coded route claimed, and only when
// UKV_CMS_ENABLED is on and the page is a published cms page. Otherwise 404 -> nothing changes.
Route::get('/{slug}', [\App\Http\Controllers\CmsController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('cms.show');
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --filter=CmsResolverTest`
Expected: PASS (3 tests).

- [ ] **Step 8: Regression — existing pages unaffected**

Run: `php artisan test --filter=AboutTeamTest`
Expected: PASS (the `/about` coded route still wins over the catch-all).

- [ ] **Step 9: Commit**

```bash
git add app/Services/PageRenderer.php app/Http/Controllers/CmsController.php resources/views/cms/page.blade.php routes/web.php tests/Feature/Cms/CmsResolverTest.php
git commit -m "feat(cms): page renderer + resolver route with coded fallback"
```

---

### Task 5: Full-page cache on published render + bust on save

**Files:**
- Modify: `app/Http/Controllers/CmsController.php` (wrap render in cache)
- Create: `app/Models/Page.php` observer hook (bust cache) — add a `booted()` method
- Test: `tests/Feature/Cms/CmsCacheTest.php`

**Interfaces:**
- Produces: cache key `cms:page:{slug}` storing rendered HTML; cleared whenever a `Page` saves or deletes.

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class CmsCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_busts_cache(): void
    {
        config(['ukv.cms.enabled' => true]);
        $p = Page::create(['slug' => 'promo', 'title' => 'Promo', 'mode' => 'cms', 'status' => 'published', 'blocks' => [['type' => 'rich-text', 'data' => ['body' => '<p>One</p>']]]]);

        $this->get('/promo')->assertSee('One', false);
        $this->assertTrue(Cache::has('cms:page:promo'));

        $p->update(['blocks' => [['type' => 'rich-text', 'data' => ['body' => '<p>Two</p>']]]]);
        $this->assertFalse(Cache::has('cms:page:promo'));
        $this->get('/promo')->assertSee('Two', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CmsCacheTest`
Expected: FAIL (no caching / no bust).

- [ ] **Step 3: Cache the render in the controller**

Replace the `return` in `CmsController::show()`:

```php
        $html = Cache::remember('cms:page:'.$page->slug, now()->addHours(6), fn () => $renderer->render($page));

        return response()->view('cms.page', ['page' => $page, 'rendered' => $html]);
```

Add `use Illuminate\Support\Facades\Cache;` at the top.

- [ ] **Step 4: Bust cache on Page save/delete**

In `app/Models/Page.php`, add:

```php
    protected static function booted(): void
    {
        $bust = fn (Page $page) => Cache::forget('cms:page:'.$page->slug);
        static::saved($bust);
        static::deleted($bust);
    }
```

Add `use Illuminate\Support\Facades\Cache;` at the top of the model.

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=CmsCacheTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/CmsController.php app/Models/Page.php tests/Feature/Cms/CmsCacheTest.php
git commit -m "feat(cms): full-page render cache + bust on save"
```

---

### Task 6: Filament PageResource (Builder field) + Editor policy

**Files:**
- Create: `app/Filament/Resources/PageResource.php`
- Create: `app/Filament/Resources/PageResource/Pages/ListPages.php`
- Create: `app/Filament/Resources/PageResource/Pages/CreatePage.php`
- Create: `app/Filament/Resources/PageResource/Pages/EditPage.php`
- Create: `app/Policies/PagePolicy.php`
- Modify: `app/Providers/AuthServiceProvider.php` (register policy) — or use Laravel auto-discovery if the app relies on it (check for an existing `$policies` array first)
- Test: `tests/Feature/Cms/PageResourceAccessTest.php`

**Interfaces:**
- Consumes: `BlockRegistry::builderBlocks()`.
- Produces: `/admin/pages` resource in the "Content" nav group; `PagePolicy` allowing `Admin` + `Editor`, denying others.

- [ ] **Step 1: Write the failing access test**

```php
<?php
declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PageResourceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_list_pages(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $this->actingAs($editor)->get('/admin/pages')->assertOk();
    }

    public function test_editor_cannot_reach_orders(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $this->actingAs($editor)->get('/admin/orders')->assertForbidden();
    }
}
```

> If `/admin/orders` returns 403 vs 404 differs by Filament version; assert `assertForbidden()` OR `assertNotFound()` — pick whichever the existing Viewer-denial tests use (see `tests/Feature/AdminPanelSmokeTest.php`) and match it.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PageResourceAccessTest`
Expected: FAIL (no PageResource / policy).

- [ ] **Step 3: Write the PagePolicy**

`app/Policies/PagePolicy.php`:

```php
<?php
declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Editor], true);
    }

    public function view(User $user): bool { return $this->viewAny($user); }
    public function create(User $user): bool { return $this->viewAny($user); }
    public function update(User $user): bool { return $this->viewAny($user); }
    public function delete(User $user): bool { return $user->role === UserRole::Admin; }
}
```

Register it (if the app uses an explicit map in `app/Providers/AuthServiceProvider.php`):

```php
        \App\Models\Page::class => \App\Policies\PagePolicy::class,
```

- [ ] **Step 4: Write the PageResource**

`app/Filament/Resources/PageResource.php` (follow `GuideResource` structure; key parts):

```php
<?php
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Cms\BlockRegistry;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)
                ->helperText('Lowercase letters, numbers, and hyphens only.'),
            Forms\Components\Select::make('mode')
                ->options(['coded' => 'Coded (theme file)', 'cms' => 'CMS blocks'])
                ->default('coded')->required()
                ->helperText('Coded keeps the existing theme page. CMS renders the blocks below.'),
            Forms\Components\Select::make('status')
                ->options(['draft' => 'Draft', 'published' => 'Published'])->default('draft')->required(),
            Forms\Components\Builder::make('blocks')
                ->blocks(app(BlockRegistry::class)->builderBlocks())
                ->collapsible()->cloneable()->blockNumbers(false)
                ->columnSpanFull(),
            Forms\Components\Fieldset::make('SEO')->schema([
                Forms\Components\TextInput::make('seo_title'),
                Forms\Components\Textarea::make('seo_description')->rows(2),
                Forms\Components\Toggle::make('noindex'),
                Forms\Components\Toggle::make('in_sitemap')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('slug')->searchable(),
            Tables\Columns\BadgeColumn::make('mode'),
            Tables\Columns\BadgeColumn::make('status'),
            Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
```

Create the three Page classes (standard Filament stubs):

`app/Filament/Resources/PageResource/Pages/ListPages.php`:

```php
<?php
declare(strict_types=1);

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
```

`CreatePage.php`:

```php
<?php
declare(strict_types=1);

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;
}
```

`EditPage.php`:

```php
<?php
declare(strict_types=1);

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=PageResourceAccessTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Filament/Resources/PageResource.php app/Filament/Resources/PageResource app/Policies/PagePolicy.php app/Providers/AuthServiceProvider.php tests/Feature/Cms/PageResourceAccessTest.php
git commit -m "feat(cms): Filament Pages resource (Builder field) + Editor policy"
```

---

### Task 7: Revision on publish + revert

**Files:**
- Create: `app/Services/PagePublisher.php`
- Modify: `app/Filament/Resources/PageResource/Pages/EditPage.php` (snapshot on save; add Revert action)
- Test: `tests/Feature/Cms/PageRevisionTest.php`

**Interfaces:**
- Produces: `PagePublisher::snapshot(Page $page, ?int $editorId): PageRevision`; `PagePublisher::revertTo(Page $page, PageRevision $rev): void` (copies revision blocks/title/seo back onto the page and saves).

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Models\Page;
use App\Services\PagePublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PageRevisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_then_revert_restores_blocks(): void
    {
        $page = Page::create(['slug' => 'p', 'title' => 'P', 'mode' => 'cms', 'status' => 'published', 'blocks' => [['type' => 'rich-text', 'data' => ['body' => 'v1']]]]);
        $pub = app(PagePublisher::class);

        $rev = $pub->snapshot($page, null);
        $page->update(['blocks' => [['type' => 'rich-text', 'data' => ['body' => 'v2']]]]);
        $this->assertSame('v2', $page->fresh()->blocks[0]['data']['body']);

        $pub->revertTo($page->fresh(), $rev);
        $this->assertSame('v1', $page->fresh()->blocks[0]['data']['body']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PageRevisionTest`
Expected: FAIL (no `PagePublisher`).

- [ ] **Step 3: Write the PagePublisher**

`app/Services/PagePublisher.php`:

```php
<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Page;
use App\Models\PageRevision;

class PagePublisher
{
    /** Snapshot the page's current content, keeping only the latest 10 revisions. */
    public function snapshot(Page $page, ?int $editorId): PageRevision
    {
        $rev = $page->revisions()->create([
            'title' => $page->title,
            'blocks' => $page->blocks,
            'seo_title' => $page->seo_title,
            'seo_description' => $page->seo_description,
            'og_image' => $page->og_image,
            'editor_id' => $editorId,
        ]);

        $page->revisions()->latest()->skip(10)->take(PHP_INT_MAX)->get()->each->delete();

        return $rev;
    }

    public function revertTo(Page $page, PageRevision $rev): void
    {
        $page->update([
            'title' => $rev->title,
            'blocks' => $rev->blocks,
            'seo_title' => $rev->seo_title,
            'seo_description' => $rev->seo_description,
            'og_image' => $rev->og_image,
        ]);
    }
}
```

- [ ] **Step 4: Snapshot on save in EditPage**

In `EditPage.php`, add a snapshot before each save:

```php
    protected function beforeSave(): void
    {
        if ($this->record->exists) {
            app(\App\Services\PagePublisher::class)->snapshot($this->record, auth()->id());
        }
    }
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=PageRevisionTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Services/PagePublisher.php app/Filament/Resources/PageResource/Pages/EditPage.php tests/Feature/Cms/PageRevisionTest.php
git commit -m "feat(cms): page revisions on save + revert"
```

---

### Task 8: Phase gate — full suite + public smoke (flag off)

**Files:** none (verification task)

- [ ] **Step 1: Run the whole suite**

Run: `php artisan test`
Expected: PASS (all existing + new CMS tests).

- [ ] **Step 2: Confirm flag is off by default**

Run: `php artisan tinker --execute="var_dump(config('ukv.cms.enabled'));"`
Expected: `bool(false)`.

- [ ] **Step 3: Public smoke — existing pages unchanged**

Start server and screenshot `/`, `/about`, `/schengen-visa` (use the existing automation/*.cjs Playwright harness). Confirm they render exactly as before (flag off → coded views own every slug).

- [ ] **Step 4: Commit any smoke fixtures / notes, then tag the phase**

```bash
git commit --allow-empty -m "chore(cms): Phase 1 (Foundation) complete — flag off, no public change"
```

---

## Self-Review

**Spec coverage (Phase 1 items):**
- `UKV_CMS_ENABLED` flag → Task 1 ✅
- Per-page `mode` (coded/cms) → Task 2 (`isPublishedCms`) + Task 4 (resolver) ✅
- pages + page_revisions tables → Task 2 ✅
- BlockRegistry (extension point) → Task 3 ✅
- Renderer + resolver + coded fallback → Task 4 ✅
- Full-page cache + bust → Task 5 ✅
- Editor role + policy (deny orders/payments) → Task 1 + Task 6 ✅
- Revisions + revert → Task 7 ✅
- Phase gate: suite + flag-off smoke → Task 8 ✅
- media, site_settings tables → **deferred to Phase 4 plan** (spec lists them under Settings/Media). Not required for Foundation. Noted, not a gap.

**Deferred to later phase plans (not this plan):** golden-master screenshot diff + Lighthouse gate (Phase 2, Home pilot — they need a coded page to diff against); media pipeline, site settings, nav builder, per-page SEO into `<head>`/sitemap (Phase 4); global blocks, templates, revision UI, layout toggle (Phase 5).

**Placeholder scan:** none — every step has real code/commands.

**Type consistency:** `isPublishedCms()`, `BlockRegistry::view()/all()/builderBlocks()`, `PageRenderer::render()`, `PagePublisher::snapshot()/revertTo()` consistent across tasks.

---

## Notes for Phase 2 (Home pilot) — next plan

Phase 2 gets its own plan and adds the real theme blocks (hero, trust-band, steps, destination-grid, quote, faq, cta-band, image, locked-component), the golden-master screenshot-diff gate (coded `/` vs cms `/`), the Lighthouse CWV no-regression gate, and inverts the Home route so `/` can serve the cms Home when published (with coded fallback preserved). Do NOT start Phase 2 until this Foundation plan is merged and green.
