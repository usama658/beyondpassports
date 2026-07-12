<?php

declare(strict_types=1);

namespace App\Filament\Resources\GlobalBlockResource\Pages;

use App\Filament\Resources\GlobalBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGlobalBlock extends EditRecord
{
    protected static string $resource = GlobalBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
