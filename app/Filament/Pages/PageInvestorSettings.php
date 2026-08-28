<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PageInvestorSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Pages';
    protected static ?string $navigationLabel = 'Join as Investor Page';
    protected static ?string $title = 'Join as Investor Page Content';
    protected static string $view = 'filament.pages.site-settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = SiteSetting::forForm('page_investor');
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Components\Section::make('Hero')
                ->description('The top of the Join as Investor page. The heading itself has a fixed line break, so it isn\'t editable here.')
                ->schema([
                    Components\TextInput::make('hero_eyebrow')
                        ->label('Small label above the heading')
                        ->placeholder('Join the network')
                        ->columnSpanFull(),
                    Components\Textarea::make('hero_description')
                        ->label('Description paragraph')
                        ->rows(2)
                        ->placeholder('Meet pre-screened Pakistani startups in a focused environment built for informed conversations and serious investment decisions.')
                        ->columnSpanFull(),
                ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            SiteSetting::set($key, $value, 'page_investor');
        }

        Notification::make()
            ->title('Join as Investor page content saved')
            ->body('This won\'t appear live until the site is rebuilt and redeployed.')
            ->success()
            ->send();
    }
}
