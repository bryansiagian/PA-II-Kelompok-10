<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ProductOrderDetail;
use App\Models\ProductOrderStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\OrdersExport;

class AdminController extends Controller
{
    /**
     * Menampilkan daftar pengguna aktif (selain admin).
     */
    public function getUsers() {
        try {
            // PERBAIKAN: Tambahkan 'courierDetail' ke dalam with()
            $users = User::with(['roles', 'courierDetail'])
                ->where('status', 1)
                ->whereHas('roles', function($query) {
                    $query->where('name', '!=', 'admin');
                })
                ->latest()
                ->get();

            return response()->json($users, 200);
        } catch (\Exception $e) {
            // Senior Tip: Selalu log error asli agar mudah debugging jika terjadi sesuatu di server
            \Log::error("Error in getUsers: " . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil data user'], 500);
        }
    }

    /**
     * Mengambil daftar pengguna yang baru mendaftar dan sudah verifikasi OTP.
     */
    public function getPendingUsers() {
        try {
            return User::with('roles', 'courierDetail')
                ->where('status', 0)
                ->whereNotNull('email_verified_at')
                ->latest()
                ->get();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data antrian'], 500);
        }
    }

    public function showUser($id) {
        return User::with('roles')->findOrFail($id);
    }

    /**
     * Menambahkan User/Operator/Kurir secara manual.
     */
    public function storeUser(Request $request) {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone'    => $request->phone,
            'role_id'  => 'required|exists:roles,id',
            'address'  => 'nullable|string',
            'vehicle_type' => 'nullable|required_if:role_name,courier|in:motorcycle,car',
            'vehicle_plate' => 'nullable|required_if:role_name,courier|string',
        ]);

