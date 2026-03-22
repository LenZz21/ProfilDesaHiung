<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PopulationChartSectionResource\Pages;
use App\Filament\Resources\PopulationChartSectionResource\RelationManagers\ItemsRelationManager;
use App\Models\PopulationChartSection;
use App\Models\PopulationInfographic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PopulationChartSectionResource extends Resource
{
    protected static ?string $model = PopulationChartSection::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Situs Web';

    protected static ?string $navigationLabel = 'Bagian Grafik Penduduk';

    protected static ?string $modelLabel = 'Bagian Grafik Penduduk';

    protected static ?string $pluralModelLabel = 'Bagian Grafik Penduduk';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('population_infographic_id')
                ->label('Infografis')
                ->options(PopulationInfographic::query()->pluck('title', 'id'))
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('title')
                ->label('Judul')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('title_en')
                ->label('Judul (English)')
                ->maxLength(255),
            Forms\Components\Select::make('type')
                ->label('Jenis Grafik')
                ->required()
                ->default('bar')
                ->options([
                    'bar' => 'Batang',
                    'line' => 'Garis',
                    'pie' => 'Pai',
                    'doughnut' => 'Donat',
                    'radar' => 'Radar',
                    'polarArea' => 'Area Polar',
                ]),
            Forms\Components\TextInput::make('sort_order')
                ->label('Urutan')
                ->numeric()
                ->default(0)
                ->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('infographic.title')
                    ->label('Infografis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title_en')
                    ->label('Judul (EN)')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis Grafik')
                    ->badge(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Jumlah Item'),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPopulationChartSections::route('/'),
            'create' => Pages\CreatePopulationChartSection::route('/create'),
            'edit' => Pages\EditPopulationChartSection::route('/{record}/edit'),
        ];
    }
}
