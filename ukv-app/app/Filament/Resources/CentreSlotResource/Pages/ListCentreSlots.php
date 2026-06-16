<?php

namespace App\Filament\Resources\CentreSlotResource\Pages;

use App\Filament\Resources\CentreSlotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCentreSlots extends ListRecords
{
    protected static string $resource = CentreSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
