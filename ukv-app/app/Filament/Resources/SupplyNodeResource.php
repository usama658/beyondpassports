<?php

namespace App\Filament\Resources;

use App\Enums\SupplyNodeType;
use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Resources\SupplyNodeResource\Pages;
use App\Models\SupplyNode;
use App\Services\PostcodeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
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

                Forms\Components\Section::make('Location & geo')
                    ->description('Used by the nearest-centre finder. Enter a postcode then "Geocode from postcode" to fill lat/lng, or set them manually.')
                    ->columns(2)
                    ->headerActions([
                        Forms\Components\Actions\Action::make('geocode')
                            ->label('Geocode from postcode')
                            ->icon('heroicon-o-map-pin')
                            ->action(function (Forms\Get $get, Forms\Set $set): void {
                                $postcode = $get('postcode');

                                if (blank($postcode)) {
                                    Notification::make()
                                        ->title('Enter a postcode first')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                $geo = app(PostcodeService::class)->lookup($postcode);

                                if ($geo === null) {
                                    Notification::make()
                                        ->title('Geocode failed')
                                        ->body('No coordinates found for that postcode.')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $set('lat', $geo['lat']);
                                $set('lng', $geo['lng']);

                                Notification::make()
                                    ->title('Coordinates filled')
                                    ->body('Lat/lng set from postcode.')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('postcode')
                            ->maxLength(16),
                        Forms\Components\Toggle::make('we_book_here')
                            ->label('UKVisaCo books appointments here')
                            ->inline(false),
                        Forms\Components\TextInput::make('lat')
                            ->label('Latitude')
                            ->numeric()
                            ->step('any'),
                        Forms\Components\TextInput::make('lng')
                            ->label('Longitude')
                            ->numeric()
                            ->step('any'),
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
                Tables\Columns\IconColumn::make('we_book_here')
                    ->label('We book here')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('located')
                    ->label('Geo')
                    ->boolean()
                    ->tooltip('Has latitude & longitude')
                    ->state(fn (SupplyNode $record): bool => $record->lat !== null && $record->lng !== null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(SupplyNodeType::class),
                Tables\Filters\TernaryFilter::make('is_global')
                    ->label('Global'),
                Tables\Filters\TernaryFilter::make('we_book_here')
                    ->label('We book here'),
            ])
            ->actions([
                Tables\Actions\Action::make('geocode')
                    ->label('Geocode from postcode')
                    ->icon('heroicon-o-map-pin')
                    ->visible(fn (SupplyNode $record): bool => ! self::isViewer() && filled($record->postcode))
                    ->action(function (SupplyNode $record): void {
                        $geo = app(PostcodeService::class)->lookup($record->postcode);

                        if ($geo === null) {
                            Notification::make()
                                ->title('Geocode failed')
                                ->body('No coordinates found for that postcode.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->update([
                            'lat' => $geo['lat'],
                            'lng' => $geo['lng'],
                        ]);

                        Notification::make()
                            ->title('Coordinates updated')
                            ->body('Lat/lng set from postcode.')
                            ->success()
                            ->send();
                    }),
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
