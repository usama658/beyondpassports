<?php

namespace App\Filament\Resources;

use App\Enums\FeedbackSource;
use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Resources\FeedbackResource\Pages;
use App\Models\Feedback;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FeedbackResource extends Resource
{
    use AuthorizesByRole;

    protected static ?string $model = Feedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'feedback';

    protected static ?string $pluralModelLabel = 'feedback';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Feedback')
                    ->columns(2)
                    ->schema([
                        Select::make('source')
                            ->label('Source')
                            ->options(FeedbackSource::class)
                            ->required()
                            ->default(FeedbackSource::ReviewRequest),
                        Select::make('order_id')
                            ->label('Related order')
                            ->relationship('order', 'order_ref')
                            ->searchable()
                            ->preload()
                            ->placeholder('— none —'),
                        TextInput::make('rating')
                            ->label('Rating (1-5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5),
                        Toggle::make('consented')
                            ->label('Story consent')
                            ->default(false),
                        Textarea::make('comment')
                            ->label('Body')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source')
                    ->label('Source')
                    ->badge(),
                TextColumn::make('comment')
                    ->label('Excerpt')
                    ->limit(70)
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('rating')
                    ->label('Rating')
                    ->numeric()
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('order.order_ref')
                    ->label('Order ref')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('consented')
                    ->label('Consent')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->label('Source')
                    ->options(FeedbackSource::class),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedback::route('/'),
            'create' => Pages\CreateFeedback::route('/create'),
            'edit' => Pages\EditFeedback::route('/{record}/edit'),
        ];
    }
}
