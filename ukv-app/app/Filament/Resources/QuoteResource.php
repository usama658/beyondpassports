<?php

namespace App\Filament\Resources;

use App\Enums\QuoteStatus;
use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Resources\QuoteResource\Pages;
use App\Models\Quote;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuoteResource extends Resource
{
    use \App\Filament\Concerns\HiddenFromEditor;

    use AuthorizesByRole;

    protected static ?string $model = Quote::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Commerce';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Quote')
                    ->columns(2)
                    ->schema([
                        Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'order_ref')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('amount')
                            ->label('Amount (£)')
                            ->numeric()
                            ->prefix('£')
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options(QuoteStatus::class)
                            ->required()
                            ->default(QuoteStatus::None),
                        DateTimePicker::make('sent_at')
                            ->label('Sent at')
                            ->seconds(false),
                        TextInput::make('payment_link')
                            ->label('Payment link')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->helperText('Stripe Payment Link sent to the customer.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_ref')
                    ->label('Order ref')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('GBP')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('sent_at')
                    ->label('Sent at')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(QuoteStatus::class),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'edit' => Pages\EditQuote::route('/{record}/edit'),
        ];
    }
}
