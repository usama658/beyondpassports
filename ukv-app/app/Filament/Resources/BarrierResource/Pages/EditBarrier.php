<?php

namespace App\Filament\Resources\BarrierResource\Pages;

use App\Filament\Resources\BarrierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBarrier extends EditRecord
{
    protected static string $resource = BarrierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
