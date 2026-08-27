<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactSubmissionResource\Pages;
use App\Models\ContactSubmission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContactSubmissionResource extends Resource
{
    protected static ?string $model = ContactSubmission::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Submissions';
    protected static ?string $navigationLabel = 'Contact Submissions';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Submission')->schema([
                Forms\Components\TextInput::make('name')->disabled(),
                Forms\Components\TextInput::make('email')->disabled(),
                Forms\Components\Textarea::make('message')->disabled()->rows(4),
                Forms\Components\TextInput::make('created_at')->label('Submission Date')->disabled(),
                Forms\Components\TextInput::make('ip_address')->disabled(),
            ])->columns(2),
            Forms\Components\Section::make('Follow-up')->schema([
                Forms\Components\Select::make('status')
                    ->options(ContactSubmission::statusOptions())
                    ->default(ContactSubmission::STATUS_NEW)
                    ->required(),
                Forms\Components\Textarea::make('admin_notes')->label('Admin Notes')->rows(3)
                    ->helperText('Internal notes about this submission. Never shown publicly.'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Submission Date')->dateTime('d M Y, g:i A')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('email')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('message')->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ContactSubmission::statusOptions()[$state] ?? ucfirst($state))
                    ->colors([
                        'info'    => ContactSubmission::STATUS_NEW,
                        'warning' => ContactSubmission::STATUS_IN_PROGRESS,
                        'success' => ContactSubmission::STATUS_RESOLVED,
                        'danger'  => ContactSubmission::STATUS_SPAM,
                    ])
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(ContactSubmission::statusOptions()),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $rows = ContactSubmission::orderByDesc('created_at')->get();
                        $headers = ['Name', 'Email', 'Message', 'Status', 'Admin Notes', 'Submission Date'];

                        $callback = function () use ($rows, $headers) {
                            $out = fopen('php://output', 'w');
                            fputcsv($out, $headers);
                            foreach ($rows as $r) {
                                fputcsv($out, [
                                    $r->name,
                                    $r->email,
                                    $r->message,
                                    ContactSubmission::statusOptions()[$r->status] ?? $r->status,
                                    $r->admin_notes,
                                    $r->created_at?->format('Y-m-d H:i:s'),
                                ]);
                            }
                            fclose($out);
                        };

                        return response()->streamDownload(
                            $callback,
                            'contact-submissions-' . now()->format('Y-m-d-Hi') . '.csv',
                            ['Content-Type' => 'text/csv']
                        );
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactSubmissions::route('/'),
            'edit' => Pages\EditContactSubmission::route('/{record}/edit'),
        ];
    }
}
