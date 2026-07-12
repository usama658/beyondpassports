<?php

declare(strict_types=1);

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Services\PagePublisher;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    /** Snapshot the current content before overwriting it, so any save can be reverted. */
    protected function beforeSave(): void
    {
        if ($this->record->exists) {
            app(PagePublisher::class)->snapshot($this->record, auth()->id());
        }
    }
}
