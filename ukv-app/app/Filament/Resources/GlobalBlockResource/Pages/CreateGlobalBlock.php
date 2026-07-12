<?php

declare(strict_types=1);

namespace App\Filament\Resources\GlobalBlockResource\Pages;

use App\Filament\Resources\GlobalBlockResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGlobalBlock extends CreateRecord
{
    protected static string $resource = GlobalBlockResource::class;
}
