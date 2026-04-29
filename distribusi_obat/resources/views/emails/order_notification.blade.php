<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px; border-radius: 10px;">
        <h2 style="color: #3fbbc0; text-align: center;">E-PHARMA SYSTEM</h2>
        <hr>
        <p>Halo, <strong>{{ $productOrder->user->name }}</strong></p>
        <p>Pemberitahuan resmi mengenai pesanan Anda dengan nomor ID: <strong>#{{ $productOrder->id }}</strong></p>

        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center;">
            Status Pesanan Saat Ini: <br>
            <strong style="font-size: 1.2rem; color: #2c4964;">{{ strtoupper($statusLabel) }}</strong>
        </div>

        <h4 style="margin-top: 25px;">Rincian Produk:</h4>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #eee;">
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Nama Produk</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productOrder->items as $item)
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        {{ $item->product ? $item->product->name : 'Produk Tidak Diketahui' }}
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                        {{ $item->quantity }} {{ $item->product ? $item->product->unit : 'Unit' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p style="margin-top: 25px;">Silakan pantau pengiriman Anda melalui portal dashboard faskes Anda.</p>
        <hr>
        <footer style="font-size: 11px; color: #777; text-align: center;">
            &copy; {{ date('Y') }} E-Pharma Logistics Hub. <br>
            Email ini dikirim otomatis oleh sistem, mohon tidak membalas.
        </footer>
    </div>
</body>
</html>
