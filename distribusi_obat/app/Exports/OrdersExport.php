<?php

namespace App\Exports;

use App\Models\ProductOrder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $statusId;

    /**
     * Constructor untuk menerima filter dari Controller
     */
    public function __construct($startDate = null, $endDate = null, $statusId = 'all')
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->statusId = $statusId;
    }

    /**
     * Menggunakan FromQuery agar filter diproses di level database (lebih cepat)
     */
    public function query()
    {
        $query = ProductOrder::query()->with(['user', 'status', 'type']);

        // Filter Berdasarkan Rentang Tanggal
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        }

        // Filter Berdasarkan Status (Jika bukan 'all')
        if ($this->statusId && $this->statusId !== 'all') {
            $query->where('product_order_status_id', $this->statusId);
        }

        return $query->latest();
    }

    /**
     * Header kolom di Excel
     */
    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Nama Unit/Faskes',
            'Jenis Pengiriman',
            'Status',
            'Total Nilai (Rp)',
            'Catatan',
            'Tanggal Pengajuan'
        ];
    }

    /**
     * Map data ke kolom yang sesuai
     */
    public function map($order): array
    {
        return [
            '#ORDER-' . strtoupper(substr($order->id, 0, 8)),
            $order->user->name ?? 'N/A',
            $order->type->name ?? 'N/A',
            $order->status->name ?? 'N/A',
            number_format($order->total, 0, ',', '.'), // Tanpa desimal agar rapi di Excel
            $order->notes ?? '-',
            $order->created_at->format('d/m/Y H:i')
        ];
    }
}
