<?php

namespace App\Filament\Resources\Records\Pages;

use App\Filament\Resources\Records\RecordResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord as BaseEditRecord;
use Filament\Support\Icons\Heroicon;

class EditRecord extends BaseEditRecord
{
    protected static string $resource = RecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Jump to the public record page. Opens in a new tab so the admin
            // session (and any unsaved edits) stay put — mirrors the "Edit record"
            // shortcut on the public detail page.
            Action::make('viewOnSite')
                ->label('View on site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (): string => route('records.show', $this->record))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }
}
