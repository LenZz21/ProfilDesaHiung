<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficialResource\Pages;
use App\Models\Official;
use App\Models\OfficialStructureTitle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OfficialResource extends Resource
{
    protected static ?string $model = Official::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Situs Web';

    protected static ?string $navigationLabel = 'Profil Desa';

    protected static ?string $modelLabel = 'Profil Desa';

    protected static ?string $pluralModelLabel = 'Profil Desa';

    protected static function structureGroupOptions(): array
    {
        return OfficialStructureTitle::resolvedGroupOptions();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(255),
            Forms\Components\TextInput::make('position')->label('Jabatan')->required()->maxLength(255),
            Forms\Components\Select::make('structure_group')
                ->label('Kelompok Struktur')
                ->options(static::structureGroupOptions())
                ->default(Official::GROUP_OTHER)
                ->required(),
            Forms\Components\TextInput::make('section_title')
                ->label('Judul Struktur (Halaman Publik)')
                ->helperText('Opsional. Jika diisi, judul bagian pada halaman profil akan memakai nilai ini.')
                ->maxLength(255),
            Forms\Components\FileUpload::make('photo')->label('Foto')->image()->disk(config('filesystems.default'))->directory('officials'),
            Forms\Components\TextInput::make('phone')->label('Nomor Telepon')->maxLength(30),
            Forms\Components\TextInput::make('instagram_url')
                ->label('Link Instagram')
                ->url()
                ->maxLength(255)
                ->placeholder('https://instagram.com/username'),
            Forms\Components\TextInput::make('facebook_url')
                ->label('Link Facebook')
                ->url()
                ->maxLength(255)
                ->placeholder('https://facebook.com/username'),
            Forms\Components\TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        $groupOptions = static::structureGroupOptions();

        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')->label('Foto')->disk(config('filesystems.default')),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('position')->label('Jabatan')->searchable(),
                Tables\Columns\TextColumn::make('structure_group')
                    ->label('Kelompok')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $groupOptions[$state] ?? $state),
                Tables\Columns\TextColumn::make('section_title')
                    ->label('Judul Struktur')
                    ->limit(28),
                Tables\Columns\TextColumn::make('instagram_url')
                    ->label('Instagram')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(28),
                Tables\Columns\TextColumn::make('facebook_url')
                    ->label('Facebook')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(28),
                Tables\Columns\ToggleColumn::make('is_active')->label('Aktif'),
                Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('structure_group')
                    ->label('Kelompok')
                    ->options($groupOptions),
            ])
            ->defaultSort('sort_order')
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
            'index' => Pages\ListOfficials::route('/'),
            'create' => Pages\CreateOfficial::route('/create'),
            'edit' => Pages\EditOfficial::route('/{record}/edit'),
        ];
    }
}
