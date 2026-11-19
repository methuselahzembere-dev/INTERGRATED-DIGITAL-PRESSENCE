<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Events\Schemas\EventForm;
use App\Support\Alerts;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    private bool $justPublished = false;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->justPublished = ! $this->record->isPublished();

        return EventForm::prepare($data, $this->record);
    }

    protected function afterSave(): void
    {
        // Alert members only on the draft -> published change, not on every edit.
        if ($this->justPublished && $this->record->isPublished()) {
            Alerts::eventPublished($this->record);
        }
    }
}
