<?php

namespace App\Filament\Resources;

use App\Enums\AppointmentStatus;
use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppointmentResource extends Resource
{
    use AuthorizesByRole;

    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Operations';

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
                TextInput::make('centre')
                    ->label('Centre / location')
                    ->maxLength(160),
                TextInput::make('reference')
                    ->label('Reference')
                    ->maxLength(120),
                DatePicker::make('scheduled_at')
                    ->label('Scheduled date'),
                Select::make('status')
                    ->label('Status')
                    ->options(AppointmentStatus::class)
                    ->required()
                    ->default(AppointmentStatus::NotRequired),
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
                TextColumn::make('centre')
                    ->label('Centre')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('scheduled_at')
                    ->label('Scheduled')
                    ->date()
                    ->sortable()
                    ->placeholder('—'),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'not_required',
                        'warning' => 'to_book',
                        'info' => 'booked',
                        'primary' => 'attended',
                        'success' => 'completed',
                    ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(AppointmentStatus::class),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
