@extends('layouts.portal')

@section('content')
<style>
    :root {
        --primary: #00838f;
        --secondary: #2c4964;
    }
    .profile-header {
        background: linear-gradient(135deg, var(--primary) 0%, #3a5a78 100%);
        padding: 60px 0;
        color: white;
        border-radius: 0 0 50px 50px;
        margin-bottom: -50px;
    }
    .profile-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        background: #fff;
    }
    .avatar-wrapper {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 5px solid #fff;
        overflow: hidden;
        margin: 0 auto 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .info-label {
        font-size: 11px;
        font-weight: 700;
        color: #999;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    .info-value {
        font-weight: 600;
        color: var(--secondary);
        margin-bottom: 20px;
        font-size: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f4f4;
    }
    .badge-status {
        font-size: 12px;
        padding: 6px 15px;
        border-radius: 50px;
    }
</style>

<div class="profile-header text-center">
    <div class="container">
        <h3 class="fw-bold">Informasi Akun</h3>
        <p class="opacity-75">Detail data unit kesehatan yang terdaftar di sistem E-Pharma</p>
    </div>
</div>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="profile-card p-4 p-md-5">
                <div class="text-center mb-5">
                    <div class="avatar-wrapper">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=00838f&color=fff&size=128&bold=true" alt="Avatar" class="w-100">
                    </div>
                    <h4 class="fw-bold text-dark mb-1">{{ Auth::user()->name }}</h4>
                    <div id="statusBadgeContainer">
                        <!-- Filled by JS -->
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-label">Nama Lengkap Unit / Petugas</div>
                        <div class="info-value">{{ Auth::user()->name }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label">Alamat Email Resmi</div>
                        <div class="info-value">{{ Auth::user()->email }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label">Nomor Telepon</div>
                        <div class="info-value">{{ Auth::user()->phone ?? 'Belum diatur' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label">Terdaftar Sejak</div>
                        <div class="info-value">{{ Auth::user()->created_at->format('d F Y') }}</div>
                    </div>
                    <div class="col-12">
                        <div class="info-label">Alamat Utama Pengiriman</div>
                        <div class="info-value" style="border-bottom: none;">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                            {{ Auth::user()->address ?? 'Alamat belum dilengkapi' }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-light rounded-4 border-start border-4 border-info">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                        <small class="text-muted">
                            Data profil ini disetujui oleh Administrator Pusat. Jika ada perubahan data unit, silakan hubungi Customer Support E-Pharma.
                        </small>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <a href="/customer/history" class="btn btn-outline-secondary rounded-pill px-4 me-2">Lihat Riwayat</a>
                    <a href="/" class="btn btn-medinest rounded-pill px-4 shadow-sm">Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        renderStatusBadge();
    });

    function renderStatusBadge() {
        const status = {{ Auth::user()->status }};
        const container = document.getElementById('statusBadgeContainer');

        let badgeHtml = '';
        if(status == 1) {
            badgeHtml = '<span class="badge bg-success bg-opacity-10 text-success border border-success px-3 rounded-pill badge-status">Mitra Terverifikasi</span>';
        } else if(status == 0) {
            badgeHtml = '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-3 rounded-pill badge-status">Menunggu Persetujuan</span>';
        } else {
            badgeHtml = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 rounded-pill badge-status">Akun Ditolak</span>';
        }
        container.innerHTML = badgeHtml;
    }
</script>
@endsection
