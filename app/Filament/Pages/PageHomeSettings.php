<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PageHomeSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Pages';
    protected static ?string $navigationLabel = 'Homepage';
    protected static ?string $title = 'Homepage Content';
    protected static string $view = 'filament.pages.site-settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = SiteSetting::forForm('page_home');
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Components\Section::make('Hero')
                ->description('The first thing a visitor sees at the top of the homepage.')
                ->schema([
                    Components\TextInput::make('hero_eyebrow')
                        ->label('Small label above the heading')
                        ->placeholder('Early-stage capital · Hands-on partnership')
                        ->columnSpanFull(),
                    Components\TextInput::make('hero_heading_line1')
                        ->label('Heading — first line')
                        ->placeholder('Backing the ideas'),
                    Components\TextInput::make('hero_heading_line2')
                        ->label('Heading — second line (plain part)')
                        ->placeholder('Pakistan will'),
                    Components\TextInput::make('hero_heading_emphasis')
                        ->label('Heading — emphasized words (shown in gold italic)')
                        ->placeholder('build on.')
                        ->columnSpanFull(),
                    Components\Textarea::make('hero_description')
                        ->label('Description paragraph')
                        ->rows(3)
                        ->placeholder('We bring bold founders and experienced investors together, turning promising ventures into lasting businesses.')
                        ->columnSpanFull(),
                    Components\TextInput::make('hero_cta_text')
                        ->label('Primary button text')
                        ->placeholder('Apply as startup'),
                ])->columns(2),

            Components\Section::make('Hero — Capital Deployed Stat')
                ->description('The stat shown in the bottom-right corner of the hero video.')
                ->schema([
                    Components\TextInput::make('hero_stat_value')->label('Value')->placeholder('$18M+'),
                    Components\TextInput::make('hero_stat_label')->label('Label')->placeholder('CAPITAL DEPLOYED'),
                    Components\TextInput::make('hero_stat_caption')->label('Caption')->placeholder('Across high-potential ventures'),
                ])->columns(3),

            Components\Section::make('Institutional Backing')
                ->description('The "A project of Al-Kawthar University and SISL" section.')
                ->schema([
                    Components\TextInput::make('institutional_eyebrow')
                        ->label('Small label')
                        ->placeholder('Institutionally backed · Purposefully built')
                        ->columnSpanFull(),
                    Components\Textarea::make('institutional_description')
                        ->label('Description paragraph')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Components\Section::make('Bottom CTA')
                ->description('The "Building something worth backing?" section near the footer. The heading itself has a fixed line break, so it isn\'t editable here — only the label above it and the description below.')
                ->schema([
                    Components\TextInput::make('cta_eyebrow')->label('Small label')->placeholder('Start the conversation'),
                    Components\Textarea::make('cta_description')->label('Description')->rows(2)->columnSpanFull(),
                ])->columns(2),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            SiteSetting::set($key, $value, 'page_home');
        }

        Notification::make()
            ->title('Homepage content saved')
            ->body('This won\'t appear live until the site is rebuilt and redeployed — same as any other content change on the Astro site.')
            ->success()
            ->send();
    }
}
