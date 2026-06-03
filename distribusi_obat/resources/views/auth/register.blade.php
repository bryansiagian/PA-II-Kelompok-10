<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Registrasi Mitra - Yayasan E-Pharma</title>

    <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
    <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

    <!-- GOOGLE FONT -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Ubuntu:wght@500;700&display=swap" rel="stylesheet">

    <!-- BOOTSTRAP -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

    <style>

        :root {
            --primary: #00838f;
            --secondary: #2c4964;
            --hover-color: #006064;
        }

        body {
            background:
                linear-gradient(rgba(241,247,248,.92), rgba(241,247,248,.92)),
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
            border-radius: 24px;
            overflow: hidden;

            box-shadow:
                0 20px 60px rgba(0,0,0,.08);
        }

        .register-header {
            background: var(--primary);
            color: white;

            padding: 42px 30px;
            text-align: center;
        }

        .register-logo {
            font-family: 'Ubuntu', sans-serif;

            font-size: 2.3rem;
            font-weight: 700;

            color: #fff;
            text-decoration: none;

            display: inline-block;
            margin-bottom: 8px;
        }

        .register-logo span {
            color: var(--secondary);
        }

        .form-label {
            color: var(--secondary);

            font-size: .82rem;
            font-weight: 700;

            margin-bottom: 8px;
        }

        .form-control,
        .form-select {

            border-radius: 14px;

            border: 1px solid #deebec;

            background: #fcfcfc;

            padding: 14px 16px;

            font-size: .93rem;

            transition: all .3s ease;
        }

        .form-control:focus,
        .form-select:focus {

            border-color: var(--primary);

            background: #fff;

            box-shadow:
                0 0 0 .25rem rgba(0,131,143,.10);
        }

        .input-group-text {

            border-radius: 14px 0 0 14px;

            border-color: #deebec;

            background: #fff;

            color: var(--primary);

            padding-left: 16px;
            padding-right: 16px;
        }

        .form-with-icon {
            border-radius: 0 14px 14px 0 !important;
        }

        /* REGION */

        .region-select {
            height: 56px;
        }

        .region-select:disabled {
            background: #f3f5f6;
            opacity: .8;
            cursor: not-allowed;
        }

        /* ADDRESS */

        #address {
            min-height: 110px;
            resize: vertical;
        }

        /* PASSWORD */

        .password-group {
            transition: .3s;
        }

        .password-group:focus-within {

            border-radius: 14px;

            box-shadow:
                0 0 0 .25rem rgba(0,131,143,.10);
        }

        .password-icon {
            border-radius: 14px 0 0 14px !important;
        }

        .password-input {
            height: 56px;
            letter-spacing: .3px;
        }

        /* BUTTON */

        .btn-register {

            width: 100%;

            border: none;

            border-radius: 18px;

            background: var(--primary);
            color: white;

            padding: 15px;

            font-weight: 600;

            transition: .3s;
        }

        .btn-register:hover {

            background: var(--hover-color);

            color: white;

            transform: translateY(-1px);

            box-shadow:
                0 10px 25px rgba(0,131,143,.25);
        }

        .login-link {

            color: var(--primary);

            text-decoration: none;

            font-weight: 600;
        }

        .login-link:hover {
            color: var(--hover-color);
            text-decoration: underline;
        }

        @media (max-width: 768px) {

    body {
        padding: 15px;
        align-items: flex-start;
    }

    .container {
        padding: 0;
    }

    .card-register {
        border-radius: 20px;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .register-header {
        padding: 28px 20px;
    }

    .register-header h3 {
        font-size: 1.8rem;
    }

    .register-logo {
        font-size: 2rem;
    }

    .card-body {
        padding: 24px !important;
    }

    .form-label {
        font-size: .78rem;
    }

    .form-control,
    .form-select {
        font-size: .9rem;
        padding: 13px 14px;
    }

    .region-select,
    .password-input {
        height: 52px;
    }

    #address {
        min-height: 100px;
    }

    .btn-register {
        padding: 14px;
        font-size: .95rem;
        border-radius: 15px;
    }

    .swal2-popup {
        width: 85% !important;
        border-radius: 18px !important;
        padding: 25px 18px !important;
    }

    .swal2-title {
        font-size: 1.6rem !important;
    }

    .swal2-html-container {
        font-size: .95rem !important;
        line-height: 1.6;
    }

    .swal2-confirm {
        border-radius: 12px !important;
        padding: 10px 20px !important;
    }
}

    </style>
</head>

<body>

