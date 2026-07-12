<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Resources\DocumentRequirementResource\Pages;
use App\Models\DocumentRequirement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentRequirementResource extends Resource
{
    use \App\Filament\Concerns\HiddenFromEditor;

    use AuthorizesByRole;

    protected static ?string $model = DocumentRequirement::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationLabel = 'Document rules';

    protected static ?string $modelLabel = 'document requirement';

    protected static ?string $recordTitleAttribute = 'label';

    /**
     * Multi-select condition keys: form field name => option list.
     * Stored under the matching key inside `conditions` only when non-empty.
     *
     * @var array<string, array<string, string>>
     */
    public const ARRAY_CONDITIONS = [
        'destinations' => [
            'turkey' => 'Turkey',
            'india' => 'India',
            'vietnam' => 'Vietnam',
            'egypt' => 'Egypt',
            'thailand' => 'Thailand',
            'uae' => 'UAE',
            'usa-esta' => 'USA (ESTA)',
            'australia-eta' => 'Australia (ETA)',
        ],
        'trip_purpose' => [
            'tourist' => 'Tourist',
            'business' => 'Business',
            'family_visit' => 'Family visit',
            'transit' => 'Transit',
            'medical' => 'Medical',
            'study' => 'Study',
        ],
        'residency_status' => [
            'citizen' => 'Citizen',
            'permanent' => 'Permanent resident',
            'visa_holder' => 'Visa holder',
            'other' => 'Other',
        ],
        'employment_status' => [
            'employed' => 'Employed',
            'self_employed' => 'Self-employed',
            'student' => 'Student',
            'retired' => 'Retired',
            'unemployed' => 'Unemployed',
            'other' => 'Other',
        ],
        'accommodation_type' => [
            'hotel' => 'Hotel',
            'host' => 'Host',
            'own_property' => 'Own property',
            'other' => 'Other',
        ],
        'funding_source' => [
            'self' => 'Self-funded',
            'sponsored' => 'Sponsored',
        ],
    ];

    /**
     * Tri-state condition keys (Any = not stored, true, false).
     *
     * @var list<string>
     */
    public const TRISTATE_CONDITIONS = [
        'is_minor',
        'prior_refusal',
        'payer_is_applicant',
        'passport_validity_short',
    ];

    /**
     * Numeric condition keys.
     *
     * @var list<string>
     */
    public const NUMERIC_CONDITIONS = [
        'min_stay_days',
        'max_stay_days',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Document')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('document_key')
                            ->label('Document key')
                            ->required()
                            ->maxLength(120)
                            ->helperText('Stable slug, e.g. passport, photo, employer_letter.'),
                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(160)
                            ->helperText('Customer-facing name.'),
                        Forms\Components\Textarea::make('note')
                            ->rows(3)
                            ->maxLength(2000)
                            ->helperText('Optional guidance shown under the document.')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('category')
                            ->required()
                            ->options([
                                'identity' => 'Identity',
                                'funding' => 'Funding',
                                'logistics' => 'Logistics',
                                'health' => 'Health',
                                'core' => 'Core',
                            ])
                            ->native(false)
                            ->default('core'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sort order')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->helperText('Lower numbers appear first.'),
                        Forms\Components\Toggle::make('mandatory')
                            ->label('Mandatory')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Off = recommended.'),
                        Forms\Components\Toggle::make('active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),
                    ]),

                Forms\Components\Section::make('Applies when (leave empty = always)')
                    ->description('All filled conditions must match (AND); within a multi-select, any value matches (OR). Empty fields and "Any" are ignored.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('cond_destinations')
                            ->label('Destinations')
                            ->multiple()
                            ->options(self::ARRAY_CONDITIONS['destinations'])
                            ->native(false),
                        Forms\Components\Select::make('cond_trip_purpose')
                            ->label('Trip purpose')
                            ->multiple()
                            ->options(self::ARRAY_CONDITIONS['trip_purpose'])
                            ->native(false),
                        Forms\Components\Select::make('cond_residency_status')
                            ->label('Residency status')
                            ->multiple()
                            ->options(self::ARRAY_CONDITIONS['residency_status'])
                            ->native(false),
                        Forms\Components\Select::make('cond_employment_status')
                            ->label('Employment status')
                            ->multiple()
                            ->options(self::ARRAY_CONDITIONS['employment_status'])
                            ->native(false),
                        Forms\Components\Select::make('cond_accommodation_type')
                            ->label('Accommodation type')
                            ->multiple()
                            ->options(self::ARRAY_CONDITIONS['accommodation_type'])
                            ->native(false),
                        Forms\Components\Select::make('cond_funding_source')
                            ->label('Funding source')
                            ->multiple()
                            ->options(self::ARRAY_CONDITIONS['funding_source'])
                            ->native(false),
                        Forms\Components\Select::make('cond_is_minor')
                            ->label('Is minor')
                            ->options(['1' => 'Yes', '0' => 'No'])
                            ->placeholder('Any')
                            ->native(false),
                        Forms\Components\Select::make('cond_prior_refusal')
                            ->label('Prior refusal')
                            ->options(['1' => 'Yes', '0' => 'No'])
                            ->placeholder('Any')
                            ->native(false),
                        Forms\Components\Select::make('cond_payer_is_applicant')
                            ->label('Payer is applicant')
                            ->options(['1' => 'Yes', '0' => 'No'])
                            ->placeholder('Any')
                            ->native(false),
                        Forms\Components\Select::make('cond_passport_validity_short')
                            ->label('Passport validity short')
                            ->options(['1' => 'Yes', '0' => 'No'])
                            ->placeholder('Any')
                            ->native(false),
                        Forms\Components\TextInput::make('cond_min_stay_days')
                            ->label('Min stay (days)')
                            ->numeric()
                            ->integer()
                            ->minValue(0),
                        Forms\Components\TextInput::make('cond_max_stay_days')
                            ->label('Max stay (days)')
                            ->numeric()
                            ->integer()
                            ->minValue(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_key')
                    ->label('Key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('mandatory')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('active'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'identity' => 'Identity',
                        'funding' => 'Funding',
                        'logistics' => 'Logistics',
                        'health' => 'Health',
                        'core' => 'Core',
                    ]),
                Tables\Filters\TernaryFilter::make('active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    /**
     * Flatten a stored `conditions` array onto the prefixed `cond_*` form fields.
     * Used by the Edit page when filling the form.
     *
     * @param  array<string, mixed>  $conditions
     * @return array<string, mixed>
     */
    public static function conditionsToFormFields(?array $conditions): array
    {
        $conditions ??= [];
        $fields = [];

        foreach (array_keys(self::ARRAY_CONDITIONS) as $key) {
            $fields['cond_' . $key] = $conditions[$key] ?? [];
        }

        foreach (self::TRISTATE_CONDITIONS as $key) {
            $fields['cond_' . $key] = array_key_exists($key, $conditions)
                ? ($conditions[$key] ? '1' : '0')
                : null;
        }

        foreach (self::NUMERIC_CONDITIONS as $key) {
            $fields['cond_' . $key] = $conditions[$key] ?? null;
        }

        return $fields;
    }

    /**
     * Collapse the prefixed `cond_*` form fields back into a `conditions` array,
     * dropping empty arrays and "Any" tri-states, then strip the helper fields.
     * Used by both Create and Edit pages before persisting.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function formFieldsToConditions(array $data): array
    {
        $conditions = [];

        foreach (array_keys(self::ARRAY_CONDITIONS) as $key) {
            $value = $data['cond_' . $key] ?? [];
            if (is_array($value) && $value !== []) {
                $conditions[$key] = array_values($value);
            }
            unset($data['cond_' . $key]);
        }

        foreach (self::TRISTATE_CONDITIONS as $key) {
            $value = $data['cond_' . $key] ?? null;
            if ($value === '1' || $value === '0') {
                $conditions[$key] = $value === '1';
            }
            unset($data['cond_' . $key]);
        }

        foreach (self::NUMERIC_CONDITIONS as $key) {
            $value = $data['cond_' . $key] ?? null;
            if ($value !== null && $value !== '') {
                $conditions[$key] = (int) $value;
            }
            unset($data['cond_' . $key]);
        }

        $data['conditions'] = $conditions;

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentRequirements::route('/'),
            'create' => Pages\CreateDocumentRequirement::route('/create'),
            'edit' => Pages\EditDocumentRequirement::route('/{record}/edit'),
        ];
    }
}
