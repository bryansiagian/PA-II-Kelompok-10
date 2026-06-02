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

    <!-- BANNER: DELAY -->
    <div id="bannerDelay" class="alert border-0 rounded-3 mb-4 d-none" style="background:#fff8e1; border-left:5px solid #ffc107 !important;">
        <div class="d-flex align-items-start gap-3">
            <i class="ph-clock-countdown fs-4" style="color:#e6a817; flex-shrink:0; margin-top:2px;"></i>
            <div class="flex-fill">
                <div class="fw-bold" style="color:#856404;">Kurir Melaporkan Keterlambatan</div>
                <div class="small text-muted mt-1">
                    Paket masih dalam perjalanan namun kemungkinan tiba lebih lambat dari estimasi awal.
                </div>
                <div id="delayReasonOp" class="small mt-1 fst-italic" style="color:#856404;"></div>
                <div id="delayTimeOp" class="small text-muted mt-1"></div>
            </div>
        </div>
    </div>

    <!-- BANNER: CANNOT CONTINUE — operator perlu tindakan -->
    <div id="bannerCancel" class="alert border-0 rounded-3 mb-4 d-none" style="background:#fff3e0; border-left:5px solid #fd7e14 !important;">
        <div class="d-flex align-items-start gap-3">
            <i class="ph-warning fs-4" style="color:#e8650a; flex-shrink:0; margin-top:2px;"></i>
            <div class="flex-fill">
                <div class="fw-bold" style="color:#7d3c0a;">
                    <i class="ph-warning-circle me-1"></i> Kurir Tidak Dapat Melanjutkan — Tindakan Diperlukan
                </div>
                <div class="small text-muted mt-1">
                    Kurir sebelumnya melaporkan tidak dapat melanjutkan pengiriman. Pesanan sudah kembali ke antrian dan menunggu penugasan kurir pengganti.
                </div>
                <div id="cancelReasonOp" class="small mt-1 fst-italic" style="color:#7d3c0a;"></div>
                <div class="mt-2">
                    <a href="/operator/orders" class="btn btn-sm btn-warning fw-bold rounded-pill">
                        <i class="ph-arrow-square-out me-1"></i> Assign Kurir Baru di Halaman Pesanan
                    </a>
                </div>
            </div>
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

                <!-- Estimasi tiba (hanya tampil jika In Transit dan ada tanggal) -->
                <div id="estimasiBar" class="d-none px-4 py-2" style="background:#e8f5e9; border-top:1px solid #c8e6c9;">
                    <small class="fw-bold" style="color:#2e7d32;">
                        <i class="ph-calendar-check me-1"></i>
                        Estimasi Tiba: <span id="estimasiTgl"></span>
                    </small>
                </div>

                <div class="bg-light p-3 px-4 border-top rounded-bottom-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <div class="small fw-bold text-dark">
                                <i class="ph-identification-badge me-2 text-indigo"></i>
                                <span id="courierName">Kurir: -</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="small fw-bold text-dark">
                                <i class="ph-truck me-2 text-indigo"></i>
                                <span id="vehicleInfo">Kendaraan: -</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted" id="lastUpdate">
                                <i class="ph-clock-clockwise me-1"></i> Update: -
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 2: BUKTI FOTO (Hanya muncul jika Delivered) -->
            <div id="proofSection" class="card border-0 shadow-sm rounded-3 mb-4 d-none">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="ph-camera me-2 text-success"></i>Foto Bukti Penerimaan</h6>
                    <div class="row align-items-center">
                        <div class="col-md-5 text-center mb-3 mb-md-0">
                            <div class="bg-light p-2 rounded-3 d-inline-block border">
                                <img
                                    id="proofImg"
                                    src=""
                                    class="img-fluid rounded-3 shadow-sm"
                                    style="max-height: 250px; cursor: zoom-in;"
                                    onclick="window.open(this.src)">
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="bg-light p-3 rounded-3 border-start border-start-width-5 border-start-success">
                                <div class="mb-2">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Diterima Oleh</small>
                                    <div class="fw-bold text-dark" id="receiverName">-</div>
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
                        <div class="fw-bold text-dark" id="destName">-</div>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 10px;">Alamat Lengkap</small>
                        <div class="text-dark small" id="destAddress">-</div>
                    </div>
                </div>
            </div>

            <!-- INFO KURIR & KENDARAAN -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold m-0">Informasi Kurir</h6>
                </div>
                <div class="card-body" id="courierCard">
                    <div class="text-muted small fst-italic">Belum ada kurir ditugaskan.</div>
                </div>
            </div>

            <!-- INFO KENDALA (hanya muncul jika ada) -->
            <div id="issueCard" class="card border-0 shadow-sm rounded-3 mb-4 d-none">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="fw-bold m-0 text-warning">
                        <i class="ph-warning me-2"></i>Detail Laporan Kendala
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:10px;">Tipe Kendala</small>
                        <div id="issueType" class="fw-bold text-dark">-</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:10px;">Keterangan</small>
                        <div id="issueReason" class="text-dark small">-</div>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:10px;">Dilaporkan Pada</small>
                        <div id="issueTime" class="text-dark small">-</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
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
    .timeline-node.node-delay::before {
        border-color: #ffc107; background: #ffc107;
        box-shadow: 0 0 0 5px rgba(255, 193, 7, 0.15);
    }
    .timeline-node.node-cancel::before {
        border-color: #fd7e14; background: #fd7e14;
        box-shadow: 0 0 0 5px rgba(253, 126, 20, 0.15);
    }
    .timeline-node:last-child { padding-bottom: 0; }
    .timeline-date  { font-size: 11px; font-weight: 800; color: #adb5bd; text-transform: uppercase; }
    .timeline-title { font-weight: 700; color: #2c3e50; margin-top: 2px; }
    .timeline-desc  { font-size: 13px; color: #6c757d; line-height: 1.5; }
    .border-start-width-5 { border-left-width: 5px !important; }
    .text-indigo { color: #5c6bc0 !important; }
</style>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchTracking() {
        const deliveryId = "{{ $id }}";

        axios.get(`/api/deliveries/${deliveryId}/tracking`)
            .then(res => {
                const d = res.data;

                // ── 1. Info Resi ──────────────────────────────────────
                document.getElementById('trackNum').innerText   = d.tracking_number || 'N/A';
                document.getElementById('lastUpdate').innerText =
                    `Update: ${new Date(d.updated_at).toLocaleString('id-ID')} WIB`;

                document.getElementById('courierName').innerText = d.courier
                    ? `Kurir: ${d.courier.name}`
                    : 'Kurir: Belum Ditentukan';

                if (d.vehicle) {
                    const v = d.vehicle;
                    document.getElementById('vehicleInfo').innerText =
                        `Kendaraan: ${v.brand} ${v.subtype} — ${v.plate_number} (${v.color})`;
                } else {
                    document.getElementById('vehicleInfo').innerText = 'Kendaraan: Belum Ditentukan';
                }

                // ── 2. Estimasi tiba ──────────────────────────────────
                const currentStatus = d.status ? d.status.name.toLowerCase() : '';
                if (currentStatus === 'in transit' && d.estimated_arrival) {
                    const tgl = new Date(d.estimated_arrival).toLocaleDateString('id-ID', {
                        weekday: 'long', day: '2-digit', month: 'long', year: 'numeric'
                    });
                    document.getElementById('estimasiTgl').innerText = tgl;
                    document.getElementById('estimasiBar').classList.remove('d-none');
                }

                // ── 3. Banner & card kendala ──────────────────────────
                if (d.issue_type === 'delay' && d.is_delayed) {
                    document.getElementById('bannerDelay').classList.remove('d-none');
                    document.getElementById('issueCard').classList.remove('d-none');

                    if (d.delay_reason) {
                        document.getElementById('delayReasonOp').innerText =
                            `Keterangan: "${d.delay_reason}"`;
                    }
                    if (d.delay_reported_at) {
                        document.getElementById('delayTimeOp').innerText =
                            `Dilaporkan: ${new Date(d.delay_reported_at).toLocaleString('id-ID')} WIB`;
                    }

                    document.getElementById('issueType').innerText    = 'Keterlambatan (Delay)';
                    document.getElementById('issueReason').innerText  = d.delay_reason ?? '-';
                    document.getElementById('issueTime').innerText    = d.delay_reported_at
                        ? new Date(d.delay_reported_at).toLocaleString('id-ID') + ' WIB'
                        : '-';

                } else if (d.issue_type === 'cannot_continue') {
                    document.getElementById('bannerCancel').classList.remove('d-none');
                    document.getElementById('issueCard').classList.remove('d-none');

                    if (d.delay_reason) {
                        document.getElementById('cancelReasonOp').innerText =
                            `Alasan: "${d.delay_reason}"`;
                    }

                    document.getElementById('issueType').innerText    = 'Tidak Dapat Melanjutkan';
                    document.getElementById('issueReason').innerText  = d.delay_reason ?? '-';
                    document.getElementById('issueTime').innerText    = d.delay_reported_at
                        ? new Date(d.delay_reported_at).toLocaleString('id-ID') + ' WIB'
                        : '-';
                }

                // ── 4. Card kurir di sidebar ──────────────────────────
                const courierCard = document.getElementById('courierCard');
                if (d.courier) {
                    const v = d.vehicle;
                    courierCard.innerHTML = `
                        <div class="mb-3">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:10px">Nama Kurir</small>
                            <div class="fw-bold text-dark">${d.courier.name}</div>
                            <div class="text-muted small">${d.courier.email ?? ''}</div>
                        </div>
                        ${v ? `
                        <div class="mb-0">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:10px">Kendaraan</small>
                            <div class="fw-bold text-dark">${v.brand} ${v.subtype}</div>
                            <div class="text-muted small">${v.plate_number} &bull; ${v.color}</div>
                            <span class="badge bg-light text-dark border mt-1">
                                ${v.type === 'car'
                                    ? '<i class="ph-truck me-1"></i>Mobil'
                                    : '<i class="ph-motorcycle me-1"></i>Motor'}
                            </span>
                        </div>` : `
                        <div class="text-muted small fst-italic">Kendaraan belum ditentukan.</div>`}
                    `;
                } else {
                    courierCard.innerHTML = '<div class="text-muted small fst-italic">Belum ada kurir ditugaskan.</div>';
                }

                // ── 5. Info Tujuan ────────────────────────────────────
                if (d.order) {
                    const order    = d.order;
                    const village  = order.village          ?? '';
                    const district = order.district         ?? '';
                    const regency  = order.regency          ?? '';
                    const detail   = order.shipping_address ?? '';

                    const addressParts = [
                        detail,
                        village  ? `Kel. ${village}`  : '',
                        district ? `Kec. ${district}` : '',
                        regency,
                        regency  ? 'Sumatera Utara'   : '',
                    ].filter(Boolean);

                    document.getElementById('destName').innerText    = order.user?.name ?? '-';
                    document.getElementById('destAddress').innerText =
                        addressParts.length ? addressParts.join(', ') : 'Alamat tidak tersedia';
                }

                // ── 6. Badge Status ───────────────────────────────────
                const badge = document.getElementById('badgeStatus');
                badge.innerText = d.status?.name ?? 'Unknown';

                if (currentStatus === 'delivered') {
                    badge.className = 'badge bg-success rounded-pill px-3 py-2 fs-6';
                } else if (currentStatus === 'in transit') {
                    badge.className = 'badge bg-primary rounded-pill px-3 py-2 fs-6';
                } else if (currentStatus === 'claimed') {
                    badge.className = 'badge bg-indigo text-white rounded-pill px-3 py-2 fs-6';
                } else if (currentStatus === 'ready') {
                    badge.className = 'badge bg-warning text-dark rounded-pill px-3 py-2 fs-6';
                } else {
                    badge.className = 'badge bg-secondary rounded-pill px-3 py-2 fs-6';
                }

                // ── 7. Bukti Foto ─────────────────────────────────────
                if (currentStatus === 'delivered') {
                    document.getElementById('proofSection').classList.remove('d-none');
                    const proofImg = document.getElementById('proofImg');
                    if (d.image) {
                        proofImg.src           = `/storage/${d.image}`;
                        proofImg.style.display = 'block';
                    } else {
                        proofImg.style.display = 'none';
                    }
                    document.getElementById('receiverName').innerText     = d.receiver_name     || 'N/A';
                    document.getElementById('receiverRelation').innerText = d.receiver_relation || 'N/A';
                }

                // ── 8. Timeline ───────────────────────────────────────
                const allEvents = [];

                if (d.courier && d.created_at) {
                    allEvents.push({
                        created_at:  d.created_at,
                        location:    'Gudang / Operator',
                        description: `Kurir ${d.courier.name} ditugaskan untuk menjemput pesanan.`,
                        _synthetic:  true,
                    });
                }

                if (d.trackings && d.trackings.length > 0) {
                    d.trackings.forEach(t => allEvents.push(t));
                }

                allEvents.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

                let html = '';
                if (allEvents.length > 0) {
                    allEvents.forEach((t, index) => {
                        const date = new Date(t.created_at).toLocaleString('id-ID', {
                            day: '2-digit', month: 'short', year: 'numeric',
                            hour: '2-digit', minute: '2-digit'
                        });

                        let nodeClass = index === 0 ? 'active' : '';
                        const desc    = t.description ?? '';
                        if (desc.toLowerCase().includes('keterlambatan')) nodeClass = 'node-delay';
                        if (desc.toLowerCase().includes('tidak dapat melanjutkan')) nodeClass = 'node-cancel';

                        html += `
                        <div class="timeline-node ${nodeClass}">
                            <div class="timeline-date">${date}</div>
                            <div class="timeline-title">${t.location}</div>
                            <div class="timeline-desc">${desc}</div>
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
