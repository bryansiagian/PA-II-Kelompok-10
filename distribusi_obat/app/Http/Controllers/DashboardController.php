<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Drug;
use App\Models\User;
use App\Models\DrugRequest;
use App\Models\Delivery;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Menampilkan Dashboard Utama berdasarkan Role.
     */
    public function index()
    {
        $user = auth()->user();

        // Jika Admin, Operator, atau Kurir -> Masuk ke Backoffice (Sidebar)
        if ($user->hasAnyRole(['admin', 'operator', 'courier'])) {
            if ($user->hasRole('admin')) return view('admin.dashboard');
            if ($user->hasRole('operator')) return view('operator.dashboard');
            if ($user->hasRole('courier')) return view('courier.dashboard');
        }

        // Jika Customer -> Arahkan kembali ke Landing Page (welcome)
        // Tapi kita beri tahu view bahwa ini adalah mode dashboard
        return view('welcome', ['isDashboard' => true]);

        // Jika user tidak punya role sama sekali
        Auth::logout();
        return redirect('/login')->withErrors(['email' => 'Akun Anda tidak memiliki hak akses yang sah.']);
    }

    // --- Logika Pengumpulan Data tiap Dashboard ---

    private function adminDashboard($data)
    {
        $data['total_users'] = User::count();
        $data['total_drugs'] = Drug::count();
        $data['total_requests'] = DrugRequest::count();
        // Menampilkan view admin (kita gunakan view yang sudah dibuat sebelumnya)
        return view('admin.users', $data);
    }

    private function operatorDashboard($data)
    {
        $data['pending_requests'] = DrugRequest::where('status', 'pending')->count();
        $data['low_stock_drugs'] = Drug::whereColumn('stock', '<=', 'min_stock')->count();
        $data['total_drugs'] = Drug::count();
        // Menampilkan view operator
        return view('operator.requests', $data);
    }

    private function customerDashboard($data)
    {
        $userId = Auth::id();
        $data['my_requests_count'] = DrugRequest::where('user_id', $userId)->count();
        $data['active_deliveries'] = Delivery::whereHas('request', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('status', '!=', 'delivered')->count();

        return view('customer.requests', $data);
    }

    private function courierDashboard($data)
    {
        $data['assigned_tasks'] = Delivery::where('courier_id', Auth::id())
                                    ->where('status', '!=', 'delivered')
                                    ->count();

        return view('courier.tasks', $data);
    }
}