<?php

namespace App\Filament\Resources\GuideResource\Pages;

use App\Filament\Resources\GuideResource;
use App\Models\Guide;
use App\Services\GuideContentService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;

class EditGuide extends EditRecord
{
    protected static string $resource = GuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // AI draft from destination facts (country guides only). Mirrors the row action.
            Actions\Action::make('aiDraft')
                ->label('AI draft')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('AI-draft this guide body')
                ->modalDescription('Generates an HTML body from the destination\'s structured facts only (no customer data). The result is a DRAFT you must verify against the official source before publishing.')
                ->visible(fn (): bool => GuideResource::canEdit($this->record)
                    && $this->record->destination_id !== null
                    && $this->record->guide_type !== null)
                ->action(function (): void {
                    /** @var Guide $record */
                    $record = $this->record;
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
                    $this->fillForm();

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

            // Publish gate (mirrors the table action).
            Actions\Action::make('publish')
                ->label('Publish')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn (): bool => GuideResource::canEdit($this->record) && $this->record->status !== 'published')
                ->modalHeading('Publish guide')
                ->modalDescription('Publishing makes this guide live. The factuality gate must pass and you must confirm you have verified every figure against the official source.')
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
                ->action(function (array $data): void {
                    /** @var Guide $record */
                    $record = $this->record;
                    $errors = GuideResource::publishGateErrors($record, $data);

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
                    $this->fillForm();

                    Notification::make()
                        ->title('Guide published')
                        ->body("\"{$record->title}\" is now live, reviewed by {$record->reviewed_by}.")
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
