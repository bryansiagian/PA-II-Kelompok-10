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

        // Tentukan rentang tanggal
        [$start, $end] = $this->resolveDateRange($period, $startDate, $endDate);

        // --- Stats harian/mingguan/bulanan ---
        $stats = $this->buildStats($period, $start, $end);

        // --- Top produk berdasarkan total qty yang dipesan ---
        $topDrugs = DB::table('order_items_snapshot')
            ->join('orders_snapshot', 'order_items_snapshot.order_id', '=', 'orders_snapshot.id')
            ->whereBetween('orders_snapshot.created_at', [$start, $end])
            ->select('order_items_snapshot.product_name as name', DB::raw('SUM(order_items_snapshot.quantity) as total_qty'))
            ->groupBy('order_items_snapshot.product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // --- Rasio pengiriman ---
        $shipped    = OrderSnapshot::whereBetween('created_at', [$start, $end])
            ->whereIn('status_name', ['Shipping', 'Completed'])
            ->count();
        $notShipped = OrderSnapshot::whereBetween('created_at', [$start, $end])
            ->whereNotIn('status_name', ['Shipping', 'Completed'])
            ->count();

        // --- Summary ---
        $totalUsers    = UserSnapshot::where('active', 1)->count();
        $totalProducts = ProductSnapshot::where('active', 1)->count();
        $totalOrders   = OrderSnapshot::whereBetween('created_at', [$start, $end])->count();
        $lowStock      = ProductSnapshot::where('active', 1)
            ->whereRaw('stock <= min_stock')
            ->count();

        $totalItemsDistributed = DB::table('order_items_snapshot')
            ->join('orders_snapshot', 'order_items_snapshot.order_id', '=', 'orders_snapshot.id')
            ->whereBetween('orders_snapshot.created_at', [$start, $end])
            ->whereIn('orders_snapshot.status_name', ['Shipping', 'Completed'])
            ->sum('order_items_snapshot.quantity');

        return response()->json([
            'stats'          => $stats,
            'top_drugs'      => $topDrugs,
            'delivery_ratio' => [
                'shipped'     => $shipped,
                'not_shipped' => $notShipped,
            ],
            'summary' => [
                'total_users'              => $totalUsers,
                'total_products'           => $totalProducts,
                'total_orders'             => $totalOrders,
                'not_shipped'              => $notShipped,
                'total_items_distributed'  => (int) $totalItemsDistributed,
                'low_stock_products'       => $lowStock,
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

        $orders = OrderSnapshot::with('items')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($order) {
                return [
                    'id'             => $order->id,
                    'user_id'        => $order->user_id,
                    'status'         => $order->status_name,
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

        return response()->json($orders);
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

        $orders = OrderSnapshot::with('items')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->get();

        return Excel::download(new \App\Exports\OrdersExport($orders), 'laporan-pesanan.xlsx');
    }

    // -----------------------------------------------------------------------
    // EXPORT PDF
    // -----------------------------------------------------------------------

    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', Carbon::now()->toDateString());

        $orders = OrderSnapshot::with('items')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->get();

        $pdf = Pdf::loadView('reports.orders', [
            'orders'    => $orders,
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

    private function buildStats(string $period, Carbon $start, Carbon $end): array
    {
        $format = match ($period) {
            'monthly' => '%Y-%m',
            'weekly'  => '%Y-%u',
            default   => '%Y-%m-%d',
        };

        $labelFormat = match ($period) {
            'monthly' => 'M Y',
            'weekly'  => 'W\\eek W',
            default   => 'd M',
        };

        $raw = DB::table('orders_snapshot')
            ->selectRaw("DATE_FORMAT(created_at, '$format') as period_key, COUNT(*) as total_requests")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->pluck('total_requests', 'period_key');

        // Generate semua label dalam rentang (isi 0 untuk yang kosong)
        $stats = [];
        $cursor = $start->copy();
        while ($cursor <= $end) {
            $key = $cursor->format(match ($period) {
                'monthly' => 'Y-m',
                'weekly'  => 'Y-W',
                default   => 'Y-m-d',
            });

            $stats[] = [
                'label'          => $cursor->format($labelFormat),
                'total_requests' => $raw[$key] ?? 0,
            ];

            match ($period) {
                'monthly' => $cursor->addMonth(),
                'weekly'  => $cursor->addWeek(),
                default   => $cursor->addDay(),
            };
        }

        return $stats;
    }
}
