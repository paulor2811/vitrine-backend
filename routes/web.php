<?php

use App\Http\Controllers\ClickRedirectController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/r/{productId}',  [ClickRedirectController::class, 'redirect'])->name('click.redirect');
Route::get('/sitemap.xml',    [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt',     [SitemapController::class, 'robots'])->name('robots');
