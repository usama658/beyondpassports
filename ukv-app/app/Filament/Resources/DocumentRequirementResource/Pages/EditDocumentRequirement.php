<?php

namespace App\Filament\Resources\DocumentRequirementResource\Pages;

use App\Filament\Resources\DocumentRequirementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumentRequirement extends EditRecord
{
    protected static string $resource = DocumentRequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Spread the stored `conditions` array onto the friendly `cond_*` fields for editing.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $conditions = $data['conditions'] ?? [];

        if (! is_array($conditions)) {
            $conditions = [];
        }

        return array_merge(
            $data,
            DocumentRequirementResource::conditionsToFormFields($conditions)
        );
    }

    /**
     * Collapse the friendly `cond_*` fields back into the `conditions` array before update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return DocumentRequirementResource::formFieldsToConditions($data);
    }
}
