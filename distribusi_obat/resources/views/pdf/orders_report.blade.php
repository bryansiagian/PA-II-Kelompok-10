<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 11px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #00838f; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; color: #00838f; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #00838f; color: white; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .footer { text-align: right; margin-top: 30px; font-size: 9px; color: #777; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">E-PHARMA LOGISTICS HUB</div>
        <div>Laporan Rekapitulasi Distribusi Obat & Vaksin</div>
        <small>Periode Cetak: {{ date('d F Y H:i') }}</small>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Order</th>
                <th>Unit Kesehatan</th>
                <th>Status</th>
                <th>Metode</th>
                <th style="text-align: right;">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($orders as $o)
            <tr>
                <td>#{{ substr($o['id'], 0, 8) }}</td>
                <td>{{ $o['user']['name'] ?? 'N/A' }}</td>
                <td>{{ $o['status']['name'] ?? 'Pending' }}</td>
                <td>{{ $o['type']['name'] ?? 'N/A' }}</td>
                <td style="text-align: right;">{{ number_format($o['total'], 0, ',', '.') }}</td>
            </tr>
            @php $grandTotal += $o['total']; @endphp
            @endforeach
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">TOTAL DISTRIBUSI</td>
                <td style="text-align: right;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh Sistem Manajemen E-Pharma.
    </div>
</body>
</html>
