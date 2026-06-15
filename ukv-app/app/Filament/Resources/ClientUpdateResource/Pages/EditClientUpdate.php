<?php

namespace App\Filament\Resources\ClientUpdateResource\Pages;

use App\Filament\Resources\ClientUpdateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClientUpdate extends EditRecord
{
    protected static string $resource = ClientUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
