<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ProductController;         // Ganti dari DrugController
use App\Http\Controllers\Api\ProductCategoryController; // Ganti dari CategoryController
use App\Http\Controllers\Api\ProductOrderController;    // Ganti dari RequestController
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\CmsController;
use App\Http\Controllers\Api\WarehouseController;       // Ganti dari InventoryController

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- 1. RUTE PUBLIK ---
Route::get('/public/landing-page', [CmsController::class, 'getLandingPageData']);
Route::get('/products', [ProductController::class, 'index']); // Ganti drugs ke products
Route::get('/public/products', [ProductController::class, 'publicProducts']); // Ganti drugs ke products
Route::get('/public/posts', [CmsController::class, 'indexPosts']); // <--- TAMBAHKAN INI
Route::get('/public/posts/{id}', [CmsController::class, 'showPost']); // <--- TAMBAHKAN INI
Route::get('/public/post-categories', [CmsController::class, 'indexPostCategories']); // <--- TAMBAHKAN INI
Route::get('/public/files', function() {
    return \App\Models\GeneralFile::where('active', 1)->latest()->get();
});

// --- INTERNAL SERVICE ROUTES (untuk Report Service) ---
Route::middleware('internal.secret')->prefix('internal')->group(function () {
    Route::get('/orders',   [AdminController::class, 'getOrdersForReport']);
    Route::get('/users',    [AdminController::class, 'getUsersForReport']);
    Route::get('/products', [AdminController::class, 'getProductsForReport']);
    Route::get('/analytics',[AdminController::class, 'getAnalytics']);
});


