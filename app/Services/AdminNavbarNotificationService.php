<?php

namespace App\Services;

use App\Models\ComplaintSubmission;
use App\Models\LetterSubmission;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminNavbarNotificationService
{
    public function notifyNewLetterSubmission(LetterSubmission $submission): void
    {
        $users = User::query()->get();
        if ($users->isEmpty()) {
            return;
        }

        $serviceName = trim((string) ($submission->service_name ?? $submission->service_type ?? 'Pengajuan surat'));
        $fullName = trim((string) ($submission->full_name ?? 'Warga'));

        $notification = Notification::make()
            ->title('Pengajuan Surat Baru')
            ->body($fullName.' mengajukan '.$serviceName.'.')
            ->icon('heroicon-o-document-text')
            ->actions([
                Action::make('open')
                    ->label('Buka')
                    ->button()
                    ->url(route('filament.admin.resources.letter-submissions.index')),
            ]);

        $this->sendToUsersNow($notification, $users);
    }

    public function notifyNewComplaintSubmission(ComplaintSubmission $submission): void
    {
        $users = User::query()->get();
        if ($users->isEmpty()) {
            return;
        }

        $fullName = trim((string) ($submission->full_name ?? 'Warga'));
        $shortComplaint = Str::limit(trim((string) ($submission->complaint ?? '')), 90);
        $body = $shortComplaint !== '' ? ($fullName.': '.$shortComplaint) : ($fullName.' mengirim pengaduan baru.');

        $notification = Notification::make()
            ->title('Pengaduan Baru')
            ->body($body)
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->actions([
                Action::make('open')
                    ->label('Buka')
                    ->button()
                    ->url(route('filament.admin.resources.complaint-submissions.index')),
            ]);

        $this->sendToUsersNow($notification, $users);
    }

    private function sendToUsersNow(Notification $notification, Collection $users): void
    {
        foreach ($users as $user) {
            $user->notifyNow($notification->toDatabase());
        }
    }
}
