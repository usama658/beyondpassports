<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\EligibilityLane;
use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\OrderTier;
use App\Enums\ResidencyStatus;
use App\Enums\TripPurpose;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\OrderService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Orders';

    protected static ?string $recordTitleAttribute = 'order_ref';

    /**
     * Build {value => 'Label'} option arrays from a backed enum's cases.
     * These enums are plain backed enums (no Filament HasLabel/HasColor), so labels
     * and colours are derived here.
     *
     * @param  class-string<\BackedEnum>  $enum
     * @return array<string, string>
     */
    protected static function enumOptions(string $enum): array
    {
        $options = [];
        foreach ($enum::cases() as $case) {
            $options[$case->value] = str($case->name)->headline()->toString();
        }

        return $options;
    }

    /** Badge colour for an order status. */
    protected static function statusColor(?OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::Paid => 'info',
            OrderStatus::AwaitingDocs, OrderStatus::DocReview => 'warning',
            OrderStatus::Submitted, OrderStatus::AwaitingDecision => 'primary',
            OrderStatus::Delivered, OrderStatus::Won => 'success',
            OrderStatus::Rejected => 'danger',
            OrderStatus::Refunded => 'gray',
            default => 'gray',
        };
    }

    /** Badge colour for an eligibility lane. */
    protected static function laneColor(?EligibilityLane $lane): string
    {
        return match ($lane) {
            EligibilityLane::Standard, EligibilityLane::Cleared => 'success',
            EligibilityLane::ManualReview => 'warning',
            EligibilityLane::Referred => 'danger',
            default => 'gray',
        };
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Customer')
                ->columns(2)
                ->schema([
                    TextInput::make('order_ref')
                        ->label('Order ref')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Auto-generated on create'),
                    TextInput::make('name')
                        ->label('Name')
                        ->maxLength(255),
                    TextInput::make('applicant_name')
                        ->label('Applicant name')
                        ->maxLength(255),
                    TextInput::make('guardian_name')
                        ->label('Guardian name')
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('passport_number')
                        ->label('Passport number')
                        ->maxLength(255),
                    TextInput::make('hubspot_deal_id')
                        ->label('HubSpot deal ID')
                        ->maxLength(255),
                ]),

            Section::make('Trip & Eligibility')
                ->columns(2)
                ->schema([
                    TextInput::make('destination_name')
                        ->label('Destination')
                        ->maxLength(255),
                    TextInput::make('destination_id')
                        ->label('Destination ID')
                        ->numeric()
                        ->helperText('FK to destinations; set automatically from intake.'),
                    TextInput::make('nationality')
                        ->label('Nationality')
                        ->maxLength(255),
                    TextInput::make('residence_country')
                        ->label('Residence country')
                        ->maxLength(255),
                    Select::make('residency_status')
                        ->label('Residency status')
                        ->options(self::enumOptions(ResidencyStatus::class)),
                    Select::make('trip_purpose')
                        ->label('Trip purpose')
                        ->options(self::enumOptions(TripPurpose::class)),
                    TextInput::make('visa_entries')
                        ->label('Visa entries')
                        ->maxLength(255),
                    TextInput::make('dual_nationality')
                        ->label('Dual nationality')
                        ->maxLength(255),
                    DatePicker::make('travel_date')
                        ->label('Travel date'),
                    DatePicker::make('passport_expiry')
                        ->label('Passport expiry'),
                    DatePicker::make('residency_visa_expiry')
                        ->label('Residency visa expiry'),
                    Toggle::make('is_minor')->label('Is minor')->inline(false),
                    Toggle::make('prior_refusal')->label('Prior refusal')->inline(false),
                    Toggle::make('insurance_required')->label('Insurance required')->inline(false),
                    Toggle::make('risk_flag')->label('Risk flag')->inline(false),
                ]),

            Section::make('Eligibility lane')
                ->columns(2)
                ->description('Manual clear/refer control. Standard & manual_review are computed; cleared/referred are agent-set.')
                ->schema([
                    Select::make('eligibility')
                        ->label('Eligibility lane')
                        ->options(self::enumOptions(EligibilityLane::class)),
                    Textarea::make('eligibility_note')
                        ->label('Eligibility note')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Section::make('Status & Tier')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options(self::enumOptions(OrderStatus::class))
                        ->helperText('Use the "Advance stage" table action to move through gated transitions.'),
                    Select::make('status_last')
                        ->label('Previous status')
                        ->options(self::enumOptions(OrderStatus::class))
                        ->disabled()
                        ->dehydrated(false),
                    Select::make('tier')
                        ->label('Tier')
                        ->options(self::enumOptions(OrderTier::class)),
                    Select::make('priority')
                        ->label('Priority')
                        ->options(self::enumOptions(OrderPriority::class))
                        ->default(OrderPriority::Normal->value),
                    TextInput::make('next_action')
                        ->label('Next action')
                        ->maxLength(255),
                    DatePicker::make('next_due')
                        ->label('Next due'),
                ]),

            Section::make('Fees')
                ->columns(3)
                ->schema([
                    TextInput::make('service_fee')
                        ->label('Service fee')
                        ->numeric()
                        ->prefix('£'),
                    TextInput::make('govt_fee')
                        ->label('Government fee')
                        ->numeric()
                        ->prefix('£'),
                    TextInput::make('total')
                        ->label('Total')
                        ->numeric()
                        ->prefix('£'),
                ]),

            Section::make('Government submission')
                ->columns(2)
                ->schema([
                    TextInput::make('govt_ref')
                        ->label('Government reference')
                        ->maxLength(255),
                    Toggle::make('govt_fee_paid')
                        ->label('Government fee paid')
                        ->inline(false)
                        ->live(),
                    DatePicker::make('govt_fee_paid_at')
                        ->label('Government fee paid at')
                        ->visible(fn (Get $get): bool => (bool) $get('govt_fee_paid')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_ref')
                    ->label('Ref')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn (Order $record): ?string => $record->email)
                    ->toggleable(),
                TextColumn::make('destination_name')
                    ->label('Destination')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (?OrderStatus $state): string => $state
                        ? str($state->name)->headline()->toString()
                        : '—')
                    ->color(fn (?OrderStatus $state): string => self::statusColor($state))
                    ->sortable(),
                BadgeColumn::make('eligibility')
                    ->label('Lane')
                    ->formatStateUsing(fn (?EligibilityLane $state): string => $state
                        ? str($state->name)->headline()->toString()
                        : '—')
                    ->color(fn (?EligibilityLane $state): string => self::laneColor($state)),
                TextColumn::make('tier')
                    ->label('Tier')
                    ->badge()
                    ->formatStateUsing(fn (?OrderTier $state): string => $state
                        ? str($state->name)->headline()->toString()
                        : '—')
                    ->toggleable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('GBP')
                    ->sortable(),
                TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->formatStateUsing(fn (?OrderPriority $state): string => $state
                        ? str($state->name)->headline()->toString()
                        : '—')
                    ->color(fn (?OrderPriority $state): string => match ($state) {
                        OrderPriority::Urgent => 'danger',
                        OrderPriority::High => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(self::enumOptions(OrderStatus::class)),
                SelectFilter::make('eligibility')
                    ->label('Eligibility lane')
                    ->options(self::enumOptions(EligibilityLane::class)),
                SelectFilter::make('destination_name')
                    ->label('Destination')
                    ->options(fn (): array => Order::query()
                        ->whereNotNull('destination_name')
                        ->distinct()
                        ->orderBy('destination_name')
                        ->pluck('destination_name', 'destination_name')
                        ->all()),
            ])
            ->actions([
                Action::make('advanceStage')
                    ->label('Advance stage')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('primary')
                    ->visible(fn (Order $record): bool => self::nextStatuses($record) !== [])
                    ->form(fn (Order $record): array => [
                        Select::make('to')
                            ->label('Move to status')
                            ->options(self::nextStatuses($record))
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        try {
                            app(OrderService::class)->transition(
                                $record,
                                OrderStatus::from($data['to']),
                            );

                            Notification::make()
                                ->title('Stage advanced')
                                ->body("Order {$record->order_ref} moved to ".$data['to'].'.')
                                ->success()
                                ->send();
                        } catch (\DomainException $e) {
                            Notification::make()
                                ->title('Transition blocked')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('clearOrRefer')
                    ->label('Clear / Refer')
                    ->icon('heroicon-o-shield-check')
                    ->color('warning')
                    ->visible(fn (Order $record): bool => $record->eligibility === EligibilityLane::ManualReview)
                    ->form([
                        Select::make('lane')
                            ->label('Decision')
                            ->options([
                                EligibilityLane::Cleared->value => 'Clear (allow into pipeline)',
                                EligibilityLane::Referred->value => 'Refer (block)',
                            ])
                            ->required(),
                        Textarea::make('eligibility_note')
                            ->label('Note')
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->eligibility = EligibilityLane::from($data['lane']);
                        $record->eligibility_note = $data['eligibility_note'];
                        $record->save();

                        Notification::make()
                            ->title('Eligibility updated')
                            ->body("Order {$record->order_ref} set to ".$data['lane'].'.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    /**
     * The legal next statuses for an order, mirroring OrderService::ALLOWED adjacency.
     *
     * @return array<string, string>
     */
    protected static function nextStatuses(Order $record): array
    {
        $allowed = [
            OrderStatus::Paid->value => [OrderStatus::AwaitingDocs, OrderStatus::Refunded],
            OrderStatus::AwaitingDocs->value => [OrderStatus::DocReview, OrderStatus::Refunded],
            OrderStatus::DocReview->value => [OrderStatus::Submitted, OrderStatus::Refunded],
            OrderStatus::Submitted->value => [OrderStatus::AwaitingDecision, OrderStatus::Refunded],
            OrderStatus::AwaitingDecision->value => [OrderStatus::Delivered, OrderStatus::Rejected, OrderStatus::Refunded],
            OrderStatus::Delivered->value => [OrderStatus::Won, OrderStatus::Rejected, OrderStatus::Refunded],
            OrderStatus::Won->value => [],
            OrderStatus::Rejected->value => [],
            OrderStatus::Refunded->value => [],
        ];

        $current = $record->status instanceof OrderStatus
            ? $record->status->value
            : (string) $record->status;

        $options = [];
        foreach ($allowed[$current] ?? [] as $status) {
            $options[$status->value] = str($status->name)->headline()->toString();
        }

        return $options;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
