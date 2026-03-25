<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeFinishedEvents extends Command
{
    protected $signature = 'events:purge-finished {--days=3 : Hapus agenda yang selesai lebih dari X hari} {--dry-run : Tampilkan jumlah data tanpa menghapus}';

    protected $description = 'Hapus agenda yang sudah selesai dan melewati batas hari tertentu';

    public function handle(): int
    {
        $days = max((int) $this->option('days'), 1);
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $query = Event::query()
            ->where(function ($builder) use ($cutoff) {
                $builder
                    ->where(function ($inner) use ($cutoff) {
                        $inner->whereNotNull('end_at')
                            ->where('end_at', '<=', $cutoff);
                    })
                    ->orWhere(function ($inner) use ($cutoff) {
                        $inner->whereNull('end_at')
                            ->where('start_at', '<=', $cutoff);
                    });
            });

        $totalCandidates = (clone $query)->count();

        if ($totalCandidates === 0) {
            $this->info('Tidak ada agenda selesai yang melewati batas hapus.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info("Dry-run: {$totalCandidates} agenda akan dihapus (cutoff: {$cutoff->toDateTimeString()}).");

            return self::SUCCESS;
        }

        $deletedCount = 0;

        (clone $query)
            ->orderBy('id')
            ->chunkById(100, function ($events) use (&$deletedCount) {
                foreach ($events as $event) {
                    if (filled($event->banner)) {
                        Storage::disk(config('filesystems.default'))->delete($event->banner);
                    }

                    $event->delete();
                    $deletedCount++;
                }
            });

        $this->info("Berhasil menghapus {$deletedCount} agenda selesai yang melewati {$days} hari.");

        return self::SUCCESS;
    }
}
