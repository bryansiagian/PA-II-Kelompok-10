@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold text-dark mb-0">Ringkasan Kerja Kurir</h4>
        <p class="text-muted small mb-0">Pantau performa dan tugas pengiriman Anda.</p>
    </div>

    <div class="row g-4">
        <!-- Tugas Sedang Berjalan -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-warning border-width-5 position-relative card-hover">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3 text-warning">
                        <i class="ph-truck fs-1"></i>
                    </div>
                    <div class="ms-4">
                        <small class="text-muted fw-bold d-block mb-1 text-uppercase" style="font-size: 11px;">Tugas Aktif Saya</small>
                        <h1 class="fw-bold mb-0 text-dark" id="statActive">0</h1>
                    </div>
                </div>
                <a href="/courier/active" class="stretched-link"></a>
            </div>
        </div>

        <!-- Total Pengiriman Selesai -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-success border-width-5 position-relative card-hover">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-3 text-success">
                        <i class="ph-check-circle fs-1"></i>
                    </div>
                    <div class="ms-4">
                        <small class="text-muted fw-bold d-block mb-1 text-uppercase" style="font-size: 11px;">Total Berhasil</small>
                        <h1 class="fw-bold mb-0 text-dark" id="statCompleted">0</h1>
                    </div>
                </div>
                <a href="/courier/history" class="stretched-link"></a>
            </div>
        </div>
    </div>
</div>

<script>
    function loadCourierStats() {
        axios.get('/api/courier/stats')
            .then(res => {
                const data = res.data;
                document.getElementById('statActive').innerText    = data.active;
                document.getElementById('statCompleted').innerText = data.completed;
            })
            .catch(err => {
                console.error("Gagal memuat statistik kurir:", err);
            });
    }

    document.addEventListener('DOMContentLoaded', loadCourierStats);
</script>

<style>
    .border-width-5 { border-left-width: 5px !important; }
    .card-hover { transition: transform 0.2s, box-shadow 0.2s; border: 1px solid rgba(0,0,0,.05) !important; }
    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
</style>
@endsection
