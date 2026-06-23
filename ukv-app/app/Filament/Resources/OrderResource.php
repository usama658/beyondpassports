<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\EligibilityLane;
use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\OrderTier;
use App\Enums\ResidencyStatus;
use App\Enums\TripPurpose;
use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\EmailService;
use App\Services\LoyaltyService;
use App\Services\OrderService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    use AuthorizesByRole;

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

    /** Human labels for the fraud-guard reason codes stored in orders.risk_reason. */
    protected static function riskReasonLabel(string $code): string
    {
        return match ($code) {
            'velocity' => 'High order velocity (same email/IP)',
            'duplicate' => 'Duplicate recent order (same email + destination)',
            'disposable_email' => 'Disposable email domain',
            'email_pattern' => 'Suspicious email pattern',
            'prior_refusal' => 'Declared prior visa refusal',
            'contradictory_fields' => 'Contradictory intake fields',
            'missing_contact' => 'Missing contact details',
            default => str($code)->headline()->toString(),
        };
    }

    /** Render an order's stored risk reasons as a readable, comma-separated list. */
    protected static function riskReasonLabels(Order $record): string
    {
        $reasons = $record->risk_reason;

        if (! is_array($reasons) || $reasons === []) {
            return $record->risk_flag ? 'Flagged (no reasons recorded)' : 'Not flagged';
        }

        return collect($reasons)
            ->map(fn (string $code): string => self::riskReasonLabel($code))
            ->implode(', ');
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
                    TextInput::make('phone')
                        ->label('Phone')
                        ->tel()
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

            Section::make('Risk review')
                ->columns(2)
                ->description('Advisory fraud guard (#128). Set automatically on apply — never blocks the customer. Clear the flag once reviewed.')
                ->schema([
                    Toggle::make('risk_flag')
                        ->label('Flagged for review')
                        ->inline(false),
                    TextInput::make('risk_score')
                        ->label('Risk score')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(false),
                    Placeholder::make('risk_reason_display')
                        ->label('Reasons')
                        ->columnSpanFull()
                        ->content(fn (?Order $record): string => $record !== null
                            ? self::riskReasonLabels($record)
                            : '—'),
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
                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                BadgeColumn::make('risk_flag')
                    ->label('Risk')
                    ->formatStateUsing(fn (bool $state, Order $record): string => $state
                        ? 'Flagged'.($record->risk_score ? " ({$record->risk_score})" : '')
                        : 'OK')
                    ->color(fn (bool $state): string => $state ? 'danger' : 'gray')
                    ->icon(fn (bool $state): ?string => $state ? 'heroicon-o-flag' : null)
                    ->tooltip(fn (Order $record): ?string => $record->risk_flag
                        ? self::riskReasonLabels($record)
                        : null)
                    ->sortable(),
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
                Filter::make('risk_flag')
                    ->label('Flagged for review')
                    ->query(fn (Builder $query): Builder => $query->where('risk_flag', true))
                    ->toggle(),
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

                Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-o-banknotes')
                    ->color('danger')
                    ->modalHeading('Process refund')
                    ->modalDescription(fn (Order $record): string => 'Record a refund for order '
                        .$record->order_ref.' and move it to the refunded stage.')
                    // Only show where a refund transition is actually legal (not already closed
                    // to a non-refundable terminal state). OrderService::refund() enforces the
                    // gate; this just hides a dead button.
                    ->visible(fn (Order $record): bool => ! in_array(
                        $record->status,
                        [OrderStatus::Won, OrderStatus::Rejected, OrderStatus::Refunded],
                        true,
                    ))
                    ->form([
                        TextInput::make('amount')
                            ->label('Refund amount')
                            ->numeric()
                            ->prefix('£')
                            ->required()
                            ->default(fn (Order $record): ?float => $record->service_fee !== null
                                ? (float) $record->service_fee
                                : null),
                        Textarea::make('reason')
                            ->label('Reason')
                            ->rows(3),
                    ])
                    ->action(function (Order $record, array $data): void {
                        try {
                            app(OrderService::class)->refund(
                                $record,
                                (float) $data['amount'],
                                $data['reason'] ?? null,
                            );

                            Notification::make()
                                ->title('Refund recorded')
                                ->body("Order {$record->order_ref} refunded £"
                                    .number_format((float) $data['amount'], 2).'.')
                                ->success()
                                ->send();
                        } catch (\DomainException $e) {
                            Notification::make()
                                ->title('Refund blocked')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('issueReviewIncentive')
                    ->label('Issue review-incentive code')
                    ->icon('heroicon-o-gift')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Issue review-incentive code')
                    ->modalDescription(fn (Order $record): string => 'Mint a next-order discount code tied to '
                        .($record->email ?: 'this order').'. Sent with the review-request email.')
                    ->action(function (Order $record): void {
                        $discount = app(LoyaltyService::class)->issueReviewIncentive($record);

                        Notification::make()
                            ->title('Review-incentive code issued')
                            ->body("Code {$discount->code} (£".number_format((float) $discount->amount, 2).' off the next order).')
                            ->success()
                            ->send();
                    }),

                Action::make('resendReviewRequest')
                    ->label('Resend review request')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Resend review request')
                    ->modalDescription(fn (Order $record): string => 'Email the review request to '
                        .($record->email ?: 'this order')
                        .' and BCC the Trustpilot invite alias. Only send to genuine customers who used the service.')
                    ->visible(fn (Order $record): bool => ! empty($record->email))
                    ->action(function (Order $record): void {
                        $sent = app(EmailService::class)->sendReviewRequest($record);
                        Notification::make()
                            ->title($sent ? 'Review request sent' : 'Review request not sent')
                            ->body($sent
                                ? "Emailed {$record->email} (Trustpilot invite BCC'd)."
                                : 'Send was skipped or failed — check mail config and logs.')
                            ->{$sent ? 'success' : 'danger'}()
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
        // NOTE: Refunded is intentionally excluded from every adjacency here (audit H-2).
        // Refunds must go through the dedicated "Refund" action so the refund amount/reason
        // are recorded via OrderService::refund(); the generic advance-stage path no longer
        // offers it.
        $allowed = [
            OrderStatus::Paid->value => [OrderStatus::AwaitingDocs],
            OrderStatus::AwaitingDocs->value => [OrderStatus::DocReview],
            OrderStatus::DocReview->value => [OrderStatus::Submitted],
            OrderStatus::Submitted->value => [OrderStatus::AwaitingDecision],
            OrderStatus::AwaitingDecision->value => [OrderStatus::Delivered, OrderStatus::Rejected],
            OrderStatus::Delivered->value => [OrderStatus::Won, OrderStatus::Rejected],
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
