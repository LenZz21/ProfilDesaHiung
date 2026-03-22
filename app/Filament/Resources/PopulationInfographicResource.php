<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PopulationInfographicResource\Pages;
use App\Filament\Resources\PopulationInfographicResource\RelationManagers\ChartSectionsRelationManager;
use App\Filament\Resources\PopulationInfographicResource\RelationManagers\SummaryStatsRelationManager;
use App\Models\PopulationInfographic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;

class PopulationInfographicResource extends Resource
{
    protected static ?string $model = PopulationInfographic::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Situs Web';

    protected static ?string $navigationLabel = 'Infografis Penduduk';

    protected static ?string $modelLabel = 'Infografis Penduduk';

    protected static ?string $pluralModelLabel = 'Infografis Penduduk';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Halaman')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->default('Infografis Penduduk')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('title_en')
                        ->label('Judul (English)')
                        ->maxLength(255)
                        ->helperText('Opsional. Jika kosong, website akan menggunakan judul Indonesia.'),
                    Forms\Components\Textarea::make('subtitle')
                        ->label('Subjudul')
                        ->rows(2)
                        ->maxLength(500),
                    Forms\Components\Textarea::make('subtitle_en')
                        ->label('Subjudul (English)')
                        ->rows(2)
                        ->maxLength(500)
                        ->helperText('Opsional. Jika kosong, website akan menggunakan subjudul Indonesia.'),
                    Forms\Components\FileUpload::make('hero_image')
                        ->label('Gambar Utama')
                        ->image()
                        ->disk('public')
                        ->directory('infographics')
                        ->visibility('public')
                        ->imageEditor()
                        ->maxSize(4096)
                        ->helperText('Pilih gambar dari perangkat Anda. Maksimal 4MB.'),
                ])
                ->columns(2),
            Forms\Components\Placeholder::make('manage_data_note')
                ->label('Pengolahan Data')
                ->content('Data Statistik Umum dan Bagian Grafik dikelola melalui tab manajer relasi di bawah form ini.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title_en')
                    ->label('Judul (EN)')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('summary_stats_count')
                    ->label('Statistik Umum')
                    ->formatStateUsing(fn (int $state): string => $state . ' entri'),
                Tables\Columns\TextColumn::make('chart_sections_count')
                    ->label('Bagian Grafik')
                    ->formatStateUsing(fn (int $state): string => $state . ' bagian'),
                Tables\Columns\TextColumn::make('chart_items_count')
                    ->label('Total Data Grafik')
                    ->formatStateUsing(fn (int $state): string => $state . ' entri'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->actionsPosition(ActionsPosition::BeforeColumns)
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            SummaryStatsRelationManager::class,
            ChartSectionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPopulationInfographics::route('/'),
            'create' => Pages\CreatePopulationInfographic::route('/create'),
            'edit' => Pages\EditPopulationInfographic::route('/{record}/edit'),
        ];
    }
}
