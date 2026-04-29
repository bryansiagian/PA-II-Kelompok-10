@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Manajemen Pengantaran</h4>
            <p class="text-muted small mb-0">Ambil tugas baru dari bursa atau selesaikan pengiriman aktif Anda.</p>
        </div>
        <button onclick="fetchTasks()" class="btn btn-white border shadow-sm rounded-pill px-3">
            <i class="bi bi-arrow-clockwise me-1"></i> Perbarui Daftar
        </button>
    </div>

    <!-- ==========================================
         BAGIAN 1: BURSA TUGAS (TERSEDIA)
         ========================================== -->
    <section class="mb-5">
        <div class="d-flex align-items-center mb-3">
            <div class="bg-primary rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                <i class="bi bi-megaphone-fill text-white small"></i>
            </div>
            <h6 class="fw-bold text-dark m-0">Bursa Tugas (Tersedia untuk Diambil)</h6>
        </div>

        <div id="availableList" class="row g-3">
            <!-- Data Available dimuat di sini -->
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                <span class="ms-2 text-muted small">Memeriksa bursa tugas...</span>
            </div>
        </div>
    </section>

    <hr class="my-5 opacity-25">

    <!-- ==========================================
         BAGIAN 2: TUGAS SAYA (AKTIF)
         ========================================== -->
    <section>
        <div class="d-flex align-items-center mb-3">
            <div class="bg-warning rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                <i class="bi bi-bicycle text-dark small"></i>
            </div>
            <h6 class="fw-bold text-dark m-0">Tugas Aktif Saya (Sedang Berjalan)</h6>
        </div>

        <div id="myTasks" class="row g-3">
            <!-- Data Aktif dimuat di sini -->
            <div class="col-12 text-center py-5">
                <p class="text-muted small italic">Memuat tugas aktif Anda...</p>
            </div>
        </div>
    </section>
</div>

<!-- ==========================================
     MODAL: KONFIRMASI SELESAI & FOTO BUKTI
     ========================================== -->
