<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Same shape as the sibling faithfuture project's SeoSettings page - kept
 * deliberately identical since that's already a familiar pattern. The real
 * difference is what consumes it: faithfuture's own Blade views read these
 * via SiteSetting::get() directly (same app, same request). This backend
 * doesn't render any pages itself - the Astro frontend's BaseLayout.astro
 * fetches GET /api/site-settings/seo at build time instead (see that
 * project's src/lib/site-settings.ts), so there's no SeoService/Blade
 * component equivalent here - Astro builds the <meta>/JSON-LD tags itself
 * from whatever this page saves.
 */
class SeoSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Pages';
    protected static ?string $navigationLabel = 'SEO Settings';
    protected static ?int $navigationSort = 10;
    protected static ?string $title = 'SEO Settings';
    protected static string $view = 'filament.pages.site-settings-form';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = SiteSetting::forForm('seo');
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Components\Section::make('Meta Defaults')
                ->description('Used on any page that doesn\'t set its own title/description/image.')
                ->schema([
                    Components\TextInput::make('seo_title')->label('Default Meta Title Suffix')->placeholder('Angel Investor')
                        ->helperText('Every page title is rendered as "{Page Title} | {this}".'),
                    Components\TextInput::make('seo_site_name')->label('Site Name')->placeholder('Angel Investor')
                        ->helperText('Used for og:site_name and the Organization schema below.'),
                    Components\Textarea::make('seo_description')->label('Default Meta Description')->rows(3)->columnSpanFull()
                        ->placeholder('Angel investor backing early-stage startups.'),
                    Components\FileUpload::make('seo_og_image')
                        ->label('Default Social Share Image')
                        ->image()
                        ->disk('public')
                        ->directory('seo')
                        ->maxSize(3072)
                        ->helperText('Recommended: 1200x630px. Used for og:image/twitter:image on any page that doesn\'t set its own.'),
                ])->columns(2),

            Components\Section::make('Organization')
                ->description('Feeds the Organization + WebSite structured data (JSON-LD) on the homepage.')
                ->schema([
                    Components\TextInput::make('seo_org_email')->label('Contact Email')->email()->placeholder('info@angelinvestor.pk'),
                    Components\TextInput::make('seo_org_phone')->label('Contact Phone')->placeholder('+92-371-7576025'),
                    Components\TextInput::make('seo_org_address')->label('Street Address')->placeholder('Al-Kawthar University, Gulshan-e-Iqbal')->columnSpanFull(),
                    Components\TextInput::make('seo_org_city')->label('City')->placeholder('Karachi'),
                ])->columns(2),

            Components\Section::make('Social Media')
                ->description('Populates the Organization schema\'s sameAs list — leave any blank that don\'t apply.')
                ->schema([
                    Components\TextInput::make('social_twitter')->label('Twitter / X URL')->url()->placeholder('https://twitter.com/...'),
                    Components\TextInput::make('social_linkedin')->label('LinkedIn URL')->url()->placeholder('https://linkedin.com/company/...'),
                    Components\TextInput::make('social_instagram')->label('Instagram URL')->url()->placeholder('https://instagram.com/...'),
                    Components\TextInput::make('social_facebook')->label('Facebook URL')->url()->placeholder('https://facebook.com/...'),
                    Components\TextInput::make('social_youtube')->label('YouTube URL')->url()->placeholder('https://youtube.com/@...'),
                ])->columns(2),

            Components\Section::make('Analytics & Tracking')
                ->description('Leave blank to skip — Astro only embeds a tracking snippet when its ID is set.')
                ->schema([
                    Components\TextInput::make('seo_gtm_id')->label('Google Tag Manager ID')->placeholder('GTM-XXXXXXX'),
                    Components\TextInput::make('seo_ga_id')->label('Google Analytics ID')->placeholder('G-XXXXXXXXXX'),
                    Components\TextInput::make('seo_fb_pixel')->label('Facebook Pixel ID'),
                ])->columns(3),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            SiteSetting::set($key, $value, 'seo');
        }

        Notification::make()
            ->title('SEO settings saved')
            ->body('This won\'t appear live until the Astro site is rebuilt and redeployed.')
            ->success()
            ->send();
    }
}
