<?php

namespace App\Filament\Pages;

use App\Models\WhatsAppSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

class WhatsAppBotPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Layanan Warga';

    protected static ?string $navigationLabel = 'WhatsApp Gateway';

    protected static ?string $title = 'WhatsApp Gateway';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.whatsapp-bot';

    public string $gatewayBaseUrl = '';

    public bool $gatewayOnline = false;

    public string $gatewayStatus = 'offline';

    public bool $qrAvailable = false;

    public string $qrViewUrl = '';

    public string $qrImageUrl = '';

    public bool $notifyOnLetterSubmission = true;

    public string $notifyLetterTemplate = '';

    public string $notifyLetterAcceptedTemplate = '';

    public string $notifyLetterDoneTemplate = '';

    public bool $chatAutoReplyEnabled = true;

    public string $chatAutoReplyTemplate = '';

    public int $chatAutoReplyCooldownSeconds = 900;

    public string $chatAutoReplyIgnoreNumbers = '';

    public function mount(): void
    {
        $this->gatewayBaseUrl = rtrim((string) env('WHATSAPP_GATEWAY_BASE_URL', 'http://127.0.0.1:3070'), '/');
        $this->refreshQrUrls();
        $this->loadMessageTemplateSettings();

        $this->refreshGatewayStatus();
    }

    public function saveMessageTemplateSettings(): void
    {
        WhatsAppSetting::setValue(
            'notify_on_letter_submission',
            $this->notifyOnLetterSubmission ? '1' : '0'
        );
        WhatsAppSetting::setValue('notify_letter_template', trim($this->notifyLetterTemplate));
        WhatsAppSetting::setValue('notify_letter_accepted_template', trim($this->notifyLetterAcceptedTemplate));
        WhatsAppSetting::setValue('notify_letter_done_template', trim($this->notifyLetterDoneTemplate));
        WhatsAppSetting::setValue(
            'gateway_auto_reply_enabled',
            $this->chatAutoReplyEnabled ? '1' : '0'
        );
        WhatsAppSetting::setValue('gateway_auto_reply_text', trim($this->chatAutoReplyTemplate));
        WhatsAppSetting::setValue(
            'gateway_auto_reply_cooldown_seconds',
            (string) max(0, (int) $this->chatAutoReplyCooldownSeconds)
        );
        WhatsAppSetting::setValue(
            'gateway_auto_reply_ignore_numbers',
            trim($this->chatAutoReplyIgnoreNumbers)
        );

        $this->syncGatewayRuntimeSettingsFile();

        Notification::make()
            ->title('Template pesan bot berhasil disimpan.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh Status')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (): void {
                    $this->refreshGatewayStatus();

                    Notification::make()
                        ->title('Status WhatsApp bot diperbarui.')
                        ->success()
                        ->send();
                }),
            Action::make('openQr')
                ->label('Buka QR Login')
                ->icon('heroicon-o-qr-code')
                ->color('gray')
                ->url(fn (): string => $this->qrViewUrl, shouldOpenInNewTab: true),
            Action::make('sendTest')
                ->label('Kirim Tes')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->disabled(fn (): bool => ! $this->gatewayOnline)
                ->form([
                    TextInput::make('phone')
                        ->label('Nomor WhatsApp')
                        ->helperText('Format bebas: 08..., 62..., atau +62...')
                        ->required()
                        ->maxLength(25),
                    Textarea::make('message')
                        ->label('Pesan')
                        ->required()
                        ->rows(4)
                        ->default('Halo, ini pesan tes dari dashboard admin.'),
                ])
                ->action(function (array $data): void {
                    try {
                        $response = Http::timeout(8)
                            ->acceptJson()
                            ->post($this->gatewayBaseUrl.'/api/v1/send-message', [
                                'to' => (string) ($data['phone'] ?? ''),
                                'message' => (string) ($data['message'] ?? ''),
                            ]);

                        if (! $response->successful()) {
                            Notification::make()
                                ->title('Gagal kirim pesan tes.')
                                ->body((string) ($response->json('message') ?? $response->body()))
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Pesan tes berhasil dikirim.')
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        report($exception);

                        Notification::make()
                            ->title('Gateway tidak terhubung.')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function refreshGatewayStatus(): void
    {
        $this->gatewayOnline = false;
        $this->gatewayStatus = 'offline';
        $this->qrAvailable = false;
        $this->refreshQrUrls();

        try {
            $response = Http::timeout(4)
                ->acceptJson()
                ->get($this->gatewayBaseUrl.'/api/v1/status');

            if (! $response->successful()) {
                return;
            }

            $this->gatewayOnline = true;
            $this->gatewayStatus = (string) ($response->json('status') ?? 'online');
            $this->qrAvailable = (bool) ($response->json('qr_available') ?? false);
        } catch (Throwable) {
            // keep offline defaults
        }
    }

    private function refreshQrUrls(): void
    {
        $cacheBuster = (int) floor(microtime(true) * 1000);
        $this->qrViewUrl = $this->gatewayBaseUrl.'/api/v1/qr-view?ts='.$cacheBuster;
        $this->qrImageUrl = $this->gatewayBaseUrl.'/api/v1/qr-image?ts='.$cacheBuster;
    }

    private function loadMessageTemplateSettings(): void
    {
        $enabledValue = WhatsAppSetting::getValue(
            'notify_on_letter_submission',
            env('WHATSAPP_NOTIFY_ON_LETTER_SUBMISSION', true) ? '1' : '0'
        );
        $this->notifyOnLetterSubmission = in_array(strtolower((string) $enabledValue), ['1', 'true', 'yes', 'on'], true);

        $this->notifyLetterTemplate = (string) WhatsAppSetting::getValue(
            'notify_letter_template',
            (string) env(
                'WHATSAPP_NOTIFY_LETTER_TEMPLATE',
                'Halo {name}, pengajuan layanan "{service_name}" sudah kami terima dan saat ini sedang diproses oleh admin kampung. Nomor pengajuan: #{id}. Terima kasih.'
            )
        );
        $this->notifyLetterAcceptedTemplate = (string) WhatsAppSetting::getValue(
            'notify_letter_accepted_template',
            (string) env(
                'WHATSAPP_NOTIFY_LETTER_ACCEPTED_TEMPLATE',
                'Halo {name}, pengajuan layanan "{service_name}" sudah kami terima dengan baik. Keperluan Anda: "{purpose}". Nomor pengajuan: #{id}.'
            )
        );
        $this->notifyLetterDoneTemplate = (string) WhatsAppSetting::getValue(
            'notify_letter_done_template',
            (string) env(
                'WHATSAPP_NOTIFY_LETTER_DONE_TEMPLATE',
                'Halo {name}, pengajuan "{service_name}" (#{id}) sudah selesai diproses. Silakan hubungi admin untuk tindak lanjut.'
            )
        );

        $chatEnabled = WhatsAppSetting::getValue(
            'gateway_auto_reply_enabled',
            env('WA_AUTO_REPLY_ENABLED', true) ? '1' : '0'
        );
        $this->chatAutoReplyEnabled = in_array(strtolower((string) $chatEnabled), ['1', 'true', 'yes', 'on'], true);

        $this->chatAutoReplyTemplate = (string) WhatsAppSetting::getValue(
            'gateway_auto_reply_text',
            (string) env(
                'WA_AUTO_REPLY_TEXT',
                'Halo {name}, pesan Anda sudah kami terima. Mohon tunggu, admin kami akan segera merespons.'
            )
        );

        $cooldownValue = (string) WhatsAppSetting::getValue(
            'gateway_auto_reply_cooldown_seconds',
            (string) env('WA_AUTO_REPLY_COOLDOWN_SECONDS', 900)
        );
        $this->chatAutoReplyCooldownSeconds = max(0, (int) $cooldownValue);

        $this->chatAutoReplyIgnoreNumbers = (string) WhatsAppSetting::getValue(
            'gateway_auto_reply_ignore_numbers',
            (string) env('WA_AUTO_REPLY_IGNORE_NUMBERS', '')
        );
    }

    private function syncGatewayRuntimeSettingsFile(): void
    {
        $runtimePath = base_path('tools/whatsapp-gateway/runtime-settings.json');
        $payload = [
            'auto_reply_enabled' => $this->chatAutoReplyEnabled,
            'auto_reply_text' => trim($this->chatAutoReplyTemplate),
            'auto_reply_cooldown_seconds' => max(0, (int) $this->chatAutoReplyCooldownSeconds),
            'auto_reply_ignore_numbers' => trim($this->chatAutoReplyIgnoreNumbers),
        ];

        File::put(
            $runtimePath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
