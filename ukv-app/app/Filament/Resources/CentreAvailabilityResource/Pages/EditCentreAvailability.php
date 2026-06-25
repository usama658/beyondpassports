<?php

namespace App\Filament\Resources\CentreAvailabilityResource\Pages;

use App\Filament\Resources\CentreAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCentreAvailability extends EditRecord
{
    protected static string $resource = CentreAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Map a stored null band back to the 'none' select option for the form.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['band'])) {
            $data['band'] = 'none';
        }

        return $data;
    }

    /**
     * Stamp source=manual + a fresh freshness window, and reset date/band when "none".
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return CentreAvailabilityResource::normalise($data);
    }
}
