<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Pengguna E-Pharma</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 11px; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #00838f; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; color: #00838f; margin-bottom: 5px; }
        .period-text { font-size: 12px; color: #555; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        th { background-color: #00838f; color: white; padding: 10px; text-align: left; text-transform: uppercase; }
        td { padding: 8px; border-bottom: 1px solid #eee; word-wrap: break-word; }
        .footer { text-align: right; margin-top: 30px; font-size: 9px; color: #777; }
        .no-column { width: 30px; }
        .date-column { width: 80px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">E-PHARMA LOGISTICS HUB</div>
        <div class="period-text">Laporan Data Pengguna (Mitra / Customer)</div>
        <small>
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
            s/d
            {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
        </small>
        <br>
        <small style="color: #999;">Dicetak pada: {{ date('d F Y H:i') }}</small>
    </div>

    <table>
        <thead>
            <tr>
                <th class="no-column">No</th>
                <th>Nama Mitra</th>
                <th>Email</th>
                <th>Telepon</th>
                <th>Alamat Pengiriman</th>
                <th class="date-column">Tgl Gabung</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $key => $user)
            <tr>
                <td class="no-column">{{ $key + 1 }}</td>
                <td style="font-weight: bold;">{{ $user['name'] }}</td>
                <td>{{ $user['email'] }}</td>
                <td>{{ $user['phone'] ?? '-' }}</td>
                <td>{{ $user['address'] ?? 'Alamat belum diatur' }}</td>
                <td class="date-column">{{ \Carbon\Carbon::parse($user['created_at'])->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">Tidak ada data pengguna dalam rentang tanggal ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <p><strong>Ringkasan:</strong> Total terdapat {{ count($data) }} mitra yang terdaftar dalam laporan ini.</p>
    </div>

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh Sistem Manajemen E-Pharma.<br>
        Yayasan Satriabudi Dharma Setia.
    </div>
</body>
</html>
