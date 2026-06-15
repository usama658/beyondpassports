<?php

namespace App\Filament\Resources\SupplyNodeResource\Pages;

use App\Filament\Resources\SupplyNodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupplyNode extends EditRecord
{
    protected static string $resource = SupplyNodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
