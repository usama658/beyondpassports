<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Resources\CentreSlotResource\Pages;
use App\Models\CentreSlot;
use App\Models\SupplyNode;
use Carbon\CarbonImmutable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CentreSlotResource extends Resource
{
    use \App\Filament\Concerns\HiddenFromEditor;

    use AuthorizesByRole;

    protected static ?string $model = CentreSlot::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $recordTitleAttribute = 'slot_at';

    /**
     * Status options shared by the form select and table filter.
     *
     * @var array<string, string>
     */
    protected static array $statusOptions = [
        'available' => 'Available',
        'held' => 'Held',
        'booked' => 'Booked',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Slot')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('supply_node_id')
                            ->label('Centre')
                            ->relationship('supplyNode', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DateTimePicker::make('slot_at')
                            ->label('Slot at')
                            ->seconds(false)
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(static::$statusOptions)
                            ->default('available')
                            ->native(false)
                            ->live()
                            ->required(),
                        Forms\Components\DateTimePicker::make('hold_expires_at')
                            ->label('Hold expires at')
                            ->seconds(false)
                            ->visible(fn (Forms\Get $get): bool => $get('status') === 'held'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplyNode.name')
                    ->label('Centre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slot_at')
                    ->label('Slot at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'held' => 'warning',
                        'booked' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('hold_expires_at')
                    ->label('Hold expires')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supply_node_id')
                    ->label('Centre')
                    ->relationship('supplyNode', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(static::$statusOptions),
                Tables\Filters\Filter::make('slot_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('slot_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('slot_at', '<=', $date));
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('bulkAdd')
                    ->label('Bulk add slots')
                    ->icon('heroicon-o-calendar-days')
                    ->visible(fn (): bool => static::canCreate())
                    ->form([
                        Forms\Components\Select::make('supply_node_id')
                            ->label('Centre')
                            ->options(fn (): array => SupplyNode::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->required(),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start date')
                            ->default(now())
                            ->required(),
                        Forms\Components\TimePicker::make('time')
                            ->label('Time of day')
                            ->seconds(false)
                            ->default('09:00')
                            ->required(),
                        Forms\Components\TextInput::make('days')
                            ->label('Number of daily slots')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(90)
                            ->default(5)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $start = CarbonImmutable::parse($data['start_date'] . ' ' . $data['time']);
                        $count = (int) $data['days'];

                        $rows = [];
                        for ($i = 0; $i < $count; $i++) {
                            $rows[] = [
                                'supply_node_id' => $data['supply_node_id'],
                                'slot_at' => $start->addDays($i),
                                'status' => 'available',
                            ];
                        }

                        foreach ($rows as $row) {
                            CentreSlot::create($row);
                        }

                        Notification::make()
                            ->title("Added {$count} daily slots")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('slot_at');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCentreSlots::route('/'),
            'create' => Pages\CreateCentreSlot::route('/create'),
            'edit' => Pages\EditCentreSlot::route('/{record}/edit'),
        ];
    }
}
