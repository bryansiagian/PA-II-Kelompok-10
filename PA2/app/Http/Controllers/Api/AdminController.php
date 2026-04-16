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
// Import Model Role dari Spatie
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\OrdersExport;

class AdminController extends Controller
{
    /**
     * Ambil daftar user Aktif (Status 1) selain Admin.
     */
    public function getUsers() {
        try {
            $users = User::with('roles')
                ->where('status', 1)
                ->whereHas('roles', function($query) {
                    $query->where('name', '!=', 'admin');
                })
                ->latest()
                ->get();

            return response()->json($users, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data user'], 500);
        }
    }

    /**
     * MODIFIKASI: Ambil daftar user yang baru mendaftar (Status 0)
     * DAN sudah memverifikasi email (email_verified_at tidak null).
     */
    public function getPendingUsers() {
        try {
            return User::with('roles', 'courierDetail')
                ->where('status', 0)
                ->whereNotNull('email_verified_at') // <--- Filter PENTING
                ->latest()
                ->get();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data antrian'], 500);
        }
    }

    /**
     * Tampilkan detail satu user.
     */
    public function showUser($id) {
        return User::with('roles')->findOrFail($id);
    }

    /**
     * Tambah User Baru secara manual oleh Admin.
     */
    public function storeUser(Request $request) {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id'  => 'required|exists:roles,id',
            'address'  => 'nullable|string',
            'vehicle_type' => 'nullable|required_if:role_name,courier|in:motorcycle,car',
            'vehicle_plate' => 'nullable|required_if:role_name,courier|string',
        ]);

        try {
            return DB::transaction(function() use ($request) {
                // Pastikan mencari role pada guard 'web'
                $role = Role::where('id', $request->role_id)->where('guard_name', 'web')->firstOrFail();

                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'address'  => $request->address,
                    'status'   => 1, // Langsung Aktif
                    'email_verified_at' => now() // Langsung terverifikasi
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

    /**
     * Menyetujui pendaftaran akun baru.
     */
    public function approveUser($id) {
        try {
            $user = User::findOrFail($id);
            $user->update(['status' => 1]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "APPROVE USER: Menyetujui pendaftaran akun {$user->name}"
            ]);

            return response()->json(['message' => 'Pendaftaran akun telah disetujui']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses persetujuan'], 500);
        }
    }

    /**
     * Menolak pendaftaran akun baru.
     */
    public function rejectUser($id) {
        try {
            $user = User::findOrFail($id);
            $user->update(['status' => 2]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "REJECT USER: Menolak pendaftaran akun {$user->name}"
            ]);

            return response()->json(['message' => 'Pendaftaran akun telah ditolak']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses penolakan'], 500);
        }
    }

    /**
     * Update Data/Role User.
     */
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

    /**
     * Hapus User secara permanen.
     */
    public function destroyUser($id) {
        try {
            $user = User::findOrFail($id);
            $userName = $user->name;

            if ($user->id === auth()->id()) {
                return response()->json(['message' => 'Anda tidak bisa menghapus akun Anda sendiri'], 403);
            }

            $user->delete();

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "DELETE USER: Menghapus akun {$userName} secara permanen"
            ]);

            return response()->json(['message' => 'User berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus user'], 500);
        }
    }

    /**
     * Ambil daftar audit logs terbaru.
     */
    public function getLogs() {
        try {
            $logs = AuditLog::with(['user.roles'])->latest()->get();
            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil log'], 500);
        }
    }

    /**
     * Ambil daftar Role selain Admin.
     */
    public function getRoles() {
        return Role::where('name', '!=', 'admin')->get();
    }

    /**
     * Dashboard Analytics.
     */
    public function getAnalytics(Request $request) {
        try {
            $period = $request->query('period', 'daily');

            $completedStatus = ProductOrderStatus::where('name', 'Completed')->first();
            $completedId = $completedStatus ? $completedStatus->id : 0;

            $totalCompleted = ProductOrder::where('product_order_status_id', $completedId)->count();

            $totalItems = DB::table('product_order_details')
                ->join('product_orders', 'product_order_details.product_order_id', '=', 'product_orders.id')
                ->where('product_orders.product_order_status_id', $completedId)
                ->sum('product_order_details.quantity');

            if ($period == 'daily') {
                $stats = ProductOrder::select(
                        DB::raw('DATE_FORMAT(created_at, "%d %b") as label'),
                        DB::raw('COUNT(*) as total_requests')
                    )
                    ->where('created_at', '>=', now()->subDays(7))
                    ->groupBy('label')
                    ->orderBy('created_at', 'ASC')
                    ->get();
            } else {
                $stats = ProductOrder::select(
                        DB::raw('DATE_FORMAT(created_at, "%b %Y") as label'),
                        DB::raw('COUNT(*) as total_requests')
                    )
                    ->where('created_at', '>=', now()->subMonths(6))
                    ->groupBy('label')
                    ->orderBy('created_at', 'ASC')
                    ->get();
            }

            $topProducts = DB::table('product_order_details')
                ->join('products', 'products.id', '=', 'product_order_details.product_id')
                ->join('product_orders', 'product_orders.id', '=', 'product_order_details.product_order_id')
                ->where('product_orders.product_order_status_id', $completedId)
                ->select('products.name', DB::raw('SUM(product_order_details.quantity) as total_qty'))
                ->groupBy('products.name')
                ->orderBy('total_qty', 'DESC')
                ->limit(5)
                ->get();

            return response()->json([
                'stats' => $stats,
                'top_drugs' => $topProducts,
                'summary' => [
                    'total_completed' => (int)$totalCompleted,
                    'total_items_distributed' => (int)$totalItems,
                    'total_products' => Product::where('active', 1)->count(),
                    'low_stock_products' => Product::where('active', 1)->whereRaw('stock <= min_stock')->count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function exportExcel()
    {
        return Excel::download(new OrdersExport, 'Laporan_Distribusi_EPharma_' . date('Ymd') . '.xlsx');
    }

    public function exportPdf()
    {
        $orders = ProductOrder::with(['user', 'status', 'type', 'items'])->latest()->get();
        $pdf = Pdf::loadView('pdf.orders_report', compact('orders'));
        return $pdf->download('Laporan_Distribusi_EPharma_' . date('Ymd') . '.pdf');
    }
}
