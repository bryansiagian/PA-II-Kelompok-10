<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductOrderController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\CmsController;
use App\Http\Controllers\Api\WarehouseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- 1. RUTE PUBLIK ---
Route::get('/public/landing-page', [CmsController::class, 'getLandingPageData']);
Route::get('/public/products', [ProductController::class, 'index']);
Route::get('/public/files', function() {
    return \App\Models\GeneralFile::where('active', 1)->latest()->get();
});


// --- 2. RUTE TERPROTEKSI (Wajib Login) ---
Route::middleware('auth:sanctum')->group(function () {

    // A. MANAJEMEN USER & VERIFIKASI (Admin & Operator)
    Route::middleware(['permission:manage users'])->group(function () {
        Route::get('/users/pending', [AdminController::class, 'getPendingUsers']);
        Route::post('/users/{id}/approve', [AdminController::class, 'approveUser']);
        Route::post('/users/{id}/reject', [AdminController::class, 'rejectUser']);
    });

    // B. KHUSUS ADMIN
    Route::middleware(['role:admin'])->group(function () {

        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/users/{id}', [AdminController::class, 'showUser']);
        Route::post('/users', [AdminController::class, 'storeUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser']);
        Route::get('/roles', [AdminController::class, 'getRoles']);

        Route::get('/admin/logs', [AdminController::class, 'getLogs']);
        Route::get('/admin/analytics', [AdminController::class, 'getAnalytics']);

        Route::put('/cms/profile', [CmsController::class, 'updateProfile']);
        Route::put('/cms/contact', [CmsController::class, 'updateContact']);
        Route::get('/cms/org', [CmsController::class, 'indexOrg']);
        Route::post('/cms/org', [CmsController::class, 'storeOrg']);
        Route::put('/cms/org/{id}', [CmsController::class, 'updateOrg']);
        Route::delete('/cms/org/{id}', [CmsController::class, 'deleteOrg']);

        Route::get('/cms/posts', [CmsController::class, 'indexPosts']);
        Route::post('/cms/posts', [CmsController::class, 'storePost']);
        Route::get('/cms/posts/{id}', [CmsController::class, 'showPost']);
        Route::put('/cms/posts/{id}', [CmsController::class, 'updatePost']);
        Route::delete('/cms/posts/{id}', [CmsController::class, 'deletePost']);

        Route::get('/cms/post-categories', [CmsController::class, 'indexPostCategories']);
        Route::post('/cms/post-categories', [CmsController::class, 'storePostCategory']);
        Route::put('/cms/post-categories/{id}', [CmsController::class, 'updatePostCategory']);
        Route::delete('/cms/post-categories/{id}', [CmsController::class, 'deletePostCategory']);

        Route::get('/cms/galleries', [CmsController::class, 'indexGalleries']);
        Route::post('/cms/galleries', [CmsController::class, 'storeGallery']);
        Route::put('/cms/galleries/{id}', [CmsController::class, 'updateGallery']);
        Route::delete('/cms/galleries/{id}', [CmsController::class, 'deleteGallery']);
        Route::delete('/cms/gallery-files/{id}', [CmsController::class, 'deleteGalleryFile']);

        Route::get('/cms/contacts', [CmsController::class, 'indexContacts']);
        Route::post('/cms/contacts', [CmsController::class, 'storeContact']);
        Route::put('/cms/contacts/{id}', [CmsController::class, 'updateContact']);
        Route::delete('/cms/contacts/{id}', [CmsController::class, 'deleteContact']);

        Route::get('/cms/general-files', [CmsController::class, 'indexGeneralFiles']);
        Route::post('/cms/general-files', [CmsController::class, 'storeGeneralFile']);
        Route::delete('/cms/general-files/{id}', [CmsController::class, 'deleteGeneralFile']);
    });

    // C. INVENTORY
    Route::middleware(['permission:manage inventory'])->group(function () {

        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        Route::post('/products/stock-in', [ProductController::class, 'updateStock']);

        Route::post('/product-categories', [ProductCategoryController::class, 'store']);
        Route::put('/product-categories/{id}', [ProductCategoryController::class, 'update']);
        Route::delete('/product-categories/{id}', [ProductCategoryController::class, 'destroy']);

        Route::post('/orders/{id}/approve', [ProductOrderController::class, 'approve']);
        Route::post('/orders/{id}/reject', [ProductOrderController::class, 'reject']);
        Route::post('/orders/{id}/complete-pickup', [ProductOrderController::class, 'completePickup']);

        Route::post('/deliveries/ready/{id}', [DeliveryController::class, 'makeReady']);

        Route::get('/inventory/warehouses', [WarehouseController::class, 'indexWarehouses']);
        Route::post('/inventory/warehouses', [WarehouseController::class, 'storeWarehouse']);
        Route::put('/inventory/warehouses/{id}', [WarehouseController::class, 'updateWarehouse']);
        Route::delete('/inventory/warehouses/{id}', [WarehouseController::class, 'destroyWarehouse']);

        Route::get('/inventory/racks', [WarehouseController::class, 'indexRacks']);
        Route::post('/inventory/racks', [WarehouseController::class, 'storeRack']);
        Route::put('/inventory/racks/{id}', [WarehouseController::class, 'updateRack']);
        Route::delete('/inventory/racks/{id}', [WarehouseController::class, 'destroyRack']);
    });

    // D. CUSTOMER
Route::middleware(['role:customer'])->group(function () {

    // Cart
    Route::get('/cart', [CartApiController::class, 'index']);
    Route::post('/cart', [CartApiController::class, 'store']);
    Route::put('/cart/{id}', [CartApiController::class, 'update']);
    Route::delete('/cart/{id}', [CartApiController::class, 'destroy']);
    Route::delete('/cart-clear', [CartApiController::class, 'clear']);

    // Orders lama
    Route::post('/orders', [ProductOrderController::class, 'store']);
    Route::post('/orders/{id}/cancel', [ProductOrderController::class, 'cancel']);

    // 🔥 CHECKOUT BARU
    Route::post('/checkout', [CartApiController::class, 'checkout']);

    // 🔥 TAMBAHAN WAJIB (INI YANG KURANG)
    Route::get('/my-orders', [ProductOrderController::class, 'myOrders']);
});

    // E. COURIER
    Route::middleware(['role:courier'])->group(function () {
        Route::get('/courier/stats', [DeliveryController::class, 'getCourierStats']);
        Route::get('/deliveries/history', [DeliveryController::class, 'getCourierHistory']);
        Route::get('/deliveries/available', [DeliveryController::class, 'getAvailableDeliveries']);
        Route::get('/deliveries/active', [DeliveryController::class, 'getActiveDeliveries']);
        Route::post('/deliveries/claim/{id}', [DeliveryController::class, 'claim']);
        Route::post('/deliveries/start/{id}', [DeliveryController::class, 'startShipping']);
        Route::post('/deliveries/complete/{id}', [DeliveryController::class, 'complete']);
    });

    // F. UMUM
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/product-categories', [ProductCategoryController::class, 'index']);
    Route::get('/orders', [ProductOrderController::class, 'index']);
    Route::get('/orders/{id}', [ProductOrderController::class, 'show']);
    Route::get('/deliveries/{id}/tracking', [DeliveryController::class, 'getTracking']);
    Route::get('/post-categories', [CmsController::class, 'indexPostCategories']);
});
