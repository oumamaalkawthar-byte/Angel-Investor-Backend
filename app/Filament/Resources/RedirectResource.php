<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RedirectResource\Pages;
use App\Models\Redirect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Editable redirect list, consumed by the Astro site at build time (see
 * GET /api/redirects and astro.config.mjs) rather than by a runtime
 * Route::fallback like the sibling faithfuture project — this site is
 * fully static, so a redirect here only takes effect after the next
 * rebuild+redeploy of the Astro site.
 */
class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-top-right-on-square';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Redirects';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('from_path')
                ->label('Redirect From')
                ->required()
                ->placeholder('/old-page or https://angelinvestor.pk/old-page')
                ->helperText('The old URL that no longer has a page. Domain and trailing slash are stripped automatically.')
                ->columnSpanFull(),
            Forms\Components\TextInput::make('to_path')
                ->label('Redirect To')
                ->required()
                ->placeholder('/new-page')
                ->helperText('Where visitors land instead. Can be a path on this site or a full https:// URL.')
                ->rule(function (Forms\Get $get) {
                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                        if (Redirect::normalize((string) $get('from_path')) === Redirect::normalize((string) $value)) {
                            $fail('Redirect From and Redirect To can\'t be the same page — that would loop forever.');
                        }
                    };
                })
                ->columnSpanFull(),
            Forms\Components\Select::make('status_code')
                ->label('Redirect Type')
                ->options([
                    301 => '301 — Permanent (recommended for retired URLs)',
                    302 => '302 — Temporary',
                ])
                ->default(301)
                ->required()
                ->helperText('The static site currently always redirects instantly regardless of type — this is kept for when the site gets real server-side 301s.'),
            Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('from_path')->label('From')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('to_path')->label('To')->searchable(),
                Tables\Columns\TextColumn::make('status_code')->label('Type')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('Last Updated')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('from_path')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRedirects::route('/'),
            'create' => Pages\CreateRedirect::route('/create'),
            'edit'   => Pages\EditRedirect::route('/{record}/edit'),
        ];
    }
}
