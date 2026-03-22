<?php

namespace App\Services;

use App\Models\LetterSubmission;
use App\Models\WhatsAppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppGatewayNotifier
{
    public function notifyLetterSubmissionReceived(LetterSubmission $submission): void
    {
        if (! $this->isLetterSubmissionNotificationEnabled()) {
            return;
        }

        if (blank($submission->whatsapp)) {
            return;
        }

        $baseUrl = rtrim((string) env('WHATSAPP_GATEWAY_BASE_URL', 'http://127.0.0.1:3070'), '/');
        if ($baseUrl === '') {
            return;
        }

        $message = $this->buildLetterSubmissionReceivedMessage($submission);
        if (blank($message)) {
            return;
        }

        $this->sendMessageToSubmission($submission, $message);
    }

    public function notifyLetterSubmissionStatus(LetterSubmission $submission, string $statusKey): bool
    {
        if (! $this->isLetterSubmissionStatusNotificationEnabled()) {
            return false;
        }

        if (blank($submission->whatsapp)) {
            return false;
        }

        $message = $this->buildStatusMessage($submission, $statusKey);
        if ($message === '') {
            return false;
        }

        return $this->sendMessageToSubmission($submission, $message);
    }

    public function notifyLetterSubmissionDoneWithFile(LetterSubmission $submission, string $absoluteFilePath): bool
    {
        if (! $this->isLetterSubmissionStatusNotificationEnabled()) {
            return false;
        }

        if (blank($submission->whatsapp) || blank($absoluteFilePath) || ! is_file($absoluteFilePath)) {
            return false;
        }

        $baseUrl = rtrim((string) env('WHATSAPP_GATEWAY_BASE_URL', 'http://127.0.0.1:3070'), '/');
        if ($baseUrl === '') {
            return false;
        }

        $token = trim((string) env('WHATSAPP_GATEWAY_API_TOKEN', ''));
        $caption = $this->buildStatusMessage($submission, 'done');
        $payload = [
            'to' => (string) $submission->whatsapp,
            'path' => $absoluteFilePath,
            'filename' => basename($absoluteFilePath),
            'caption' => $caption,
        ];

        try {
            $request = Http::connectTimeout(2)
                ->timeout(20)
                ->acceptJson();

            if ($token !== '') {
                $request = $request->withToken($token);
            }

            $response = $request->post($baseUrl.'/api/v1/send-file', $payload);
            if ($response->successful()) {
                return true;
            }

            Log::warning('Gagal kirim file WhatsApp pengajuan surat.', [
                'submission_id' => $submission->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Gagal kirim file WhatsApp pengajuan surat.', [
                'submission_id' => $submission->id,
                'error' => $exception->getMessage(),
            ]);
        }

        // Fallback tetap kirim teks selesai meski kirim file gagal.
        return $this->notifyLetterSubmissionStatus($submission, 'done');
    }

    private function sendMessageToSubmission(LetterSubmission $submission, string $message): bool
    {
        $token = trim((string) env('WHATSAPP_GATEWAY_API_TOKEN', ''));
        $lastExceptionMessage = null;
        $lastStatus = null;
        $lastBody = null;
        $baseUrl = rtrim((string) env('WHATSAPP_GATEWAY_BASE_URL', 'http://127.0.0.1:3070'), '/');

        if ($baseUrl === '' || blank($message)) {
            return false;
        }

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $request = Http::connectTimeout(2)
                    ->timeout(6)
                    ->acceptJson();

                if ($token !== '') {
                    $request = $request->withToken($token);
                }

                $response = $request->post($baseUrl.'/api/v1/send-message', [
                    'to' => (string) $submission->whatsapp,
                    'message' => $message,
                ]);

                if ($response->successful()) {
                    return true;
                }

                $lastStatus = $response->status();
                $lastBody = $response->body();

                $isRetriableHttp = in_array($lastStatus, [500, 502, 503, 504], true);
                $isRetriableBody = str_contains(strtolower($lastBody), 'detached frame')
                    || str_contains(strtolower($lastBody), 'transisi');

                if (($isRetriableHttp || $isRetriableBody) && $attempt < 3) {
                    usleep(400000 * $attempt);
                    continue;
                }

                break;
            } catch (Throwable $exception) {
                $lastExceptionMessage = $exception->getMessage();
                if ($attempt < 3) {
                    usleep(400000 * $attempt);
                    continue;
                }
                break;
            }
        }

        Log::warning('Gagal kirim notifikasi WhatsApp pengajuan surat.', [
            'submission_id' => $submission->id,
            'status' => $lastStatus,
            'body' => $lastBody,
            'error' => $lastExceptionMessage,
        ]);

        return false;
    }

    private function isLetterSubmissionNotificationEnabled(): bool
    {
        $settingValue = WhatsAppSetting::getValue('notify_on_letter_submission');
        if ($settingValue !== null) {
            return in_array(strtolower($settingValue), ['1', 'true', 'yes', 'on'], true);
        }

        return filter_var(
            env('WHATSAPP_NOTIFY_ON_LETTER_SUBMISSION', true),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        ) !== false;
    }

    private function isLetterSubmissionStatusNotificationEnabled(): bool
    {
        $settingValue = WhatsAppSetting::getValue('notify_on_letter_status');
        if ($settingValue !== null) {
            return in_array(strtolower($settingValue), ['1', 'true', 'yes', 'on'], true);
        }

        // Status notifikasi (Terima/Kirim) default aktif walau pengajuan masuk dimatikan.
        return filter_var(
            env('WHATSAPP_NOTIFY_ON_LETTER_STATUS', true),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        ) !== false;
    }

    private function buildLetterSubmissionReceivedMessage(LetterSubmission $submission): string
    {
        $template = $this->getTemplateValue(
            'notify_letter_template',
            'WHATSAPP_NOTIFY_LETTER_TEMPLATE',
            'Halo {name}, pengajuan layanan "{service_name}" sudah kami terima dan saat ini sedang diproses oleh admin kampung. Nomor pengajuan: #{id}. Terima kasih.'
        );

        return strtr($template, [
            '{id}' => (string) $submission->id,
            '{name}' => (string) $submission->full_name,
            '{service_name}' => (string) $submission->service_name,
            '{purpose}' => (string) $submission->purpose,
            '{status}' => 'diproses',
        ]);
    }

    private function buildStatusMessage(LetterSubmission $submission, string $statusKey): string
    {
        $template = match ($statusKey) {
            'accepted' => $this->getTemplateValue(
                'notify_letter_accepted_template',
                'WHATSAPP_NOTIFY_LETTER_ACCEPTED_TEMPLATE',
                'Halo {name}, pengajuan layanan "{service_name}" sudah kami terima. Nomor pengajuan: #{id}.'
            ),
            'processing' => $this->getTemplateValue(
                'notify_letter_process_template',
                'WHATSAPP_NOTIFY_LETTER_PROCESS_TEMPLATE',
                'Halo {name}, pengajuan "{service_name}" (#{id}) sedang diproses oleh admin kampung.'
            ),
            'done' => $this->getTemplateValue(
                'notify_letter_done_template',
                'WHATSAPP_NOTIFY_LETTER_DONE_TEMPLATE',
                'Halo {name}, pengajuan "{service_name}" (#{id}) sudah selesai diproses. Silakan hubungi admin untuk tindak lanjut.'
            ),
            default => '',
        };

        if ($template === '') {
            return '';
        }

        return strtr($template, [
            '{id}' => (string) $submission->id,
            '{name}' => (string) $submission->full_name,
            '{service_name}' => (string) $submission->service_name,
            '{purpose}' => (string) $submission->purpose,
            '{status}' => (string) $submission->status,
        ]);
    }

    private function getTemplateValue(string $dbKey, string $envKey, string $default): string
    {
        return trim((string) WhatsAppSetting::getValue(
            $dbKey,
            (string) env($envKey, $default)
        ));
    }
}
