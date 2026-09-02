<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationGroup = 'Pages';
    protected static ?string $navigationLabel = 'Blog Posts';
    protected static ?string $modelLabel = 'Blog Post';
    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Post details')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                            // Only auto-fill the slug while creating a brand new post
                            // and it hasn't been hand-edited yet - never overwrite an
                            // existing post's slug just because the title changed
                            // (that would break any link already shared to it).
                            if (blank($get('slug'))) {
                                $set('slug', Str::slug($state));
                            }
                        })
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->suffixAction(
                            Forms\Components\Actions\Action::make('regenerate')
                                ->icon('heroicon-m-arrow-path')
                                ->action(function (Forms\Set $set, Forms\Get $get) {
                                    $set('slug', Str::slug($get('title')));
                                })
                        )
                        ->helperText('The URL: /blog/{slug}. Click the refresh icon to regenerate from the title.')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->required()
                        ->rows(3)
                        ->helperText('Shown on the blog index card and used as the meta description.')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Metadata')
                ->schema([
                    Forms\Components\DateTimePicker::make('pub_date')->required()->default(now()),
                    Forms\Components\TextInput::make('author')->default('Angel Investor'),
                    Forms\Components\Select::make('category')
                        ->options([
                            'Founder Playbook' => 'Founder Playbook',
                            'Investment' => 'Investment',
                            'Valuation' => 'Valuation',
                            'Market Insights' => 'Market Insights',
                            'Ecosystem' => 'Ecosystem',
                        ]),
                    Forms\Components\TextInput::make('read_time')->placeholder('5 min read'),
                    Forms\Components\Select::make('art')
                        ->label('Card artwork style')
                        ->options([
                            'photo' => 'Photo',
                            'valuation' => 'Valuation (generated graphic)',
                            'traction' => 'Traction (generated graphic)',
                            'market' => 'Market (generated graphic)',
                            'terms' => 'Terms (generated graphic)',
                        ])
                        ->default('photo')
                        ->live()
                        ->helperText('"Photo" needs the Image below. The others draw a generated graphic and ignore it.'),
                    Forms\Components\FileUpload::make('image')
                        ->image()
                        ->disk('public')
                        ->directory('blog')
                        ->visible(fn (Forms\Get $get) => $get('art') === 'photo'),
                ])->columns(3),

            Forms\Components\Section::make('Body')
                ->schema([
                    Forms\Components\RichEditor::make('body')
                        ->required()
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('blog-inline')
                        ->toolbarButtons([
                            'bold', 'italic', 'underline', 'strike', 'link',
                            'h2', 'h3', 'bulletList', 'orderedList', 'blockquote',
                            'table', 'attachFiles', 'undo', 'redo',
                        ])
                        ->helperText('For an FAQ section, write an "H2: Frequently Asked Questions" heading followed by H3 questions with their answers below each - the site automatically turns that into an expandable accordion.')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('video_url')
                        ->label('Video URL (optional)')
                        ->url()
                        ->placeholder('https://www.youtube.com/watch?v=... or https://vimeo.com/...')
                        ->helperText('Shown as an embedded player right after the article body.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->disk('public'),
                Tables\Columns\TextColumn::make('title')->searchable()->weight('bold')->limit(50),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('pub_date')->label('Published')->dateTime('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Last Updated')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('pub_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')->options([
                    'Founder Playbook' => 'Founder Playbook',
                    'Investment' => 'Investment',
                    'Valuation' => 'Valuation',
                    'Market Insights' => 'Market Insights',
                    'Ecosystem' => 'Ecosystem',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
