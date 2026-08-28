<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PageAboutSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-information-circle';
    protected static ?string $navigationGroup = 'Pages';
    protected static ?string $navigationLabel = 'About Page';
    protected static ?string $title = 'About Page Content';
    protected static string $view = 'filament.pages.site-settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = SiteSetting::forForm('page_about');
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Components\Section::make('Hero')
                ->description('The top of the About page. The heading itself has a fixed line break, so it isn\'t editable here.')
                ->schema([
                    Components\TextInput::make('hero_eyebrow')
                        ->label('Small label above the heading')
                        ->placeholder('About Angel Investor by SISL')
                        ->columnSpanFull(),
                    Components\Textarea::make('hero_description')
                        ->label('Description paragraph')
                        ->rows(2)
                        ->placeholder('We connect ethical startups with serious investors in an environment built for real decisions, responsible growth and lasting impact.')
                        ->columnSpanFull(),
                ]),

            Components\Section::make('Hero Banner Slider')
                ->description('The 4-image slideshow behind the hero text. Leave any image blank to keep the current one.')
                ->schema([
                    Components\FileUpload::make('banner_1_image')->label('Image 1')->image()->disk('public')->directory('page-media')->maxSize(3072),
                    Components\TextInput::make('banner_1_caption')->label('Caption 1')->placeholder('Investor dialogue'),
                    Components\FileUpload::make('banner_2_image')->label('Image 2')->image()->disk('public')->directory('page-media')->maxSize(3072),
                    Components\TextInput::make('banner_2_caption')->label('Caption 2')->placeholder('Founder pitches'),
                    Components\FileUpload::make('banner_3_image')->label('Image 3')->image()->disk('public')->directory('page-media')->maxSize(3072),
                    Components\TextInput::make('banner_3_caption')->label('Caption 3')->placeholder('Ideas in focus'),
                    Components\FileUpload::make('banner_4_image')->label('Image 4')->image()->disk('public')->directory('page-media')->maxSize(3072),
                    Components\TextInput::make('banner_4_caption')->label('Caption 4')->placeholder('Commitments made'),
                ])->columns(2),

            Components\Section::make('Our Purpose')
                ->description('The paragraph under the "Capital should move more than a company forward..." heading.')
                ->schema([
                    Components\Textarea::make('purpose_description')
                        ->label('Description paragraph')
                        ->rows(3)
                        ->placeholder('Angel Investor by SISL brings innovation and values into the same conversation. We create a focused platform where responsible businesses can earn investor confidence and where capital can support outcomes that matter.')
                        ->columnSpanFull(),
                ]),

            Components\Section::make('Our Foundation')
                ->description('The "An initiative of Al-Kawthar University" section.')
                ->schema([
                    Components\Textarea::make('institution_paragraph_1')
                        ->label('First paragraph')
                        ->rows(2)
                        ->placeholder('Rooted in the School of Islamic Scholars & Leaders, Angel Investor brings an institutional commitment to ethics, leadership and service into the startup ecosystem.')
                        ->columnSpanFull(),
                    Components\Textarea::make('institution_paragraph_2')
                        ->label('Second paragraph')
                        ->rows(2)
                        ->placeholder('That foundation shapes how opportunities are assessed, how relationships are built and how growth is understood.')
                        ->columnSpanFull(),
                ]),

            Components\Section::make('Bottom CTA')
                ->description('The "Ready to move forward?" section near the footer. The heading itself has a fixed line break, so it isn\'t editable here.')
                ->schema([
                    Components\TextInput::make('cta_eyebrow')->label('Small label')->placeholder('Ready to move forward?')->columnSpanFull(),
                    Components\Textarea::make('cta_description')
                        ->label('Description')
                        ->rows(2)
                        ->placeholder('Whether you are building a responsible venture or looking for purposeful deal flow, the next conversation starts here.')
                        ->columnSpanFull(),
                    Components\TextInput::make('cta_button_primary')->label('Primary button text')->placeholder('Apply as startup'),
                    Components\TextInput::make('cta_button_secondary')->label('Secondary button text')->placeholder('Apply as investor'),
                ])->columns(2),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            SiteSetting::set($key, $value, 'page_about');
        }

        Notification::make()
            ->title('About page content saved')
            ->body('This won\'t appear live until the site is rebuilt and redeployed.')
            ->success()
            ->send();
    }
}
