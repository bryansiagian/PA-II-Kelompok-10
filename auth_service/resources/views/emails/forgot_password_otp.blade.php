<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #333; text-align: center;">
    <div style="max-width: 500px; margin: auto; border: 1px solid #3fbbc0; padding: 30px; border-radius: 15px;">
        <h2 style="color: #2c4964;">Reset Password E-Pharma</h2>
        <p>Halo, <strong>{{ $userName }}</strong></p>
        <p>Kami menerima permintaan reset password untuk akun Anda. Gunakan kode OTP berikut:</p>

        <div style="background: #f1f7f8; padding: 20px; border-radius: 10px; margin: 20px 0;">
            <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #3fbbc0;">{{ $otpCode }}</span>
        </div>

        <p style="font-size: 13px; color: #777;">Kode ini berlaku selama 10 menit. Jika Anda tidak merasa meminta reset password, abaikan email ini.</p>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 11px; color: #aaa;">&copy; {{ date('Y') }} Yayasan Satriabudi Dharma Setia</p>
    </div>
</body>
</html>
