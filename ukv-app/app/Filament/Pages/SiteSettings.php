<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Models\Setting;
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
        ]);
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
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        Setting::put('announcement_enabled', ! empty($state['announcement_enabled']) ? '1' : '0');
        Setting::put('announcement_text', (string) ($state['announcement_text'] ?? ''));
        Setting::put('announcement_link', (string) ($state['announcement_link'] ?? ''));

        Notification::make()->title('Site settings saved')->success()->send();
    }
}
