<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * Strip null/empty values so NOT NULL columns that carry a DB default (e.g. trip_purpose
     * defaults to 'tourist') fall back to that default instead of receiving an explicit null,
     * which violates the constraint and 500s. Nullable columns are unaffected.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return array_filter($data, static fn ($v) => $v !== null && $v !== '');
    }
}
