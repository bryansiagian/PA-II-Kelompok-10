@extends('layouts.backoffice')

@section('page_title', 'Pelacakan Logistik')

@section('content')
<div class="container-fluid">
    <!-- HEADER & TOMBOL KEMBALI -->
    <div class="d-flex align-items-center mb-4">
        <a href="/operator/orders" class="btn btn-light btn-icon rounded-circle me-3">
            <i class="ph-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-0">Detail Pelacakan Logistik</h4>
            <div class="text-muted small">Pantau posisi paket dan kinerja kurir secara real-time.</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- CARD 1: INFO UTAMA RESI -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="text-uppercase fw-bold text-muted mb-1" style="font-size: 10px; letter-spacing: 1px;">Nomor Resi / Tracking ID</div>
                            <h3 class="fw-bold text-indigo m-0" id="trackNum">-------</h3>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="text-muted small text-uppercase fw-bold mb-1">Status Saat Ini</div>
                            <span id="badgeStatus" class="badge rounded-pill px-3 py-2 fs-6">Memuat...</span>
                        </div>
                    </div>
                </div>
                <div class="bg-light p-3 px-4 d-flex justify-content-between align-items-center border-top rounded-bottom-3">
                    <div class="small fw-bold text-dark">
                        <i class="ph-identification-badge me-2 text-indigo"></i> <span id="courierName">Kurir: -</span>
                    </div>
                    <div class="small text-muted" id="lastUpdate"><i class="ph-clock-clockwise me-1"></i> Update: -</div>
                </div>
            </div>

            <!-- CARD 2: BUKTI FOTO (Hanya muncul jika Delivered) -->
            <div id="proofSection" class="card border-0 shadow-sm rounded-3 mb-4 d-none">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="ph-camera me-2 text-success"></i>Foto Bukti Penerimaan</h6>
                    <div class="row align-items-center">
                        <div class="col-md-5 text-center mb-3 mb-md-0">
                            <div class="bg-light p-2 rounded-3 d-inline-block border">
                                <img id="proofImg" src="" class="img-fluid rounded-3 shadow-sm" style="max-height: 250px; cursor: zoom-in;" onclick="window.open(this.src)">
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="bg-light p-3 rounded-3 border-start border-start-width-5 border-start-success">
                                <div class="mb-2">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Diterima Oleh</small>
                                    <div class="fw-bold text-dark fs-base" id="receiverName">-</div>
                                </div>
                                <div>
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Hubungan / Jabatan</small>
                                    <div class="text-dark" id="receiverRelation">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 3: TIMELINE PERJALANAN -->
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <h5 class="fw-bold mb-4">
                    <i class="ph-timer me-2 text-indigo"></i>Riwayat Distribusi
                </h5>

                <!-- CONTAINER TIMELINE -->
                <div id="timelineContainer" class="ms-2">
                    <div class="text-center py-5">
                        <div class="spinner-border text-indigo" role="status"></div>
                        <p class="mt-2 text-muted small fw-bold">Sinkronisasi data logistik...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- INFO UNIT TUJUAN -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold m-0">Informasi Tujuan</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 10px;">Nama Faskes / Unit</small>
                        <div class="fw-bold text-dark fs-base" id="destName">-</div>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 10px;">Alamat Lengkap</small>
                        <div class="text-dark small" id="destAddress">-</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling Timeline Khusus Limitless Admin */
    #timelineContainer {
        border-left: 2px solid #e0e6ed;
        padding-left: 30px;
        position: relative;
    }
    .timeline-node { position: relative; padding-bottom: 2rem; }
    .timeline-node::before {
        content: ''; position: absolute; left: -41px; top: 0;
        width: 18px; height: 18px; background: #fff;
        border: 4px solid #dee2e6; border-radius: 50%; z-index: 2;
    }
    .timeline-node.active::before {
        border-color: #5c6bc0; background: #5c6bc0;
        box-shadow: 0 0 0 5px rgba(92, 107, 192, 0.15);
    }
    .timeline-node:last-child { padding-bottom: 0; }
    .timeline-date { font-size: 11px; font-weight: 800; color: #adb5bd; text-transform: uppercase; }
    .timeline-title { font-weight: 700; color: #2c3e50; margin-top: 2px; }
    .timeline-desc { font-size: 13px; color: #6c757d; line-height: 1.5; }

    .border-start-width-5 { border-left-width: 5px !important; }
</style>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchTracking() {
        const deliveryId = "{{ $id }}";

        axios.get(`/api/deliveries/${deliveryId}/tracking`)
            .then(res => {
                const d = res.data;

                // 1. Update Info Card Utama
                document.getElementById('trackNum').innerText = d.tracking_number || 'N/A';
                document.getElementById('courierName').innerText = `Kurir: ${d.courier ? d.courier.name : 'Belum Ditentukan'}`;
                document.getElementById('lastUpdate').innerText = `Update: ${new Date(d.updated_at).toLocaleString('id-ID')} WIB`;

                // 2. Info Tujuan (Faskes)
                if (d.order && d.order.user) {
                    document.getElementById('destName').innerText = d.order.user.name;
                    document.getElementById('destAddress').innerText = d.order.user.address || 'Alamat tidak lengkap';
                }

                // 3. Handle Bukti Foto & Penerima
                const proofSection = document.getElementById('proofSection');
                // Gunakan d.status.name karena sekarang tabel lookup
                const currentStatus = d.status ? d.status.name.toLowerCase() : '';

                if (currentStatus === 'delivered') {
                    proofSection.classList.remove('d-none');
                    document.getElementById('proofImg').src = d.proof_image_url || '/assets/img/no-image.png';
                    document.getElementById('receiverName').innerText = d.receiver_name || 'N/A';
                    document.getElementById('receiverRelation').innerText = d.receiver_relation || 'N/A';
                }

                // 4. Update Badge Status
                const badge = document.getElementById('badgeStatus');
                badge.innerText = currentStatus.toUpperCase();

                if(currentStatus === 'delivered') {
                    badge.className = "badge bg-success rounded-pill px-3 py-2";
                } else if(currentStatus === 'in transit' || currentStatus === 'claimed') {
                    badge.className = "badge bg-indigo text-white rounded-pill px-3 py-2";
                } else {
                    badge.className = "badge bg-warning text-dark rounded-pill px-3 py-2";
                }

                // 5. Render Timeline
                let html = '';
                if (d.trackings && d.trackings.length > 0) {
                    d.trackings.forEach((t, index) => {
                        const date = new Date(t.created_at).toLocaleString('id-ID', {
                            day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
                        });
                        const isActive = index === 0 ? 'active' : '';

                        html += `
                        <div class="timeline-node ${isActive}">
                            <div class="timeline-date">${date}</div>
                            <div class="timeline-title">${t.location}</div>
                            <div class="timeline-desc">${t.description}</div>
                        </div>`;
                    });
                } else {
                    html = '<div class="text-center py-4 text-muted small fw-bold"><i class="ph-info me-2"></i>Belum ada riwayat pergerakan.</div>';
                }
                document.getElementById('timelineContainer').innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                document.getElementById('timelineContainer').innerHTML = `
                    <div class="alert bg-danger text-white border-0">
                        Gagal memuat data tracking. Pastikan ID pengiriman valid.
                    </div>`;
            });
    }

    document.addEventListener('DOMContentLoaded', fetchTracking);
</script>
@endsection