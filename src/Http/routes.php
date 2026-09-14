<?php

use Illuminate\Support\Facades\Route;
use Woodynew\DcatAdminKit\Http\Controllers\LocaleController;

if (config('dcat-admin-kit.features.locale_switcher', false)) {
    Route::post('kit/locale', '\\'.LocaleController::class.'@update')
        ->name('kit.locale')
        // Every authenticated administrator may change their own UI preference.
        ->withoutMiddleware(\Dcat\Admin\Http\Middleware\Permission::class);
}
