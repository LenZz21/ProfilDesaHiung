<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OfficialStructureTitle extends Model
{
    protected $fillable = [
        'leader_title',
        'secretary_title',
        'section_heads_title',
        'kaur_title',
        'head_lindongang_title',
        'additional_titles',
    ];

    protected function casts(): array
    {
        return [
            'additional_titles' => 'array',
        ];
    }

    public static function current(): ?self
    {
        return static::query()->latest('id')->first();
    }

    public static function resolvedTitles(?self $record = null): array
    {
        $record ??= static::current();
        $defaults = static::defaults();

        return [
            'leader_title' => $record?->leader_title ?: $defaults['leader_title'],
            'secretary_title' => $record?->secretary_title ?: $defaults['secretary_title'],
            'section_heads_title' => $record?->section_heads_title ?: $defaults['section_heads_title'],
            'kaur_title' => $record?->kaur_title ?: $defaults['kaur_title'],
            'head_lindongang_title' => $record?->head_lindongang_title ?: $defaults['head_lindongang_title'],
        ];
    }

    public static function normalizeAdditionalTitles(array $rows): array
    {
        $reservedKeys = [
            Official::GROUP_LEADER,
            Official::GROUP_SECRETARY,
            Official::GROUP_SECTION_HEADS,
            Official::GROUP_KAUR,
            Official::GROUP_HEAD_LINDONGANG,
            Official::GROUP_OTHER,
        ];

        $usedKeys = [];
        $normalized = [];

        foreach ($rows as $row) {
            $title = trim((string) ($row['title'] ?? ''));

            if ($title === '') {
                continue;
            }

            $baseKey = trim((string) ($row['key'] ?? ''));
            $baseKey = $baseKey !== '' ? $baseKey : Str::slug($title, '_');
            $baseKey = $baseKey !== '' ? $baseKey : 'group';
            $baseKey = Str::slug($baseKey, '_');

            if ($baseKey === '') {
                $baseKey = 'group';
            }

            $key = $baseKey;
            $suffix = 2;

            while (in_array($key, $reservedKeys, true) || in_array($key, $usedKeys, true)) {
                $key = "{$baseKey}_{$suffix}";
                $suffix++;
            }

            $usedKeys[] = $key;

            $normalized[] = [
                'key' => $key,
                'title' => $title,
            ];
        }

        return $normalized;
    }

    public static function additionalGroupTitles(?self $record = null): array
    {
        $record ??= static::current();
        $rows = static::normalizeAdditionalTitles((array) ($record?->additional_titles ?? []));
        $map = [];

        foreach ($rows as $row) {
            $map[$row['key']] = $row['title'];
        }

        return $map;
    }

    public static function resolvedGroupOptions(?self $record = null): array
    {
        $titles = static::resolvedTitles($record);

        return [
            Official::GROUP_LEADER => $titles['leader_title'],
            Official::GROUP_SECRETARY => $titles['secretary_title'],
            Official::GROUP_SECTION_HEADS => $titles['section_heads_title'],
            Official::GROUP_KAUR => $titles['kaur_title'],
            Official::GROUP_HEAD_LINDONGANG => $titles['head_lindongang_title'],
            ...static::additionalGroupTitles($record),
            Official::GROUP_OTHER => 'Lainnya',
        ];
    }

    public static function defaults(): array
    {
        return [
            'leader_title' => 'KEPALA DESA',
            'secretary_title' => 'SEKRETARIS DESA',
            'section_heads_title' => 'KEPALA SEKSI',
            'kaur_title' => 'KAUR',
            'head_lindongang_title' => 'KEPALA LINDONGANG',
            'additional_titles' => [],
        ];
    }
}
