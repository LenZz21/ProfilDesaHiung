<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['id', 'en'];
        $defaultLocale = 'id';

        $locale = (string) $request->session()->get('locale', '');
        $referer = (string) $request->headers->get('referer', '');
        $refererHost = $referer !== '' ? parse_url($referer, PHP_URL_HOST) : null;
        $currentHost = $request->getHost();
        $isInternalNavigation = is_string($refererHost) && $refererHost !== '' && $refererHost === $currentHost;

        // Reset ke default saat masuk baru (bukan navigasi internal antar halaman situs).
        if (! $isInternalNavigation) {
            $locale = $defaultLocale;
            $request->session()->put('locale', $locale);
        } elseif (! in_array($locale, $supportedLocales, true)) {
            $locale = $defaultLocale;
            $request->session()->put('locale', $locale);
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);
        $request->setLocale($locale);

        return $next($request);
    }
}
