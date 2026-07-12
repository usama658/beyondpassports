<?php

declare(strict_types=1);

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\PageRevision;
use App\Services\PagePublisher;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn () => route('cms.preview', $this->record))
                ->openUrlInNewTab(),
            $this->revisionsAction(),
            Actions\DeleteAction::make(),
        ];
    }

    /** Snapshot the current content before overwriting it, so any save can be reverted. */
    protected function beforeSave(): void
    {
        if ($this->record->exists) {
            app(PagePublisher::class)->snapshot($this->record, auth()->id());
        }
    }

    /** Restore the page to any of its recent saved versions (kept by PagePublisher). */
    private function revisionsAction(): Actions\Action
    {
        return Actions\Action::make('revisions')
            ->label('Revisions')
            ->icon('heroicon-o-clock')
            ->color('gray')
            ->modalHeading('Restore a previous version')
            ->modalSubmitActionLabel('Restore')
            ->visible(fn () => $this->record->revisions()->exists())
            ->form([
                Select::make('revision_id')
                    ->label('Version to restore')
                    ->options(fn () => $this->record->revisions()->latest()->get()
                        ->mapWithKeys(fn (PageRevision $r) => [$r->id => $r->created_at->format('D j M Y, H:i')])
                        ->all())
                    ->required(),
            ])
            ->action(function (array $data): void {
                $rev = PageRevision::find($data['revision_id']);
                if (! $rev || $rev->page_id !== $this->record->id) {
                    return;
                }
                // Snapshot the current state first so a restore can itself be undone.
                app(PagePublisher::class)->snapshot($this->record, auth()->id());
                app(PagePublisher::class)->revertTo($this->record->fresh(), $rev);

                Notification::make()->title('Restored an earlier version')->success()->send();
                $this->redirect(PageResource::getUrl('edit', ['record' => $this->record->id]));
            });
    }
}
