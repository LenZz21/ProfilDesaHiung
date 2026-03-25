<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageSettingResource\Pages;
use App\Models\PageSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageSettingResource extends Resource
{
    protected static ?string $model = PageSetting::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Situs Web';

    protected static ?string $navigationLabel = 'Pengaturan Halaman';

    protected static ?string $modelLabel = 'Pengaturan Halaman';

    protected static ?string $pluralModelLabel = 'Pengaturan Halaman';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('page_key')
                ->label('Halaman')
                ->options(PageSetting::pageOptions())
                ->disabled()
                ->dehydrated(),
            Forms\Components\TextInput::make('title')
                ->label('Judul')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('subtitle')
                ->label('Subjudul')
                ->rows(3)
                ->maxLength(1000),
            Forms\Components\FileUpload::make('hero_image')
                ->label('Gambar Hero')
                ->image()
                ->disk(config('filesystems.default'))
                ->directory('page-settings')
                ->visibility('public')
                ->imageEditor()
                ->maxSize(4096)
                ->helperText('Opsional. Jika dikosongkan, halaman memakai gambar default.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('page_label')
                    ->label('Halaman'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->limit(50),
                Tables\Columns\TextColumn::make('subtitle')
                    ->label('Subjudul')
                    ->limit(60),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPageSettings::route('/'),
            'edit' => Pages\EditPageSetting::route('/{record}/edit'),
        ];
    }
}
