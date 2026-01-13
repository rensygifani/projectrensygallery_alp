<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RajaOngkirController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CouponController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('products');
});

// Product Routes (Public)
Route::controller(ProductController::class)
    ->prefix('products')
    ->group(function () {
        Route::get('/', 'index')->name('products');
        Route::get('/show/{id}', 'show')->name('products.show');
    });

// RajaOngkir Routes (Public)
Route::get('/cities/{province_id}', [RajaOngkirController::class, 'getCities']);
Route::get('/districts/{cityId}', [RajaOngkirController::class, 'getDistricts']);
Route::post('/check-ongkir', [RajaOngkirController::class, 'checkOngkir']);

// Midtrans Callback (Public - tanpa CSRF)
Route::post('/midtrans/callback', [PaymentController::class, 'callback'])
    ->withoutMiddleware(['csrf'])
    ->name('midtrans.callback');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
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

    // Buy Now
    Route::post('/buy-now/{product}', [CheckoutController::class, 'buyNow'])->name('buy.now');

    Route::post('/payment/check-status', [PaymentController::class, 'checkStatus'])
        ->name('payment.checkStatus');

    // Review
    Route::post('/products/{product}/review', [ReviewController::class, 'store'])->name('review.store');

    // Checkout
    Route::post('/checkout/preview', [CheckoutController::class, 'form'])->name('checkout.preview');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/pay/{order}', [OrderController::class, 'pay'])->name('orders.pay');
    Route::post('/orders/{order}/process-payment', [OrderController::class, 'processPayment'])->name('orders.processPayment');

    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Manual Status Update (untuk debugging)
    Route::post('/orders/{order}/update-status', function (\App\Models\Order $order, Request $request) {
        \Log::info('🔄 Manual Status Update', [
            'order_id' => $order->id,
            'old_status' => $order->status,
            'new_status' => $request->status,
        ]);

        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'order_id' => $order->id,
            'status' => $order->status
        ]);
    });

    // Product Management
    Route::controller(ProductController::class)->group(function () {
        Route::get('/products/create', 'create')->name('products.create');
        Route::post('/products/store', 'store')->name('products.store');
        Route::get('/products/edit/{id}', 'edit')->name('products.edit');
        Route::put('/products/update/{id}', 'update')->name('products.update');
        Route::delete('/products/delete/{id}', 'destroy')->name('products.destroy');
    });

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/toggle/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/remove/{id}', [WishlistController::class, 'remove'])->name('wishlist.remove');

    // Payment
    Route::post('/midtrans/token', [PaymentController::class, 'token'])->name('midtrans.token');

    // COUPON ROUTES
    
    Route::prefix('admin')->group(function () {
        Route::resource('coupons', CouponController::class);
    });
    Route::post('/coupons/validate', [CouponController::class, 'validateCoupon'])->name('coupons.validate');
    Route::post('/coupons/apply', [CouponController::class, 'apply'])->name('coupons.apply');
});

/*
|--------------------------------------------------------------------------
| Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';