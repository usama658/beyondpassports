<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Resources\DataChangeProposalResource\Pages;
use App\Models\DataChangeProposal;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * "Data changes" inbox (Module C, #138).
 *
 * Read-only review queue of model-flagged differences between a destination's stored facts and its
 * official source. NO create/edit form — proposals are produced by DataChangeService. A human
 * resolves each one with the row Accept / Dismiss actions:
 *
 *   - Accept  : writes proposed_value to the destination's flagged field, bumps the destination's
 *               facts_checked_at (re-stamps freshness — Module B), marks the proposal accepted, and
 *               records the resolver name. NEVER auto-applied — only this explicit action writes.
 *   - Dismiss : marks the proposal dismissed + records the resolver. No data change.
 *
 * Write actions are gated by AuthorizesByRole (Viewers are read-only).
 */
class DataChangeProposalResource extends Resource
{
    use AuthorizesByRole;

    protected static ?string $model = DataChangeProposal::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationLabel = 'Data changes';

    protected static ?string $modelLabel = 'data change proposal';

    /** Badge: count of open proposals awaiting review. */
    public static function getNavigationBadge(): ?string
    {
        $open = static::getModel()::query()
            ->where('status', DataChangeProposal::STATUS_OPEN)
            ->count();

        return $open > 0 ? (string) $open : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('destination.name')
                    ->label('Destination')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('field')
                    ->label('Field')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_value')
                    ->label('Current')
                    ->limit(30)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('proposed_value')
                    ->label('Proposed')
                    ->limit(30)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('model_summary')
                    ->label('Evidence')
                    ->limit(50)
                    ->tooltip(fn (?DataChangeProposal $record): ?string => $record?->model_summary)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('source_url')
                    ->label('Source')
                    ->url(fn (?DataChangeProposal $record): ?string => $record?->source_url)
                    ->openUrlInNewTab()
                    ->limit(24)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => DataChangeProposal::STATUS_OPEN,
                        'success' => DataChangeProposal::STATUS_ACCEPTED,
                        'gray' => DataChangeProposal::STATUS_DISMISSED,
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Detected')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('resolved_by')
                    ->label('Resolved by')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        DataChangeProposal::STATUS_OPEN => 'Open',
                        DataChangeProposal::STATUS_ACCEPTED => 'Accepted',
                        DataChangeProposal::STATUS_DISMISSED => 'Dismissed',
                    ])
                    ->default(DataChangeProposal::STATUS_OPEN),
                Tables\Filters\SelectFilter::make('destination_id')
                    ->label('Destination')
                    ->relationship('destination', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Action::make('accept')
                    ->label('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Accept proposed change')
                    ->modalDescription('This writes the proposed value to the destination and re-stamps its review date. It cannot be undone automatically.')
                    ->visible(fn (DataChangeProposal $record): bool => $record->status === DataChangeProposal::STATUS_OPEN && ! static::isViewer())
                    ->action(fn (DataChangeProposal $record) => static::accept($record)),
                Action::make('dismiss')
                    ->label('Dismiss')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (DataChangeProposal $record): bool => $record->status === DataChangeProposal::STATUS_OPEN && ! static::isViewer())
                    ->action(fn (DataChangeProposal $record) => static::dismiss($record)),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Apply a proposal: write proposed_value -> destination field, bump facts_checked_at,
     * mark accepted, record the resolver. The SOLE place a proposal mutates a destination.
     */
    protected static function accept(DataChangeProposal $record): void
    {
        $destination = $record->destination;

        if ($destination !== null) {
            // Only write a field we recognise as fillable to avoid setting an unintended attribute.
            if (in_array($record->field, $destination->getFillable(), true)) {
                $destination->setAttribute($record->field, $record->proposed_value);
            }
            $destination->facts_checked_at = Carbon::now();
            $destination->save();
        }

        $record->update([
            'status' => DataChangeProposal::STATUS_ACCEPTED,
            'resolved_by' => static::resolverName(),
            'resolved_at' => Carbon::now(),
        ]);
    }

    /**
     * Close a proposal without changing any destination data.
     */
    protected static function dismiss(DataChangeProposal $record): void
    {
        $record->update([
            'status' => DataChangeProposal::STATUS_DISMISSED,
            'resolved_by' => static::resolverName(),
            'resolved_at' => Carbon::now(),
        ]);
    }

    /** Resolver name for the audit trail (E-E-A-T). */
    protected static function resolverName(): string
    {
        $user = auth()->user();

        return trim((string) ($user?->name ?? $user?->email ?? 'unknown'));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('destination');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataChangeProposals::route('/'),
        ];
    }
}
