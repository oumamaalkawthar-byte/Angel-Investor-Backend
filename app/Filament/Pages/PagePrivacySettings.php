<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Deliberately lighter-touch than the marketing pages: the 16 numbered legal
 * sections themselves are NOT exposed here (editing clause-by-clause through
 * a generic form risks silently breaking legal text) — only the intro line
 * and the "Last updated" date, which are the two things that actually change
 * day-to-day. Edit the sections array in src/pages/privacy-policy.astro
 * directly for anything else.
 */
class PagePrivacySettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Pages';
    protected static ?string $navigationLabel = 'Privacy Policy Page';
    protected static ?string $title = 'Privacy Policy Page Content';
    protected static string $view = 'filament.pages.site-settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = SiteSetting::forForm('page_privacy');
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
                        ->placeholder('A clear account of what we collect, why we use it and how we handle it responsibly.')
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
            SiteSetting::set($key, $value, 'page_privacy');
        }

        Notification::make()
            ->title('Privacy Policy page content saved')
            ->body('This won\'t appear live until the site is rebuilt and redeployed.')
            ->success()
            ->send();
    }
}
