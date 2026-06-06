<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 30px; border-radius: 10px;">
        <h2 style="color: #3fbbc0; text-align: center;">E-PHARMA SYSTEM</h2>
        <hr>
        <p>Halo, <strong>{{ $userName }}</strong></p>

        @if($status === 'approved')
            <p>Kami dengan senang hati memberitahukan bahwa pendaftaran akun Anda sebagai mitra faskes E-Pharma telah <strong style="color: #10b981;">disetujui</strong>.</p>

            <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <strong style="color: #10b981;">✓ Akun Aktif</strong><br>
                <span style="font-size: 13px; color: #555;">Anda sekarang dapat login ke portal E-Pharma menggunakan email dan password yang telah Anda daftarkan.</span>
            </div>
        @else
            <p>Kami ingin memberitahukan bahwa pendaftaran akun Anda sebagai mitra faskes E-Pharma <strong style="color: #ef4444;">tidak dapat kami setujui</strong> saat ini.</p>

            <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <strong style="color: #ef4444;">✗ Pendaftaran Ditolak</strong><br>
                <span style="font-size: 13px; color: #555;">Jika Anda merasa ada kekeliruan, silakan hubungi administrator E-Pharma untuk informasi lebih lanjut.</span>
            </div>
        @endif

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <footer style="font-size: 11px; color: #aaa; text-align: center;">
            &copy; {{ date('Y') }} E-Pharma Logistics Hub.<br>
            Email ini dikirim otomatis oleh sistem, mohon tidak membalas.
        </footer>
    </div>
</body>
</html>
