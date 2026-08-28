<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Same deliberately lighter-touch approach as PagePrivacySettings — the 16
 * numbered legal sections aren't exposed here, only the intro line and the
 * "Last updated" date. See that class's docblock for why.
 */
class PageTermsSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Pages';
    protected static ?string $navigationLabel = 'Terms & Conditions Page';
    protected static ?string $title = 'Terms & Conditions Page Content';
    protected static string $view = 'filament.pages.site-settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = SiteSetting::forForm('page_terms');
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Components\Section::make('Header')
                ->description('The numbered legal sections themselves aren\'t editable here — only the intro line and date. Ask a developer to edit the section text directly in the codebase.')
                ->schema([
                    Components\Textarea::make('intro')
                        ->label('Intro paragraph')
                        ->rows(2)
                        ->placeholder('The principles and responsibilities that keep every interaction clear, responsible and fair.')
                        ->columnSpanFull(),
                    Components\TextInput::make('updated')
                        ->label('"Last updated" date')
                        ->placeholder('21 August 2026'),
                ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            SiteSetting::set($key, $value, 'page_terms');
        }

        Notification::make()
            ->title('Terms & Conditions page content saved')
            ->body('This won\'t appear live until the site is rebuilt and redeployed.')
            ->success()
            ->send();
    }
}