<div class="modal fade" id="modalProof" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Konfirmasi Sampai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="text-muted small mb-4">Harap ambil foto paket atau foto bersama penerima sebagai bukti pengiriman.</p>

                <!-- Area Kamera/Galeri -->
                <div id="uploadArea" class="rounded-4 p-4 mb-3 border border-2 border-dashed d-flex flex-column align-items-center justify-content-center position-relative"
                     style="height: 220px; cursor: pointer; background-color: #f8f9fa;"
                     onclick="document.getElementById('proofInput').click()">

                    <div id="placeholderUI">
                        <i class="bi bi-camera-fill text-primary mb-2" style="font-size: 2.5rem;"></i>
                        <p class="mb-0 fw-bold small">Klik untuk Ambil Foto</p>
                        <small class="text-muted" style="font-size: 11px;">Mendukung Kamera & Galeri</small>
                    </div>

                    <img id="imagePreview" src="#" alt="Preview" class="img-fluid rounded-4 d-none" style="max-height: 100%; width: 100%; object-fit: cover;">
                </div>

                <!-- Input File Tersembunyi (capture="environment" memicu kamera belakang di HP) -->
                <input type="file" id="proofInput" accept="image/*" capture="environment" class="d-none" onchange="handlePreview(this)">

                <div class="d-grid gap-2">
                    <button id="btnComplete" onclick="submitComplete()" class="btn btn-success py-3 rounded-pill fw-bold shadow-sm" disabled>
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i> Kirim & Selesaikan
                    </button>
                    <button type="button" class="btn btn-link text-muted text-decoration-none small" data-bs-dismiss="modal">Batalkan</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Header Token Global
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    let selectedDeliveryId = null;

    function fetchTasks() {
        const bursaList = document.getElementById('availableList');
        const activeList = document.getElementById('myTasks');

        // 1. Ambil Bursa (Ready status)
        axios.get('/api/deliveries/available').then(res => {
            let html = '';
            if (res.data.length === 0) {
                html = '<div class="col-12 text-center py-4 bg-white rounded-4 border"><small class="text-muted italic">Tidak ada tugas tersedia di bursa saat ini.</small></div>';
            } else {
                res.data.forEach(d => {
                    html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 card-hover">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="badge bg-light text-primary border rounded-pill px-3">#${d.tracking_number}</span>
                                    <small class="text-muted"><i class="bi bi-clock"></i> Baru</small>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">${d.request.user.name}</h6>
                                <p class="small text-muted mb-4"><i class="bi bi-geo-alt-fill text-danger"></i> ${d.request.user.address || 'Alamat tidak diset'}</p>
                                <button onclick="claimTask(${d.id})" class="btn btn-primary w-100 rounded-pill fw-bold btn-sm shadow-sm">Ambil Tugas</button>
                            </div>
                        </div>
                    </div>`;
                });
            }
            bursaList.innerHTML = html;
        });

        // 2. Ambil Tugas Saya (Claimed / In Transit)
        axios.get('/api/deliveries').then(res => {
            let html = '';
            const myTasks = res.data.filter(d => d.status !== 'delivered');

            if (myTasks.length === 0) {
                html = '<div class="col-12 text-center py-4 bg-white rounded-4 border"><small class="text-muted italic">Anda tidak memiliki tugas pengiriman yang aktif.</small></div>';
            } else {
                myTasks.forEach(d => {
                    const isMoving = d.status === 'in_transit';
                    html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 ${isMoving ? 'border-warning' : 'border-info'}">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge ${isMoving ? 'bg-warning text-dark' : 'bg-info text-white'} rounded-pill px-3">
                                        ${isMoving ? 'DALAM PERJALANAN' : 'DIAMBIL'}
                                    </span>
                                    <small class="fw-bold text-muted">#${d.tracking_number}</small>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">${d.request.user.name}</h6>
                                <p class="small text-muted mb-4">${d.request.user.address}</p>
                                <div class="d-grid gap-2">
                                    ${!isMoving ? `
                                        <button onclick="startMoving(${d.id})" class="btn btn-warning btn-sm rounded-pill fw-bold">
                                            <i class="bi bi-play-fill me-1"></i> Mulai Perjalanan
                                        </button>
                                    ` : `
                                        <button onclick="openModalComplete(${d.id})" class="btn btn-success btn-sm rounded-pill fw-bold shadow-sm">
                                            <i class="bi bi-camera me-1"></i> Konfirmasi Sampai
                                        </button>
                                    `}
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
            }
            activeList.innerHTML = html;
        });
    }

    // --- LOGIKA AKSI ---

    function claimTask(id) {
        Swal.fire({
            title: 'Ambil tugas ini?',
            text: "Pastikan Anda bisa segera menjemput paket di gudang.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Saya Ambil'
        }).then(result => {
            if(result.isConfirmed) {
                axios.post(`/api/deliveries/claim/${id}`).then(() => {
                    Swal.fire('Berhasil!', 'Tugas ditambahkan ke daftar Anda.', 'success');
                    fetchTasks();
                }).catch(() => Swal.fire('Gagal', 'Tugas mungkin sudah diambil orang lain.', 'error'));
            }
        });
    }

    function startMoving(id) {
        axios.post(`/api/deliveries/start/${id}`).then(() => {
            Swal.fire('Hati-hati di jalan!', 'Status: Dalam Perjalanan.', 'info');
            fetchTasks();
        });
    }

    function openModalComplete(id) {
        selectedDeliveryId = id;
        // Reset preview
        document.getElementById('imagePreview').classList.add('d-none');
        document.getElementById('placeholderUI').classList.remove('d-none');
        document.getElementById('btnComplete').disabled = true;
        document.getElementById('proofInput').value = "";

        new bootstrap.Modal(document.getElementById('modalProof')).show();
    }

    function handlePreview(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('imagePreview');
                img.src = e.target.result;
                img.classList.remove('d-none');
                document.getElementById('placeholderUI').classList.add('d-none');
                document.getElementById('btnComplete').disabled = false;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function submitComplete() {
        const fileInput = document.getElementById('proofInput');
        const btn = document.getElementById('btnComplete');

        if (!fileInput.files[0]) return;

        const formData = new FormData();
        formData.append('image', fileInput.files[0]);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengirim...';

        axios.post(`/api/deliveries/complete/${selectedDeliveryId}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(() => {
            Swal.fire('Sukses!', 'Pengiriman telah selesai.', 'success').then(() => location.reload());
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = 'Kirim & Selesaikan';
            Swal.fire('Gagal', 'Terjadi error saat upload foto.', 'error');
        });
    }

    document.addEventListener('DOMContentLoaded', fetchTasks);
</script>

<style>
    .card-hover { transition: transform 0.2s; cursor: default; }
    .card-hover:hover { transform: translateY(-3px); }
    .nav-pills .nav-link { color: #64748b; font-weight: 600; font-size: 0.9rem; }
    .nav-pills .nav-link.active { background-color: #0d6efd; color: #fff; }
    .italic { font-style: italic; }
</style>
@endsection