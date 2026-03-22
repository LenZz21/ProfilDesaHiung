<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\CarbonInterface;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    public const AGENDA_STATUS_ONGOING = 'ongoing';
    public const AGENDA_STATUS_UPCOMING = 'upcoming';
    public const AGENDA_STATUS_FINISHED = 'finished';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'start_at',
        'end_at',
        'location',
        'banner',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Event $event) {
            if (! $event->slug || $event->isDirty('title')) {
                $event->slug = Str::slug($event->title) . '-' . Str::lower(Str::random(5));
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function agendaStatus(?CarbonInterface $reference = null): string
    {
        $now = $reference ? Carbon::instance($reference) : now();
        $startAt = $this->start_at;
        $endAt = $this->end_at;

        if (! $startAt) {
            return self::AGENDA_STATUS_UPCOMING;
        }

        if ($startAt->isFuture()) {
            return self::AGENDA_STATUS_UPCOMING;
        }

        if ($endAt instanceof CarbonInterface) {
            if ($now->greaterThan($endAt)) {
                return self::AGENDA_STATUS_FINISHED;
            }

            return self::AGENDA_STATUS_ONGOING;
        }

        // Fallback when end date is not set: event is ongoing until end of start day.
        return $now->greaterThan($startAt->copy()->endOfDay())
            ? self::AGENDA_STATUS_FINISHED
            : self::AGENDA_STATUS_ONGOING;
    }
}
