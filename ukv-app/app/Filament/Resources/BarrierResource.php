<?php

namespace App\Filament\Resources;

use App\Enums\BarrierDetectedBy;
use App\Enums\BarrierNature;
use App\Enums\BarrierScope;
use App\Enums\BarrierStatus;
use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Resources\BarrierResource\Pages;
use App\Models\Barrier;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BarrierResource extends Resource
{
    use \App\Filament\Concerns\HiddenFromEditor;

    use AuthorizesByRole;

    protected static ?string $model = Barrier::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

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
                    ->helperText('Required for case-scoped barriers; leave blank for destination/all scope.'),
                TextInput::make('title')
                    ->label('Title')
                    ->maxLength(190),
                Select::make('nature')
                    ->label('Nature')
                    ->options(BarrierNature::class)
                    ->required()
                    ->default(BarrierNature::Temporary),
                Select::make('scope')
                    ->label('Scope')
                    ->options(BarrierScope::class)
                    ->required()
                    ->default(BarrierScope::Case_),
                Select::make('status')
                    ->label('Status')
                    ->options(BarrierStatus::class)
                    ->required()
                    ->default(BarrierStatus::Open),
                Select::make('detected_by')
                    ->label('Detected by')
                    ->options(BarrierDetectedBy::class)
                    ->required()
                    ->default(BarrierDetectedBy::Agent),
                TextInput::make('rule_key')
                    ->label('Rule key')
                    ->maxLength(80)
                    ->helperText('Idempotency key for auto-detected barriers.'),
                Textarea::make('guidance')
                    ->label('Guidance')
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_ref')
                    ->label('Order')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('title')
                    ->label('Title')
                    ->limit(40)
                    ->searchable(),
                BadgeColumn::make('nature')
                    ->label('Nature'),
                TextColumn::make('scope')
                    ->label('Scope')
                    ->badge(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'open',
                        'success' => 'resolved',
                    ]),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(BarrierStatus::class),
                SelectFilter::make('nature')
                    ->label('Nature')
                    ->options(BarrierNature::class),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarriers::route('/'),
            'create' => Pages\CreateBarrier::route('/create'),
            'edit' => Pages\EditBarrier::route('/{record}/edit'),
        ];
    }
}
