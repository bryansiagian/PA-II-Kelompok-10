<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Registrasi Mitra - Yayasan E-Pharma</title>

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

  <style>
    :root {
      --primary: #00838f; /* Hijau Toska Tua sesuai Welcome & Login */
      --secondary: #2c4964;
      --hover-color: #006064;
    }

    body {
      background: linear-gradient(rgba(241, 247, 248, 0.9), rgba(241, 247, 248, 0.9)),
                  url('https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=1920&q=80');
      background-size: cover;
      background-attachment: fixed;
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      padding: 40px 0;
    }

    .card-register {
      border: none;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .register-header {
      background: var(--primary);
      color: white;
      padding: 40px 30px;
      text-align: center;
    }

    .register-logo {
      font-family: 'Ubuntu', sans-serif;
      font-size: 2rem;
      font-weight: 700;
      color: #fff;
      text-decoration: none;
      display: block;
      margin-bottom: 10px;
    }

    .register-logo span {
      color: var(--secondary);
    }

    .form-label {
      color: var(--secondary);
      font-weight: 600;
      font-size: 0.85rem;
      margin-bottom: 8px;
    }

    .form-control, .form-select {
      border-radius: 10px;
      padding: 12px 15px;
      border: 1px solid #deebec;
      background-color: #fcfcfc;
      font-size: 0.9rem;
      transition: 0.3s;
    }

    .form-control:focus, .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.25rem rgba(0, 131, 143, 0.1);
      background-color: #fff;
    }

    .btn-register {
      background: var(--primary);
      color: white;
      border-radius: 30px;
      padding: 15px;
      font-weight: 600;
      border: none;
      transition: 0.3s;
      width: 100%;
      margin-top: 20px;
    }

    .btn-register:hover {
      background: var(--hover-color);
      box-shadow: 0 8px 20px rgba(0, 131, 143, 0.3);
      color: white;
    }

    .login-link {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
    }

    .login-link:hover {
      text-decoration: underline;
      color: var(--hover-color);
    }

    .input-group-text {
      background: #fff;
      border-color: #deebec;
      color: var(--primary);
      border-radius: 10px 0 0 10px;
    }

    .form-with-icon {
      border-radius: 0 10px 10px 0;
    }
  </style>
</head>

<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-register border-0">
                <div class="register-header">
                    <a href="/" class="register-logo">E-<span>Pharma</span></a>
                    <h4 class="fw-bold mb-1">Registrasi Mitra Faskes</h4>
                    <p class="mb-0 opacity-75 small">Khusus untuk Unit Kesehatan, Rumah Sakit, dan Klinik Mitra</p>
                </div>
                <div class="card-body p-4 p-md-5">

                    <form id="formRegister" onsubmit="submitRegister(event)">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-uppercase">Nama Unit / Petugas</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" class="form-control form-with-icon" placeholder="Contoh: Klinik Sehat / Budi Santoso" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-uppercase">Alamat Email Resmi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control form-with-icon" placeholder="kontak@unitkesehatan.id" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-uppercase">Nomor Telepon / WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" name="phone" class="form-control form-with-icon" placeholder="08xxxxxxxxxx" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-uppercase">Alamat Lengkap Pengiriman</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <textarea name="address" class="form-control form-with-icon" rows="2" placeholder="Sebutkan jalan, nomor, dan kota lokasi unit kesehatan Anda..." required></textarea>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Alamat ini akan digunakan sebagai tujuan utama distribusi obat.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-uppercase">Kata Sandi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                                    <input type="password" name="password" class="form-control form-with-icon" placeholder="••••••••" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-uppercase">Konfirmasi Sandi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                    <input type="password" name="password_confirmation" class="form-control form-with-icon" placeholder="••••••••" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="btnSubmit" class="btn btn-register shadow">
                            Daftar Sebagai Mitra <i class="bi bi-arrow-right-circle ms-2"></i>
                        </button>

                        <div class="text-center mt-4">
                            <p class="small text-muted">Sudah memiliki akun mitra? <a href="/login" class="login-link">Masuk di sini</a></p>
                            <hr class="my-4 opacity-25">
                            <a href="/" class="text-muted small text-decoration-none">
                                <i class="bi bi-house-door me-1"></i> Kembali ke Beranda
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function submitRegister(event) {
        event.preventDefault();
        const btn = document.getElementById('btnSubmit');
        const formData = new FormData(document.getElementById('formRegister'));

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses Pendaftaran...';

        axios.post('/register', formData)
            .then(res => {
                Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Berhasil',
                    text: 'Kode OTP telah dikirim ke email Anda. Silakan masukkan kode tersebut pada halaman berikutnya.',
                    confirmButtonColor: '#00838f',
                }).then(() => {
                    // MENGARAHKAN KE HALAMAN VERIFIKASI OTP SESUAI RESPON SERVER
                    window.location.href = res.data.redirect;
                });
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Daftar Sebagai Mitra <i class="bi bi-arrow-right-circle ms-2"></i>';

                let msg = 'Terjadi kesalahan sistem.';
                if (err.response && err.response.data) {
                    msg = err.response.data.message || msg;
                    if (err.response.data.errors) {
                        // Ambil pesan error validasi pertama jika ada
                        msg = Object.values(err.response.data.errors)[0][0];
                    }
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Pendaftaran Gagal',
                    text: msg,
                    confirmButtonColor: '#00838f',
                });
            });
    }
</script>
</body>
</html>
