<?php

namespace App\Filament\Resources;

use App\Enums\ClientUpdateChannel;
use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Resources\ClientUpdateResource\Pages;
use App\Models\ClientUpdate;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClientUpdateResource extends Resource
{
    use AuthorizesByRole;

    protected static ?string $model = ClientUpdate::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $modelLabel = 'Client update';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('order_id')
                    ->label('Order')
                    ->relationship('order', 'order_ref')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('barrier_id')
                    ->label('Barrier')
                    ->relationship('barrier', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('The barrier this update relates to (required).'),
                Select::make('channel')
                    ->label('Channel')
                    ->options(ClientUpdateChannel::class)
                    ->required()
                    ->default(ClientUpdateChannel::Email),
                TextInput::make('subject')
                    ->label('Subject')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('body')
                    ->label('Message body')
                    ->rows(6)
                    ->columnSpanFull(),
                DateTimePicker::make('sent_at')
                    ->label('Sent at')
                    ->helperText('Leave blank if drafted only (not yet sent).'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_ref')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('channel')
                    ->label('Channel'),
                TextColumn::make('body')
                    ->label('Excerpt')
                    ->limit(60)
                    ->placeholder('—'),
                TextColumn::make('sent_at')
                    ->label('Sent')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Draft'),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->label('Channel')
                    ->options(ClientUpdateChannel::class),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientUpdates::route('/'),
            'create' => Pages\CreateClientUpdate::route('/create'),
            'edit' => Pages\EditClientUpdate::route('/{record}/edit'),
        ];
    }
}
