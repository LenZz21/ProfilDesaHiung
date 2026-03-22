<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeOverviewTranslationResource\Pages;
use App\Models\HomeOverviewTranslation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HomeOverviewTranslationResource extends Resource
{
    protected static ?string $model = HomeOverviewTranslation::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationGroup = 'Situs Web';

    protected static ?string $navigationLabel = 'Selayang Pandang EN';

    protected static ?string $modelLabel = 'Selayang Pandang EN';

    protected static ?string $pluralModelLabel = 'Selayang Pandang EN';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('locale')
                ->label('Locale')
                ->default('en')
                ->required()
                ->maxLength(10)
                ->disabled()
                ->dehydrated(),
            Forms\Components\Textarea::make('about')
                ->label('Selayang Pandang (English)')
                ->rows(12)
                ->helperText('Konten ini dipakai di beranda saat bahasa Inggris aktif. Pisahkan paragraf dengan baris kosong.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('locale')
                    ->label('Locale')
                    ->badge(),
                Tables\Columns\TextColumn::make('about')
                    ->label('Selayang Pandang')
                    ->limit(80),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomeOverviewTranslations::route('/'),
            'edit' => Pages\EditHomeOverviewTranslation::route('/{record}/edit'),
        ];
    }
}
