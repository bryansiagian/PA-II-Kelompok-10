<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/analytics',     [ReportController::class, 'analytics']);
Route::get('/reports',       [ReportController::class, 'reportData']);
Route::get('/export/excel',  [ReportController::class, 'exportExcel']);
Route::get('/export/pdf',    [ReportController::class, 'exportPdf']);

Route::get('/test', function () {
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(5)
            ->get('http://localhost:8000/api/internal/analytics', [
                'internal_secret' => 'rahasia-report-service-2024'
            ]);

        return response()->json([
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'class' => get_class($e),
        ]);
    }
});
