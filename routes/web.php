<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (ShopController $c) => $c->page())->name('home');
Route::get('/catalog', [ShopController::class, 'catalog'])->name('catalog');
Route::get('/products/{slug}', [ShopController::class, 'product'])->name('product');
Route::get('/product-page.html', fn () => redirect('/catalog'));
Route::post('/cart', [ShopController::class, 'cart'])->middleware('throttle:120,1');
Route::middleware('guest')->group(function () {
    Route::get('/login', fn (ShopController $c) => $c->render('lk-login', ['authMode' => 'login']))->name('login');
    Route::get('/register', fn (ShopController $c) => $c->render('lk-login', ['authMode' => 'register']));
    Route::get('/forgot-password', fn (ShopController $c) => $c->render('lk-login', ['authMode' => 'forgot']))->name('password.request');
    Route::get('/reset-password/{token}', fn (string $token, ShopController $c) => $c->render('lk-login', ['authMode' => 'reset', 'token' => $token, 'email' => request('email')]))->name('password.reset');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::post('/forgot-password', [AuthController::class, 'forgot'])->middleware('throttle:4,1');
    Route::post('/reset-password', [AuthController::class, 'reset'])->middleware('throttle:6,1');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/checkout', [ShopController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [ShopController::class, 'place'])->middleware('throttle:10,1');
    Route::get('/account', [ShopController::class, 'account'])->name('account');
    Route::patch('/account', [ShopController::class, 'profile']);
    Route::patch('/account/password', [ShopController::class, 'password'])->middleware('throttle:6,1');
    Route::get('/account/orders/{number}', [ShopController::class, 'order']);
    Route::get('/favourites', [ShopController::class, 'favourites']);
    Route::post('/favourites/{product}', [ShopController::class, 'favourite']);
});
// Preserve links in the supplied layouts while moving to canonical application routes.
Route::get('/catalog.html', fn () => redirect('/catalog', 301));
Route::get('/order.html', fn () => redirect('/checkout', 301));
Route::get('/lk-login.html', fn () => redirect('/login', 301));
Route::get('/lk-personal-info.html', fn () => redirect('/account', 301));
Route::get('/{slug}.html', fn (string $slug, ShopController $c) => $c->page($slug));
Route::get('/{slug}',[ShopController::class, 'page'])->where('slug','[a-z0-9-]+');
