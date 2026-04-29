<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class UsersExport implements FromQuery, WithHeadings, WithMapping
{
    protected $start, $end;

    public function __construct($start, $end) {
        $this->start = $start;
        $this->end = $end;
    }

    public function query() {
        return User::role('customer')
            ->where('status', 1)
            ->whereBetween('created_at', [
                Carbon::parse($this->start)->startOfDay(),
                Carbon::parse($this->end)->endOfDay()
            ]);
    }

    public function headings(): array {
        return [
            'ID',
            'Nama Mitra',
            'Email',
            'Telepon', // Tambahkan ini
            'Alamat',
            'Tanggal Bergabung'
        ];
    }

    public function map($user): array {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone ?? '-',
            $user->address,
            $user->created_at->format('d/m/Y'),
        ];
    }
}
