<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Verifikasi OTP - E-Pharma</title>
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body { background: #f1f7f8; font-family: 'Poppins', sans-serif; display: flex; align-items: center; min-height: 100vh; }
    .card-otp { border: none; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    .btn-medinest { background: #3fbbc0; color: white; border-radius: 30px; padding: 12px; font-weight: 600; border: none; transition: 0.3s; width: 100%; }
    .btn-medinest:hover { background: #329ea2; }
    .otp-input { letter-spacing: 15px; font-size: 2rem; font-weight: bold; text-align: center; border-radius: 15px; border: 2px solid #deebec; }
  </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-otp p-4 p-md-5">
                <div class="text-center mb-4">
                    <h3 class="fw-bold" style="color: #2c4964;">Verifikasi Email</h3>
                    <p class="text-muted small">Kami telah mengirimkan kode OTP ke email Anda. Silakan masukkan kode tersebut di bawah ini.</p>
                </div>

                <form id="formOtp" onsubmit="submitOtp(event)">
                    <div class="mb-4">
                        <input type="text" name="otp" class="form-control otp-input" maxlength="6" placeholder="000000" required autofocus>
                    </div>
                    <button type="submit" id="btnVerify" class="btn btn-medinest shadow">
                        Verifikasi Akun
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="/login" class="text-muted small text-decoration-none">Kembali ke Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function submitOtp(event) {
        event.preventDefault();
        const btn = document.getElementById('btnVerify');
        const formData = new FormData(event.target);

        btn.disabled = true;
        btn.innerHTML = 'Memproses...';

        axios.post('/verify-otp', formData)
            .then(res => {
                Swal.fire({
                    icon: 'success',
                    title: 'Email Terverifikasi!',
                    text: res.data.message,
                    confirmButtonColor: '#3fbbc0',
                }).then(() => window.location.href = res.data.redirect);
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Verifikasi Akun';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: err.response.data.message || 'Kode OTP tidak valid.',
                    confirmButtonColor: '#3fbbc0',
                });
            });
    }
</script>
</body>
</html>