// --- 2. RUTE TERPROTEKSI (Wajib Login) ---
Route::middleware('auth:sanctum')->group(function () {

    // A. MANAJEMEN USER & VERIFIKASI (Admin & Operator)
    Route::middleware(['permission:manage users'])->group(function () {
        Route::get('/users/pending', [AdminController::class, 'getPendingUsers']);
        Route::post('/users/{id}/approve', [AdminController::class, 'approveUser']);
        Route::post('/users/{id}/reject', [AdminController::class, 'rejectUser']);
    });

    // B. KHUSUS ADMIN (Full System Control & CMS)
    Route::middleware(['role:admin'])->group(function () {
        // Master Users
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/users/{id}', [AdminController::class, 'showUser']);
        Route::post('/users', [AdminController::class, 'storeUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser']);
        Route::get('/roles', [AdminController::class, 'getRoles']);
        Route::get('/order-statuses', [AdminController::class, 'getOrderStatuses']);

        // Logs & Analytics
        Route::get('/admin/logs', [AdminController::class, 'getLogs']);
        Route::get('/admin/analytics', function (Request $request) {
            try {
                $response = Http::timeout(10)
                    ->get('http://localhost:8002/api/analytics', $request->query());
                return response()->json($response->json(), $response->status());
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                return response()->json(['message' => 'Layanan report sedang tidak tersedia.'], 503);
            }
        });

        Route::get('/admin/reports', function (Request $request) {
            try {
                $response = Http::timeout(10)
                    ->get('http://localhost:8002/api/reports', $request->query());
                return response()->json($response->json(), $response->status());
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                return response()->json(['message' => 'Layanan report sedang tidak tersedia.'], 503);
            }
        });

        // CMS Content Management
        Route::put('/cms/profile', [CmsController::class, 'updateProfile']);
        Route::put('/cms/contact', [CmsController::class, 'updateContact']);
        Route::get('/cms/org', [CmsController::class, 'indexOrg']);
        Route::post('/cms/org', [CmsController::class, 'storeOrg']);
        Route::put('/cms/org/{id}', [CmsController::class, 'updateOrg']);
        Route::delete('/cms/org/{id}', [CmsController::class, 'deleteOrg']);

        // CMS Posts (Berita & Kegiatan)
        Route::get('/cms/posts', [CmsController::class, 'indexPosts']);
        Route::post('/cms/posts', [CmsController::class, 'storePost']);
        Route::get('/cms/posts/{id}', [CmsController::class, 'showPost']);
        Route::put('/cms/posts/{id}', [CmsController::class, 'updatePost']);
        Route::delete('/cms/posts/{id}', [CmsController::class, 'deletePost']);

        // CMS Post Categories
        Route::get('/cms/post-categories', [CmsController::class, 'indexPostCategories']);
        Route::post('/cms/post-categories', [CmsController::class, 'storePostCategory']);
        Route::put('/cms/post-categories/{id}', [CmsController::class, 'updatePostCategory']);
        Route::delete('/cms/post-categories/{id}', [CmsController::class, 'deletePostCategory']);

        // CMS Galleries
        Route::get('/cms/galleries', [CmsController::class, 'indexGalleries']);
        Route::post('/cms/galleries', [CmsController::class, 'storeGallery']);
        Route::put('/cms/galleries/{id}', [CmsController::class, 'updateGallery']);
        Route::delete('/cms/galleries/{id}', [CmsController::class, 'deleteGallery']);
        Route::delete('/cms/gallery-files/{id}', [CmsController::class, 'deleteGalleryFile']);

        // CMS Contacts
        Route::get('/cms/contacts', [CmsController::class, 'indexContacts']);
        Route::post('/cms/contacts', [CmsController::class, 'storeContact']);
        Route::put('/cms/contacts/{id}', [CmsController::class, 'updateContact']);
        Route::delete('/cms/contacts/{id}', [CmsController::class, 'deleteContact']);

        // CMS General File
        Route::get('/cms/general-files', [CmsController::class, 'indexGeneralFiles']);
        Route::post('/cms/general-files', [CmsController::class, 'storeGeneralFile']);
        Route::delete('/cms/general-files/{id}', [CmsController::class, 'deleteGeneralFile']);

        // PDF & Excel
        // Route::get('/admin/export/excel', [AdminController::class, 'exportExcel']);
        // Route::get('/admin/export/pdf', [AdminController::class, 'exportPdf']);
    });

    // C. MANAJEMEN INVENTARIS (Admin & Operator)
    Route::middleware(['permission:manage inventory'])->group(function () {
        // Products CRUD
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        Route::post('/products/stock-in', [ProductController::class, 'updateStock']);

        // Product Categories CRUD
        Route::get('/product-categories/{id}', [ProductCategoryController::class, 'show']);
        Route::post('/product-categories', [ProductCategoryController::class, 'store']);
        Route::put('/product-categories/{id}', [ProductCategoryController::class, 'update']);
        Route::delete('/product-categories/{id}', [ProductCategoryController::class, 'destroy']);

        // Transactional Approval (Orders)
        Route::post('/orders/{id}/approve', [ProductOrderController::class, 'approve']);
        Route::post('/orders/{id}/reject', [ProductOrderController::class, 'reject']);
        Route::post('/orders/{id}/complete-pickup', [ProductOrderController::class, 'completePickup']);
        Route::post('/admin/orders', [ProductOrderController::class, 'adminStore']);

        // Logistics Preparation
        Route::post('/deliveries/ready/{id}', [DeliveryController::class, 'makeReady']);

        // Warehouses (Ganti dari storages)
        Route::get('/inventory/warehouses', [WarehouseController::class, 'indexWarehouses']);
        Route::post('/inventory/warehouses', [WarehouseController::class, 'storeWarehouse']);
        Route::put('/inventory/warehouses/{id}', [WarehouseController::class, 'updateWarehouse']);
        Route::delete('/inventory/warehouses/{id}', [WarehouseController::class, 'destroyWarehouse']);

        // Racks (Jika diperlukan sesuai DBML baru)
        Route::get('/inventory/racks', [WarehouseController::class, 'indexRacks']);
        Route::post('/inventory/racks', [WarehouseController::class, 'storeRack']);
        Route::put('/inventory/racks/{id}', [WarehouseController::class, 'updateRack']);
        Route::delete('/inventory/racks/{id}', [WarehouseController::class, 'destroyRack']);
    });

    // D. KHUSUS CUSTOMER (Cart & Orders)
    Route::middleware(['role:customer'])->group(function () {
        // Shopping Cart
        Route::get('/cart', [CartApiController::class, 'index']);
        Route::post('/cart', [CartApiController::class, 'store']);
        Route::put('/cart/{id}', [CartApiController::class, 'update']);
        Route::delete('/cart/{id}', [CartApiController::class, 'destroy']);
        Route::delete('/cart-clear', [CartApiController::class, 'clear']);

        // Order Process
        Route::post('/orders', [ProductOrderController::class, 'store']);
        Route::post('/orders/{id}/cancel', [ProductOrderController::class, 'cancel']);
        Route::post('/orders/quick', [ProductOrderController::class, 'quickStore']);
    });

    // E. KHUSUS COURIER (Logistics)
    Route::middleware(['role:courier'])->group(function () {
        Route::get('/courier/stats', [DeliveryController::class, 'getCourierStats']);
        Route::get('/deliveries/history', [DeliveryController::class, 'getCourierHistory']);
        Route::get('/deliveries/available', [DeliveryController::class, 'getAvailableDeliveries']);
        Route::get('/deliveries/active', [DeliveryController::class, 'getActiveDeliveries']);
        Route::post('/deliveries/claim/{id}', [DeliveryController::class, 'claim']);
        Route::post('/deliveries/start/{id}', [DeliveryController::class, 'startShipping']);
        Route::post('/deliveries/complete/{id}', [DeliveryController::class, 'complete']);
    });

    // F. AKSES UMUM TERAUTENTIKASI (Common Routes)
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/product-categories', [ProductCategoryController::class, 'index']);
    Route::get('/orders', [ProductOrderController::class, 'index']);
    Route::get('/orders/{id}', [ProductOrderController::class, 'show']);
    Route::get('/deliveries/{id}/tracking', [DeliveryController::class, 'getTracking']);
    Route::get('/post-categories', [CmsController::class, 'indexPostCategories']);
});
