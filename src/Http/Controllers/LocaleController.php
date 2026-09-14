<?php

namespace Woodynew\DcatAdminKit\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController
{
    public function update(Request $request)
    {
        abort_unless(config('dcat-admin-kit.features.locale_switcher', false), 404);

        $locale = $request->input('locale');
        $locales = config('dcat-admin-kit.locale.locales', []);
        abort_unless(is_string($locale) && array_key_exists($locale, $locales), 422);

        $request->session()->put(config('dcat-admin-kit.locale.session_key', 'dcat-admin-kit.locale'), $locale);

        return response()->json(['locale' => $locale]);
    }
}
