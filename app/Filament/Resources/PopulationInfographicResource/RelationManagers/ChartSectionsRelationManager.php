<?php

namespace App\Filament\Resources\PopulationInfographicResource\RelationManagers;

use App\Filament\Resources\PopulationChartSectionResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ChartSectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'chartSections';

    protected static ?string $title = 'Bagian Grafik';

    public function form(Form $form): Form
    {
        return $form->schema([
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

    public function table(Table $table): Table
    {
        return $table
            ->columns([
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
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('manage_items')
                    ->label('Kelola Item')
                    ->icon('heroicon-o-list-bullet')
                    ->url(fn (Model $record): string => PopulationChartSectionResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
