<?php

use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\PasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('settings')->name('settings.')->group(function () {
    Route::redirect('/', '/settings/profile')->name('index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/password', [PasswordController::class, 'edit'])->name('password');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
});
