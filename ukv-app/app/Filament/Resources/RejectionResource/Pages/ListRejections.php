<?php

namespace App\Filament\Resources\RejectionResource\Pages;

use App\Filament\Resources\RejectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRejections extends ListRecords
{
    protected static string $resource = RejectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
