<?php

namespace App\Filament\Resources;

use App\Enums\DiscountContext;
use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Resources\DiscountResource\Pages;
use App\Models\Discount;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DiscountResource extends Resource
{
    use AuthorizesByRole;

    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Commerce';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Discount')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(48)
                            ->unique(ignoreRecord: true)
                            ->helperText('Unique single-use code, e.g. LOYAL-AB12.'),
                        Select::make('context')
                            ->label('Context')
                            ->options(DiscountContext::class)
                            ->required()
                            ->default(DiscountContext::Code),
                        TextInput::make('amount')
                            ->label('Amount (£ off)')
                            ->numeric()
                            ->prefix('£')
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(190),
                    ]),
                Section::make('Redemption')
                    ->columns(2)
                    ->schema([
                        TextInput::make('order_ref')
                            ->label('Order ref')
                            ->maxLength(32)
                            ->helperText('Snapshot reference (UKV-YYYY-NNNNNN) of the order this was redeemed on. Loose string link, not an FK.'),
                        Toggle::make('used')
                            ->label('Used')
                            ->default(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('context')
                    ->label('Context')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Value')
                    ->money('GBP')
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('order_ref')
                    ->label('Order ref')
                    ->searchable()
                    ->placeholder('—')
                    ->description(fn (Discount $record): ?string => $record->order?->name)
                    ->toggleable(),
                IconColumn::make('used')
                    ->label('Used')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('context')
                    ->label('Context')
                    ->options(DiscountContext::class),
                TernaryFilter::make('used')
                    ->label('Used'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }
}
