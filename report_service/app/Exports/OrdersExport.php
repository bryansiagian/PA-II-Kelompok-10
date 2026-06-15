<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected Collection $data;

    /**
     * Terima Eloquent Collection dari OrderSnapshot (bukan array dari service utama).
     */
    public function __construct($data)
    {
        $this->data = $data instanceof Collection ? $data : collect($data);
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Nama Customer',
            'No. Telepon',
            'Wilayah (Kab/Kota)',
            'Kecamatan',
            'Kelurahan/Desa',
            'Status Pesanan',
            'Status Pembayaran',
            'Metode Pembayaran',
            'Total Item',
            'Total Nilai (Rp)',
            'Tanggal Bayar',
            'Tanggal Pengajuan',
        ];
    }

    public function map($order): array
    {
        // $order bisa berupa Eloquent model OrderSnapshot atau plain object/array
        $o = is_array($order) ? (object) $order : $order;

        // Hitung total item dari relasi atau dari array
        $totalItems = 0;
        if (isset($o->items)) {
            $totalItems = is_countable($o->items) ? count($o->items) : 0;
        }

        return [
            '#ORDER-' . strtoupper(substr($o->id, 0, 8)),
            $o->user_name ?? 'N/A',
            $o->phone_order  ?? '-',
            $o->regency   ?? '-',
            $o->district  ?? '-',
            $o->village   ?? '-',
            $o->status_name     ?? '-',
            $this->formatPaymentStatus($o->payment_status ?? 'unpaid'),
            strtoupper($o->payment_method ?? '-'),
            $totalItems . ' jenis',
            'Rp ' . number_format($o->total ?? 0, 0, ',', '.'),
            $o->paid_at
                ? \Carbon\Carbon::parse($o->paid_at)->format('d/m/Y H:i')
                : '-',
            \Carbon\Carbon::parse($o->created_at)->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function formatPaymentStatus(string $status): string
    {
        return match ($status) {
            'paid'     => 'Lunas',
            'unpaid'   => 'Belum Bayar',
            'refunded' => 'Refund',
            'cash'     => 'Tunai',
            default    => ucfirst($status),
        };
    }
}
