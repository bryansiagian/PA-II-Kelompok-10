<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Reset Password - E-Pharma</title>
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root { --primary: #00838f; --secondary: #2c4964; }
    body {
      background: linear-gradient(rgba(255,255,255,0.9), rgba(255,255,255,0.9)),
                  url('https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1920&q=80');
      background-size: cover; background-position: center;
      min-height: 100vh; display: flex; align-items: center; justify-content: center;
      font-family: 'Poppins', sans-serif;
    }
    .login-card { background: #fff; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); width: 100%; max-width: 450px; overflow: hidden; }
    .card-header-accent { background: var(--primary); height: 6px; }
    .login-logo { font-size: 2.5rem; font-weight: 700; color: var(--secondary); text-decoration: none; display: block; }
    .login-logo span { color: var(--primary); }
    .form-control { border-radius: 10px; padding: 12px 15px; border: 1px solid #e1e1e1; }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 0.2rem rgba(0,131,143,0.1); }
    .btn-primary-custom { background: var(--primary); color: white; border-radius: 30px; padding: 12px; font-weight: 600; border: none; width: 100%; margin-top: 10px; transition: 0.3s; }
    .btn-primary-custom:hover { background: #006064; color: white; }
    .input-group-text { background: #f8f9fa; border-radius: 10px 0 0 10px; border: 1px solid #e1e1e1; border-right: none; color: var(--primary); }
    .form-control-with-icon { border-radius: 0 10px 10px 0; }
    .otp-info { background: #f0fdf4; border-left: 4px solid #10b981; padding: 10px 15px; border-radius: 5px; font-size: 13px; color: #555; }
  </style>
</head>
<body>
<div class="container p-3">
  <div class="login-card mx-auto">
    <div class="card-header-accent"></div>
    <div class="card-body p-4 p-md-5">
      <div class="text-center mb-4">
        <a href="/" class="login-logo">E-<span>Pharma</span></a>
        <p class="text-muted small">Portal Logistik Farmasi Terpadu</p>
      </div>

      <h5 class="fw-bold mb-2 text-dark text-center">Reset Password</h5>

      @if(session('forgot_email'))
      <div class="otp-info mb-4">
        <i class="bi bi-envelope-check me-1"></i>
        OTP dikirim ke <strong>{{ session('forgot_email') }}</strong>. Berlaku 10 menit.
      </div>
      @endif

      <div class="mb-3">
        <label class="form-label small fw-bold text-muted text-uppercase">Kode OTP</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
          <input type="text" id="inputOtp" class="form-control form-control-with-icon"
                 placeholder="6 digit kode OTP" maxlength="6" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-bold text-muted text-uppercase">Password Baru</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" id="inputPassword" class="form-control form-control-with-icon"
                 placeholder="Min. 6 karakter" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-bold text-muted text-uppercase">Konfirmasi Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
          <input type="password" id="inputConfirm" class="form-control form-control-with-icon"
                 placeholder="Ulangi password baru" required>
        </div>
      </div>

      <button id="btnReset" onclick="resetPassword()" class="btn btn-primary-custom shadow-sm">
        Reset Password <i class="bi bi-check-circle ms-1"></i>
      </button>

      <div class="text-center mt-3">
        <a href="/forgot-password" class="text-decoration-none small" style="color:var(--primary);">
          <i class="bi bi-arrow-clockwise"></i> Kirim ulang OTP
        </a>
      </div>

      <hr class="my-4 opacity-25">
      <div class="text-center">
        <a href="/login" class="text-decoration-none small" style="color:var(--primary);">
          <i class="bi bi-arrow-left"></i> Kembali ke Login
        </a>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
function resetPassword() {
    const btn      = document.getElementById('btnReset');
    const otp      = document.getElementById('inputOtp').value.trim();
    const password = document.getElementById('inputPassword').value;
    const confirm  = document.getElementById('inputConfirm').value;

    if (!otp)      return Swal.fire('Peringatan', 'Kode OTP wajib diisi.', 'warning');
    if (!password) return Swal.fire('Peringatan', 'Password baru wajib diisi.', 'warning');
    if (password.length < 6) return Swal.fire('Peringatan', 'Password minimal 6 karakter.', 'warning');
    if (password !== confirm) return Swal.fire('Peringatan', 'Konfirmasi password tidak cocok.', 'warning');

    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';

    axios.post('/reset-password', {
        otp_code:              otp,
        password:              password,
        password_confirmation: confirm,
    })
    .then(res => {
        Swal.fire({
            icon: 'success',
            title: 'Password Direset',
            text: res.data.message,
            confirmButtonColor: '#00838f',
            confirmButtonText: 'Login Sekarang'
        }).then(() => window.location.href = res.data.redirect);
    })
    .catch(err => {
        btn.disabled  = false;
        btn.innerHTML = 'Reset Password <i class="bi bi-check-circle ms-1"></i>';
        Swal.fire('Gagal', err.response?.data?.message ?? 'Terjadi kesalahan.', 'error');
    });
}
</script>
</body>
</html>
