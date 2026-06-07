<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 30px; border-radius: 10px;">
        <h2 style="color: #3fbbc0; text-align: center;">E-PHARMA SYSTEM</h2>
        <hr>
        <p>Halo, <strong>{{ $userName }}</strong></p>
        <p>Akun E-Pharma Anda telah <strong style="color: #3fbbc0;">dibuat</strong> oleh administrator. Gunakan kredensial berikut untuk masuk ke portal E-Pharma:</p>

        <div style="background: #f0f9ff; border-left: 4px solid #3fbbc0; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <table style="width:100%;">
                <tr>
                    <td style="color:#555; font-size:13px; padding-bottom:8px;">Password Sementara:</td>
                </tr>
                <tr>
                    <td>
                        <span style="font-size: 24px; font-weight: bold; letter-spacing: 3px; color: #3fbbc0; font-family: monospace;">
                            {{ $plainPassword }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <p style="font-size: 13px; color: #777;">
            Segera ganti password Anda setelah login pertama kali demi keamanan akun.
        </p>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <footer style="font-size: 11px; color: #aaa; text-align: center;">
            &copy; {{ date('Y') }} E-Pharma Logistics Hub.<br>
            Email ini dikirim otomatis oleh sistem, mohon tidak membalas.
        </footer>
    </div>
</body>
</html>
