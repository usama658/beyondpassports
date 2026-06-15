<?php

namespace App\Filament\Resources;

use App\Enums\RejectionReason;
use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Resources\RejectionResource\Pages;
use App\Models\Rejection;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RejectionResource extends Resource
{
    use AuthorizesByRole;

    protected static ?string $model = Rejection::class;

    protected static ?string $navigationIcon = 'heroicon-o-x-circle';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Rejection')
                    ->columns(2)
                    ->schema([
                        Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'order_ref')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('reason')
                            ->label('Reason')
                            ->options(RejectionReason::class)
                            ->required(),
                        DateTimePicker::make('recorded_at')
                            ->label('Recorded at')
                            ->seconds(false)
                            ->default(now())
                            ->required(),
                        Textarea::make('note')
                            ->label('Detail')
                            ->rows(4)
                            ->columnSpanFull(),
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
                TextColumn::make('reason')
                    ->label('Reason')
                    ->badge(),
                TextColumn::make('note')
                    ->label('Detail')
                    ->limit(60)
                    ->toggleable(),
                TextColumn::make('recorded_at')
                    ->label('Recorded at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('reason')
                    ->label('Reason')
                    ->options(RejectionReason::class),
            ])
            ->defaultSort('recorded_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRejections::route('/'),
            'create' => Pages\CreateRejection::route('/create'),
            'edit' => Pages\EditRejection::route('/{record}/edit'),
        ];
    }
}
