<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Verifikasi OTP - E-Pharma</title>

  <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@400;600;700&family=Ubuntu:wght@700&display=swap" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
      --primary: #00838f;
      --secondary: #2c4964;
      --hover-color: #006064;
      --light-bg: #f1f7f8;
    }

    body { background: var(--light-bg); font-family: 'Poppins', sans-serif; display: flex; align-items: center; min-height: 100vh; }
    .card-otp { border: none; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); overflow: hidden; }
    .card-header-accent { background: var(--primary); height: 6px; width: 100%; }
    .sitename { font-family: 'Ubuntu', sans-serif; font-size: 2rem; font-weight: 700; color: var(--secondary); text-decoration: none; }
    .sitename span { color: var(--primary); }
    .btn-medinest { background: var(--primary); color: white; border-radius: 30px; padding: 12px; font-weight: 600; border: none; transition: 0.3s; width: 100%; }
    .btn-medinest:hover { background: var(--hover-color); color: white; }
    .otp-input { letter-spacing: 15px; font-size: 2.5rem; font-weight: 800; text-align: center; border-radius: 15px; border: 2px solid #deebec; color: var(--secondary); }
    .back-link { color: var(--primary); text-decoration: none; font-weight: 500; font-size: 0.9rem; }
    .resend-text { font-size: 0.85rem; color: #777; }
    .disabled-link { color: #ccc !important; cursor: not-allowed; text-decoration: none !important; }
  </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-otp">
                <div class="card-header-accent"></div>
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <a href="/" class="sitename">E-<span>Pharma</span></a>
                        <h4 class="fw-bold mt-3" style="color: var(--secondary);">Verifikasi Akun</h4>
                        <p class="text-muted small">Kode OTP telah dikirim. Periksa kotak masuk atau folder spam email Anda.</p>
                    </div>

                    <form id="formOtp" onsubmit="submitOtp(event)">
                        @csrf
                        <div class="mb-4">
                            <input type="text" name="otp" class="form-control otp-input" maxlength="6" placeholder="000000" required autofocus autocomplete="off">
                        </div>
                        <button type="submit" id="btnVerify" class="btn btn-medinest shadow">
                            Verifikasi Sekarang <i class="bi bi-shield-check ms-1"></i>
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <div class="resend-text">
                            Tidak menerima kode? <br>
                            <a href="javascript:void(0)" id="btnResend" onclick="handleResend()" class="back-link fw-bold">Kirim Ulang OTP</a>
                            <span id="timerText" class="d-none text-muted">(Tunggu <span id="seconds">60</span>s)</span>
                        </div>
                        <hr class="opacity-25 my-4">
                        <a href="/login" class="back-link">
                           <i class="bi bi-arrow-left"></i> Kembali ke Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    // 1. Fungsi Submit OTP (Standard)
    function submitOtp(event) {
        event.preventDefault();
        const btn = document.getElementById('btnVerify');
        const formData = new FormData(event.target);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';

        axios.post('/verify-otp', formData)
            .then(res => {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.data.message, confirmButtonColor: '#00838f' })
                .then(() => window.location.href = res.data.redirect);
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Verifikasi Sekarang <i class="bi bi-shield-check ms-1"></i>';
                Swal.fire({ icon: 'error', title: 'Gagal', text: err.response.data.message || 'OTP Salah', confirmButtonColor: '#00838f' });
            });
    }

    // 2. Fungsi Kirim Ulang OTP dengan Timer
    let cooldown = 0;
    function handleResend() {
        if (cooldown > 0) return;

        const btnResend = document.getElementById('btnResend');
        const timerText = document.getElementById('timerText');

        // Visual Loading
        btnResend.classList.add('disabled-link');
        btnResend.innerText = 'Mengirim...';

        axios.post('/resend-otp')
            .then(res => {
                Swal.fire({ icon: 'success', title: 'Terkirim!', text: res.data.message, timer: 2000, showConfirmButton: false });
                startTimer(60); // Mulai Cooldown 60 detik
            })
            .catch(err => {
                btnResend.classList.remove('disabled-link');
                btnResend.innerText = 'Kirim Ulang OTP';
                Swal.fire({ icon: 'error', title: 'Gagal', text: err.response.data.message });
            });
    }

    function startTimer(seconds) {
        cooldown = seconds;
        const btnResend = document.getElementById('btnResend');
        const timerText = document.getElementById('timerText');
        const secondsEl = document.getElementById('seconds');

        btnResend.classList.add('d-none');
        timerText.classList.remove('d-none');

        const interval = setInterval(() => {
            cooldown--;
            secondsEl.innerText = cooldown;

            if (cooldown <= 0) {
                clearInterval(interval);
                btnResend.classList.remove('d-none', 'disabled-link');
                btnResend.innerText = 'Kirim Ulang OTP';
                timerText.classList.add('d-none');
            }
        }, 1000);
    }
</script>
</body>
</html>
