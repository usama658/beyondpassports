<?php

namespace App\Filament\Resources;

use App\Enums\SupplyNodeType;
use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Resources\SupplyNodeResource\Pages;
use App\Models\SupplyNode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SupplyNodeResource extends Resource
{
    use AuthorizesByRole;

    protected static ?string $model = SupplyNode::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identity')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(160)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Get $get, Forms\Set $set): void {
                                if ($operation === 'create') {
                                    $type = $get('type') ?: 'node';
                                    $set('node_key', Str::slug($type . '-' . $state));
                                }
                            }),
                        Forms\Components\Select::make('type')
                            ->required()
                            ->options(SupplyNodeType::class)
                            ->native(false),
                        Forms\Components\TextInput::make('node_key')
                            ->required()
                            ->maxLength(80)
                            ->unique(ignoreRecord: true)
                            ->helperText('Deterministic key (type-slug). Auto-filled from name on create.'),
                        Forms\Components\Toggle::make('is_global')
                            ->label('Global (serves all destinations)')
                            ->inline(false),
                    ]),

                Forms\Components\Section::make('Contact & service')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('contact')
                            ->label('Contact (URL / phone)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sla')
                            ->label('SLA')
                            ->maxLength(160),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_global')
                    ->label('Global')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(SupplyNodeType::class),
                Tables\Filters\TernaryFilter::make('is_global')
                    ->label('Global'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupplyNodes::route('/'),
            'create' => Pages\CreateSupplyNode::route('/create'),
            'edit' => Pages\EditSupplyNode::route('/{record}/edit'),
        ];
    }
}
