<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Support\NavService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Site settings — curated, theme-safe globals the CMS Editor can change (announcement bar for now).
 * Reads/writes the Setting key/value store. In the Content group; Editor + Admin only.
 */
class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Site settings';

    protected static ?string $title = 'Site settings';

    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, [UserRole::Admin, UserRole::Editor], true);
    }

    public function mount(): void
    {
        $this->form->fill([
            'announcement_enabled' => Setting::get('announcement_enabled') === '1',
            'announcement_text' => Setting::get('announcement_text'),
            'announcement_link' => Setting::get('announcement_link'),
            // Prefill the nav editors with the LIVE menus (saved override, or the coded default) so
            // the team edits from what's actually on the site rather than a blank slate.
            'nav_primary' => $this->linkRows(NavService::primary()),
            'nav_ctas' => $this->linkRows(NavService::ctas()),
            'nav_footer' => array_map(fn ($col) => [
                'heading' => (string) ($col['heading'] ?? ''),
                'links' => $this->linkRows($col['links'] ?? []),
            ], NavService::footerColumns()),
        ]);
    }

    /** @param array<int,array> $links */
    private function linkRows(array $links): array
    {
        return array_map(fn ($l) => [
            'label' => (string) ($l['label'] ?? ''),
            'url' => (string) ($l['url'] ?? ''),
        ], $links);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Announcement bar')
                    ->description('A thin bar at the very top of every public page. Hidden unless enabled with a message.')
                    ->schema([
                        Toggle::make('announcement_enabled')->label('Show the announcement bar'),
                        TextInput::make('announcement_text')->label('Message')->maxLength(160),
                        TextInput::make('announcement_link')->label('Link (optional)')->url()
                            ->helperText('Full URL, e.g. https://beyondpassports.co.uk/services'),
                    ]),
                Section::make('Header navigation')
                    ->description('The links in the top menu. Structural items (logo, phone, WhatsApp, styling) stay fixed. Leave a section empty to keep the built-in defaults.')
                    ->schema([
                        Repeater::make('nav_primary')->label('Primary links')
                            ->schema($this->linkFields())
                            ->reorderable()->collapsible()->itemLabel(fn (array $state) => $state['label'] ?? null),
                        Repeater::make('nav_ctas')->label('Buttons')
                            ->schema($this->linkFields())
                            ->reorderable(false)->addable(false)->deletable(false)
                            ->helperText('The two header buttons. You can change the wording and link; the styling and the eligibility button behaviour stay fixed.'),
                    ])->collapsed(),
                Section::make('Footer navigation')
                    ->description('The footer link columns. The brand column (logo, newsletter, social, address) stays fixed.')
                    ->schema([
                        Repeater::make('nav_footer')->label('Columns')
                            ->schema([
                                TextInput::make('heading')->required(),
                                Repeater::make('links')->schema($this->linkFields())
                                    ->reorderable()->collapsible()->itemLabel(fn (array $state) => $state['label'] ?? null),
                            ])
                            ->reorderable()->collapsible()->itemLabel(fn (array $state) => $state['heading'] ?? null),
                    ])->collapsed(),
            ])
            ->statePath('data');
    }

    /** Shared label + url fields for every nav row. */
    private function linkFields(): array
    {
        return [
            TextInput::make('label')->required(),
            TextInput::make('url')->required()
                ->helperText('A path like /schengen-visa or a full https:// URL.'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();
        Setting::put('announcement_enabled', ! empty($state['announcement_enabled']) ? '1' : '0');
        Setting::put('announcement_text', (string) ($state['announcement_text'] ?? ''));
        Setting::put('announcement_link', (string) ($state['announcement_link'] ?? ''));

        // Store nav overrides as JSON (label + url only; NavService re-applies structural styling).
        Setting::put('nav_primary', json_encode(array_values($state['nav_primary'] ?? [])));
        Setting::put('nav_ctas', json_encode(array_values($state['nav_ctas'] ?? [])));
        Setting::put('nav_footer', json_encode(array_values(array_map(fn ($col) => [
            'heading' => (string) ($col['heading'] ?? ''),
            'links' => array_values($col['links'] ?? []),
        ], $state['nav_footer'] ?? []))));

        Notification::make()->title('Site settings saved')->success()->send();
    }
}
