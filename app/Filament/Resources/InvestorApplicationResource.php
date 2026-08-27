<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestorApplicationResource\Pages;
use App\Models\InvestorApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvestorApplicationResource extends Resource
{
    protected static ?string $model = InvestorApplication::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Submissions';
    protected static ?string $navigationLabel = 'Investor Applications';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Applicant')->schema([
                Forms\Components\TextInput::make('reference')->disabled(),
                Forms\Components\TextInput::make('investor_name')->label('Name')->disabled(),
                Forms\Components\TextInput::make('investor_email')->label('Email')->disabled(),
                Forms\Components\TextInput::make('investor_phone')->label('Phone')->disabled(),
                Forms\Components\TextInput::make('investor_city')->label('City')->disabled(),
                Forms\Components\TextInput::make('investor_org')->label('Organization')->disabled(),
                Forms\Components\TextInput::make('investor_linkedin')->label('LinkedIn')->disabled(),
            ])->columns(2),
            Forms\Components\Section::make('Investment Profile')->schema([
                Forms\Components\TextInput::make('sectors_of_interest')->disabled()->columnSpanFull(),
                Forms\Components\TextInput::make('ticket_size')->disabled(),
                Forms\Components\TextInput::make('preferred_stage')->disabled(),
                Forms\Components\TextInput::make('experience')->disabled(),
                Forms\Components\Textarea::make('value_add')->disabled()->rows(3)->columnSpanFull(),
            ])->columns(2),
            Forms\Components\Section::make('Declarations')->schema([
                Forms\Components\Toggle::make('declaration_confidentiality')->disabled(),
                Forms\Components\Toggle::make('declaration_source_of_funds')->disabled(),
            ])->columns(2),
            Forms\Components\Section::make('Follow-up')->schema([
                Forms\Components\Select::make('status')
                    ->options(InvestorApplication::statusOptions())
                    ->default(InvestorApplication::STATUS_NEW)
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
                Tables\Columns\TextColumn::make('investor_name')->label('Name')->searchable(),
                Tables\Columns\TextColumn::make('investor_email')->label('Email')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('ticket_size')->label('Ticket Size'),
                Tables\Columns\TextColumn::make('preferred_stage')->label('Stage'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => InvestorApplication::statusOptions()[$state] ?? ucfirst($state))
                    ->colors([
                        'info'    => InvestorApplication::STATUS_NEW,
                        'warning' => InvestorApplication::STATUS_IN_PROGRESS,
                        'success' => InvestorApplication::STATUS_RESOLVED,
                        'danger'  => InvestorApplication::STATUS_SPAM,
                    ])
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(InvestorApplication::statusOptions()),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $rows = InvestorApplication::orderByDesc('created_at')->get();
                        $headers = ['Reference', 'Name', 'Email', 'Phone', 'City', 'Organization', 'LinkedIn', 'Sectors', 'Ticket Size', 'Stage', 'Experience', 'Value Add', 'Status', 'Date'];

                        $callback = function () use ($rows, $headers) {
                            $out = fopen('php://output', 'w');
                            fputcsv($out, $headers);
                            foreach ($rows as $r) {
                                fputcsv($out, [
                                    $r->reference,
                                    $r->investor_name,
                                    $r->investor_email,
                                    $r->investor_phone,
                                    $r->investor_city,
                                    $r->investor_org,
                                    $r->investor_linkedin,
                                    $r->sectors_of_interest,
                                    $r->ticket_size,
                                    $r->preferred_stage,
                                    $r->experience,
                                    $r->value_add,
                                    InvestorApplication::statusOptions()[$r->status] ?? $r->status,
                                    $r->created_at?->format('Y-m-d H:i:s'),
                                ]);
                            }
                            fclose($out);
                        };

                        return response()->streamDownload(
                            $callback,
                            'investor-applications-' . now()->format('Y-m-d-Hi') . '.csv',
                            ['Content-Type' => 'text/csv']
                        );
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvestorApplications::route('/'),
            'edit' => Pages\EditInvestorApplication::route('/{record}/edit'),
        ];
    }
}
