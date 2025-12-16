<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('products');
});

/*
|--------------------------------------------------------------------------
| Product Routes (Public)
|--------------------------------------------------------------------------
*/
Route::controller(ProductController::class)
    ->prefix('products')
    ->group(function () {
        Route::get('/', 'index')->name('products');
        Route::get('/show/{id}', 'show')->name('products.show');
    });

/*
|--------------------------------------------------------------------------
| Dashboard (Breeze default)
|--------------------------------------------------------------------------
*/
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', function () {
    return redirect()->route('products');
})->middleware(['auth'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

    // Checkout
    // Route::get('/checkout', [CheckoutController::class, 'form'])->name('checkout');
    // Route::post('/checkout', [CheckoutController::class, 'form'])->name('checkout.preview');
    // Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

    // Checkout
    Route::post('/checkout/preview', [CheckoutController::class, 'form'])
        ->name('checkout.preview');

    Route::post('/checkout/process', [CheckoutController::class, 'process'])
        ->name('checkout.process');


    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');

    // Product Management (optional, jika hanya admin)
    Route::controller(ProductController::class)->group(function () {
        Route::get('/products/create', 'create')->name('products.create');
        Route::post('/products/store', 'store')->name('products.store');
        Route::get('/products/edit/{id}', 'edit')->name('products.edit');
        Route::put('/products/update/{id}', 'update')->name('products.update');
        Route::delete('/products/delete/{id}', 'destroy')->name('products.destroy');
    });

});

/*
|--------------------------------------------------------------------------
| Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
