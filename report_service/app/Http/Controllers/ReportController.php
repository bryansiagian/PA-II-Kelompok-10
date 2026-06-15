<?php

namespace App\Http\Controllers;

use App\Models\OrderSnapshot;
use App\Models\UserSnapshot;
use App\Models\ProductSnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    // -----------------------------------------------------------------------
    // ANALYTICS — dulu balik ke service utama, sekarang query db_report
    // -----------------------------------------------------------------------

    public function analytics(Request $request)
    {
        $period    = $request->query('period', 'daily');
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $statusId  = $request->query('status_id'); // ← tambahkan ini

        [$start, $end] = $this->resolveDateRange($period, $startDate, $endDate);

        // --- Stats grafik ---
        $stats = $this->buildStats($period, $start, $end, $statusId); // teruskan status

        // --- Top produk ---
        $topDrugsQuery = DB::table('order_items_snapshot')
            ->join('orders_snapshot', 'order_items_snapshot.order_id', '=', 'orders_snapshot.id')
            ->whereBetween('orders_snapshot.created_at', [$start, $end]);

        // Filter status jika bukan "all"
        if ($statusId && $statusId !== 'all') {
            $topDrugsQuery->where('orders_snapshot.status_name', $statusId);
        }

        $topDrugs = $topDrugsQuery
            ->select('order_items_snapshot.product_name as name', DB::raw('SUM(order_items_snapshot.quantity) as total_qty'))
            ->groupBy('order_items_snapshot.product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // --- Rasio pengiriman ---
        $shipped    = OrderSnapshot::whereBetween('created_at', [$start, $end])
            ->whereIn('status_name', ['Shipping', 'Completed'])->count();
        $notShipped = OrderSnapshot::whereBetween('created_at', [$start, $end])
            ->whereNotIn('status_name', ['Shipping', 'Completed'])->count();

        // --- Summary dengan filter status ---
        $totalOrdersQuery = OrderSnapshot::whereBetween('created_at', [$start, $end]);
        if ($statusId && $statusId !== 'all') {
            $totalOrdersQuery->where('status_name', $statusId);
        }
        $totalOrders = $totalOrdersQuery->count();

        // Total items — filter status juga
        $totalItemsQuery = DB::table('order_items_snapshot')
            ->join('orders_snapshot', 'order_items_snapshot.order_id', '=', 'orders_snapshot.id')
            ->whereBetween('orders_snapshot.created_at', [$start, $end]);

        if ($statusId && $statusId !== 'all') {
            // Filter status spesifik
            $totalItemsQuery->where('orders_snapshot.status_name', $statusId);
        }
        // Kalau "all", tetap hitung semua tanpa filter status (hapus hardcode Shipping/Completed)

        $totalItemsDistributed = $totalItemsQuery->sum('order_items_snapshot.quantity');

        $totalUsers    = UserSnapshot::where('active', 1)->count();
        $totalProducts = ProductSnapshot::where('active', 1)->count();
        $lowStock      = ProductSnapshot::where('active', 1)->whereRaw('stock <= min_stock')->count();

        return response()->json([
            'stats'          => $stats,
            'top_drugs'      => $topDrugs,
            'delivery_ratio' => ['shipped' => $shipped, 'not_shipped' => $notShipped],
            'summary' => [
                'total_users'             => $totalUsers,
                'total_products'          => $totalProducts,
                'total_orders'            => $totalOrders,
                'not_shipped'             => $notShipped,
                'total_items_distributed' => (int) $totalItemsDistributed,
                'low_stock_products'      => $lowStock,
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // REPORTS — data orders & users untuk export
    // -----------------------------------------------------------------------

    public function orders(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', Carbon::now()->toDateString());
        $statusId  = $request->query('status_id');

        $query = OrderSnapshot::with('items')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderByDesc('created_at');

        if ($statusId && $statusId !== 'all') {
            $query->where('status_name', $statusId); // filter by status_name karena tidak ada status_id di snapshot
        }

        $orders = $query->get();

        // Load semua user sekaligus (hindari N+1)
        $userIds = $orders->pluck('user_id')->unique();
        $users   = UserSnapshot::whereIn('id', $userIds)->get()->keyBy('id');

        $mapped = $orders->map(function ($order) use ($users) {
            $user = $users->get($order->user_id);

            return [
                'id'             => $order->id,
                'user'           => [
                    'name' => $user?->name ?? 'N/A',
                ],
                'status'         => [
                    'name' => $order->status_name ?? 'PENDING',
                ],
                'payment_status' => $order->payment_status,
                'total'          => $order->total,
                'regency'        => $order->regency,
                'paid_at'        => $order->paid_at,
                'created_at'     => $order->created_at,
                'items'          => $order->items->map(fn($i) => [
                    'product_name'   => $i->product_name,
                    'quantity'       => $i->quantity,
                    'price_at_order' => $i->price_at_order,
                ]),
            ];
        });

        return response()->json($mapped);
    }

    public function users(Request $request)
    {
        $users = UserSnapshot::where('active', 1)
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'email', 'status', 'regency', 'district', 'village', 'created_at']);

        return response()->json($users);
    }

    public function products(Request $request)
    {
        $products = ProductSnapshot::where('active', 1)
            ->orderBy('name')
            ->get(['id', 'product_code', 'name', 'category_name', 'price', 'unit', 'stock', 'min_stock']);

        return response()->json($products);
    }

    // -----------------------------------------------------------------------
    // EXPORT EXCEL
    // -----------------------------------------------------------------------

    public function exportExcel(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', Carbon::now()->toDateString());
        $statusId  = $request->query('status_id');
        $type      = $request->query('type', 'orders');

        if ($type === 'users') {
            $users = UserSnapshot::where('active', 1)
                ->orderByDesc('created_at')
                ->get();

            return Excel::download(new \App\Exports\UsersExport($users), 'laporan-pengguna.xlsx');
        }

        // Default: orders
        $query = OrderSnapshot::with('items')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderByDesc('created_at');

        if ($statusId && $statusId !== 'all') {
            $query->where('status_name', $statusId);
        }

        $orders = $query->get();

        // Attach user_name
        $userIds = $orders->pluck('user_id')->unique();
        $users   = UserSnapshot::whereIn('id', $userIds)->get()->keyBy('id');
        $orders->each(function ($order) use ($users) {
            $order->user_name = $users->get($order->user_id)?->name ?? 'N/A';
        });

        return Excel::download(new \App\Exports\OrdersExport($orders), 'laporan-pesanan.xlsx');
    }

    // -----------------------------------------------------------------------
    // EXPORT PDF
    // -----------------------------------------------------------------------

    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', Carbon::now()->toDateString());
        $statusId  = $request->query('status_id');
        $type      = $request->query('type', 'orders');

        if ($type === 'users') {
            $users = UserSnapshot::where('active', 1)
                ->orderByDesc('created_at')
                ->get();

            // Mapping ke array sesuai yang diharapkan blade users_report ($data, $user['name'], dll)
            $data = $users->map(fn($u) => [
                'name'       => $u->name,
                'email'      => $u->email,
                'phone'      => $u->phone    ?? '-',
                'address'    => collect([$u->village, $u->district, $u->regency])
                                    ->filter()->implode(', ') ?: 'Alamat belum diatur',
                'created_at' => $u->created_at,
            ])->toArray();

            $pdf = Pdf::loadView('pdf.users_report', [
                'data'      => $data,
                'startDate' => $startDate,
                'endDate'   => $endDate,
            ]);

            return $pdf->download('laporan-pengguna.pdf');
        }

        // Orders
        $query = OrderSnapshot::with('items')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderByDesc('created_at');

        if ($statusId && $statusId !== 'all') {
            $query->where('status_name', $statusId);
        }

        $orders = $query->get();

        // Attach user_name dari users_snapshot
        $userIds = $orders->pluck('user_id')->unique();
        $users   = UserSnapshot::whereIn('id', $userIds)->get()->keyBy('id');

        // Mapping ke array sesuai struktur yang diharapkan blade orders_report
        $mapped = $orders->map(function ($order) use ($users) {
            $user = $users->get($order->user_id);
            return [
                'id'             => $order->id,
                'user'           => ['name' => $user?->name ?? 'N/A'],
                'status'         => ['name' => $order->status_name ?? 'Pending'],
                'phone_order'    => $order->phone_order ?? '-',
                'payment_method' => $order->payment_method ?? '-',
                'payment_status' => $order->payment_status ?? 'unpaid',
                'total'          => $order->total,
                'regency'        => $order->regency ?? '-',
                'created_at'     => $order->created_at,
                'items'          => $order->items->map(fn($i) => [
                    'product_name'   => $i->product_name,
                    'quantity'       => $i->quantity,
                    'price_at_order' => $i->price_at_order,
                ])->toArray(),
            ];
        })->toArray();

        $pdf = Pdf::loadView('pdf.orders_report', [
            'orders'    => $mapped,
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ]);

        return $pdf->download('laporan-pesanan.pdf');
    }

    // -----------------------------------------------------------------------
    // HELPER
    // -----------------------------------------------------------------------

    private function resolveDateRange(string $period, ?string $startDate, ?string $endDate): array
    {
        if ($startDate && $endDate) {
            return [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ];
        }

        return match ($period) {
            'weekly'  => [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()],
            'monthly' => [Carbon::now()->subDays(29)->startOfDay(), Carbon::now()->endOfDay()],
            default   => [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()], // daily = 7 hari
        };
    }

    private function buildStats(string $period, Carbon $start, Carbon $end, ?string $statusId = null): array
    {
        $format = match ($period) {
            'monthly' => '%Y-%m',
            'weekly'  => '%Y-%u',
            default   => '%Y-%m-%d',
        };

        $labelFormat = match ($period) {
            'monthly' => 'M Y',
            'weekly'  => 'Week W',
            default   => 'd M',
        };

        $query = DB::table('orders_snapshot')
            ->selectRaw("DATE_FORMAT(created_at, '$format') as period_key, COUNT(*) as total_requests")
            ->whereBetween('created_at', [$start, $end]);

        // ← tambahkan filter status di sini
        if ($statusId && $statusId !== 'all') {
            $query->where('status_name', $statusId);
        }

        $raw = $query->groupBy('period_key')->orderBy('period_key')->pluck('total_requests', 'period_key');

        $stats  = [];
        $cursor = $start->copy();
        while ($cursor <= $end) {
            $key = $cursor->format(match ($period) {
                'monthly' => 'Y-m',
                'weekly'  => 'Y-W',
                default   => 'Y-m-d',
            });
            $stats[] = ['label' => $cursor->format($labelFormat), 'total_requests' => $raw[$key] ?? 0];
            match ($period) {
                'monthly' => $cursor->addMonth(),
                'weekly'  => $cursor->addWeek(),
                default   => $cursor->addDay(),
            };
        }

        return $stats;
    }
}
