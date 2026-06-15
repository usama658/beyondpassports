<?php

namespace App\Filament\Resources\SupplyNodeResource\Pages;

use App\Filament\Resources\SupplyNodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupplyNodes extends ListRecords
{
    protected static string $resource = SupplyNodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
