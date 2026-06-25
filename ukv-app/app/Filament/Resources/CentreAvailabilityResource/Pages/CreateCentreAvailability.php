<?php

namespace App\Filament\Resources\CentreAvailabilityResource\Pages;

use App\Filament\Resources\CentreAvailabilityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCentreAvailability extends CreateRecord
{
    protected static string $resource = CentreAvailabilityResource::class;

    /**
     * Stamp source=manual + a fresh freshness window, and reset date/band when "none".
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return CentreAvailabilityResource::normalise($data);
    }
}
