<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 10px; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #00838f; padding-bottom: 10px; }
        .title { font-size: 17px; font-weight: bold; color: #00838f; margin-bottom: 4px; }
        .meta { font-size: 10px; color: #555; margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #00838f; color: white; padding: 8px 6px; text-align: left; font-size: 9px; text-transform: uppercase; }
        td { padding: 7px 6px; border-bottom: 1px solid #eee; vertical-align: top; }
        tr:nth-child(even) td { background: #f9fafb; }
        .total-row td { font-weight: bold; background-color: #e8f5e9; border-top: 2px solid #00838f; }
        .footer { text-align: right; margin-top: 24px; font-size: 9px; color: #999; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; }
        .badge-paid     { background: #dcfce7; color: #166534; }
        .badge-cash     { background: #dbeafe; color: #1e40af; }
        .badge-unpaid   { background: #f1f5f9; color: #475569; }
        .badge-refunded { background: #fee2e2; color: #991b1b; }
        .summary { margin-bottom: 16px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 4px; }
        .summary table { margin-top: 0; }
        .summary td { border: none; padding: 3px 8px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">SI-DOBAT</div>
        <div class="meta">Laporan Rekapitulasi Distribusi Obat & Vaksin</div>
        <div class="meta">
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
            s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
        </div>
        <small style="color:#999;">Dicetak pada: {{ date('d F Y H:i') }} WIB</small>
    </div>

    @php
        $grandTotal  = array_sum(array_column($orders, 'total'));
        $totalItems  = array_sum(array_map(fn($o) => array_sum(array_column($o['items'], 'quantity')), $orders));
        $totalOrders = count($orders);
    @endphp

    <div class="summary">
        <table>
            <tr>
                <td>Total Pesanan</td>
                <td><strong>{{ $totalOrders }}</strong></td>
                <td width="40"></td>
                <td>Total Item</td>
                <td><strong>{{ number_format($totalItems) }} unit</strong></td>
                <td width="40"></td>
                <td>Total Nilai</td>
                <td><strong>Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:12%">ID Order</th>
                <th style="width:18%">Mitra / Customer</th>
                <th style="width:10%">No. Telepon</th>
                <th style="width:10%">Wilayah</th>
                <th style="width:22%">Produk</th>
                <th style="width:9%">Status</th>
                <th style="width:9%">Pembayaran</th>
                <th style="width:8%" class="text-right">Total (Rp)</th>
                <th style="width:8%">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($orders as $i => $o)
            @php
                $payStatus = $o['payment_status'] ?? 'unpaid';
                $payLabel  = match($payStatus) {
                    'paid'     => ['cls' => 'badge-paid',     'label' => 'Lunas'],
                    'cash'     => ['cls' => 'badge-cash',     'label' => 'Tunai'],
                    'refunded' => ['cls' => 'badge-refunded', 'label' => 'Refund'],
                    default    => ['cls' => 'badge-unpaid',   'label' => 'Belum Bayar'],
                };
                $grandTotal += $o['total'];
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td style="font-size:8px;color:#5c6bc0;font-weight:bold;">#{{ substr($o['id'], 0, 8) }}</td>
                <td><strong>{{ $o['user']['name'] ?? 'N/A' }}</strong></td>
                <td style="font-size:9px;">{{ $o['phone_order'] ?? '-' }}</td>
                <td style="font-size:9px;">{{ $o['regency'] ?? '-' }}</td>
                <td style="font-size:9px;">
                    @foreach(array_slice($o['items'], 0, 3) as $item)
                        {{ $item['product_name'] }} ({{ $item['quantity'] }})<br>
                    @endforeach
                    @if(count($o['items']) > 3)
                        <span style="color:#94a3b8;">+{{ count($o['items']) - 3 }} lainnya</span>
                    @endif
                </td>
                <td style="font-size:9px;">{{ $o['status']['name'] ?? 'Pending' }}</td>
                <td>
                    <span class="badge {{ $payLabel['cls'] }}">{{ $payLabel['label'] }}</span>
                </td>
                <td class="text-right">{{ number_format($o['total'], 0, ',', '.') }}</td>
                <td style="font-size:9px;">{{ \Carbon\Carbon::parse($o['created_at'])->format('d/m/Y') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="8" class="text-right">TOTAL DISTRIBUSI</td>
                <td class="text-right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh SI-DOBAT &mdash; Yayasan Satriabudi Dharma Setia.
    </div>
</body>
</html>
