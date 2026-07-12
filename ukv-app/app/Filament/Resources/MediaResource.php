<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Media library. Upload an image once here, then reference it from any image block (pick by name).
 * Editing the file or alt updates every place it's used.
 */
class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Media library';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('path')
                ->label('Image')
                ->image()
                ->disk('public')
                ->directory('cms')
                ->maxSize(3072)
                ->imageEditor()
                ->required(),
            Forms\Components\TextInput::make('name')->label('Label')
                ->helperText('A name so the team can find this image, e.g. "Team photo 2026".'),
            Forms\Components\TextInput::make('alt')->label('Default alt text')->maxLength(160)
                ->helperText('Describes the image for screen readers; used unless a block overrides it.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('path')->label('Preview')->disk('public')->height(48),
            Tables\Columns\TextColumn::make('name')->label('Label')->searchable(),
            Tables\Columns\TextColumn::make('alt')->limit(40)->toggleable(),
            Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
