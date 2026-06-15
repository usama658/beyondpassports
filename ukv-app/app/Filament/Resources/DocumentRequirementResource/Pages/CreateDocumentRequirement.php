<?php

namespace App\Filament\Resources\DocumentRequirementResource\Pages;

use App\Filament\Resources\DocumentRequirementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumentRequirement extends CreateRecord
{
    protected static string $resource = DocumentRequirementResource::class;

    /**
     * Collapse the friendly `cond_*` fields into the `conditions` array before insert.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return DocumentRequirementResource::formFieldsToConditions($data);
    }
}