        try {
            return DB::transaction(function() use ($request) {
                $role = Role::where('id', $request->role_id)->where('guard_name', 'web')->firstOrFail();

                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'address'  => $request->address,
                    'status'   => 1,
                    'email_verified_at' => now()
                ]);

                $user->assignRole($role->name);

                if ($role->name === 'courier') {
                    \App\Models\CourierDetail::create([
                        'user_id' => $user->id,
                        'vehicle_type' => $request->vehicle_type,
                        'vehicle_plate' => strtoupper($request->vehicle_plate),
                    ]);
                }

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action'  => "CREATE USER: Admin membuat akun {$role->name} - {$user->name}"
                ]);

                return response()->json(['message' => 'Akun ' . ucfirst($role->name) . ' berhasil dibuat'], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan user: ' . $e->getMessage()], 500);
        }
    }

    public function approveUser($id) {
        try {
            $user = User::findOrFail($id);
            $user->update(['status' => 1]);
            AuditLog::create(['user_id' => auth()->id(), 'action' => "APPROVE USER: Menyetujui akun {$user->name}"]);
            return response()->json(['message' => 'Pendaftaran akun telah disetujui']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses persetujuan'], 500);
        }
    }

    public function rejectUser($id) {
        try {
            $user = User::findOrFail($id);
            $user->update(['status' => 2]);
            AuditLog::create(['user_id' => auth()->id(), 'action' => "REJECT USER: Menolak akun {$user->name}"]);
            return response()->json(['message' => 'Pendaftaran akun telah ditolak']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses penolakan'], 500);
        }
    }

    public function updateUser(Request $request, $id) {
        try {
            $user = User::findOrFail($id);
            $role = Role::where('id', $request->role_id)->where('guard_name', 'web')->firstOrFail();
            $user->update(['name' => $request->name]);
            $user->syncRoles([$role->name]);
            return response()->json(['message' => 'Success']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal update role'], 500);
        }
    }

    public function destroyUser($id) {
        try {
            $user = User::findOrFail($id);
            if ($user->id === auth()->id()) return response()->json(['message' => 'Aksi dilarang'], 403);
            $user->delete();
            return response()->json(['message' => 'User berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus user'], 500);
        }
    }

    public function getLogs() {
        return response()->json(AuditLog::with(['user.roles'])->latest()->limit(100)->get());
    }

    public function getRoles() {
        return Role::where('name', '!=', 'admin')->get();
    }

    /**
     * Dashboard Analytics & Rekapitulasi Umum.
     */
    public function getAnalytics(Request $request) {
        try {
            $period = $request->query('period', 'daily');
            $statusId = $request->query('status_id');
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            // Ambil ID Status penting untuk kalkulasi rasio & produk terlaris
            $completedStatus = ProductOrderStatus::where('name', 'Completed')->first();
            $completedId = $completedStatus ? $completedStatus->id : 0;

            $cancelledStatus = ProductOrderStatus::where('name', 'Cancelled')->first();
            $cancelledId = $cancelledStatus ? $cancelledStatus->id : 0;

            // --- 1. SET RENTANG WAKTU UNTUK GRAFIK ---
            if ($period == 'daily') {
                $daysCount = 7;
                // Jika ada start_date manual, hitung selisih harinya, jika tidak default 7 hari
                $start = $startDate ? Carbon::parse($startDate) : now()->subDays($daysCount - 1);
                $end = $endDate ? Carbon::parse($endDate) : now();
            } else {
                $monthCount = 6;
                $start = $startDate ? Carbon::parse($startDate)->startOfMonth() : now()->subMonths($monthCount - 1)->startOfMonth();
                $end = $endDate ? Carbon::parse($endDate)->endOfMonth() : now()->endOfMonth();
            }

            // --- 2. BASE QUERY (UNTUK FILTER TRANSAKSI) ---
            // Query ini digunakan untuk menghitung ringkasan di dashboard berdasarkan filter
            $baseOrderQuery = ProductOrder::query();

            if ($statusId && $statusId !== 'all') {
                $baseOrderQuery->where('product_order_status_id', $statusId);
            }

            if ($startDate && $endDate) {
                $baseOrderQuery->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
            }

            // --- 3. HITUNG STATISTIK UTAMA (RINGKASAN) ---
            $totalUsers = User::role('customer')->where('status', 1)->count(); // Hanya Mitra Aktif
            $totalProducts = Product::where('active', 1)->count();

            // Total Pesanan & Item Berdasarkan Filter yang dipilih di UI
            $summaryOrders = (clone $baseOrderQuery)->count();
            $summaryItems = (int)DB::table('product_order_details')
                ->join('product_orders', 'product_order_details.product_order_id', '=', 'product_orders.id')
                // Terapkan filter yang sama dengan base query
                ->when($statusId && $statusId !== 'all', function($q) use ($statusId) {
                    return $q->where('product_orders.product_order_status_id', $statusId);
                })
                ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                    return $q->whereBetween('product_orders.created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
                })
                ->sum('quantity');

            // Belum Terkirim (Status selain Completed & Cancelled) - Global
            $notShippedCount = ProductOrder::whereNotIn('product_order_status_id', [$completedId, $cancelledId])->count();

            // --- 4. LOGIKA GRAFIK TREN (DENGAN FILLER ANGKA 0) ---
            $chartData = [];
            if ($period == 'daily') {
                $diff = (int)Carbon::parse($start)->diffInDays($end);
                for ($i = 0; $i <= $diff; $i++) {
                    $dateLabel = Carbon::parse($start)->addDays($i)->format('d M');
                    $chartData[$dateLabel] = 0;
                }
                $dbDateFormat = '%d %b';
            } else {
                $diff = (int)Carbon::parse($start)->diffInMonths($end);
                for ($i = 0; $i <= $diff; $i++) {
                    $dateLabel = Carbon::parse($start)->addMonths($i)->format('M Y');
                    $chartData[$dateLabel] = 0;
                }
                $dbDateFormat = '%b %Y';
            }

            // Ambil data trend dari DB berdasarkan filter
            $trendStats = (clone $baseOrderQuery)
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '$dbDateFormat') as label"),
                    DB::raw('COUNT(*) as total_requests')
                )
                ->groupBy('label')
                ->get();

            foreach ($trendStats as $stat) {
                if (isset($chartData[$stat->label])) {
                    $chartData[$stat->label] = $stat->total_requests;
                }
            }

            // Format ulang untuk Chart.js
            $finalChartStats = [];
            foreach ($chartData as $label => $val) {
                $finalChartStats[] = ['label' => $label, 'total_requests' => $val];
            }

            // --- 5. PRODUK TERLARIS (TOP 5) ---
            $topProducts = DB::table('product_order_details')
                ->join('products', 'products.id', '=', 'product_order_details.product_id')
                ->join('product_orders', 'product_orders.id', '=', 'product_order_details.product_order_id')
                ->where('product_orders.product_order_status_id', $completedId)
                ->select('products.name', DB::raw('SUM(product_order_details.quantity) as total_qty'))
                ->groupBy('products.id', 'products.name')
                ->orderBy('total_qty', 'DESC')
                ->limit(5)->get();

            // --- 6. DATA RASIO PENGIRIMAN (DOUGHNUT CHART) ---
            $shippedCount = ProductOrder::where('product_order_status_id', $completedId)->count();

            return response()->json([
                'stats' => $finalChartStats,
                'top_drugs' => $topProducts,
                'delivery_ratio' => [
                    'shipped' => $shippedCount,
                    'not_shipped' => $notShippedCount
                ],
                'summary' => [
                    'total_users' => $totalUsers,
                    'total_products' => $totalProducts,
                    'total_orders' => $summaryOrders, // Terpengaruh filter
                    'not_shipped' => $notShippedCount,
                    'total_items_distributed' => $summaryItems, // Terpengaruh filter
                    'low_stock_products' => Product::where('active', 1)->whereRaw('stock <= min_stock')->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mengambil data untuk halaman Reports (Laporan).
     */
    public function getReportData(Request $request) {
        $query = ProductOrder::with(['user', 'status', 'items.product']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        return response()->json($query->latest()->get());
    }

    public function getOrderStatuses() {
        try {
            // Mengambil semua data dari tabel product_order_statuses
            $statuses = \App\Models\ProductOrderStatus::all();
            return response()->json($statuses);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil status'], 500);
        }
    }

    /**
     * Export Laporan.
     */
    public function exportExcel(Request $request) {
        // Tambahkan ini untuk mencegah timeout (5 menit)
        ini_set('max_execution_time', 300);

        $type = $request->query('type', 'orders');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $statusId = $request->query('status_id');

        if ($type === 'users') {
            return Excel::download(new \App\Exports\UsersExport($startDate, $endDate), 'Data_Mitra.xlsx');
        }

        return Excel::download(
            new \App\Exports\OrdersExport($startDate, $endDate, $statusId),
            'Laporan_Distribusi_EPharma_' . date('Ymd') . '.xlsx'
        );
    }

    public function exportPdf(Request $request) {
        $type = $request->query('type', 'orders');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $statusId = $request->query('status_id');

        if ($type === 'users') {
            $data = User::role('customer')
                ->where('status', 1)
                ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
                ->get();
            $pdf = Pdf::loadView('pdf.users_report', compact('data', 'startDate', 'endDate'));
            return $pdf->download('Data_User.pdf');
        }

        $query = ProductOrder::with(['user', 'status']);
        if ($statusId !== 'all') {
            $query->where('product_order_status_id', $statusId);
        }
        $orders = $query->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])->get();

        $pdf = Pdf::loadView('pdf.orders_report', compact('orders', 'startDate', 'endDate'));
        return $pdf->download('Laporan_Distribusi.pdf');
    }

    // Endpoint internal untuk Report Service
    public function getOrdersForReport(Request $request)
    {
        $query = ProductOrder::with(['user', 'status', 'type', 'items.product']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        if ($request->filled('status_id') && $request->status_id !== 'all') {
            $query->where('product_order_status_id', $request->status_id);
        }

        return response()->json($query->latest()->get());
    }

    public function getUsersForReport(Request $request)
    {
        $query = User::role('customer')->where('status', 1);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        return response()->json($query->get());
    }

    public function getProductsForReport()
    {
        return response()->json(Product::where('active', 1)->get());
    }
}
