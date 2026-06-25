<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Resources\CentreAvailabilityResource\Pages;
use App\Models\CentreAvailability;
use App\Models\SupplyNode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Per-row editing of CentreAvailability snapshots — the fix-up surface.
 *
 * Ops normally drive availability through the bulk "Update availability" page; this
 * resource is for one-off corrections. The status badge and "stale?" column read the
 * model's own status()/isStale() so the list mirrors the public board's honesty rules.
 * Saving (create or edit) stamps source='manual', confirmed_at=now, expires_at=now+FRESHNESS
 * via mutateFormDataBeforeSave/Create so a hand-edited row gets a fresh freshness window.
 */
class CentreAvailabilityResource extends Resource
{
    use AuthorizesByRole;

    protected static ?string $model = CentreAvailability::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Centre availability';

    protected static ?string $recordTitleAttribute = 'supply_node_id';

    /**
     * Band options shared by the form select (none => clears date+band on save).
     *
     * @var array<string, string>
     */
    protected static array $bandOptions = [
        'good' => 'Good',
        'limited' => 'Limited',
        'none' => 'None (reset to "ask")',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Snapshot')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('supply_node_id')
                            ->label('Centre')
                            ->options(fn (): array => SupplyNode::query()
                                ->where('we_book_here', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\DatePicker::make('next_available_on')
                            ->label('Next available on')
                            ->native(false)
                            ->helperText('Leave blank to report "ask".'),
                        Forms\Components\Select::make('band')
                            ->label('Band')
                            ->options(static::$bandOptions)
                            ->default('none')
                            ->native(false)
                            ->required(),
                        Forms\Components\Textarea::make('note')
                            ->label('Note (internal)')
                            ->rows(2)
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('next_available_on')
                    ->label('Next available')
                    ->date()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('band')
                    ->label('Band')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'good' => 'success',
                        'limited' => 'warning',
                        default => 'gray',
                    })
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (CentreAvailability $record): string => $record->status())
                    ->color(fn (string $state): string => match ($state) {
                        'ok' => 'success',
                        'lim' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('stale')
                    ->label('Stale?')
                    ->boolean()
                    ->state(fn (CentreAvailability $record): bool => $record->isStale())
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->tooltip(fn (CentreAvailability $record): string => $record->isStale()
                        ? 'Expired — reports "ask"'
                        : ($record->isExpiring(2) ? 'Expiring within 2 days' : 'Fresh')),
                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('confirmed_at')
                    ->label('Confirmed')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
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
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'manual' => 'Manual',
                        'derived' => 'Derived',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('supply_node_id');
    }

    /**
     * Normalise hand-edited snapshot data: a 'none' band (or blank date) clears both date
     * and band, and every save stamps a fresh manual freshness window. Shared by create + edit.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalise(array $data): array
    {
        $now = now();

        $band = $data['band'] ?? 'none';

        if ($band === 'none' || empty($data['next_available_on'])) {
            $data['next_available_on'] = null;
            $data['band'] = null;
        } else {
            $data['band'] = $band;
        }

        $data['source'] = 'manual';
        $data['confirmed_at'] = $now;
        $data['expires_at'] = $now->copy()->addDays(CentreAvailability::FRESHNESS_DAYS);

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCentreAvailability::route('/'),
            'create' => Pages\CreateCentreAvailability::route('/create'),
            'edit' => Pages\EditCentreAvailability::route('/{record}/edit'),
        ];
    }
}
