@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0 text-dark">Bursa Tugas Tersedia</h4>
            <div class="text-muted small">Daftar paket yang siap dijemput di gudang pusat</div>
        </div>
        <div class="ms-3">
            <button onclick="fetchAvailable()" class="btn btn-light btn-icon shadow-sm rounded-circle" title="Refresh Bursa">
                <i class="ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Statistik Ringkas Kurir -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert bg-indigo text-white border-0 shadow-sm rounded-3 d-flex align-items-center mb-3">
                <i class="ph-info ph-2x me-3"></i>
                <div>
                    <span class="fw-bold">Informasi Kendaraan:</span> Sistem otomatis memfilter paket yang sesuai dengan jenis kendaraan Anda. Silakan ambil tugas jika Anda sudah siap di lokasi gudang.
                </div>
            </div>
        </div>
    </div>

    <!-- LIST BURSA GRID -->
    <div id="availableList" class="row g-3">
        <!-- Data dimuat via JavaScript -->
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-indigo spinner-border-sm" role="status"></div>
            <span class="ms-2 text-muted small fw-bold">Memeriksa ketersediaan paket...</span>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: RINCIAN ISI PAKET (Limitless Style)
     ========================================== -->
<div class="modal fade" id="modalItems" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-package me-2"></i>Rincian Isi Paket</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="modalItemsBody">
                <!-- List produk diisi via JS -->
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">TUTUP</button>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchAvailable() {
        const container = document.getElementById('availableList');

        axios.get('/api/deliveries/available')
            .then(res => {
                let html = '';
                if (res.data.length === 0) {
                    html = `
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-3 py-5 text-center border-2 border-dashed border-light">
                            <div class="card-body">
                                <i class="ph-package ph-4x text-muted opacity-25 mb-3"></i>
                                <h5 class="fw-bold text-muted">Bursa Sedang Kosong</h5>
                                <p class="text-muted mx-auto" style="max-width: 400px;">Belum ada paket baru yang sesuai dengan kendaraan Anda. Silakan periksa kembali beberapa saat lagi.</p>
                                <button onclick="fetchAvailable()" class="btn btn-indigo rounded-pill px-4 mt-2">
                                    <i class="ph-arrows-clockwise me-2"></i>Cek Lagi
                                </button>
                            </div>
                        </div>
                    </div>`;
                } else {
                    res.data.forEach(d => {
                        // Data dari backend sekarang menggunakan relasi d.order (sebelumnya d.request)
                        const itemsJson = JSON.stringify(d.order.items).replace(/"/g, '&quot;');
                        const vehicleName = d.order.type ? d.order.type.name : 'Motorcycle';
                        const address = d.order.user.address || 'Alamat tidak tersedia';

                        html += `
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm rounded-3 h-100 task-card">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <span class="badge bg-indigo bg-opacity-10 text-indigo fw-bold px-2 py-1">
                                            <i class="ph-hash-straight me-1"></i>${d.tracking_number}
                                        </span>
                                        <button onclick="showItems('${itemsJson}')" class="btn btn-sm btn-light text-indigo border-0 rounded-pill px-3 fw-bold">
                                            <i class="ph-magnifying-glass me-1"></i>Detail Isi
                                        </button>
                                    </div>

                                    <div class="mb-2">
                                        <div class="fs-xs text-muted text-uppercase fw-bold mb-1">Tujuan Faskes</div>
                                        <h6 class="fw-bold text-dark mb-0">${d.order.user.name}</h6>
                                    </div>

                                    <div class="mb-3">
                                        <span class="badge bg-light text-indigo border-indigo border-opacity-25 rounded-pill px-2">
                                            <i class="ph-truck me-1"></i> ${vehicleName}
                                        </span>
                                    </div>

                                    <div class="mb-4">
                                        <div class="fs-xs text-muted text-uppercase fw-bold mb-1">Alamat Tujuan</div>
                                        <div class="small text-dark d-flex align-items-start">
                                            <i class="ph-map-pin text-danger me-2 mt-1"></i>
                                            <span class="text-limit-2-row">${address}</span>
                                        </div>
                                    </div>

                                    <button onclick="claimTask('${d.id}')" class="btn btn-indigo w-100 fw-bold shadow-sm py-2 rounded-pill">
                                        <i class="ph-hand-pointing me-2"></i>AMBIL TUGAS INI
                                    </button>
                                </div>
                            </div>
                        </div>`;
                    });
                }
                container.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<div class="col-12 text-center text-danger py-5 fw-bold"><i class="ph-warning-octagon me-2"></i>Gagal memuat data. Periksa koneksi internet Anda.</div>';
            });
    }

    function showItems(encoded) {
        const items = JSON.parse(encoded);
        let html = '<div class="list-group list-group-flush">';
        items.forEach(i => {
            // Menggunakan i.product.name sesuai relasi terbaru
            const name = i.product ? i.product.name : 'Produk Tidak Diketahui';
            const unit = i.product ? i.product.unit : 'Unit';
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-light p-2 rounded me-3 text-indigo">
                            <i class="ph-pill fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">${name}</div>
                            <div class="fs-xs text-muted">Satuan: ${unit}</div>
                        </div>
                    </div>
                    <span class="badge bg-light text-indigo border rounded-pill px-3 fs-base">x${i.quantity}</span>
                </div>`;
        });
        html += '</div>';
        document.getElementById('modalItemsBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('modalItems')).show();
    }

    function claimTask(id) {
        Swal.fire({
            title: 'Konfirmasi Tugas',
            text: "Pastikan Anda sudah berada di lokasi gudang untuk mengambil paket ini.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Ambil',
            confirmButtonColor: '#5c68e2',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-indigo px-4 rounded-pill',
                cancelButton: 'btn btn-light px-4 rounded-pill'
            }
        }).then(result => {
            if(result.isConfirmed) {
                axios.post(`/api/deliveries/claim/${id}`)
                    .then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Tugas Diambil!',
                            text: 'Segera lakukan pengiriman paket.',
                            confirmButtonColor: '#5c68e2'
                        }).then(() => window.location.href = '/courier/active');
                    })
                    .catch(err => {
                        Swal.fire('Gagal', err.response?.data?.message || 'Tugas sudah diambil kurir lain.', 'error');
                        fetchAvailable();
                    });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchAvailable);
</script>

<style>
    /* Styling Indigo Limitless */
    .bg-indigo { background-color: #5c68e2 !important; }
    .text-indigo { color: #5c68e2 !important; }
    .btn-indigo { background-color: #5c68e2; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #4e59cf; color: #fff; }

    /* Card & Animation */
    .task-card { transition: all 0.3s ease; border: 1px solid rgba(0,0,0,.05) !important; }
    .task-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }

    /* Text Helpers */
    .text-limit-2-row {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .fs-xs { font-size: 0.7rem; }
    .ph-4x { font-size: 4rem; }
    #modalItemsBody { max-height: 450px; overflow-y: auto; }
</style>
@endsection