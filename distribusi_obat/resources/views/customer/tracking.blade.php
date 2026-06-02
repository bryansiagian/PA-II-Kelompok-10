@extends('layouts.portal')

@section('content')
<style>
    :root {
        --primary: #00838f;
        --secondary: #2c4964;
        --hover-color: #006064;
    }

    .text-accent { color: var(--primary) !important; }

    #timelineContainer { border-left: 2px solid #e0ebec; padding-left: 35px; position: relative; }
    .timeline-node { position: relative; padding-bottom: 2.5rem; }
    .timeline-node::before {
        content: ''; position: absolute; left: -46px; top: 0;
        width: 20px; height: 20px; background: #fff;
        border: 4px solid #dee2e6; border-radius: 50%; z-index: 2;
    }
    .timeline-node.active::before {
        border-color: var(--primary); background: var(--primary);
        box-shadow: 0 0 0 6px rgba(0, 131, 143, 0.1);
    }
    .timeline-node.node-delay::before {
        border-color: #ffc107; background: #ffc107;
        box-shadow: 0 0 0 6px rgba(255, 193, 7, 0.15);
    }
    .timeline-node.node-cancel::before {
        border-color: #fd7e14; background: #fd7e14;
        box-shadow: 0 0 0 6px rgba(253, 126, 20, 0.15);
    }
    .timeline-node:last-child { padding-bottom: 0; }
    .timeline-date { font-size: 11px; font-weight: 700; color: #9eb5b6; text-transform: uppercase; }
    .timeline-title { font-weight: 700; color: var(--secondary); margin-top: 2px; }
    .timeline-desc { font-size: 13px; color: #6c757d; line-height: 1.5; }
    .italic { font-style: italic; }
</style>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-9">

            <!-- HEADER & TOMBOL KEMBALI -->
            <div class="d-flex align-items-center mb-4">
                <a href="/customer/history" class="btn btn-outline-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="fw-bold m-0" style="color: var(--secondary);">Detail Pelacakan Paket</h3>
            </div>

            <!-- BANNER: DELAY -->
            <div id="bannerDelay" class="alert border-0 rounded-4 mb-4 d-none" style="background:#fff8e1; border-left: 5px solid #ffc107 !important;">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-clock-history fs-4" style="color:#e6a817; flex-shrink:0; margin-top:2px;"></i>
                    <div>
                        <div class="fw-bold" style="color:#856404;">Paket Mengalami Keterlambatan</div>
                        <div class="small text-muted mt-1">
                            Kurir melaporkan kendala dalam perjalanan. Paket tetap akan diantarkan, namun mungkin tiba lebih lambat dari estimasi awal.
                        </div>
                        <div id="delayReasonText" class="small mt-1 fst-italic" style="color:#856404;"></div>
                    </div>
                </div>
            </div>

            <!-- BANNER: CANNOT CONTINUE (kurir dilepas, menunggu kurir baru) -->
            <div id="bannerCancel" class="alert border-0 rounded-4 mb-4 d-none" style="background:#fff3e0; border-left: 5px solid #fd7e14 !important;">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-exclamation-triangle-fill fs-4" style="color:#e8650a; flex-shrink:0; margin-top:2px;"></i>
                    <div>
                        <div class="fw-bold" style="color:#7d3c0a;">Pengiriman Mengalami Kendala</div>
                        <div class="small text-muted mt-1">
                            Kurir sebelumnya tidak dapat melanjutkan pengiriman. Tim kami sedang mencarikan kurir pengganti untuk mengantarkan paket Anda. Mohon tunggu konfirmasi lebih lanjut.
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 1: INFO UTAMA RESI -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="border-top: 5px solid var(--primary) !important;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <small class="text-uppercase fw-bold text-muted" style="font-size: 11px; letter-spacing: 1px;">Nomor Resi Pengiriman</small>
                            <h4 class="fw-bold m-0" style="color: var(--primary);" id="trackNum">-------</h4>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <small class="text-muted d-block mb-1 small text-uppercase">Status Terkini</small>
                            <span id="badgeStatus" class="badge rounded-pill px-4 py-2 shadow-sm fs-6">Memuat...</span>
                        </div>
                    </div>
                </div>

                <!-- Estimasi tiba (hanya tampil jika In Transit dan ada tanggal) -->
                <div id="estimasiBar" class="d-none px-4 py-2" style="background:#e8f5e9; border-top: 1px solid #c8e6c9;">
                    <small class="fw-bold" style="color:#2e7d32;">
                        <i class="bi bi-calendar-check me-2"></i>
                        Estimasi Tiba: <span id="estimasiTgl"></span>
                    </small>
                </div>

                <div class="bg-light p-3 px-4 d-flex justify-content-between align-items-center border-top">
                    <div class="small fw-bold text-dark">
                        <i class="bi bi-person-badge me-2 text-accent"></i> <span id="courierName">Kurir: -</span>
                    </div>
                    <div class="small fw-bold text-dark">
                        <i class="bi bi-truck me-2 text-accent"></i> <span id="vehicleInfo">Kendaraan: -</span>
                    </div>
                    <div class="small text-muted" id="lastUpdate">Update: -</div>
                </div>
            </div>

            <!-- CARD 2: BUKTI FOTO (Hanya muncul jika Delivered) -->
            <div id="proofSection" class="card border-0 shadow-sm rounded-4 mb-4 d-none">
                <div class="card-body p-4 text-center">
                    <h6 class="fw-bold mb-3 text-start"><i class="bi bi-camera-fill me-2 text-accent"></i>Konfirmasi Foto Penerimaan</h6>
                    <div class="bg-light p-2 rounded-4 d-inline-block border">
                        <img id="proofImg" src="" class="img-fluid rounded-4 shadow-sm" style="max-height: 350px; cursor: zoom-in;" onclick="window.open(this.src)">
                    </div>
                    <p class="text-muted small mt-3 mb-0 italic">Paket telah diterima dengan sukses di lokasi tujuan.</p>
                </div>
            </div>

            <!-- CARD 3: TIMELINE PERJALANAN -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                <h5 class="fw-bold mb-4" style="color: var(--secondary);">
                    <i class="bi bi-clock-history me-2 text-accent"></i>Riwayat Perjalanan
                </h5>
                <div id="timelineContainer" class="ms-2">
                    <div class="text-center py-5">
                        <div class="spinner-border text-accent" role="status"></div>
                        <p class="mt-2 text-muted">Menghubungkan ke satelit logistik...</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchTracking() {
        const id = "{{ $id }}";

        axios.get(`/api/deliveries/${id}/tracking`)
            .then(res => {
                const d = res.data;

                // ── 1. Info Resi ──────────────────────────────────────
                document.getElementById('trackNum').innerText    = d.tracking_number || 'N/A';
                document.getElementById('courierName').innerText = d.courier
                    ? `Kurir: ${d.courier.name}`
                    : 'Kurir: Mencari Kurir...';
                document.getElementById('lastUpdate').innerText  =
                    `Update: ${new Date(d.updated_at).toLocaleString('id-ID')} WIB`;

                if (d.vehicle) {
                    const v = d.vehicle;
                    document.getElementById('vehicleInfo').innerText =
                        `Kendaraan: ${v.brand} ${v.subtype} — ${v.plate_number} (${v.color})`;
                } else {
                    document.getElementById('vehicleInfo').innerText = 'Kendaraan: Belum Ditentukan';
                }

                // ── 2. Badge Status ───────────────────────────────────
                const currentStatus = d.status ? d.status.name.toLowerCase() : '';
                const badge         = document.getElementById('badgeStatus');
                badge.innerText     = d.status?.name ?? 'Unknown';

                if (currentStatus === 'delivered') {
                    badge.className = 'badge bg-success rounded-pill px-4 py-2 shadow-sm fs-6';
                } else if (currentStatus === 'in transit') {
                    badge.className = 'badge rounded-pill px-4 py-2 shadow-sm fs-6 text-white';
                    badge.style.backgroundColor = 'var(--primary)';
                } else if (currentStatus === 'claimed') {
                    badge.className = 'badge bg-info text-dark rounded-pill px-4 py-2 shadow-sm fs-6';
                } else {
                    badge.className = 'badge bg-warning text-dark rounded-pill px-4 py-2 shadow-sm fs-6';
                }

                // ── 3. Estimasi tiba ──────────────────────────────────
                if (currentStatus === 'in transit' && d.estimated_arrival) {
                    const tgl = new Date(d.estimated_arrival).toLocaleDateString('id-ID', {
                        weekday: 'long', day: '2-digit', month: 'long', year: 'numeric'
                    });
                    document.getElementById('estimasiTgl').innerText = tgl;
                    document.getElementById('estimasiBar').classList.remove('d-none');
                }

                // ── 4. Banner kendala ─────────────────────────────────
                if (d.issue_type === 'delay' && d.is_delayed) {
                    document.getElementById('bannerDelay').classList.remove('d-none');
                    if (d.delay_reason) {
                        document.getElementById('delayReasonText').innerText =
                            `Keterangan: "${d.delay_reason}"`;
                    }
                } else if (d.issue_type === 'cannot_continue') {
                    document.getElementById('bannerCancel').classList.remove('d-none');
                }

                // ── 5. Bukti Foto ─────────────────────────────────────
                if (currentStatus === 'delivered') {
                    document.getElementById('proofSection').classList.remove('d-none');
                    const proofImg = document.getElementById('proofImg');
                    if (d.image) {
                        proofImg.src           = `/storage/${d.image}`;
                        proofImg.style.display = 'block';
                    } else {
                        proofImg.style.display = 'none';
                    }
                }

                // ── 6. Timeline ───────────────────────────────────────
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

                        // Beri highlight khusus untuk event kendala
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
                    html = '<div class="text-center py-4 text-muted small italic">Belum ada riwayat pergerakan paket.</div>';
                }

                document.getElementById('timelineContainer').innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                document.getElementById('timelineContainer').innerHTML = `
                    <div class="alert alert-danger border-0 shadow-sm text-center">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Gagal memproses data pelacakan.
                    </div>`;
            });
    }

    document.addEventListener('DOMContentLoaded', fetchTracking);
</script>
@endsection