<div class="container">

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <div class="card card-register">

                <!-- HEADER -->
                <div class="register-header">

                    <a href="/" class="register-logo">
                        E-<span>Pharma</span>
                    </a>

                    <h3 class="fw-bold mb-2">
                        Registrasi Mitra Faskes
                    </h3>

                    <p class="mb-0 opacity-75 small">
                        Khusus untuk Unit Kesehatan, Rumah Sakit, dan Klinik Mitra
                    </p>

                </div>

                <!-- BODY -->
                <div class="card-body p-4 p-md-5">

                    <form id="formRegister" onsubmit="submitRegister(event)">

                        @csrf

                        <!-- NAMA -->
                        <div class="mb-4">

                            <label class="form-label text-uppercase">
                                Nama Unit / Petugas
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>

                                <input
                                    type="text"
                                    name="name"
                                    class="form-control form-with-icon"
                                    placeholder="Contoh: Klinik Sehat / Budi Santoso"
                                    required>

                            </div>

                        </div>

                        <!-- EMAIL & PHONE -->
                        <div class="row">

                            <div class="col-md-6 mb-4">

                                <label class="form-label text-uppercase">
                                    Alamat Email Resmi
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        <i class="bi bi-envelope"></i>
                                    </span>

                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control form-with-icon"
                                        placeholder="kontak@unitkesehatan.id"
                                        required>

                                </div>

                            </div>

                            <div class="col-md-6 mb-4">

                                <label class="form-label text-uppercase">
                                    Nomor Telepon / WhatsApp
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        <i class="bi bi-telephone"></i>
                                    </span>

                                    <input
                                        type="tel"
                                        name="phone"
                                        class="form-control form-with-icon"
                                        placeholder="08xxxxxxxxxx"
                                        required>

                                </div>

                            </div>

                        </div>

                        <!-- WILAYAH -->
                        <div class="mb-4">

                            <label class="form-label text-uppercase">
                                Wilayah Distribusi
                            </label>

                            <div class="row g-3">

                                <div class="col-md-12">

                                    <select
                                        id="regency"
                                        class="form-select region-select"
                                        onchange="fetchDistricts(this.value)"
                                        required>

                                        <option value="" selected disabled>
                                            Pilih Kabupaten / Kota
                                        </option>

                                    </select>

                                </div>

                                <div class="col-md-6">

                                    <select
                                        id="district"
                                        class="form-select region-select"
                                        onchange="fetchVillages(this.value)"
                                        disabled
                                        required>

                                        <option value="">
                                            Pilih Kecamatan
                                        </option>

                                    </select>

                                </div>

                                <div class="col-md-6">

                                    <select
                                        id="village"
                                        class="form-select region-select"
                                        disabled
                                        required>

                                        <option value="">
                                            Pilih Kelurahan / Desa
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>

                        <!-- ADDRESS -->
                        <div class="mb-4">

                            <label class="form-label text-uppercase">
                                Alamat Lengkap Pengiriman
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bi bi-geo-alt"></i>
                                </span>

                                <textarea
                                    name="address"
                                    id="address"
                                    class="form-control form-with-icon"
                                    placeholder="Nama jalan, nomor bangunan, RT/RW..."
                                    required></textarea>

                            </div>

                            <small class="text-muted" style="font-size:11px;">
                                Alamat ini akan digunakan sebagai tujuan utama distribusi obat.
                            </small>

                        </div>

                        <!-- PASSWORD -->
                        <div class="row">

                            <div class="col-md-6 mb-4">

                                <label class="form-label text-uppercase">
                                    Kata Sandi
                                </label>

                                <div class="input-group password-group">

                                    <span class="input-group-text password-icon">
                                        <i class="bi bi-shield-lock"></i>
                                    </span>

                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control form-with-icon password-input"
                                        placeholder="Minimal 6 karakter"
                                        required>

                                </div>

                            </div>

                            <div class="col-md-6 mb-4">

                                <label class="form-label text-uppercase">
                                    Konfirmasi Sandi
                                </label>

                                <div class="input-group password-group">

                                    <span class="input-group-text password-icon">
                                        <i class="bi bi-shield-check"></i>
                                    </span>

                                    <input
                                        type="password"
                                        name="password_confirmation"
                                        class="form-control form-with-icon password-input"
                                        placeholder="Ulangi kata sandi"
                                        required>

                                </div>

                            </div>

                        </div>

                        <!-- BUTTON -->
                        <button
                            type="submit"
                            id="btnSubmit"
                            class="btn btn-register">

                            Daftar Sebagai Mitra
                            <i class="bi bi-arrow-right-circle ms-2"></i>

                        </button>

                        <!-- FOOT -->
                        <div class="text-center mt-4">

                            <p class="small text-muted mb-3">

                                Sudah memiliki akun mitra?

                                <a href="/login" class="login-link">
                                    Masuk di sini
                                </a>

                            </p>

                            <hr class="opacity-25">

                            <a
                                href="/"
                                class="text-muted small text-decoration-none">

                                <i class="bi bi-house-door me-1"></i>
                                Kembali ke Beranda

                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- SCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    // =========================
    // API WILAYAH
    // =========================

    const PROVINCE_ID = '12';

    document.addEventListener('DOMContentLoaded', () => {
        fetchRegencies();
    });

    // LOAD KABUPATEN
    async function fetchRegencies() {

        try {

            const response = await fetch(
                `https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${PROVINCE_ID}.json`
            );

            const data = await response.json();

            let html = `
                <option value="" selected disabled>
                    Pilih Kabupaten / Kota
                </option>
            `;

            data.forEach(item => {

                html += `
                    <option value="${item.id}" data-name="${item.name}">
                        ${item.name}
                    </option>
                `;
            });

            document.getElementById('regency').innerHTML = html;

        } catch (error) {

            console.error(error);
        }
    }

    // LOAD KECAMATAN
    async function fetchDistricts(regencyId) {

        const district = document.getElementById('district');
        const village = document.getElementById('village');

        district.disabled = true;
        village.disabled = true;

        district.innerHTML = '<option>Memuat...</option>';
        village.innerHTML = '<option>Pilih Kelurahan / Desa</option>';

        try {

            const response = await fetch(
                `https://www.emsifa.com/api-wilayah-indonesia/api/districts/${regencyId}.json`
            );

            const data = await response.json();

            let html = `
                <option value="" selected disabled>
                    Pilih Kecamatan
                </option>
            `;

            data.forEach(item => {

                html += `
                    <option value="${item.id}" data-name="${item.name}">
                        ${item.name}
                    </option>
                `;
            });

            district.innerHTML = html;
            district.disabled = false;

        } catch (error) {

            console.error(error);
        }
    }

    // LOAD DESA
    async function fetchVillages(districtId) {

        const village = document.getElementById('village');

        village.disabled = true;
        village.innerHTML = '<option>Memuat...</option>';

        try {

            const response = await fetch(
                `https://www.emsifa.com/api-wilayah-indonesia/api/villages/${districtId}.json`
            );

            const data = await response.json();

            let html = `
                <option value="" selected disabled>
                    Pilih Kelurahan / Desa
                </option>
            `;

            data.forEach(item => {

                html += `
                    <option value="${item.id}" data-name="${item.name}">
                        ${item.name}
                    </option>
                `;
            });

            village.innerHTML = html;
            village.disabled = false;

        } catch (error) {

            console.error(error);
        }
    }

    // REGISTER
    // REGISTER
