<?php

namespace App\Filament\Resources\BarrierResource\Pages;

use App\Filament\Resources\BarrierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBarriers extends ListRecords
{
    protected static string $resource = BarrierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
