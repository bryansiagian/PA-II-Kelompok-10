<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WelcomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ✅ ROOT (SUDAH PAKAI KATALOG)
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Optional
Route::get('/posts', function () {
    return view('customer.posts');
})->name('customer.posts');

Route::get('/customer/products', fn() => view('customer.product'))->name('customer.product');


// =====================
// GUEST
// =====================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('otp.verify');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
});


// =====================
// AUTH
// =====================
Route::middleware(['auth'])->group(function() {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/profile', function () {
        return view('admin.cms.profile');
    })->name('profile.index');


    // =====================
    // ADMIN & OPERATOR
    // =====================
    Route::middleware(['permission:manage users'])->group(function() {
        Route::get('/admin/users/pending', function () {
            return view('admin.pending_users');
        });

        Route::get('/reports', function() {
            return view('admin.reports');
        })->name('reports');
    });


    // =====================
    // ADMIN ONLY
    // =====================
    Route::middleware(['role:admin'])->group(function() {

        Route::prefix('admin/cms')->group(function() {
            Route::get('/post-categories', fn() => view('admin.cms.post_categories'));
            Route::get('/profile', fn() => view('admin.cms.profile'));
            Route::get('/posts', fn() => view('admin.cms.posts'));
            Route::get('/org', fn() => view('admin.cms.org'));
            Route::get('/gallery', fn() => view('admin.cms.gallery'));
            Route::get('/contacts', fn() => view('admin.cms.contacts'));
            Route::get('/files', fn() => view('admin.cms.general_files'));
        });

        Route::get('/admin/users', fn() => view('admin.users'));
        Route::get('/admin/logs', fn() => view('admin.logs'));
    });


    // =====================
    // OPERATOR
    // =====================
    Route::middleware(['permission:manage inventory'])->group(function() {

        Route::prefix('operator')->group(function() {
            Route::get('/products', fn() => view('operator.products'));
            Route::get('/categories', fn() => view('operator.categories'));
            Route::get('/orders', fn() => view('operator.orders'));
            Route::get('/warehouses', fn() => view('admin.inventory.warehouses'));
            Route::get('/racks', fn() => view('admin.inventory.racks'));

            Route::get('/tracking/{id}', function ($id) {
                return view('operator.tracking', compact('id'));
            })->name('operator.tracking');
        });
    });


    // =====================
    // CUSTOMER
    // =====================
    Route::middleware(['role:customer'])->group(function() {

        Route::prefix('customer')->group(function() {
            Route::get('/history', fn() => view('customer.history'))->name('customer.history');
            Route::get('/cart', fn() => view('customer.cart'))->name('customer.cart');
            Route::get('/request-new', fn() => view('customer.manual_request'))->name('customer.manual_request');

            Route::get('/tracking/{id}', function ($id) {
                return view('customer.tracking', compact('id'));
            })->name('customer.tracking');

        });
    });


    // =====================
    // COURIER
    // =====================
    Route::middleware(['role:courier'])->group(function() {

        Route::prefix('courier')->group(function() {
            Route::get('/available', fn() => view('courier.available'))->name('courier.available');
            Route::get('/active', fn() => view('courier.active'))->name('courier.active');
            Route::get('/history', fn() => view('courier.history'))->name('courier.history');
        });
    });

});
