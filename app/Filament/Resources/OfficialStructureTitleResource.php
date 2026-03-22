<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficialStructureTitleResource\Pages;
use App\Models\OfficialStructureTitle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OfficialStructureTitleResource extends Resource
{
    protected static ?string $model = OfficialStructureTitle::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Situs Web';

    protected static ?string $navigationLabel = 'Judul Struktur';

    protected static ?string $modelLabel = 'Judul Struktur';

    protected static ?string $pluralModelLabel = 'Judul Struktur';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('leader_title')
                ->label('Judul Kepala Desa')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('secretary_title')
                ->label('Judul Sekretaris')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('section_heads_title')
                ->label('Judul Kepala Seksi')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('kaur_title')
                ->label('Judul Kaur')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('head_lindongang_title')
                ->label('Judul Kepala Lindongang')
                ->required()
                ->maxLength(255),
            Forms\Components\Repeater::make('additional_titles')
                ->label('Judul Tambahan')
                ->schema([
                    Forms\Components\Hidden::make('key'),
                    Forms\Components\TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->maxLength(255),
                ])
                ->default([])
                ->addActionLabel('Tambah Judul')
                ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('leader_title')->label('Kepala Desa'),
                Tables\Columns\TextColumn::make('secretary_title')->label('Sekretaris'),
                Tables\Columns\TextColumn::make('head_lindongang_title')->label('Kepala Lindongang'),
                Tables\Columns\TextColumn::make('additional_titles')
                    ->label('Judul Tambahan')
                    ->formatStateUsing(fn ($state): string => (string) count((array) $state)),
                Tables\Columns\TextColumn::make('updated_at')->dateTime('d M Y H:i'),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfficialStructureTitles::route('/'),
            'create' => Pages\CreateOfficialStructureTitle::route('/create'),
            'edit' => Pages\EditOfficialStructureTitle::route('/{record}/edit'),
        ];
    }
}
