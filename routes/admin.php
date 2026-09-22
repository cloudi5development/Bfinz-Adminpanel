<?php

use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\ModuleController;
use App\Http\Controllers\Backend\SmsSettingController;
use App\Support\AdminMenu;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backend (Admin Panel) Routes
|--------------------------------------------------------------------------
|
| Mounted by bootstrap/app.php inside the "web" middleware group. Everything
| here is prefixed with /admin and shares the "backend." name prefix, so the
| frontend and admin never collide as the panel grows.
|
| The module routes are not written out by hand: they are generated from
| App\Support\AdminMenu, which is also what renders the sidebar. That is what
| keeps the menu and the routing table from drifting apart — a sidebar link
| cannot go dead, because the link and the route come from the same entry.
|
| Controllers live in App\Http\Controllers\Backend, views in
| resources/views/backend.
|
*/

Route::prefix('admin')->name('backend.')->group(function () {

    // Guest auth routes. Logout stays outside the guard so an expired session
    // still signs out cleanly instead of bouncing through the login redirect.
    Route::controller(AuthController::class)->name('auth.')->group(function () {
        Route::get('/login', 'login')->name('login');
        Route::post('/login', 'authenticate')->name('authenticate');
        Route::post('/logout', 'logout')->name('logout');
    });

    // Authenticated admin area.
    //
    //   admin.auth   — AdminAuthenticate: session flag (or a valid remember
    //                  cookie), otherwise back to the login screen.
    //   admin.module — EnsureModuleAccess: per-module permissions, derived from
    //                  the route name. A no-op for the dashboard, which every
    //                  signed-in admin may open.
    Route::middleware(['admin.auth', 'admin.module'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Settings pages are otherwise GET-only (rendered by ModuleController
        // below); this is the one save action wired to real storage.
        Route::post('/settings/sms', [SmsSettingController::class, 'update'])->name('settings.sms.update');

        // Every other page in the sidebar tree.
        foreach (AdminMenu::pages() as $routeName => $page) {
            if ($routeName === 'backend.dashboard') {
                continue;
            }

            Route::get('/' . $page['path'], ModuleController::class)
                ->name(substr($routeName, strlen('backend.')));
        }
    });

});
