<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection(): Collection
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Nama Unit/Faskes',
            'Jenis Pengiriman',
            'Status',
            'Total Nilai (Rp)',
            'Catatan',
            'Tanggal Pengajuan',
        ];
    }

    public function map($order): array
    {
        $order = (object) $order;
        return [
            '#ORDER-' . strtoupper(substr($order->id, 0, 8)),
            $order->user['name'] ?? 'N/A',
            $order->type['name'] ?? 'N/A',
            $order->status['name'] ?? 'N/A',
            number_format($order->total, 0, ',', '.'),
            $order->notes ?? '-',
            \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i'),
        ];
    }
}
