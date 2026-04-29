<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    protected $mainAppUrl;
    protected $internalSecret;

    public function __construct()
    {
        $this->mainAppUrl     = env('MAIN_APP_URL', 'http://localhost:8000');
        $this->internalSecret = env('INTERNAL_SECRET');
    }

    // Helper untuk HTTP call ke app utama
    private function fetchFromMainApp(string $endpoint, array $params = [])
    {
        try {
            $params['internal_secret'] = env('INTERNAL_SECRET');

            $response = Http::timeout(30)
                ->get($this->mainAppUrl . '/api/internal/' . $endpoint, $params);

            if ($response->successful()) {
                return $response->json();
            }

            \Log::error("fetchFromMainApp gagal: " . $endpoint . " status=" . $response->status());
            return null;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error("Connection error ke app utama: " . $e->getMessage());
            return null;
        }
    }

    // Analytics Dashboard
    public function analytics(Request $request)
    {
        try {
            $data = $this->fetchFromMainApp('analytics', $request->query());

            if (!$data) {
                return response()->json([
                    'message' => 'Layanan data sedang tidak tersedia.'
                ], 503);
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // Report Data
    public function reportData(Request $request)
    {
        try {
            $data = $this->fetchFromMainApp('orders', $request->query());

            if (!$data) {
                return response()->json([
                    'message' => 'Layanan data sedang tidak tersedia.'
                ], 503);
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // Export Excel
    public function exportExcel(Request $request)
    {
        try {
            $type      = $request->query('type', 'orders');
            $startDate = $request->query('start_date');
            $endDate   = $request->query('end_date');
            $statusId  = $request->query('status_id', 'all');

            if ($type === 'users') {
                $data = $this->fetchFromMainApp('users', [
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                ]);

                if (!$data) {
                    return response()->json(['message' => 'Layanan data sedang tidak tersedia.'], 503);
                }

                return Excel::download(
                    new \App\Exports\UsersExport($data),
                    'Data_Mitra.xlsx'
                );
            }

            $data = $this->fetchFromMainApp('orders', [
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'status_id'  => $statusId,
            ]);

            if (!$data) {
                return response()->json(['message' => 'Layanan data sedang tidak tersedia.'], 503);
            }

            return Excel::download(
                new \App\Exports\OrdersExport($data),
                'Laporan_Distribusi_EPharma_' . date('Ymd') . '.xlsx'
            );

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // Export PDF
    public function exportPdf(Request $request)
    {
        try {
            $type      = $request->query('type', 'orders');
            $startDate = $request->query('start_date');
            $endDate   = $request->query('end_date');
            $statusId  = $request->query('status_id', 'all');

            if ($type === 'users') {
                $data = $this->fetchFromMainApp('users', [
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                ]);

                if (!$data) {
                    return response()->json(['message' => 'Layanan data sedang tidak tersedia.'], 503);
                }

                $pdf = Pdf::loadView('pdf.users_report', [
                    'data'      => $data,
                    'startDate' => $startDate,
                    'endDate'   => $endDate,
                ]);

                return $pdf->download('Data_User.pdf');
            }

            $data = $this->fetchFromMainApp('orders', [
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'status_id'  => $statusId,
            ]);

            if (!$data) {
                return response()->json(['message' => 'Layanan data sedang tidak tersedia.'], 503);
            }

            $pdf = Pdf::loadView('pdf.orders_report', [
                'orders'    => $data,
                'startDate' => $startDate,
                'endDate'   => $endDate,
            ]);

            return $pdf->download('Laporan_Distribusi.pdf');

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
