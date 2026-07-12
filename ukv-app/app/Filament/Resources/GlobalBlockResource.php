<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Cms\BlockRegistry;
use App\Filament\Resources\GlobalBlockResource\Pages;
use App\Models\GlobalBlock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Manage reusable blocks. Pick a block type, fill its fields; the fields swap reactively to the
 * chosen type's schema (reusing the exact same block schemas as the page builder). Editing a record
 * updates every page that references it (GlobalBlock busts page caches on save).
 */
class GlobalBlockResource extends Resource
{
    protected static ?string $model = GlobalBlock::class;

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationIcon = 'heroicon-o-square-2-stack';

    protected static ?string $navigationLabel = 'Reusable blocks';

    public static function form(Form $form): Form
    {
        $registry = app(BlockRegistry::class);
        $typeOptions = collect(BlockRegistry::GLOBAL_ALLOWED)
            ->mapWithKeys(fn (string $key) => [$key => ($registry->all()[$key])::label()])
            ->all();

        return $form->schema([
            Forms\Components\TextInput::make('name')->required()
                ->helperText('Internal label so the team can find this block, e.g. "Site-wide CTA".'),
            Forms\Components\Select::make('type')
                ->options($typeOptions)
                ->required()
                ->live()
                ->helperText('The kind of section this reusable block renders.'),
            Forms\Components\Group::make()
                ->statePath('data')
                ->schema(fn (Get $get): array => $get('type') ? $registry->schemaFor($get('type')) : [])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('type')->badge(),
            Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGlobalBlocks::route('/'),
            'create' => Pages\CreateGlobalBlock::route('/create'),
            'edit' => Pages\EditGlobalBlock::route('/{record}/edit'),
        ];
    }
}
