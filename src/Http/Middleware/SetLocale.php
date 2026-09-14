<?php

namespace Woodynew\DcatAdminKit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        if (! config('dcat-admin-kit.features.locale_switcher', false) || ! $request->hasSession()) {
            return $next($request);
        }

        $previous = app()->getLocale();
        $locale = $request->session()->get(config('dcat-admin-kit.locale.session_key', 'dcat-admin-kit.locale'));
        $locales = config('dcat-admin-kit.locale.locales', []);

        if (is_string($locale) && array_key_exists($locale, $locales)) {
            // Laravel also updates app.locale, used by Dcat's JS component options.
            app()->setLocale($locale);
        }

        try {
            return $next($request);
        } finally {
            // Do not leak one administrator's preference into another request.
            app()->setLocale($previous);
        }
    }
}
