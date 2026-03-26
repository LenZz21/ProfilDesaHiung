<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LetterSubmissionResource\Pages;
use App\Models\LetterSubmission;
use App\Services\WhatsAppGatewayNotifier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class LetterSubmissionResource extends Resource
{
    protected static ?string $model = LetterSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Layanan Warga';

    protected static ?string $navigationLabel = 'Pengajuan Surat';

    protected static ?string $modelLabel = 'Pengajuan Surat';

    protected static ?string $pluralModelLabel = 'Pengajuan Surat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Pemohon')
                    ->schema([
                        Forms\Components\TextInput::make('service_name')
                            ->label('Jenis Surat')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('full_name')
                            ->label('Nama Lengkap')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('whatsapp')
                            ->label('Nomor WhatsApp')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Textarea::make('purpose')
                            ->label('Keperluan')
                            ->rows(4)
                            ->columnSpanFull()
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Tindak Lanjut Admin')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options(LetterSubmission::statusOptions()),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Catatan Admin')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service_name')
                    ->label('Jenis Surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable(),
                Tables\Columns\TextColumn::make('whatsapp')
                    ->label('WhatsApp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        LetterSubmission::STATUS_BARU => 'danger',
                        LetterSubmission::STATUS_DITERIMA => 'info',
                        LetterSubmission::STATUS_DIPROSES => 'warning',
                        LetterSubmission::STATUS_SELESAI => 'success',
                        LetterSubmission::STATUS_DITOLAK => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => LetterSubmission::statusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Kirim')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(LetterSubmission::statusOptions()),
            ])
            ->poll('5s')
            ->actions([
                Tables\Actions\Action::make('accept')
                    ->label('Terima')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->button()
                    ->requiresConfirmation()
                    ->visible(fn (LetterSubmission $record): bool => $record->status === LetterSubmission::STATUS_BARU)
                    ->action(function (LetterSubmission $record): void {
                        $record->update(['status' => LetterSubmission::STATUS_DITERIMA]);
                        $record = $record->fresh();
                        $sent = app(WhatsAppGatewayNotifier::class)->notifyLetterSubmissionStatus($record, 'accepted');

                        if ($sent) {
                            Notification::make()
                                ->title('Pengajuan diterima. Notifikasi WhatsApp terkirim.')
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Pengajuan diterima, tetapi notifikasi WhatsApp tidak terkirim.')
                            ->warning()
                            ->send();
                    }),
                Tables\Actions\Action::make('finish')
                    ->label('Kirim')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\FileUpload::make('result_file')
                            ->label('File Surat')
                            ->required()
                            ->disk('public')
                            ->directory('letter-submission-results')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'image/jpeg',
                                'image/png',
                            ])
                            ->maxSize(10240)
                            ->fetchFileInformation(false)
                            ->helperText('Maksimal 10MB. Format: PDF, DOC, DOCX, JPG, PNG.'),
                    ])
                    ->visible(fn (LetterSubmission $record): bool => in_array($record->status, [
                        LetterSubmission::STATUS_DITERIMA,
                        LetterSubmission::STATUS_DIPROSES,
                    ], true))
                    ->action(function (LetterSubmission $record, array $data): void {
                        $record->update(['status' => LetterSubmission::STATUS_SELESAI]);
                        $record = $record->fresh();

                        $relativePath = (string) ($data['result_file'] ?? '');
                        $absolutePath = $relativePath !== '' ? Storage::disk('public')->path($relativePath) : '';

                        $sent = app(WhatsAppGatewayNotifier::class)->notifyLetterSubmissionDoneWithFile($record, $absolutePath);

                        if ($sent) {
                            Notification::make()
                                ->title('Pengajuan diselesaikan dan file dikirim ke WhatsApp.')
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Pengajuan diselesaikan, tetapi notifikasi WhatsApp gagal dikirim.')
                            ->warning()
                            ->send();
                    }),
                Tables\Actions\EditAction::make()
                    ->label('Detail'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLetterSubmissions::route('/'),
        ];
    }
}
