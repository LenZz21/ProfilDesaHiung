<?php

namespace App\Observers;

use App\Models\ComplaintSubmission;
use App\Services\AdminNavbarNotificationService;
use Illuminate\Support\Facades\Log;
use Throwable;

class ComplaintSubmissionObserver
{
    public function created(ComplaintSubmission $complaintSubmission): void
    {
        try {
            app(AdminNavbarNotificationService::class)->notifyNewComplaintSubmission($complaintSubmission);
        } catch (Throwable $exception) {
            Log::warning('Gagal membuat notifikasi admin untuk pengaduan.', [
                'submission_id' => $complaintSubmission->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
