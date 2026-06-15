<?php

namespace App\Filament\Resources\ClientUpdateResource\Pages;

use App\Filament\Resources\ClientUpdateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClientUpdates extends ListRecords
{
    protected static string $resource = ClientUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
