<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Throwable;

class VillageProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'about',
        'vision',
        'mission',
        'history',
        'map_embed',
        'whatsapp',
        'email',
        'facebook_url',
        'instagram_url',
        'x_url',
        'home_background_image_1',
        'home_background_image_2',
        'home_background_image_3',
    ];

    protected function mapEmbed(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => static::normalizeMapEmbedUrl($value, true),
            set: fn ($value) => static::normalizeMapEmbedUrl($value, true),
        );
    }

    public static function normalizeMapEmbedUrl(?string $rawUrl, bool $resolveShortUrl = true): ?string
    {
        $value = trim((string) $rawUrl);
        if ($value === '') {
            return null;
        }

        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5);
        $value = static::extractIframeSrc($value) ?? $value;

        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        $resolvedUrl = $value;
        $parts = parse_url($resolvedUrl);
        if ($parts === false) {
            return $value;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');

        if ($resolveShortUrl && static::isGoogleMapsShortUrl($host, $path)) {
            $resolvedUrl = static::resolveShortMapUrl($resolvedUrl);
            $parts = parse_url($resolvedUrl);
            if ($parts === false) {
                return $value;
            }

            $host = strtolower((string) ($parts['host'] ?? ''));
            $path = (string) ($parts['path'] ?? '');
        }

        $isGoogleMapsUrl = static::isGoogleMapsUrl($host, $path);
        if (! $isGoogleMapsUrl) {
            return $resolvedUrl;
        }

        if (str_contains($path, '/maps/embed')) {
            return $resolvedUrl;
        }

        parse_str((string) ($parts['query'] ?? ''), $queryParams);

        foreach (['q', 'query', 'destination', 'center'] as $key) {
            $candidate = trim((string) ($queryParams[$key] ?? ''));
            if ($candidate !== '') {
                return static::makeGoogleEmbedUrl($candidate);
            }
        }

        if (preg_match('/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/', $path, $matches) === 1) {
            return static::makeGoogleEmbedUrl($matches[1] . ',' . $matches[2]);
        }

        if (preg_match('#/place/([^/]+)#', $path, $matches) === 1) {
            $place = trim(str_replace('+', ' ', rawurldecode($matches[1])));
            if ($place !== '') {
                return static::makeGoogleEmbedUrl($place);
            }
        }

        if (preg_match('#/search/([^/]+)#', $path, $matches) === 1) {
            $term = trim(str_replace('+', ' ', rawurldecode($matches[1])));
            if ($term !== '') {
                return static::makeGoogleEmbedUrl($term);
            }
        }

        return static::makeGoogleEmbedUrl($resolvedUrl);
    }

    protected static function extractIframeSrc(string $value): ?string
    {
        if (preg_match('/<iframe[^>]*\s+src=(["\'])(.*?)\1/i', $value, $matches) === 1) {
            return trim((string) ($matches[2] ?? '')) ?: null;
        }

        return null;
    }

    protected static function isGoogleMapsShortUrl(string $host, string $path): bool
    {
        return str_contains($host, 'maps.app.goo.gl')
            || ($host === 'goo.gl' && str_starts_with($path, '/maps'))
            || str_contains($host, 'g.co');
    }

    protected static function isGoogleMapsUrl(string $host, string $path): bool
    {
        if ($host === '') {
            return false;
        }

        if (! str_contains($host, 'google.') && ! str_contains($host, 'goog') && ! str_contains($host, 'goo.gl')) {
            return false;
        }

        return str_contains($host, 'maps.') || str_starts_with($path, '/maps');
    }

    protected static function resolveShortMapUrl(string $url): string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; ProfilDesaBot/1.0; +https://localhost)',
            ])
                ->timeout(10)
                ->withoutRedirecting()
                ->get($url);

            if ($response->redirect()) {
                $location = trim((string) $response->header('Location'));
                if ($location !== '') {
                    return str_starts_with($location, 'http')
                        ? $location
                        : rtrim($url, '/') . '/' . ltrim($location, '/');
                }
            }

            $followed = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; ProfilDesaBot/1.0; +https://localhost)',
            ])
                ->timeout(12)
                ->get($url);

            $effectiveUri = (string) ($followed->effectiveUri() ?? '');
            if ($effectiveUri !== '') {
                return $effectiveUri;
            }
        } catch (Throwable) {
            // Abaikan jika short-link tidak bisa di-resolve.
        }

        return $url;
    }

    protected static function makeGoogleEmbedUrl(string $query): string
    {
        return 'https://www.google.com/maps?q=' . rawurlencode($query) . '&output=embed';
    }
}
