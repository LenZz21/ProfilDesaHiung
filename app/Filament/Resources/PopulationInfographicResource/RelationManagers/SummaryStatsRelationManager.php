<?php

namespace App\Filament\Resources\PopulationInfographicResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SummaryStatsRelationManager extends RelationManager
{
    protected static string $relationship = 'summaryStats';

    protected static ?string $title = 'Statistik Umum';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('label')
                ->label('Label')
                ->required()
                ->maxLength(120),
            Forms\Components\TextInput::make('label_en')
                ->label('Label (English)')
                ->maxLength(120),
            Forms\Components\TextInput::make('value')
                ->label('Nilai')
                ->numeric()
                ->required(),
            Forms\Components\ColorPicker::make('color')
                ->label('Warna')
                ->default('#2563eb')
                ->required(),
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
                Tables\Columns\TextColumn::make('label')
                    ->label('Label')
                    ->searchable(),
                Tables\Columns\TextColumn::make('label_en')
                    ->label('Label (EN)')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('value')
                    ->label('Nilai')
                    ->numeric(decimalPlaces: 0),
                Tables\Columns\TextColumn::make('color')->label('Warna'),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
