<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Situs Web';

    protected static ?string $navigationLabel = 'Berita';

    protected static ?string $modelLabel = 'Berita';

    protected static ?string $pluralModelLabel = 'Berita';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->label('Slug')->maxLength(255),
            Forms\Components\TextInput::make('category')->label('Kategori')->maxLength(100),
            Forms\Components\Textarea::make('excerpt')->label('Ringkasan')->rows(3),
            Forms\Components\RichEditor::make('content')->label('Konten')->required()->columnSpanFull(),
            Forms\Components\FileUpload::make('thumbnail')->label('Gambar Utama')->image()->disk(config('filesystems.default'))->directory('posts'),
            Forms\Components\DateTimePicker::make('published_at')->label('Tanggal Publikasi'),
            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'draft' => 'Draf',
                    'published' => 'Dipublikasikan',
                ])
                ->required()
                ->default('draft'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')->label('Gambar')->disk(config('filesystems.default')),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('category')->label('Kategori')->badge(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draf',
                        'published' => 'Dipublikasikan',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('published_at')->label('Tanggal Publikasi')->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
