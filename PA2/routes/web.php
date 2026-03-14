<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rute Root: Landing Page
Route::get('/', function () {
    return view('welcome');
});

// Guest Routes (Login & Register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('otp.verify');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
});

// Authenticated Routes
Route::middleware(['auth'])->group(function() {

    // Dasar (Akses Semua Role)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Rute Profile
    Route::get('/profile', function () {
        return view('admin.cms.profile');
    })->name('profile.index');

    // VERIFIKASI USER (Admin & Operator)
    Route::middleware(['permission:manage users'])->group(function() {
        Route::get('/admin/users/pending', function () {
            return view('admin.pending_users');
        });
        Route::get('/reports', function() {
            return view('admin.reports');
        })->name('reports');
    });

    // MANAJEMEN AKUN & CMS (Hanya Admin)
    Route::middleware(['role:admin'])->group(function() {
        Route::prefix('admin/cms')->group(function() {
            Route::get('/post-categories', function() { return view('admin.cms.post_categories'); });
            Route::get('/profile', function() { return view('admin.cms.profile'); });
            Route::get('/posts', function() { return view('admin.cms.posts'); });
            Route::get('/org', function() { return view('admin.cms.org'); });
            Route::get('/gallery', function() { return view('admin.cms.gallery'); });
            Route::get('/contacts', function() { return view('admin.cms.contacts'); });
            Route::get('/files', function() { return view('admin.cms.general_files'); });
        });

        Route::get('/admin/users', function() { return view('admin.users'); });
        Route::get('/admin/logs', function() { return view('admin.logs'); });
    });

    // INVENTORY & WAREHOUSING (Operator & Admin)
    Route::middleware(['permission:manage inventory'])->group(function() {
        Route::prefix('operator')->group(function() {
            Route::get('/products', function() { return view('operator.products'); }); // Ganti drugs ke products
            Route::get('/categories', function() { return view('operator.categories'); });
            Route::get('/orders', function() { return view('operator.orders'); }); // Ganti requests ke orders
            Route::get('/warehouses', function() { return view('admin.inventory.warehouses'); }); // Ganti storages ke warehouses
            Route::get('/racks', function() { return view('admin.inventory.racks'); });
            Route::get('/tracking/{id}', function ($id) {
                return view('operator.tracking', ['id' => $id]);
            })->name('operator.tracking');
        });
    });

    // CUSTOMER MODULE
    Route::middleware(['role:customer'])->group(function() {
        Route::prefix('customer')->group(function() {
            Route::get('/history', function () { return view('customer.history'); })->name('customer.history');
            Route::get('/cart', function() { return view('customer.cart'); })->name('customer.cart');
            Route::get('/request-new', function() { return view('customer.manual_request'); })->name('customer.manual_request');
            Route::get('/tracking/{id}', function ($id) {
                return view('customer.tracking', ['id' => $id]);
            })->name('customer.tracking');
        });
    });

    // COURIER MODULE
    Route::middleware(['role:courier'])->group(function() {
        Route::prefix('courier')->group(function() {
            Route::get('/available', function () { return view('courier.available'); })->name('courier.available');
            Route::get('/active', function () { return view('courier.active'); })->name('courier.active');
            Route::get('/history', function () { return view('courier.history'); })->name('courier.history');
        });
    });
});
