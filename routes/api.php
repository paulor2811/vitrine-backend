<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\NicheController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;

// Auth — Google OAuth (web, redireciona para frontend)
Route::prefix('auth/google')->group(function () {
    Route::get('redirect', [SocialAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('callback', [SocialAuthController::class, 'callback'])->name('auth.google.callback');
});

// Auth — email/password
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('login',    [AuthController::class, 'login'])->name('auth.login');
});

// Catálogo — público
Route::prefix('niches')->group(function () {
    Route::get('/',                         [NicheController::class,   'index'])->name('niches.index');
    Route::get('{slug}',                    [NicheController::class,   'show'])->name('niches.show');
    Route::get('{slug}/products',           [ProductController::class, 'byNiche'])->name('niches.products');
});

// Analytics — público (anônimo ou autenticado)
Route::post('events', [AnalyticsController::class, 'store'])
    ->middleware('throttle:60,1')
    ->name('events.store');

// Rotas autenticadas via Passport
Route::middleware('auth:api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout',        [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('me',             [AuthController::class, 'me'])->name('auth.me');
        Route::post('claim-session', [AuthController::class, 'claimSession'])->name('auth.claim-session');
    });

    Route::prefix('favorites')->group(function () {
        Route::get('/',             [FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/',            [FavoriteController::class, 'store'])->name('favorites.store');
        Route::delete('{productId}',[FavoriteController::class, 'destroy'])->name('favorites.destroy');
    });
});
