<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Situs Web';

    protected static ?string $navigationLabel = 'Agenda';

    protected static ?string $modelLabel = 'Agenda';

    protected static ?string $pluralModelLabel = 'Agenda';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->label('Slug')->maxLength(255),
            Forms\Components\RichEditor::make('description')->label('Deskripsi')->required()->columnSpanFull(),
            Forms\Components\DateTimePicker::make('start_at')->label('Waktu Mulai')->required(),
            Forms\Components\DateTimePicker::make('end_at')->label('Waktu Selesai'),
            Forms\Components\TextInput::make('location')->label('Lokasi')->maxLength(255),
            Forms\Components\FileUpload::make('banner')->label('Banner')->image()->disk('public')->directory('events'),
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
                Tables\Columns\ImageColumn::make('banner')->label('Banner')->disk('public'),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('start_at')->label('Waktu Mulai')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('location')->label('Lokasi')->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draf',
                        'published' => 'Dipublikasikan',
                        default => $state,
                    }),
            ])
            ->defaultSort('start_at')
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
