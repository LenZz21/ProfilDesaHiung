<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryResource\Pages;
use App\Models\Gallery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Situs Web';

    protected static ?string $navigationLabel = 'Galeri';

    protected static ?string $modelLabel = 'Galeri';

    protected static ?string $pluralModelLabel = 'Galeri';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('Judul Album')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->label('Slug')->maxLength(255),
            Forms\Components\FileUpload::make('cover')->label('Foto Sampul')->image()->disk(config('filesystems.default'))->directory('galleries'),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->rows(4),
            Forms\Components\Repeater::make('items')
                ->label('Foto Album')
                ->schema([
                    Forms\Components\FileUpload::make('image')
                        ->label('Foto')
                        ->image()
                        ->disk(config('filesystems.default'))
                        ->directory('gallery-items')
                        ->required(),
                    Forms\Components\TextInput::make('caption')
                        ->label('Caption')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Urutan')
                        ->numeric()
                        ->default(0),
                ])
                ->default([])
                ->reorderableWithButtons()
                ->collapsed()
                ->cloneable()
                ->columnSpanFull()
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover')->label('Sampul')->disk(config('filesystems.default')),
                Tables\Columns\TextColumn::make('title')->label('Judul Album')->searchable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->searchable(),
                Tables\Columns\TextColumn::make('items_count')->label('Jumlah Foto'),
            ])
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
            'index' => Pages\ListGalleries::route('/'),
            'create' => Pages\CreateGallery::route('/create'),
            'edit' => Pages\EditGallery::route('/{record}/edit'),
        ];
    }
}
