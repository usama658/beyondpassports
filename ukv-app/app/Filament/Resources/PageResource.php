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
            Forms\Components\Select::make('layout')
                ->options(collect(\App\Models\Page::LAYOUTS)->keys()
                    ->mapWithKeys(fn ($k) => [$k => ucfirst($k).' layout'])->all())
                ->default('public')->required()
                ->helperText('Which site layout this page renders inside (cms mode only).'),
            Forms\Components\Select::make('status')
                ->options(['draft' => 'Draft', 'published' => 'Published'])->default('draft')->required(),
            Forms\Components\Builder::make('blocks')
                ->blocks(app(BlockRegistry::class)->builderBlocks())
                ->collapsible()->cloneable()->blockNumbers(false)
                ->columnSpanFull(),
            Forms\Components\Fieldset::make('SEO')->schema([
                Forms\Components\TextInput::make('seo_title'),
                Forms\Components\Textarea::make('seo_description')->rows(2),
                Forms\Components\TextInput::make('og_image')->label('Social share image URL')
                    ->helperText('Full URL or a /assets path. Shown when the page is shared.'),
                Forms\Components\Toggle::make('noindex')->helperText('Keep this page out of Google.'),
                Forms\Components\Toggle::make('in_sitemap')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('slug')->searchable(),
            Tables\Columns\TextColumn::make('mode')->badge(),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('duplicate')
                ->label('Duplicate')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Duplicate as a draft')
                ->modalDescription('Creates a cms-mode draft copy you can edit and publish later. The original is untouched.')
                ->action(function (\App\Models\Page $record): void {
                    $copy = $record->duplicateAsDraft();
                    \Filament\Notifications\Notification::make()
                        ->title('Duplicated to "'.$copy->title.'"')->success()->send();
                }),
        ]);
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
