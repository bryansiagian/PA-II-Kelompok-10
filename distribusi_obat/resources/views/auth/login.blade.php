<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Login - Yayasan E-Pharma</title>

  <!-- Favicons -->
  <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
  <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

  <!-- Core Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
      --primary: #00838f; /* Hijau Toska Tua sesuai Welcome */
      --secondary: #2c4964;
      --hover-color: #006064;
    }

    body {
      background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)),
                  url('https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1920&q=80');
      background-size: cover;
      background-position: center;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Poppins', sans-serif;
    }

    .login-card {
      background: #ffffff;
      border: none;
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      width: 100%;
      max-width: 450px;
    }

    .card-header-accent {
      background: var(--primary);
      height: 6px;
      width: 100%;
    }

    .login-logo {
      font-family: 'Ubuntu', sans-serif;
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--secondary);
      text-decoration: none;
      display: block;
      margin-bottom: 5px;
    }

    .login-logo span {
      color: var(--primary);
    }

    .form-control {
      border-radius: 10px;
      padding: 12px 15px;
      border: 1px solid #e1e1e1;
      font-size: 0.9rem;
      transition: all 0.3s;
    }

    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.2rem rgba(0, 131, 143, 0.1);
    }

    .btn-login {
      background: var(--primary);
      color: white;
      border-radius: 30px;
      padding: 12px;
      font-weight: 600;
      border: none;
      transition: 0.3s;
      width: 100%;
      margin-top: 10px;
    }

    .btn-login:hover {
      background: var(--hover-color);
      box-shadow: 0 5px 15px rgba(0, 131, 143, 0.3);
      color: white;
    }

    .back-to-site {
      color: var(--primary);
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 500;
      transition: 0.3s;
    }

    .back-to-site:hover {
      color: var(--secondary);
    }

    .input-group-text {
      background: #f8f9fa;
      border-radius: 10px 0 0 10px;
      border: 1px solid #e1e1e1;
      border-right: none;
      color: var(--primary);
    }

    .form-control-with-icon {
      border-radius: 0 10px 10px 0;
    }

    .text-primary-custom {
      color: var(--primary) !important;
      font-weight: 600;
    }
  </style>
</head>

<body>

  <div class="container p-3">
    <div class="login-card mx-auto">
      <div class="card-header-accent"></div>
      <div class="card-body p-4 p-md-5">

        <!-- Logo Section -->
        <div class="text-center mb-4">
          <a href="/" class="login-logo">E-<span>Pharma</span></a>
          <p class="text-muted small">Portal Logistik Farmasi Terpadu</p>
        </div>

        <h5 class="fw-bold mb-4 text-dark text-center">Silakan Masuk</h5>

        <form id="formLogin" onsubmit="submitLogin(event)">
          @csrf

          <!-- Email Field -->
          <div class="mb-3">
            <label class="form-label small fw-bold text-muted text-uppercase">Alamat Email</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email" class="form-control form-control-with-icon"
                     placeholder="nama@unitkesehatan.id" required autofocus>
            </div>
          </div>

          <!-- Password Field -->
          <div class="mb-4">
            <label class="form-label small fw-bold text-muted text-uppercase">Kata Sandi</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input type="password" name="password" class="form-control form-control-with-icon"
                     placeholder="••••••••" required>
            </div>
          </div>

          <!-- Login Button -->
          <button type="submit" id="btnLogin" class="btn btn-login shadow-sm mb-3">
            Masuk ke Sistem <i class="bi bi-box-arrow-in-right ms-1"></i>
          </button>

          <div class="text-center mt-3">
            <p class="small text-muted mb-2">Belum memiliki akun unit kesehatan?</p>
            <a href="/register" class="back-to-site fw-bold">Daftar Akun Baru</a>
          </div>

          <hr class="my-4 opacity-25">

          <div class="text-center">
            <a href="/" class="back-to-site">
              <i class="bi bi-arrow-left"></i> Kembali ke Beranda
            </a>
          </div>

        </form>
      </div>
    </div>
  </div>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

  <script>
    function submitLogin(event) {
        event.preventDefault();
        const btn = document.getElementById('btnLogin');
        const formData = new FormData(event.target);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memverifikasi...';

        axios.post('/login', formData)
            .then(res => {
                // Login Sukses
                window.location.href = res.data.redirect;
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Masuk ke Sistem <i class="bi bi-box-arrow-in-right ms-1"></i>';

                if (!err.response) {
                    Swal.fire({ icon: 'error', title: 'Koneksi Gagal', text: 'Pastikan server Anda berjalan.' });
                    return;
                }

                const response = err.response.data;
                const status = err.response.status;

                if (status === 403) { // Email Belum Verifikasi OTP
                    Swal.fire({
                        icon: 'warning',
                        title: 'Verifikasi Email',
                        text: response.message,
                        confirmButtonColor: '#00838f',
                        confirmButtonText: 'Lanjut Verifikasi'
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                } else if (status === 401) { // Menunggu Admin
                    Swal.fire({
                        icon: 'info',
                        title: 'Akun Belum Aktif',
                        text: response.message,
                        confirmButtonColor: '#2c4964'
                    });
                } else { // Salah Password atau Email (422/404)
                    Swal.fire({
                        icon: 'error',
                        title: 'Akses Ditolak',
                        text: response.message || 'Email atau password salah.',
                        confirmButtonColor: '#00838f'
                    });
                }
            });
    }
  </script>

</body>

</html>
