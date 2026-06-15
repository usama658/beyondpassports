<?php

namespace App\Filament\Resources;

use App\Enums\GuideType;
use App\Filament\Concerns\AuthorizesByRole;
use App\Filament\Resources\GuideResource\Pages;
use App\Models\Guide;
use App\Services\GuideContentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class GuideResource extends Resource
{
    use AuthorizesByRole;

    protected static ?string $model = Guide::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $recordTitleAttribute = 'title';

    /** Publish gate: a guide body must be at least this many characters (no thin content). */
    public const MIN_BODY_LENGTH = 600;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Targeting')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('destination_id')
                            ->label('Destination')
                            ->relationship('destination', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Leave empty for an evergreen (non-country) guide.'),
                        Forms\Components\Select::make('guide_type')
                            ->label('Guide type')
                            ->options(self::typeOptions())
                            ->searchable()
                            ->native(false)
                            ->helperText('The SEO silo "spoke". Leave empty for evergreen guides.'),
                    ]),

                Forms\Components\Section::make('Identity & SEO')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(200)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set): void {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(220)
                            ->unique(ignoreRecord: true)
                            ->helperText('Public URL key.'),
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta title')
                            ->maxLength(70)
                            ->helperText('SEO <title>. Falls back to the title if empty.'),
                        Forms\Components\TextInput::make('meta_description')
                            ->label('Meta description')
                            ->maxLength(180)
                            ->helperText('SEO meta description. Falls back to the excerpt.'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sort order')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->helperText('Lower numbers appear first in the hub.'),
                    ]),

                Forms\Components\Section::make('Answer & summary')
                    ->schema([
                        Forms\Components\Textarea::make('quick_answer')
                            ->label('Quick answer')
                            ->rows(3)
                            ->maxLength(600)
                            ->helperText('Featured-snippet answer block. Required to publish.')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('excerpt')
                            ->label('Excerpt')
                            ->rows(2)
                            ->required()
                            ->maxLength(400)
                            ->helperText('Card summary + meta-description fallback.')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Body')
                    ->schema([
                        Forms\Components\RichEditor::make('body')
                            ->label('Body')
                            ->toolbarButtons([
                                'bold', 'italic', 'link', 'bulletList', 'orderedList',
                                'h2', 'h3', 'blockquote', 'undo', 'redo',
                            ])
                            ->helperText('Long-form HTML. Use the "AI draft" row action to generate from destination facts, then verify every figure against the official source before publishing.')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('FAQ (drives FAQPage schema)')
                    ->schema([
                        Forms\Components\Repeater::make('faq')
                            ->label('FAQ items')
                            ->schema([
                                Forms\Components\TextInput::make('q')
                                    ->label('Question')
                                    ->required()
                                    ->maxLength(300),
                                Forms\Components\Textarea::make('a')
                                    ->label('Answer')
                                    ->required()
                                    ->rows(3)
                                    ->maxLength(1200),
                            ])
                            ->columns(1)
                            ->addActionLabel('Add FAQ')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['q'] ?? null)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Review & status')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->default('draft')
                            ->native(false)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Status changes only via the Publish action (enforces the publish gate).'),
                        Forms\Components\TextInput::make('reviewed_by')
                            ->label('Reviewed by')
                            ->maxLength(160)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('E-E-A-T byline — set when published.'),
                        Forms\Components\Placeholder::make('reviewed_at')
                            ->label('Reviewed at')
                            ->content(fn (?Guide $record): string => $record?->reviewed_at?->format('d M Y, H:i') ?? '—'),
                        Forms\Components\Placeholder::make('published_at')
                            ->label('Published at')
                            ->content(fn (?Guide $record): string => $record?->published_at?->format('d M Y, H:i') ?? '—'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(48)
                    ->wrap(),
                Tables\Columns\TextColumn::make('destination.name')
                    ->label('Destination')
                    ->placeholder('Evergreen')
                    ->sortable(),
                Tables\Columns\TextColumn::make('guide_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof GuideType ? $state->label() : (string) ($state ?? '—'))
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reviewed_by')
                    ->label('Reviewer')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('destination_id')
                    ->label('Destination')
                    ->relationship('destination', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('guide_type')
                    ->label('Type')
                    ->options(self::typeOptions()),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('aiDraft')
                    ->label('AI draft')
                    ->icon('heroicon-o-sparkles')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('AI-draft this guide body')
                    ->modalDescription('Generates an HTML body from the destination\'s structured facts only (no customer data). The result is a DRAFT you must verify against the official source before publishing.')
                    // Only country guides have a destination + type to draft facts from.
                    ->visible(fn (Guide $record): bool => ! self::isViewer()
                        && $record->destination_id !== null
                        && $record->guide_type instanceof GuideType)
                    ->action(function (Guide $record): void {
                        $service = app(GuideContentService::class);
                        $facts = $service->factsFor($record->destination, $record->guide_type);
                        $body = app(\App\Services\AiService::class)->draftGuide($facts, $record->guide_type->value);

                        if ($body === '') {
                            Notification::make()
                                ->title('AI draft unavailable')
                                ->body('No draft was produced (AI is not configured or the request failed). The body was left unchanged.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $flags = $service->flagUnsourcedFacts($body, $facts);

                        $record->body = $body;
                        $record->status = 'draft';
                        $record->save();

                        if ($flags !== []) {
                            Notification::make()
                                ->title('Draft generated — review flagged figures')
                                ->body(count($flags).' figure(s) in the draft were not found in the source facts: '.implode(' | ', $flags))
                                ->warning()
                                ->persistent()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Draft generated')
                            ->body('A draft body was written from destination facts. Verify it against the official source, then publish.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Guide $record): bool => ! self::isViewer() && $record->status !== 'published')
                    ->modalHeading('Publish guide')
                    ->modalDescription('Publishing makes this guide live. The factuality gate below must pass and you must confirm you have verified every figure against the official source.')
                    ->form([
                        Forms\Components\TextInput::make('reviewed_by')
                            ->label('Reviewed by (your name — shown as the E-E-A-T byline)')
                            ->required()
                            ->maxLength(160)
                            ->default(fn (): string => (string) (auth()->user()?->name ?? '')),
                        Forms\Components\Checkbox::make('facts_verified')
                            ->label('I have verified every figure (fees, timescales, dates, document list) in this guide against the official government / issuing-authority source.')
                            ->accepted()
                            ->required(),
                    ])
                    ->action(function (Guide $record, array $data): void {
                        $errors = self::publishGateErrors($record, $data);

                        if ($errors !== []) {
                            Notification::make()
                                ->title('Publish blocked')
                                ->body(implode(' ', $errors))
                                ->danger()
                                ->persistent()
                                ->send();

                            return;
                        }

                        $record->status = 'published';
                        $record->reviewed_by = trim((string) $data['reviewed_by']);
                        $record->reviewed_at = Carbon::now();
                        $record->published_at ??= Carbon::now();
                        $record->save();

                        Notification::make()
                            ->title('Guide published')
                            ->body("\"{$record->title}\" is now live, reviewed by {$record->reviewed_by}.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    /**
     * The PUBLISH GATE. Returns a list of human-readable reasons the guide may NOT be published;
     * an empty list means the gate passes. Enforced server-side in the Publish action so the rules
     * hold regardless of the modal form.
     *
     * Rules:
     *   1. Body present and at least MIN_BODY_LENGTH characters of real text (no thin content).
     *   2. Quick answer present (the featured-snippet block).
     *   3. Title and meta_title (when set) are unique across other guides (no duplicate SEO targets).
     *   4. "Facts verified" confirmation ticked AND a reviewer name supplied (the human verify gate /
     *      E-E-A-T byline).
     *
     * @param  array<string, mixed>  $data  The Publish modal form data.
     * @return list<string>
     */
    public static function publishGateErrors(Guide $record, array $data): array
    {
        $errors = [];

        $bodyText = trim(strip_tags((string) $record->body));
        if (mb_strlen($bodyText) < self::MIN_BODY_LENGTH) {
            $errors[] = 'Body is too short — it needs at least '.self::MIN_BODY_LENGTH.' characters of content (currently '.mb_strlen($bodyText).').';
        }

        if (trim((string) $record->quick_answer) === '') {
            $errors[] = 'A quick answer is required before publishing.';
        }

        if (Guide::query()->whereKeyNot($record->getKey())->where('title', $record->title)->exists()) {
            $errors[] = 'Another guide already uses this title — titles must be unique.';
        }

        $metaTitle = trim((string) $record->meta_title);
        if ($metaTitle !== '' && Guide::query()->whereKeyNot($record->getKey())->where('meta_title', $metaTitle)->exists()) {
            $errors[] = 'Another guide already uses this meta title — meta titles must be unique.';
        }

        if (empty($data['facts_verified'])) {
            $errors[] = 'You must confirm the facts have been verified against the official source.';
        }

        if (trim((string) ($data['reviewed_by'] ?? '')) === '') {
            $errors[] = 'A reviewer name is required for the E-E-A-T byline.';
        }

        return $errors;
    }

    /**
     * GuideType options for selects/filters.
     *
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        $options = [];
        foreach (GuideType::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuides::route('/'),
            'create' => Pages\CreateGuide::route('/create'),
            'edit' => Pages\EditGuide::route('/{record}/edit'),
        ];
    }
}
