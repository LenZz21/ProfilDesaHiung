<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PageSetting extends Model
{
    public const PAGE_HOME = 'home';
    public const PAGE_PROFILE = 'profile';
    public const PAGE_SERVICES = 'services';
    public const PAGE_GALLERIES = 'galleries';
    public const PAGE_POSTS = 'posts';
    public const PAGE_EVENTS = 'events';
    public const PAGE_DOCUMENTS = 'documents';
    public const PAGE_INFOGRAPHICS = 'infographics';

    protected $fillable = [
        'page_key',
        'title',
        'subtitle',
        'hero_image',
    ];

    public static function pageOptions(): array
    {
        return [
            self::PAGE_HOME => 'Beranda',
            self::PAGE_PROFILE => 'Profil Desa',
            self::PAGE_SERVICES => 'Layanan',
            self::PAGE_GALLERIES => 'Galeri',
            self::PAGE_POSTS => 'Berita',
            self::PAGE_EVENTS => 'Agenda',
            self::PAGE_DOCUMENTS => 'Publikasi',
            self::PAGE_INFOGRAPHICS => 'Infografis Penduduk',
        ];
    }

    public static function defaults(): array
    {
        return [
            self::PAGE_HOME => [
                'title' => 'Website Resmi Kampung',
                'subtitle' => 'Sumber informasi terbaru tentang pemerintahan kampung.',
                'hero_image' => null,
            ],
            self::PAGE_PROFILE => [
                'title' => 'Profil Desa',
                'subtitle' => 'Mengenal lebih dekat visi misi, sejarah, dan potensi desa.',
                'hero_image' => null,
            ],
            self::PAGE_SERVICES => [
                'title' => 'Layanan Kampung',
                'subtitle' => 'Nikmati kemudahan layanan digital untuk kebutuhan administrasi, pengaduan, dan informasi publik.',
                'hero_image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1920&q=80',
            ],
            self::PAGE_GALLERIES => [
                'title' => 'Galeri Kampung',
                'subtitle' => 'Dokumentasi kegiatan, pembangunan, dan momen penting masyarakat kampung.',
                'hero_image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=1920&q=80',
            ],
            self::PAGE_POSTS => [
                'title' => 'Berita Kampung',
                'subtitle' => 'Kumpulan informasi dan kegiatan terbaru di kampung.',
                'hero_image' => 'https://images.unsplash.com/photo-1495020689067-958852a7765e?auto=format&fit=crop&w=1920&q=80',
            ],
            self::PAGE_EVENTS => [
                'title' => 'Agenda Kampung',
                'subtitle' => 'Informasi jadwal kegiatan kampung yang sedang berjalan dan akan datang.',
                'hero_image' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1920&q=80',
            ],
            self::PAGE_DOCUMENTS => [
                'title' => 'Publikasi Dokumen',
                'subtitle' => 'Dokumen publikasi dan informasi resmi desa.',
                'hero_image' => null,
            ],
            self::PAGE_INFOGRAPHICS => [
                'title' => 'Infografis Penduduk',
                'subtitle' => 'Statistik kependudukan kampung yang terintegrasi dan transparan.',
                'hero_image' => null,
            ],
        ];
    }

    public static function ensureDefaults(): void
    {
        foreach (self::pageOptions() as $pageKey => $label) {
            $default = self::defaults()[$pageKey] ?? [];

            self::query()->firstOrCreate(
                ['page_key' => $pageKey],
                [
                    'title' => (string) ($default['title'] ?? $label),
                    'subtitle' => (string) ($default['subtitle'] ?? ''),
                    'hero_image' => $default['hero_image'] ?? null,
                ]
            );
        }
    }

    public static function resolve(string $pageKey): array
    {
        $default = self::defaults()[$pageKey] ?? [
            'title' => '',
            'subtitle' => '',
            'hero_image' => null,
        ];

        $record = self::query()->where('page_key', $pageKey)->first();
        $title = trim((string) ($record?->title ?? $default['title'] ?? ''));
        $subtitle = trim((string) ($record?->subtitle ?? $default['subtitle'] ?? ''));
        $heroImage = trim((string) ($record?->hero_image ?? ($default['hero_image'] ?? '')));

        return [
            'page_key' => $pageKey,
            'label' => self::pageOptions()[$pageKey] ?? strtoupper(str_replace('_', ' ', $pageKey)),
            'title' => $title,
            'subtitle' => $subtitle,
            'hero_image' => $heroImage,
            'hero_image_url' => self::resolveImageUrl($heroImage),
        ];
    }

    public function getPageLabelAttribute(): string
    {
        return self::pageOptions()[$this->page_key] ?? strtoupper(str_replace('_', ' ', (string) $this->page_key));
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        return self::resolveImageUrl($this->hero_image);
    }

    private static function resolveImageUrl(?string $image): ?string
    {
        $image = trim((string) $image);

        if ($image === '') {
            return null;
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        return Storage::url($image);
    }
}

