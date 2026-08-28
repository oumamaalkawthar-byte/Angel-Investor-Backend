<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PageBlogSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationGroup = 'Pages';
    protected static ?string $navigationLabel = 'Blog Page';
    protected static ?string $title = 'Blog Page Content';
    protected static string $view = 'filament.pages.site-settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = SiteSetting::forForm('page_blog');
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Components\Section::make('Hero')
                ->description('The top of the /blog listing page. The heading itself has a fixed line break, so it isn\'t editable here.')
                ->schema([
                    Components\TextInput::make('hero_eyebrow')
                        ->label('Small label above the heading')
                        ->placeholder('Ideas & intelligence')
                        ->columnSpanFull(),
                    Components\Textarea::make('hero_description')
                        ->label('Description paragraph')
                        ->rows(2)
                        ->placeholder('Practical thinking for founders and investors navigating fundraising, valuation and responsible growth.')
                        ->columnSpanFull(),
                ]),

            Components\Section::make('Bottom CTA')
                ->description('The "Better questions. Stronger decisions." section near the footer. The heading itself has a fixed line break, so it isn\'t editable here.')
                ->schema([
                    Components\TextInput::make('cta_eyebrow')->label('Small label')->placeholder('Keep learning')->columnSpanFull(),
                    Components\Textarea::make('cta_description')
                        ->label('Description')
                        ->rows(2)
                        ->placeholder('Explore the thinking that helps founders prepare well and investors evaluate with greater clarity.')
                        ->columnSpanFull(),
                    Components\TextInput::make('cta_button_text')->label('Button text')->placeholder('Watch founder pitches'),
                ])->columns(2),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            SiteSetting::set($key, $value, 'page_blog');
        }

        Notification::make()
            ->title('Blog page content saved')
            ->body('This won\'t appear live until the site is rebuilt and redeployed.')
            ->success()
            ->send();
    }
}
