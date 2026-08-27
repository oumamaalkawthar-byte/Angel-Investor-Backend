<?php

namespace App\Filament\Resources\InvestorApplicationResource\Pages;

use App\Filament\Resources\InvestorApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvestorApplication extends EditRecord
{
    protected static string $resource = InvestorApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
