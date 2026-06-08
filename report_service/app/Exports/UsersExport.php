<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class UsersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected Collection $data;

    /**
     * Terima Eloquent Collection dari UserSnapshot (bukan array dari service utama).
     * UserSnapshot tidak punya phone/address — diganti dengan regency/district/village.
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
            'ID',
            'Nama Mitra / Customer',
            'Email',
            'Kabupaten/Kota',
            'Kecamatan',
            'Kelurahan/Desa',
            'Status Akun',
            'Email Terverifikasi',
            'Tanggal Bergabung',
        ];
    }

    public function map($user): array
    {
        $u = is_array($user) ? (object) $user : $user;

        return [
            $u->id,
            $u->name,
            $u->email,
            $u->regency  ?? '-',
            $u->district ?? '-',
            $u->village  ?? '-',
            $u->status == 1 ? 'Aktif' : 'Pending',
            $u->email_verified_at
                ? \Carbon\Carbon::parse($u->email_verified_at)->format('d/m/Y')
                : 'Belum Verifikasi',
            \Carbon\Carbon::parse($u->created_at)->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
