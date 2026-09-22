<?php

use App\Http\Controllers\Frontend\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web (Frontend) Routes
|--------------------------------------------------------------------------
|
| Public-facing website routes. Controllers live in
| App\Http\Controllers\Frontend and views in resources/views/frontend.
| All routes share the "frontend." name prefix.
|
| The site is a single landing page for now. Add further pages here as they
| are built.
|
| Backend/admin routes are kept in routes/admin.php and the versioned API in
| routes/api.php — both registered in bootstrap/app.php.
|
*/

Route::get('/', [HomeController::class, 'index'])->name('frontend.index');
