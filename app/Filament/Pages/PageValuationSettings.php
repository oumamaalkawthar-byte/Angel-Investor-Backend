<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PageValuationSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Pages';
    protected static ?string $navigationLabel = 'Valuation Calculator Page';
    protected static ?string $title = 'Valuation Calculator Page Content';
    protected static string $view = 'filament.pages.site-settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = SiteSetting::forForm('page_valuation');
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Components\Section::make('Hero')
                ->description('The top of the dedicated /valuation-calculator page. This same calculator is also embedded on the homepage, which keeps its own separate heading and isn\'t affected by this.')
                ->schema([
                    Components\TextInput::make('eyebrow')
                        ->label('Small label above the heading')
                        ->placeholder('Valuation Calculator')
                        ->columnSpanFull(),
                    Components\TextInput::make('heading')
                        ->label('Heading')
                        ->placeholder('What could your startup be worth?')
                        ->columnSpanFull(),
                    Components\Textarea::make('description')
                        ->label('Description paragraph')
                        ->rows(2)
                        ->placeholder('A quick, illustrative estimate using established early-stage frameworks — not a formal valuation. Useful as a starting point before a real conversation with investors.')
                        ->columnSpanFull(),
                ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            SiteSetting::set($key, $value, 'page_valuation');
        }

        Notification::make()
            ->title('Valuation Calculator page content saved')
            ->body('This won\'t appear live until the site is rebuilt and redeployed.')
            ->success()
            ->send();
    }
}
