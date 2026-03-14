<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PagePreviewController;
use App\Http\Controllers\PageServeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Main Application Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Page management
    Route::get('/pages/create', [PageController::class, 'create'])->name('pages.create');
    Route::post('/pages', [PageController::class, 'store'])->name('pages.store');
    Route::get('/pages/{slug}/manager', [FileManagerController::class, 'index'])->name('pages.manager');
    Route::get('/pages/{slug}/preview', [PagePreviewController::class, 'show'])->name('pages.preview');
    Route::delete('/pages/{slug}', [PageController::class, 'destroy'])->name('pages.destroy');
    Route::put('/pages/{slug}/settings', [PageController::class, 'updateSettings'])->name('pages.settings');

    // File Manager JSON API
    Route::prefix('/pages/{slug}/files')->name('pages.files.')->group(function () {
        Route::get('/', [FileManagerController::class, 'list'])->name('list');
        Route::get('/read', [FileManagerController::class, 'read'])->name('read');
        Route::post('/', [FileManagerController::class, 'upload'])->name('upload');
        Route::post('/create', [FileManagerController::class, 'create'])->name('create');
        Route::put('/', [FileManagerController::class, 'save'])->name('save');
        Route::delete('/', [FileManagerController::class, 'delete'])->name('delete');
        Route::post('/folder', [FileManagerController::class, 'createFolder'])->name('folder');
        Route::put('/rename', [FileManagerController::class, 'rename'])->name('rename');
    });
});

/*
|--------------------------------------------------------------------------
| Subdomain Routes (must be defined before path catch-all)
|--------------------------------------------------------------------------
*/
Route::domain('{slug}.statichtmlsites.mtex.dev')
    ->group(function () {
        Route::get('/{path?}', [PageServeController::class, 'serve'])
            ->where('path', '.*')
            ->name('page.subdomain');
    });

require __DIR__ . '/auth.php';
require __DIR__ . '/settings.php';
/*
|--------------------------------------------------------------------------
| Path-based Page Serving (catch-all – keep last)
|--------------------------------------------------------------------------
*/
Route::get('/{slug}/{path?}', [PageServeController::class, 'serve'])
    ->where('slug', '[a-z0-9\-_]+')
    ->where('path', '.*')
    ->name('page.path');
