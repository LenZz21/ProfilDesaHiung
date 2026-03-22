<?php

namespace App\Observers;

use App\Models\LetterSubmission;
use App\Services\AdminNavbarNotificationService;
use App\Services\WhatsAppGatewayNotifier;
use Illuminate\Support\Facades\Log;
use Throwable;

class LetterSubmissionObserver
{
    public function created(LetterSubmission $letterSubmission): void
    {
        // Notifikasi admin harus tetap jalan walau gateway WhatsApp gagal.
        try {
            app(AdminNavbarNotificationService::class)->notifyNewLetterSubmission($letterSubmission);
        } catch (Throwable $exception) {
            Log::warning('Gagal membuat notifikasi admin untuk pengajuan surat.', [
                'submission_id' => $letterSubmission->id,
                'error' => $exception->getMessage(),
            ]);
        }

        try {
            app(WhatsAppGatewayNotifier::class)->notifyLetterSubmissionReceived($letterSubmission);
        } catch (Throwable $exception) {
            Log::warning('Gagal mengirim WhatsApp otomatis pengajuan surat.', [
                'submission_id' => $letterSubmission->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
