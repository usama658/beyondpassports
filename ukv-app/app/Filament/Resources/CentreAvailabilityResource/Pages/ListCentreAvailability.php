<?php

namespace App\Filament\Resources\CentreAvailabilityResource\Pages;

use App\Filament\Resources\CentreAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCentreAvailability extends ListRecords
{
    protected static string $resource = CentreAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
