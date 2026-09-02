<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\Enums\TiptapOutput;
use FilamentTiptapEditor\TiptapEditor;
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
                    Forms\Components\Select::make('status')
                        ->options(['draft' => 'Draft', 'published' => 'Published'])
                        ->default('draft')
                        ->required()
                        ->live()
                        ->helperText('Set "Published" with a future Publish Date/Time below to schedule it — it goes live automatically once that moment arrives, no extra action needed.'),
                    Forms\Components\DateTimePicker::make('pub_date')
                        ->label('Publish Date/Time')
                        ->required()
                        ->default(now())
                        ->helperText('A future date + "Published" status = scheduled publishing.'),
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->live(onBlur: true)
                        ->maxLength(255)
                        ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                            // Only auto-fill the slug while creating a brand new post
                            // and it hasn't been hand-edited yet - never overwrite an
                            // existing post's slug just because the title changed
                            // (that would break any link already shared to it; see
                            // the automatic-redirect-on-slug-change behavior on the
                            // model instead, for when it's changed deliberately).
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
                        ->helperText('The URL: /blog/{slug}. Changing this on an already-published post automatically creates a 301 redirect from the old URL.')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->required()
                        ->rows(3)
                        ->live()
                        ->maxLength(200)
                        ->helperText('Shown on the blog index card, and used as the meta description unless overridden in the SEO section below.')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Metadata')
                ->schema([
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
                        ->live()
                        ->visible(fn (Forms\Get $get) => $get('art') === 'photo')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('image_alt')
                        ->label('Image alt text')
                        ->maxLength(255)
                        ->visible(fn (Forms\Get $get) => $get('art') === 'photo' && filled($get('image')))
                        ->helperText('Describe the image — important for accessibility and image-search SEO.')
                        ->columnSpanFull(),
                ])->columns(3),

            Forms\Components\Section::make('Body')
                ->schema([
                    TiptapEditor::make('body')
                        ->tools([
                            'heading', 'bold', 'italic', 'underline', 'strike',
                            'bullet-list', 'ordered-list', 'blockquote', 'link',
                            'table', 'media', 'hr', 'redo', 'undo',
                        ])
                        ->output(TiptapOutput::Html)
                        ->disk('public')
                        ->directory('blog-inline')
                        ->extraAttributes(['class' => 'sticky-toolbar-editor'])
                        ->required()
                        ->live(onBlur: true)
                        ->helperText('Headings H2–H6, tables, and inline images are all available in the toolbar. For the FAQ accordion, use the dedicated FAQs section below instead of writing questions directly in the body.')
                        ->rule(function (Forms\Get $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                if ($get('status') === 'published' && stripos((string) $value, '<h2') === false) {
                                    $fail('A published post should have at least one H2 heading for good structure and SEO.');
                                }
                            };
                        })
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('video_url')
                        ->label('Video URL (optional)')
                        ->url()
                        ->placeholder('https://www.youtube.com/watch?v=... or https://vimeo.com/...')
                        ->helperText('Shown as an embedded player right after the article body.')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Body Images — Alt Text')
                ->description('The rich-text toolbar\'s image button doesn\'t ask for alt text directly, so add it here instead: match each image below by its filename (shown as a hint after you insert it) and describe it. Any image left unlisted just won\'t have alt text.')
                ->schema([
                    Forms\Components\Placeholder::make('detected_images_hint')
                        ->label('Images currently in the body')
                        ->content(function (Forms\Get $get) {
                            $filenames = BlogPost::extractBodyImageFilenames($get('body'));
                            return $filenames === []
                                ? 'No images detected in the body yet.'
                                : implode(', ', $filenames);
                        }),
                    Forms\Components\Repeater::make('body_image_alts')
                        ->label('')
                        ->schema([
                            Forms\Components\TextInput::make('filename')
                                ->label('Image filename')
                                ->required()
                                ->helperText('Copy exactly from the list above.'),
                            Forms\Components\TextInput::make('alt')
                                ->label('Alt text')
                                ->required(),
                        ])
                        ->columns(2)
                        ->addActionLabel('Add an image\'s alt text')
                        ->defaultItems(0),
                ])
                ->collapsed(fn (Forms\Get $get) => blank($get('body_image_alts'))),

            Forms\Components\Section::make('FAQ Accordion')
                ->description('Add question/answer pairs to render an expandable FAQ accordion at the end of the article, with Google-eligible FAQ structured data automatically attached.')
                ->schema([
                    Forms\Components\Repeater::make('faqs')
                        ->label('')
                        ->schema([
                            Forms\Components\TextInput::make('question')->required()->columnSpanFull(),
                            Forms\Components\Textarea::make('answer')->required()->rows(2)->columnSpanFull(),
                        ])
                        ->columns(1)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                        ->addActionLabel('Add a question')
                        ->reorderable()
                        ->defaultItems(0),
                ])
                ->collapsed(fn (Forms\Get $get) => blank($get('faqs'))),

            Forms\Components\Section::make('SEO')
                ->schema([
                    Forms\Components\TextInput::make('seo_title')
                        ->label('Meta title override (optional)')
                        ->live()
                        ->maxLength(60)
                        ->helperText(fn (?string $state) => strlen($state ?? '') . ' / 60 characters. Falls back to the post Title above if left blank.')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('meta_description')
                        ->label('Meta description override (optional)')
                        ->rows(2)
                        ->live()
                        ->maxLength(160)
                        ->helperText(fn (?string $state) => strlen($state ?? '') . ' / 160 characters. Falls back to the Description above if left blank.')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('canonical_url')
                        ->label('Canonical URL override (optional)')
                        ->url()
                        ->helperText('Only set this if this content is also published elsewhere and you want search engines to credit that other URL instead.')
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('nofollow_external_links')
                        ->label('Add "nofollow" to external links automatically')
                        ->default(true)
                        ->helperText('Applies to every link in the body pointing outside angelinvestor.pk — protects your SEO from vouching for sites you don\'t control. Internal links are never affected.'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->disk('public'),
                Tables\Columns\TextColumn::make('title')->searchable()->weight('bold')->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => $state === 'published' ? 'success' : 'gray')
                    ->formatStateUsing(fn (BlogPost $record) => match (true) {
                        $record->status === 'published' && $record->pub_date->isFuture() => 'Scheduled',
                        $record->status === 'published' => 'Published',
                        default => 'Draft',
                    }),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('pub_date')->label('Publish Date')->dateTime('d M Y, g:i A')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Last Updated')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('pub_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
                Tables\Filters\SelectFilter::make('category')->options([
                    'Founder Playbook' => 'Founder Playbook',
                    'Investment' => 'Investment',
                    'Valuation' => 'Valuation',
                    'Market Insights' => 'Market Insights',
                    'Ecosystem' => 'Ecosystem',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn (BlogPost $record) => route('blog.preview', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\ReplicateAction::make()
                    ->label('Duplicate')
                    ->excludeAttributes(['slug', 'status', 'pub_date'])
                    ->beforeReplicaSaved(function (BlogPost $replica) {
                        $replica->slug = Str::slug($replica->title) . '-copy-' . Str::random(4);
                        $replica->status = 'draft';
                        $replica->pub_date = now();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
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
