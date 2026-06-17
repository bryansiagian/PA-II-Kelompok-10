<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Lupa Password - E-Pharma</title>
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
  </style>
</head>
<body>
<div class="container p-3">
  <div class="login-card mx-auto">
    <div class="card-header-accent"></div>
    <div class="card-body p-4 p-md-5">
      <div class="text-center mb-4">
        <a href="/" class="login-logo">SI-<span>DOBAT</span></a>
        <p class="text-muted small">Portal Logistik Farmasi Terpadu</p>
      </div>

      <h5 class="fw-bold mb-2 text-dark text-center">Lupa Password</h5>
      <p class="text-muted small text-center mb-4">Masukkan email terdaftar Anda. Kami akan mengirim kode OTP untuk mereset password.</p>

      <div class="mb-3">
        <label class="form-label small fw-bold text-muted text-uppercase">Alamat Email</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" id="inputEmail" class="form-control form-control-with-icon"
                 placeholder="email@domain.com" required autofocus>
        </div>
      </div>

      <button id="btnSend" onclick="sendOtp()" class="btn btn-primary-custom shadow-sm">
        Kirim OTP <i class="bi bi-send ms-1"></i>
      </button>

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
function sendOtp() {
    const btn   = document.getElementById('btnSend');
    const email = document.getElementById('inputEmail').value.trim();

    if (!email) return Swal.fire('Peringatan', 'Email wajib diisi.', 'warning');

    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Mengirim...';

    axios.post('/forgot-password', { email })
        .then(res => {
            Swal.fire({
                icon: 'success',
                title: 'OTP Terkirim',
                text: res.data.message,
                confirmButtonColor: '#00838f',
                confirmButtonText: 'Lanjut'
            }).then(() => window.location.href = res.data.redirect);
        })
        .catch(err => {
            btn.disabled  = false;
            btn.innerHTML = 'Kirim OTP <i class="bi bi-send ms-1"></i>';
            Swal.fire('Gagal', err.response?.data?.message ?? 'Terjadi kesalahan.', 'error');
        });
}
</script>
</body>
</html>