function submitRegister(event) {
    event.preventDefault();

    const btn = document.getElementById('btnSubmit');

    // AMBIL WILAYAH
    const regencyEl  = document.getElementById('regency');
    const districtEl = document.getElementById('district');
    const villageEl  = document.getElementById('village');

    const regencyName  = regencyEl.selectedOptions[0]?.getAttribute('data-name')  || '';
    const districtName = districtEl.selectedOptions[0]?.getAttribute('data-name') || '';
    const villageName  = villageEl.selectedOptions[0]?.getAttribute('data-name')  || '';

    // Inject hidden inputs agar masuk ke FormData
    const form = document.getElementById('formRegister');

    ['regency','district','village'].forEach(k => {
        let el = form.querySelector(`input[name="${k}"]`);
        if (!el) {
            el = document.createElement('input');
            el.type = 'hidden';
            el.name = k;
            form.appendChild(el);
        }
    });

    form.querySelector('input[name="regency"]').value  = regencyName;
    form.querySelector('input[name="district"]').value = districtName;
    form.querySelector('input[name="village"]').value  = villageName;

    const formData = new FormData(form);

    // BUTTON LOADING
    btn.disabled = true;
    btn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Memproses Pendaftaran...
    `;

    axios.post('/register', formData)
        .then(res => {
            Swal.fire({
                icon: 'success',
                title: 'Pendaftaran Berhasil',
                text: 'Kode OTP telah dikirim ke email Anda.',
                confirmButtonColor: '#00838f',
            }).then(() => {
                window.location.href = res.data.redirect;
            });
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = `
                Daftar Sebagai Mitra
                <i class="bi bi-arrow-right-circle ms-2"></i>
            `;

            let msg = 'Terjadi kesalahan sistem.';
            if (err.response && err.response.data) {
                msg = err.response.data.message || msg;
                if (err.response.data.errors) {
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
