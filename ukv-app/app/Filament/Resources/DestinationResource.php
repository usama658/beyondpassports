<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DestinationResource\Pages;
use App\Models\Destination;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class DestinationResource extends Resource
{
    protected static ?string $model = Destination::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

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
                            ->maxLength(120)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set): void {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(140)
                            ->unique(ignoreRecord: true)
                            ->helperText('Join key — must match the WordPress destination slug.'),
                        Forms\Components\Select::make('visa_type')
                            ->label('Type')
                            ->options([
                                'evisa' => 'eVisa',
                                'eta' => 'ETA',
                                'visa-free' => 'Visa-free',
                                'sticker' => 'Sticker / consular',
                            ])
                            ->searchable()
                            ->native(false)
                            ->helperText('Free text in DB; pick a candidate value or type-search.'),
                        Forms\Components\Toggle::make('required_for_uk')
                            ->label('Visa required for UK')
                            ->inline(false),
                    ]),

                Forms\Components\Section::make('Fees (£)')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('govt_fee_gbp')
                            ->label('Government fee')
                            ->numeric()
                            ->prefix('£')
                            ->step('0.01')
                            ->minValue(0),
                        Forms\Components\TextInput::make('tier_standard_gbp')
                            ->label('Standard tier')
                            ->numeric()
                            ->prefix('£')
                            ->step('0.01')
                            ->minValue(0),
                        Forms\Components\TextInput::make('tier_express_gbp')
                            ->label('Express tier')
                            ->numeric()
                            ->prefix('£')
                            ->step('0.01')
                            ->minValue(0),
                        Forms\Components\TextInput::make('tier_premium_gbp')
                            ->label('Premium tier')
                            ->numeric()
                            ->prefix('£')
                            ->step('0.01')
                            ->minValue(0),
                    ]),

                Forms\Components\Section::make('Stay & passport')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('max_stay_days')
                            ->label('Max stay (days)')
                            ->numeric()
                            ->integer()
                            ->minValue(0),
                        Forms\Components\TextInput::make('passport_validity_months')
                            ->label('Passport validity (months)')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(6),
                    ]),

                Forms\Components\Section::make('IDP (International Driving Permit)')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('idp_permit_type')
                            ->label('Permit convention')
                            ->options([
                                '1926' => '1926',
                                '1949' => '1949',
                                '1968' => '1968',
                            ])
                            ->native(false),
                        Forms\Components\Toggle::make('idp_required_photocard')
                            ->label('Requires photocard')
                            ->inline(false),
                        Forms\Components\Toggle::make('idp_required_paper')
                            ->label('Requires paper licence')
                            ->inline(false),
                    ]),

                Forms\Components\Section::make('Required documents')
                    ->schema([
                        Forms\Components\TagsInput::make('required_docs')
                            ->label('Required docs')
                            ->placeholder('Add a document and press Enter')
                            ->helperText('Stored as a JSON list.')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Supply nodes')
                    ->schema([
                        Forms\Components\Select::make('supplyNodes')
                            ->label('Linked supply nodes')
                            ->relationship('supplyNodes', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
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
                Tables\Columns\TextColumn::make('visa_type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('required_for_uk')
                    ->label('UK visa')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tier_standard_gbp')
                    ->label('Standard')
                    ->money('GBP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('govt_fee_gbp')
                    ->label('Govt fee')
                    ->money('GBP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_stay_days')
                    ->label('Validity (days)')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('visa_type')
                    ->label('Type')
                    ->options([
                        'evisa' => 'eVisa',
                        'eta' => 'ETA',
                        'visa-free' => 'Visa-free',
                        'sticker' => 'Sticker / consular',
                    ]),
                Tables\Filters\TernaryFilter::make('required_for_uk')
                    ->label('Requires UK visa'),
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
            'index' => Pages\ListDestinations::route('/'),
            'create' => Pages\CreateDestination::route('/create'),
            'edit' => Pages\EditDestination::route('/{record}/edit'),
        ];
    }
}
