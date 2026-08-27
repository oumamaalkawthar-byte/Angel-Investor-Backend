<?php

namespace App\Filament\Resources\StartupApplicationResource\Pages;

use App\Filament\Resources\StartupApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStartupApplication extends EditRecord
{
    protected static string $resource = StartupApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
