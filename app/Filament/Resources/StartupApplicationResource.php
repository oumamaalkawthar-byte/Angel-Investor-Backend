<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StartupApplicationResource\Pages;
use App\Models\StartupApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class StartupApplicationResource extends Resource
{
    protected static ?string $model = StartupApplication::class;
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationGroup = 'Submissions';
    protected static ?string $navigationLabel = 'Startup Applications';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Founder')->schema([
                Forms\Components\TextInput::make('reference')->disabled(),
                Forms\Components\TextInput::make('founder_name')->label('Name')->disabled(),
                Forms\Components\TextInput::make('founder_email')->label('Email')->disabled(),
                Forms\Components\TextInput::make('founder_phone')->label('Phone')->disabled(),
                Forms\Components\TextInput::make('founder_city')->label('City')->disabled(),
                Forms\Components\TextInput::make('founder_linkedin')->label('LinkedIn')->disabled(),
                Forms\Components\Textarea::make('founder_bio')->label('Bio')->disabled()->rows(3)->columnSpanFull(),
            ])->columns(2),
            Forms\Components\Section::make('Co-founders')->schema([
                Forms\Components\Repeater::make('cofounders')
                    ->disabled()
                    ->schema([
                        Forms\Components\TextInput::make('name')->disabled(),
                        Forms\Components\TextInput::make('role')->disabled(),
                        Forms\Components\TextInput::make('linkedin')->disabled(),
                    ])
                    ->columns(3)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false),
            ]),
            Forms\Components\Section::make('Startup')->schema([
                Forms\Components\TextInput::make('startup_name')->label('Name')->disabled(),
                Forms\Components\TextInput::make('startup_website')->label('Website')->disabled(),
                Forms\Components\TextInput::make('one_liner')->label('One-liner')->disabled()->columnSpanFull(),
                Forms\Components\TextInput::make('sector')->disabled(),
                Forms\Components\TextInput::make('stage')->disabled(),
                Forms\Components\TextInput::make('registration_status')->label('Registration')->disabled(),
                Forms\Components\TextInput::make('team_size')->label('Team Size')->disabled(),
            ])->columns(2),
            Forms\Components\Section::make('The Ask')->schema([
                Forms\Components\TextInput::make('investment_ask')->label('Investment Ask')->disabled(),
                Forms\Components\TextInput::make('equity_offered')->label('Equity Offered')->disabled(),
                Forms\Components\Textarea::make('use_of_funds')->label('Use of Funds')->disabled()->rows(3)->columnSpanFull(),
                Forms\Components\Textarea::make('traction')->disabled()->rows(3)->columnSpanFull(),
            ])->columns(2),
            Forms\Components\Section::make('Pitch Deck')->schema([
                Forms\Components\Placeholder::make('pitch_deck_download')
                    ->label('')
                    ->content(fn (?StartupApplication $record) => $record
                        ? new \Illuminate\Support\HtmlString(
                            '<a href="' . e(Storage::disk('public')->url($record->pitch_deck_path)) . '" target="_blank" rel="noopener" class="text-primary-600 underline">'
                            . e($record->pitch_deck_original_name)
                            . '</a>'
                        )
                        : '—'),
            ]),
            Forms\Components\Section::make('Declarations')->schema([
                Forms\Components\Toggle::make('declaration_authentic')->disabled(),
                Forms\Components\Toggle::make('declaration_ethical')->disabled(),
                Forms\Components\Toggle::make('declaration_consent')->disabled(),
            ])->columns(3),
            Forms\Components\Section::make('Follow-up')->schema([
                Forms\Components\Select::make('status')
                    ->options(StartupApplication::statusOptions())
                    ->default(StartupApplication::STATUS_NEW)
                    ->required(),
                Forms\Components\Textarea::make('admin_notes')->label('Admin Notes')->rows(3)
                    ->helperText('Internal notes about this application. Never shown publicly.'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d M Y, g:i A')->sortable(),
                Tables\Columns\TextColumn::make('reference')->searchable()->weight('bold')->copyable(),
                Tables\Columns\TextColumn::make('startup_name')->label('Startup')->searchable(),
                Tables\Columns\TextColumn::make('founder_name')->label('Founder')->searchable(),
                Tables\Columns\TextColumn::make('sector'),
                Tables\Columns\TextColumn::make('investment_ask')->label('Ask'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => StartupApplication::statusOptions()[$state] ?? ucfirst($state))
                    ->colors([
                        'info'    => StartupApplication::STATUS_NEW,
                        'warning' => StartupApplication::STATUS_IN_PROGRESS,
                        'success' => StartupApplication::STATUS_RESOLVED,
                        'danger'  => StartupApplication::STATUS_SPAM,
                    ])
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(StartupApplication::statusOptions()),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $rows = StartupApplication::orderByDesc('created_at')->get();
                        $headers = ['Reference', 'Founder', 'Email', 'Phone', 'City', 'Startup', 'One-liner', 'Sector', 'Stage', 'Ask', 'Equity', 'Status', 'Date'];

                        $callback = function () use ($rows, $headers) {
                            $out = fopen('php://output', 'w');
                            fputcsv($out, $headers);
                            foreach ($rows as $r) {
                                fputcsv($out, [
                                    $r->reference,
                                    $r->founder_name,
                                    $r->founder_email,
                                    $r->founder_phone,
                                    $r->founder_city,
                                    $r->startup_name,
                                    $r->one_liner,
                                    $r->sector,
                                    $r->stage,
                                    $r->investment_ask,
                                    $r->equity_offered,
                                    StartupApplication::statusOptions()[$r->status] ?? $r->status,
                                    $r->created_at?->format('Y-m-d H:i:s'),
                                ]);
                            }
                            fclose($out);
                        };

                        return response()->streamDownload(
                            $callback,
                            'startup-applications-' . now()->format('Y-m-d-Hi') . '.csv',
                            ['Content-Type' => 'text/csv']
                        );
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStartupApplications::route('/'),
            'edit' => Pages\EditStartupApplication::route('/{record}/edit'),
        ];
    }
}
