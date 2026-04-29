<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class UsersExport implements FromCollection, WithHeadings, WithMapping
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
            'ID',
            'Nama Mitra',
            'Email',
            'Telepon',
            'Alamat',
            'Tanggal Bergabung',
        ];
    }

    public function map($user): array
    {
        $user = (object) $user;
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone ?? '-',
            $user->address ?? '-',
            \Carbon\Carbon::parse($user->created_at)->format('d/m/Y'),
        ];
    }
}
