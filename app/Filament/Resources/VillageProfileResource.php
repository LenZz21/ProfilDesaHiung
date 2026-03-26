<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VillageProfileResource\Pages;
use App\Models\VillageProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VillageProfileResource extends Resource
{
    protected static ?string $model = VillageProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Situs Web';

    protected static ?string $navigationLabel = 'Beranda';

    protected static ?string $modelLabel = 'Beranda';

    protected static ?string $pluralModelLabel = 'Beranda';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('address')->label('Alamat')->rows(3),
            Forms\Components\Textarea::make('about')
                ->label('Selayang Pandang (Beranda)')
                ->rows(8)
                ->helperText('Konten ini tampil pada bagian Selayang Pandang di beranda. Pisahkan paragraf dengan baris kosong.'),
            Forms\Components\Textarea::make('about_en')
                ->label('Selayang Pandang (English)')
                ->rows(8)
                ->helperText('Konten ini tampil pada beranda saat bahasa Inggris aktif. Pisahkan paragraf dengan baris kosong.'),
            Forms\Components\Textarea::make('history')->label('Sejarah')->rows(8),
            Forms\Components\TextInput::make('map_embed')
                ->label('URL Sematan Peta')
                ->url()
                ->helperText('Boleh tempel link Google Maps biasa / short-link (mis. maps.app.goo.gl). Sistem akan konversi otomatis ke format embed.'),
            Forms\Components\Section::make('Latar Beranda')
                ->description('Atur 3 gambar latar untuk slideshow utama beranda.')
                ->schema([
                    Forms\Components\FileUpload::make('home_background_image_1')
                        ->label('Gambar Beranda 1')
                        ->image()
                        ->disk(config('filesystems.default'))
                        ->directory('home-backgrounds')
                        ->visibility('public')
                        ->fetchFileInformation(false),
                    Forms\Components\FileUpload::make('home_background_image_2')
                        ->label('Gambar Beranda 2')
                        ->image()
                        ->disk(config('filesystems.default'))
                        ->directory('home-backgrounds')
                        ->visibility('public')
                        ->fetchFileInformation(false),
                    Forms\Components\FileUpload::make('home_background_image_3')
                        ->label('Gambar Beranda 3')
                        ->image()
                        ->disk(config('filesystems.default'))
                        ->directory('home-backgrounds')
                        ->visibility('public')
                        ->fetchFileInformation(false),
                ])
                ->columns(3),
            Forms\Components\TextInput::make('whatsapp')->label('Nomor WhatsApp')->maxLength(30),
            Forms\Components\TextInput::make('email')->label('Email')->email()->maxLength(255),
            Forms\Components\TextInput::make('facebook_url')
                ->label('URL Facebook')
                ->url()
                ->maxLength(255)
                ->placeholder('https://facebook.com/namakampung'),
            Forms\Components\TextInput::make('instagram_url')
                ->label('URL Instagram')
                ->url()
                ->maxLength(255)
                ->placeholder('https://instagram.com/namakampung'),
            Forms\Components\TextInput::make('x_url')
                ->label('URL X (Twitter)')
                ->url()
                ->maxLength(255)
                ->placeholder('https://x.com/namakampung'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Kampung')->searchable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Terakhir Diubah')->dateTime('d M Y H:i'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVillageProfiles::route('/'),
            'edit' => Pages\EditVillageProfile::route('/{record}/edit'),
        ];
    }
}
