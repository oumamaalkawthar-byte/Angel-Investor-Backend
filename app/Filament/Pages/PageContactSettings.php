<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PageContactSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Pages';
    protected static ?string $navigationLabel = 'Contact Page';
    protected static ?string $title = 'Contact Page Content';
    protected static string $view = 'filament.pages.site-settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = SiteSetting::forForm('page_contact');
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Components\Section::make('Hero')
                ->description('The top of the Contact page. The heading itself has a fixed line break, so it isn\'t editable here.')
                ->schema([
                    Components\TextInput::make('hero_eyebrow')
                        ->label('Small label above the heading')
                        ->placeholder('Start a conversation')
                        ->columnSpanFull(),
                    Components\Textarea::make('hero_description')
                        ->label('Description paragraph')
                        ->rows(2)
                        ->placeholder('Whether you are building, investing or exploring a partnership, reach the right team through one clear conversation.')
                        ->columnSpanFull(),
                ]),

            Components\Section::make('Story column')
                ->description('The copy next to the form itself. The heading has a fixed line break, so it isn\'t editable here.')
                ->schema([
                    Components\Textarea::make('story_description')
                        ->label('Description paragraph')
                        ->rows(2)
                        ->placeholder('Talk to us about startup applications, investor access, partnership opportunities or the Angel Investor platform.')
                        ->columnSpanFull(),
                ]),

            Components\Section::make('Contact details')
                ->description('Shown in the "01 / 02 / 03" list next to the form. Uses the same email/phone/address as SEO Settings\' Organization section unless overridden here — leave blank to just use those.')
                ->schema([
                    Components\TextInput::make('contact_email')->label('Email')->email()->placeholder('info@angelinvestor.pk'),
                    Components\TextInput::make('contact_phone')->label('Phone / WhatsApp')->placeholder('+92 371 7576025'),
                    Components\TextInput::make('contact_address')->label('Address')->placeholder('Al-Kawthar University, Gulshan-e-Iqbal, Karachi')->columnSpanFull(),
                ])->columns(2),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            SiteSetting::set($key, $value, 'page_contact');
        }

        Notification::make()
            ->title('Contact page content saved')
            ->body('This won\'t appear live until the site is rebuilt and redeployed.')
            ->success()
            ->send();
    }
}
