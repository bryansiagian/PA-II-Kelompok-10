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
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\OrdersExport;
use App\Mail\AccountStatusNotification;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    private function authServiceUrl(): string
    {
        return env('AUTH_SERVICE_URL', 'http://127.0.0.1:8001');
    }

    /**
     * Menampilkan daftar pengguna aktif (selain admin).
     */
    public function getUsers() {
        try {
            $users = User::with(['roles'])
                ->where('status', 1)
                ->where('id', '!=', auth()->id()) // ← tambah ini
                ->whereHas('roles', function($query) {
                    $query->where('name', '!=', 'admin');
                })
                ->latest()
                ->get();

            return response()->json($users, 200);
        } catch (\Exception $e) {
            \Log::error("Error in getUsers: " . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil data user'], 500);
        }
    }

    /**
     * Mengambil daftar pengguna yang baru mendaftar dan sudah verifikasi OTP.
     */
    public function getPendingUsers() {
        try {
            return User::with('roles')
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
    public function storeUser(Request $request)
    {
        $isCustomer = $request->role_name === 'customer';

        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'role_id'  => 'required|exists:roles,id',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string',
        ];

        if (!$isCustomer) {
            $rules['password'] = 'required|string|min:6';
        }

        if ($isCustomer) {
            $rules['regency']  = 'required|string';
            $rules['district'] = 'required|string';
            $rules['village']  = 'required|string';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $role = Role::findOrFail($request->role_id);

            $plainPassword = $isCustomer
                ? \Illuminate\Support\Str::random(10)
                : $request->password;

            $user = User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'password'          => Hash::make($plainPassword),
                'phone'             => $request->phone,
                'address'           => $request->address,
                'regency'           => $isCustomer ? $request->regency  : null,
                'district'          => $isCustomer ? $request->district : null,
                'village'           => $isCustomer ? $request->village  : null,
                'status'            => 1,
                'email_verified_at' => now(),
            ]);

            $user->assignRole($role);

            // Sync ke auth_service
            try {
                $authResponse = Http::timeout(10)->post($this->authServiceUrl() . '/api/register', [
                    'name'                  => $user->name,
                    'email'                 => $user->email,
                    'password'              => $plainPassword,
                    'password_confirmation' => $plainPassword,
                    'phone'                 => $user->phone   ?? '',
                    'address'               => $user->address ?? '',
                ]);

                \Log::info('Auth-service storeUser response: ' . $authResponse->status() . ' - ' . $authResponse->body());

                if ($authResponse->status() !== 500) {
                    Http::timeout(5)->post($this->authServiceUrl() . '/api/internal/update-status', [
                        'email'  => $user->email,
                        'status' => 1,
                    ]);
                }

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                \Log::warning('Gagal sync storeUser ke auth-service: ' . $e->getMessage());
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "CREATE USER: Admin membuat akun {$role->name} - {$user->name}"
            ]);

            DB::commit();

            $response = ['message' => 'Akun berhasil dibuat'];
            if ($isCustomer) {
                $response['plain_password'] = $plainPassword;
            }

            return response()->json($response, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function approveUser($id) {
        try {
            $user = User::findOrFail($id);
            $user->update(['status' => 1]);

            try {
                Http::timeout(5)->post($this->authServiceUrl() . '/api/internal/update-status', [
                    'email'  => $user->email,
                    'status' => 1,
                ]);
            } catch (\Exception $e) {
                \Log::error('Gagal sinkronisasi status ke auth-service: ' . $e->getMessage());
            }

            // Kirim email notifikasi ke user
            try {
                Mail::to($user->email)->send(new AccountStatusNotification($user->name, 'approved'));
            } catch (\Exception $e) {
                \Log::warning('Gagal kirim email approve ke ' . $user->email . ': ' . $e->getMessage());
            }

            AuditLog::create(['user_id' => auth()->id(), 'action' => "APPROVE USER: Menyetujui akun {$user->name}"]);
            return response()->json(['message' => 'Pendaftaran akun telah disetujui']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses persetujuan'], 500);
        }
    }

    public function rejectUser($id) {
        try {
            $user = User::findOrFail($id);
            $userName  = $user->name;
            $userEmail = $user->email;

            // Tandai sebagai ditolak di DB utama (tetap simpan agar bisa ditampilkan pesan jelas saat login/register)
            $user->update(['status' => 2]);

            // Hapus dari auth-service agar tidak bisa login lewat sana
            try {
                Http::timeout(5)->post($this->authServiceUrl() . '/api/internal/delete-user', [
                    'email' => $userEmail,
                ]);
            } catch (\Exception $e) {
                \Log::error('Gagal hapus user di auth-service: ' . $e->getMessage());
            }

            // Kirim email notifikasi penolakan
            try {
                \Mail::to($userEmail)->send(new \App\Mail\AccountStatusNotification($userName, 'rejected'));
            } catch (\Exception $e) {
                \Log::warning('Gagal kirim email reject ke ' . $userEmail . ': ' . $e->getMessage());
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "REJECT USER: Menolak akun {$userName}",
            ]);

            return response()->json(['message' => 'Pendaftaran akun telah ditolak.']);

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
            $user = User::with('roles')->findOrFail($id);

            if ($user->id === auth()->id()) {
                return response()->json(['message' => 'Tidak dapat menghapus akun sendiri.'], 403);
            }

            if ($user->hasRole('admin')) {
                return response()->json(['message' => 'Akun admin tidak dapat dihapus.'], 403);
            }

            $user->delete();
            return response()->json(['message' => 'User berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus user'], 500);
        }
    }

    /**
     * Mengaktifkan ulang akun yang sebelumnya ditolak.
     */
    public function activateRejectedUser($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->status !== 2) {
                return response()->json(['message' => 'Akun ini bukan akun yang ditolak.'], 422);
            }

            $plainPassword = \Illuminate\Support\Str::random(10);

            // Update status di DB utama
            $user->update([
                'status'            => 1,
                'email_verified_at' => now(),
            ]);

            // Buat ulang di auth-service dengan password baru
            try {
                Http::timeout(10)->post($this->authServiceUrl() . '/api/internal/recreate-user', [
                    'name'     => $user->name,
                    'email'    => $user->email,
                    'phone'    => $user->phone   ?? '',
                    'address'  => $user->address ?? '',
                    'password' => $plainPassword,
                ]);
            } catch (\Exception $e) {
                \Log::error('Gagal recreate user di auth-service: ' . $e->getMessage());
            }

            // Kirim email berisi password baru
            try {
                \Mail::to($user->email)->send(
                    new \App\Mail\AccountActivatedNotification($user->name, $plainPassword)
                );
            } catch (\Exception $e) {
                \Log::warning('Gagal kirim email aktivasi ke ' . $user->email . ': ' . $e->getMessage());
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "ACTIVATE REJECTED USER: Mengaktifkan ulang akun {$user->name}",
            ]);

            return response()->json(['message' => 'Akun berhasil diaktifkan. Password baru telah dikirim ke email pengguna.']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengaktifkan akun'], 500);
        }
    }

    /**
     * Mengambil daftar pengguna yang ditolak.
     */
    public function getRejectedUsers()
    {
        try {
            return response()->json(
                User::with('roles')
                    ->where('status', 2)
                    ->latest()
                    ->get()
            );
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data'], 500);
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

            $completedStatus = ProductOrderStatus::where('name', 'Completed')->first();
            $completedId = $completedStatus ? $completedStatus->id : 0;

            $cancelledStatus = ProductOrderStatus::where('name', 'Cancelled')->first();
            $cancelledId = $cancelledStatus ? $cancelledStatus->id : 0;

            if ($period == 'daily') {
                $daysCount = 7;
                $start = $startDate ? Carbon::parse($startDate) : now()->subDays($daysCount - 1);
                $end = $endDate ? Carbon::parse($endDate) : now();
            } else {
                $monthCount = 6;
                $start = $startDate ? Carbon::parse($startDate)->startOfMonth() : now()->subMonths($monthCount - 1)->startOfMonth();
                $end = $endDate ? Carbon::parse($endDate)->endOfMonth() : now()->endOfMonth();
            }

            $baseOrderQuery = ProductOrder::query();

            if ($statusId && $statusId !== 'all') {
                $baseOrderQuery->where('product_order_status_id', $statusId);
            }

            if ($startDate && $endDate) {
                $baseOrderQuery->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
            }

            $totalUsers = User::role('customer')->where('status', 1)->count();
            $totalProducts = Product::where('active', 1)->count();

            $summaryOrders = (clone $baseOrderQuery)->count();
            $summaryItems = (int)DB::table('product_order_details')
                ->join('product_orders', 'product_order_details.product_order_id', '=', 'product_orders.id')
                ->when($statusId && $statusId !== 'all', function($q) use ($statusId) {
                    return $q->where('product_orders.product_order_status_id', $statusId);
                })
                ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                    return $q->whereBetween('product_orders.created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
                })
                ->sum('quantity');

            $notShippedCount = ProductOrder::whereNotIn('product_order_status_id', [$completedId, $cancelledId])->count();

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

            $finalChartStats = [];
            foreach ($chartData as $label => $val) {
                $finalChartStats[] = ['label' => $label, 'total_requests' => $val];
            }

            $topProducts = DB::table('product_order_details')
                ->join('products', 'products.id', '=', 'product_order_details.product_id')
                ->join('product_orders', 'product_orders.id', '=', 'product_order_details.product_order_id')
                ->where('product_orders.product_order_status_id', $completedId)
                ->select('products.name', DB::raw('SUM(product_order_details.quantity) as total_qty'))
                ->groupBy('products.id', 'products.name')
                ->orderBy('total_qty', 'DESC')
                ->limit(5)->get();

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
                    'total_orders' => $summaryOrders,
                    'not_shipped' => $notShippedCount,
                    'total_items_distributed' => $summaryItems,
                    'low_stock_products' => Product::where('active', 1)->whereRaw('stock <= min_stock')->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

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
            $statuses = \App\Models\ProductOrderStatus::all();
            return response()->json($statuses);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil status'], 500);
        }
    }

    public function exportExcel(Request $request) {
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

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name'    => 'required|string',
            'email'   => 'required|email|unique:users,email',
            'phone'   => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $plainPassword = \Illuminate\Support\Str::random(10);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'phone'             => $request->phone,
                'address'           => $request->address,
                'password'          => bcrypt($plainPassword),
                'status'            => 1,
                'email_verified_at' => now(),
            ]);

            $user->assignRole('customer');

            try {
                $authResponse = Http::timeout(30)->retry(3, 500)->post($this->authServiceUrl() . '/api/register', [
                    'name'                  => $user->name,
                    'email'                 => $user->email,
                    'password'              => $plainPassword,
                    'password_confirmation' => $plainPassword,
                    'phone'                 => $user->phone   ?? '',
                    'address'               => $user->address ?? '',
                ]);

                \Log::info('Auth-service response: ' . $authResponse->status() . ' - ' . $authResponse->body());

                if ($authResponse->status() !== 500) {
                    $updateResponse = Http::timeout(10)->retry(3, 500)->post($this->authServiceUrl() . '/api/internal/update-status', [
                        'email'  => $user->email,
                        'status' => 1,
                    ]);
                    \Log::info('Update-status response: ' . $updateResponse->status() . ' - ' . $updateResponse->body());
                } else {
                    \Log::warning('Auth-service register gagal: ' . $authResponse->body());
                }

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                \Log::warning('Gagal sync storeCustomer ke auth-service: ' . $e->getMessage());
            }

            DB::commit();

            return response()->json([
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'plain_password' => $plainPassword,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ─── Delivery Status ──────────────────────────────────────────────────────────

    public function getDeliveryStatuses() {
        try {
            return response()->json(\App\Models\DeliveryStatus::latest()->get());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data status'], 500);
        }
    }

    public function storeDeliveryStatus(Request $request) {
        $request->validate(['name' => 'required|string|max:100|unique:delivery_status,name']);
        try {
            $status = \App\Models\DeliveryStatus::create(['name' => $request->name]);
            AuditLog::create(['user_id' => auth()->id(), 'action' => "CREATE DELIVERY STATUS: {$status->name}"]);
            return response()->json($status, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan status'], 500);
        }
    }

    public function updateDeliveryStatus(Request $request, $id) {
        $request->validate(['name' => "required|string|max:100|unique:delivery_status,name,{$id}"]);
        try {
            $status = \App\Models\DeliveryStatus::findOrFail($id);
            $status->update(['name' => $request->name]);
            AuditLog::create(['user_id' => auth()->id(), 'action' => "UPDATE DELIVERY STATUS: {$status->name}"]);
            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui status'], 500);
        }
    }

    public function destroyDeliveryStatus($id) {
        try {
            $status = \App\Models\DeliveryStatus::findOrFail($id);
            $name = $status->name;
            $status->delete();
            AuditLog::create(['user_id' => auth()->id(), 'action' => "DELETE DELIVERY STATUS: {$name}"]);
            return response()->json(['message' => 'Status berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus status'], 500);
        }
    }

    // ─── Product Order Status ─────────────────────────────────────────────────────

    public function getProductOrderStatuses() {
        try {
            return response()->json(\App\Models\ProductOrderStatus::latest()->get());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data status'], 500);
        }
    }

    public function storeProductOrderStatus(Request $request) {
        $request->validate([
            'name'   => 'required|string|max:100|unique:product_order_status,name',
            'active' => 'boolean',
        ]);
        try {
            $status = \App\Models\ProductOrderStatus::create([
                'name'   => $request->name,
                'active' => $request->input('active', true),
            ]);
            AuditLog::create(['user_id' => auth()->id(), 'action' => "CREATE ORDER STATUS: {$status->name}"]);
            return response()->json($status, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan status'], 500);
        }
    }

    public function updateProductOrderStatus(Request $request, $id) {
        $request->validate([
            'name'   => "required|string|max:100|unique:product_order_status,name,{$id}",
            'active' => 'boolean',
        ]);
        try {
            $status = \App\Models\ProductOrderStatus::findOrFail($id);
            $status->update([
                'name'   => $request->name,
                'active' => $request->input('active', $status->active),
            ]);
            AuditLog::create(['user_id' => auth()->id(), 'action' => "UPDATE ORDER STATUS: {$status->name}"]);
            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui status'], 500);
        }
    }

    public function destroyProductOrderStatus($id) {
        try {
            $status = \App\Models\ProductOrderStatus::findOrFail($id);
            $name = $status->name;
            $status->delete();
            AuditLog::create(['user_id' => auth()->id(), 'action' => "DELETE ORDER STATUS: {$name}"]);
            return response()->json(['message' => 'Status berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus status'], 500);
        }
    }
}
