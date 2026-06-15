<?php

namespace App\Filament\Resources\RejectionResource\Pages;

use App\Filament\Resources\RejectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRejection extends EditRecord
{
    protected static string $resource = RejectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
