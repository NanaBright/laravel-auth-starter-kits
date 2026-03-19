<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported locales and their properties.
     */
    protected array $locales = [
        'en' => ['name' => 'English', 'dir' => 'ltr'],
        'es' => ['name' => 'Español', 'dir' => 'ltr'],
        'fr' => ['name' => 'Français', 'dir' => 'ltr'],
        'de' => ['name' => 'Deutsch', 'dir' => 'ltr'],
        'pt' => ['name' => 'Português', 'dir' => 'ltr'],
        'it' => ['name' => 'Italiano', 'dir' => 'ltr'],
        'zh' => ['name' => '中文', 'dir' => 'ltr'],
        'ja' => ['name' => '日本語', 'dir' => 'ltr'],
        'ko' => ['name' => '한국어', 'dir' => 'ltr'],
        'ar' => ['name' => 'العربية', 'dir' => 'rtl'],
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Priority: 1. Query param, 2. Session, 3. Cookie, 4. Header, 5. Default
        $locale = $this->getLocale($request);

        // Validate locale
        if (!array_key_exists($locale, $this->locales)) {
            $locale = config('app.fallback_locale', 'en');
        }

        // Set application locale
        App::setLocale($locale);

        // Store in session for persistence
        session(['locale' => $locale]);

        // Get response
        $response = $next($request);

        // Add locale headers for frontend
        if ($response instanceof Response) {
            $response->headers->set('Content-Language', $locale);
            $response->headers->set('X-Locale-Dir', $this->locales[$locale]['dir']);
        }

        return $response;
    }

    /**
     * Determine the locale from various sources.
     */
    protected function getLocale(Request $request): string
    {
        // 1. Query parameter (e.g., ?lang=es)
        if ($request->has('lang')) {
            return $request->input('lang');
        }

        // 2. Session
        if (session()->has('locale')) {
            return session('locale');
        }

        // 3. Cookie
        if ($request->hasCookie('locale')) {
            return $request->cookie('locale');
        }

        // 4. Accept-Language header
        $acceptLanguage = $request->header('Accept-Language');
        if ($acceptLanguage) {
            $preferred = $this->parseAcceptLanguage($acceptLanguage);
            if ($preferred) {
                return $preferred;
            }
        }

        // 5. Default
        return config('app.locale', 'en');
    }

    /**
     * Parse Accept-Language header and return best match.
     */
    protected function parseAcceptLanguage(string $header): ?string
    {
        $languages = [];
        
        foreach (explode(',', $header) as $lang) {
            $parts = explode(';', trim($lang));
            $code = strtolower(substr(trim($parts[0]), 0, 2));
            $quality = 1.0;
            
            if (isset($parts[1]) && preg_match('/q=([0-9.]+)/', $parts[1], $matches)) {
                $quality = (float) $matches[1];
            }
            
            $languages[$code] = $quality;
        }
        
        arsort($languages);
        
        foreach (array_keys($languages) as $code) {
            if (array_key_exists($code, $this->locales)) {
                return $code;
            }
        }
        
        return null;
    }

    /**
     * Get available locales.
     */
    public static function getAvailableLocales(): array
    {
        return (new self)->locales;
    }
}
