@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">

    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0 text-dark">Tugas Pengiriman Aktif</h4>
            <div class="text-muted small">Kelola paket yang sedang Anda bawa atau dalam perjalanan</div>
        </div>
        <div class="ms-3">
            <button onclick="fetchActive()" class="btn btn-light btn-icon shadow-sm rounded-circle">
                <i class="ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- LIST TUGAS AKTIF -->
    <div id="activeList" class="row g-3"></div>

</div>


{{-- ============================================================
     MODAL: MULAI PERJALANAN (input estimasi tiba)
============================================================ --}}
<div class="modal fade" id="modalStartShipping" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">

            <div class="modal-header bg-primary text-white border-0 py-3">
                <h6 class="modal-title fw-bold">
                    <i class="ph-play-circle me-2"></i>Mulai Perjalanan
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formStartShipping" onsubmit="submitStartShipping(event)">
                <div class="modal-body p-4">

                    <input type="hidden" id="start_delivery_id">

                    <p class="text-muted small mb-3">
                        Masukkan perkiraan tanggal tiba di tujuan. Informasi ini akan ditampilkan ke customer di halaman tracking.
                    </p>

                    <div class="mb-0">
                        <label class="small fw-bold text-muted mb-1">
                            Estimasi Tanggal Tiba <span class="text-danger">*</span>
                        </label>
                        <input
                            type="date"
                            id="estimated_arrival"
                            name="estimated_arrival"
                            class="form-control border-light-subtle"
                            required>
                        <div class="form-text">Pertimbangkan jarak, kondisi jalan, dan cuaca saat ini.</div>
                    </div>

                </div>

                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" id="btnSubmitStart" class="btn btn-primary px-4 fw-bold shadow-sm rounded-pill">
                        <i class="ph-play-circle me-1"></i> MULAI SEKARANG
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


{{-- ============================================================
     MODAL: LAPORKAN KENDALA
============================================================ --}}
<div class="modal fade" id="modalReportIssue" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">

            <div class="modal-header bg-warning text-dark border-0 py-3">
                <h6 class="modal-title fw-bold">
                    <i class="ph-warning me-2"></i>Laporkan Kendala
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formReportIssue" onsubmit="submitReportIssue(event)">
                <div class="modal-body p-4">

                    <input type="hidden" id="issue_delivery_id">

                    <!-- Pilihan tipe kendala -->
                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-2">Tipe Kendala <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2">

                            <label class="issue-option flex-fill" id="opt-delay">
                                <input type="radio" name="issue_type" value="delay" required class="d-none">
                                <div class="issue-option-box text-center p-3 rounded-3 border" onclick="selectIssue('delay')">
                                    <i class="ph-clock-countdown fs-4 d-block mb-1 text-warning"></i>
                                    <div class="fw-bold small">Keterlambatan</div>
                                    <div class="text-muted" style="font-size:11px">Paket tetap saya antar,<br>tapi akan telat</div>
                                </div>
                            </label>

                            <label class="issue-option flex-fill" id="opt-cannot">
                                <input type="radio" name="issue_type" value="cannot_continue" required class="d-none">
                                <div class="issue-option-box text-center p-3 rounded-3 border" onclick="selectIssue('cannot_continue')">
                                    <i class="ph-x-circle fs-4 d-block mb-1 text-danger"></i>
                                    <div class="fw-bold small">Tidak Bisa Lanjut</div>
                                    <div class="text-muted" style="font-size:11px">Kecelakaan / kondisi darurat,<br>kurir lain yang antar</div>
                                </div>
                            </label>

                        </div>
                    </div>

                    <!-- Peringatan untuk cannot_continue -->
                    <div id="warningCannotContinue" class="alert alert-danger d-none p-2 small mb-3">
                        <i class="ph-warning-circle me-1"></i>
                        <strong>Perhatian:</strong> Memilih ini akan melepaskan Anda dari tugas ini. Admin akan menunjuk kurir pengganti.
                    </div>

                    <!-- Alasan -->
                    <div class="mb-0">
                        <label class="small fw-bold text-muted mb-1">
                            Keterangan <span class="text-danger">*</span>
                        </label>
                        <textarea
                            name="reason"
                            id="issue_reason"
                            class="form-control border-light-subtle"
                            rows="3"
                            placeholder="Jelaskan kendala yang terjadi..."
                            required></textarea>
                    </div>

                </div>

                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" id="btnSubmitIssue" class="btn btn-warning px-4 fw-bold shadow-sm rounded-pill text-dark">
                        <i class="ph-paper-plane-tilt me-1"></i> KIRIM LAPORAN
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


{{-- ============================================================
     MODAL: SELESAIKAN PENGIRIMAN
============================================================ --}}
<div class="modal fade" id="modalComplete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">

            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold">
                    <i class="ph-check-circle me-2"></i>Konfirmasi Sampai Tujuan
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formComplete" onsubmit="submitComplete(event)">
                <div class="modal-body p-4">

                    <input type="hidden" id="complete_delivery_id">

                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Nama Penerima</label>
                        <input
                            type="text"
                            name="receiver_name"
                            class="form-control border-light-subtle"
                            placeholder="Siapa yang menerima paket?"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Hubungan Penerima</label>
                        <select name="receiver_relation" class="form-select border-light-subtle" required>
                            <option value="" selected disabled>-- Pilih Hubungan --</option>
                            <option value="Staff Farmasi">Staff Farmasi</option>
                            <option value="Perawat/Dokter">Perawat/Dokter</option>
                            <option value="Resepsionis">Resepsionis</option>
                            <option value="Keamanan/Satpam">Keamanan/Satpam</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Foto Bukti Terima</label>

                        <input type="file" name="image" id="proofImage" class="d-none" accept="image/*" required>

                        <div class="text-center mb-2">
                            <img
                                id="previewImage"
                                src=""
                                style="display:none; width:100%; max-height:250px; object-fit:cover; border-radius:12px; border:1px solid #ddd;">
                        </div>

                        <div class="text-center">
                            <video id="camera" autoplay playsinline style="display:none; width:100%; border-radius:12px;"></video>
                            <canvas id="canvas" style="display:none;"></canvas>
                        </div>

                        <div class="d-flex gap-2 mt-2">
                            <button type="button" id="btnOpenCamera" class="btn btn-sm btn-indigo w-100" onclick="openCamera()">
                                <i class="ph-camera me-1"></i> Buka Kamera
                            </button>
                            <button
                                type="button"
                                id="captureBtn"
                                class="btn btn-sm btn-success w-100"
                                style="display:none;"
                                onclick="capturePhoto()">
                                <i class="ph-aperture me-1"></i> Ambil Foto
                            </button>
                        </div>

                        <div id="photoStatus" class="small text-muted mt-2 text-center"></div>
                    </div>

                    <div class="mb-0">
                        <label class="small fw-bold text-muted mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea
                            name="delivery_note"
                            class="form-control border-light-subtle"
                            rows="2"
                            placeholder="Catatan lokasi penyerahan..."></textarea>
                    </div>

                </div>

                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" id="btnSubmitComplete" class="btn btn-indigo px-4 fw-bold shadow-sm rounded-pill">
                        KONFIRMASI SELESAI
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<script>

axios.defaults.headers.common['Authorization'] =
    'Bearer ' + '{{ session('api_token') }}';

let cameraStream = null;

/* =========================
   SKELETON
========================= */
function showSkeletons() {
    let html = '';
    for (let i = 0; i < 4; i++) {
        html += `
        <div class="col-md-6 col-lg-5 mb-4">
            <div class="active-delivery-card">
                <div class="delivery-header">
                    <div style="flex:1">
                        <span class="skeleton-line mb-2" style="width:${140 + i * 20}px;height:18px;"></span>
                        <span class="skeleton-line d-block" style="width:100px;height:12px;"></span>
                    </div>
                    <span class="skeleton-line ms-3" style="width:54px;height:26px;border-radius:20px;"></span>
                </div>
                <div class="delivery-body">
                    <span class="skeleton-line d-block mb-2" style="width:${120 + i * 15}px;height:15px;"></span>
                    <span class="skeleton-line d-block mb-1" style="width:90%;height:13px;"></span>
                    <span class="skeleton-line d-block mb-3" style="width:70%;height:12px;"></span>
                    <span class="skeleton-line d-block mb-3" style="width:160px;height:32px;border-radius:8px;"></span>
                    <span class="skeleton-line d-block" style="width:100%;height:40px;border-radius:10px;"></span>
                </div>
            </div>
        </div>`;
    }
    document.getElementById('activeList').innerHTML = html;
}

/* =========================
   FETCH ACTIVE DELIVERY
========================= */
function fetchActive() {

    showSkeletons();

    const container = document.getElementById('activeList');

    axios.get('/api/deliveries/active')
    .then(res => {

        let html = '';
        const data = res.data;

        if (data.length === 0) {
            html = `
                <div class="col-12 text-center py-5 text-muted">
                    <i class="ph-package fs-1 d-block mb-2 opacity-25"></i>
                    Tidak ada tugas pengiriman aktif
                </div>`;
        } else {

            data.forEach(d => {

                const rawStatus  = d.status ? d.status.name : 'Unknown';
                const statusName = rawStatus.toLowerCase();

                const order           = d.order ?? {};
                const village         = order.village          ?? '';
                const district        = order.district         ?? '';
                const regency         = order.regency          ?? '';
                const shippingAddress = order.shipping_address ?? '';

                const createdAt = d.created_at
                    ? new Date(d.created_at).toLocaleString('id-ID', {
                        day: '2-digit', month: 'short', year: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                      })
                    : '-';

                /* ---- Kendaraan ---- */
                const vehicle = d.vehicle;
                const vehicleHtml = vehicle
                    ? `<div class="vehicle-info mt-2">
                           <i class="ph-car me-1 text-indigo"></i>
                           <span class="fw-semibold">${vehicle.brand} ${vehicle.subtype}</span>
                           <span class="text-muted ms-1">· ${vehicle.plate_number} · ${vehicle.color}</span>
                       </div>`
                    : `<div class="vehicle-info mt-2 text-muted fst-italic">
                           <i class="ph-car me-1"></i> Kendaraan belum diassign
                       </div>`;

                /* ---- Estimasi & delay ---- */
                let estimasiHtml = '';
                if (statusName === 'in transit') {
                    if (d.estimated_arrival) {
                        const tgl = new Date(d.estimated_arrival).toLocaleDateString('id-ID', {
                            day: '2-digit', month: 'long', year: 'numeric'
                        });
                        estimasiHtml = `
                            <div class="estimasi-info mt-2">
                                <i class="ph-calendar-check me-1 text-success"></i>
                                Estimasi tiba: <strong>${tgl}</strong>
                            </div>`;
                    }
                    if (d.is_delayed) {
                        estimasiHtml += `
                            <div class="delay-badge mt-1">
                                <i class="ph-warning me-1"></i> DELAY — ${d.delay_reason ?? ''}
                            </div>`;
                    }
                }

                /* ---- Tombol aksi ---- */
                let actionBtn = '';
                if (statusName === 'claimed') {
                    actionBtn = `
                        <button onclick="openStartModal('${d.id}')" class="btn btn-primary btn-delivery">
                            <i class="ph-play-circle me-1"></i> MULAI PERJALANAN
                        </button>`;
                } else {
                    // In Transit — tombol selesai + tombol laporkan kendala
                    actionBtn = `
                        <div class="d-flex gap-2">
                            <button onclick="openCompleteModal('${d.id}')" class="btn btn-success btn-delivery flex-fill">
                                <i class="ph-check-circle me-1"></i> KONFIRMASI SAMPAI
                            </button>
                            <button onclick="openIssueModal('${d.id}')" class="btn btn-warning btn-delivery-sm" title="Laporkan Kendala">
                                <i class="ph-warning"></i>
                            </button>
                        </div>`;
                }

                html += `
<div class="col-md-6 col-lg-5 mb-4">
    <div class="active-delivery-card">

        <div class="delivery-header">
            <div>
                <span class="tracking-number">${d.tracking_number}</span>
                <div class="delivery-time">
                    <i class="ph-clock me-1"></i>${createdAt}
                </div>
            </div>
            <span class="status-badge active">AKTIF</span>
        </div>

        <div class="delivery-body">

            <div class="destination-info">

                <div class="destination-name">
                    <i class="ph-user-circle me-1 text-muted" style="font-size:14px"></i>
                    ${d.order?.user?.name ?? 'Customer'}
                </div>

                <div class="destination-address mt-2">
                    <i class="ph-map-pin me-1"></i>
                    ${shippingAddress
                        ? `<span class="fw-semibold d-block text-dark">${shippingAddress}</span>`
                        : ''}
                    ${(village || district || regency)
                        ? `<span class="text-muted" style="font-size:12px">
                               ${village  ? `Kel. ${village}, ` : ''}
                               ${district ? `Kec. ${district}, ` : ''}
                               ${regency  ? `${regency}, Sumatera Utara` : ''}
                           </span>`
                        : (!shippingAddress ? '<span class="text-muted fst-italic">Alamat tidak tersedia</span>' : '')}
                </div>

                ${vehicleHtml}
                ${estimasiHtml}

            </div>

            <div class="delivery-actions mt-3">
                ${actionBtn}
            </div>

        </div>

    </div>
</div>`;
            });
        }

        container.innerHTML = html;
    })
    .catch(err => {
        console.error(err);
        container.innerHTML = `
            <div class="text-danger text-center py-5">
                <i class="ph-warning-circle fs-3 d-block mb-2"></i>
                Gagal memuat data pengiriman
            </div>`;
    });
}

/* =========================
   OPEN MODAL: MULAI PERJALANAN
========================= */
function openStartModal(id) {
    document.getElementById('start_delivery_id').value = id;
    document.getElementById('formStartShipping').reset();

    // Set min date = hari ini
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('estimated_arrival').min = today;
    document.getElementById('estimated_arrival').value = today;

    new bootstrap.Modal(document.getElementById('modalStartShipping')).show();
}

/* =========================
   SUBMIT: MULAI PERJALANAN
========================= */
function submitStartShipping(e) {
    e.preventDefault();

    const id  = document.getElementById('start_delivery_id').value;
    const tgl = document.getElementById('estimated_arrival').value;

    const btn          = document.getElementById('btnSubmitStart');
    const originalHtml = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

    axios.post(`/api/deliveries/start/${id}`, { estimated_arrival: tgl })
    .then(() => {
        bootstrap.Modal.getInstance(document.getElementById('modalStartShipping')).hide();
        Swal.fire({
            icon: 'success',
            title: 'Perjalanan Dimulai!',
            text: 'Selamat bertugas. Estimasi tiba telah disimpan.',
            timer: 2200,
            showConfirmButton: false,
            timerProgressBar: true,
        });
        fetchActive();
    })
    .catch(err => {
        Swal.fire('Gagal', err.response?.data?.message ?? 'Terjadi kesalahan.', 'error');
    })
    .finally(() => {
        btn.disabled  = false;
        btn.innerHTML = originalHtml;
    });
}

/* =========================
   OPEN MODAL: LAPORKAN KENDALA
========================= */
function openIssueModal(id) {
    document.getElementById('issue_delivery_id').value = id;
    document.getElementById('formReportIssue').reset();
    document.getElementById('warningCannotContinue').classList.add('d-none');

    // Reset visual pilihan
    document.querySelectorAll('.issue-option-box').forEach(el => {
        el.classList.remove('selected-delay', 'selected-cannot');
    });

    new bootstrap.Modal(document.getElementById('modalReportIssue')).show();
}

/* ---- Highlight pilihan tipe kendala ---- */
function selectIssue(type) {
    const boxDelay   = document.querySelector('#opt-delay .issue-option-box');
    const boxCannot  = document.querySelector('#opt-cannot .issue-option-box');
    const warning    = document.getElementById('warningCannotContinue');

    boxDelay.classList.remove('selected-delay', 'selected-cannot');
    boxCannot.classList.remove('selected-delay', 'selected-cannot');

    // Set radio value
    document.querySelector(`input[name="issue_type"][value="${type}"]`).checked = true;

    if (type === 'delay') {
        boxDelay.classList.add('selected-delay');
        warning.classList.add('d-none');
    } else {
        boxCannot.classList.add('selected-cannot');
        warning.classList.remove('d-none');
    }
}

/* =========================
   SUBMIT: LAPORKAN KENDALA
========================= */
function submitReportIssue(e) {
    e.preventDefault();

    const id     = document.getElementById('issue_delivery_id').value;
    const type   = document.querySelector('input[name="issue_type"]:checked')?.value;
    const reason = document.getElementById('issue_reason').value.trim();

    if (!type) {
        Swal.fire('Pilih Tipe Kendala', 'Pilih salah satu tipe kendala terlebih dahulu.', 'warning');
        return;
    }

    const isCannotContinue = type === 'cannot_continue';
    const confirmText = isCannotContinue
        ? 'Anda akan dilepas dari tugas ini dan admin akan menunjuk kurir pengganti. Lanjutkan?'
        : 'Laporan keterlambatan akan dikirim ke admin dan customer.';

    Swal.fire({
        title: isCannotContinue ? 'Lepas dari Tugas?' : 'Kirim Laporan Delay?',
        text: confirmText,
        icon: isCannotContinue ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Kirim',
        cancelButtonText: 'Batal',
        confirmButtonColor: isCannotContinue ? '#dc3545' : '#ffc107',
    })
    .then(result => {
        if (!result.isConfirmed) return;

        const btn          = document.getElementById('btnSubmitIssue');
        const originalHtml = btn.innerHTML;
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

        axios.post(`/api/deliveries/report-issue/${id}`, { issue_type: type, reason })
        .then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalReportIssue')).hide();
            Swal.fire({
                icon: 'success',
                title: isCannotContinue ? 'Tugas Dilepas' : 'Laporan Terkirim',
                text: isCannotContinue
                    ? 'Admin akan menunjuk kurir pengganti.'
                    : 'Laporan delay berhasil dikirim.',
                timer: 2500,
                showConfirmButton: false,
                timerProgressBar: true,
            });
            fetchActive();
        })
        .catch(err => {
            Swal.fire('Gagal', err.response?.data?.message ?? 'Terjadi kesalahan.', 'error');
        })
        .finally(() => {
            btn.disabled  = false;
            btn.innerHTML = originalHtml;
        });
    });
}

/* =========================
   OPEN MODAL COMPLETE
========================= */
function openCompleteModal(id) {
    document.getElementById('formComplete').reset();
    document.getElementById('previewImage').style.display  = 'none';
    document.getElementById('camera').style.display        = 'none';
    document.getElementById('captureBtn').style.display    = 'none';
    document.getElementById('btnOpenCamera').style.display = 'inline-block';
    document.getElementById('photoStatus').textContent     = '';
    document.getElementById('complete_delivery_id').value  = id;

    if (cameraStream) {
        cameraStream.getTracks().forEach(t => t.stop());
        cameraStream = null;
    }

    new bootstrap.Modal(document.getElementById('modalComplete')).show();
}

/* =========================
   SUBMIT COMPLETE
========================= */
function submitComplete(e) {
    e.preventDefault();

    const id       = document.getElementById('complete_delivery_id').value;
    const formData = new FormData(e.target);

    const proofInput = document.getElementById('proofImage');
    if (!proofInput.files || proofInput.files.length === 0) {
        Swal.fire('Foto Diperlukan', 'Silakan ambil foto bukti terima terlebih dahulu.', 'warning');
        return;
    }

    const btn          = document.getElementById('btnSubmitComplete');
    const originalHtml = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

    axios.post(`/api/deliveries/complete/${id}`, formData)
    .then(() => {
        if (cameraStream) {
            cameraStream.getTracks().forEach(t => t.stop());
            cameraStream = null;
        }

        bootstrap.Modal.getInstance(document.getElementById('modalComplete')).hide();

        Swal.fire({
            icon: 'success',
            title: 'Pengiriman Selesai!',
            text: 'Terima kasih, bukti penerimaan telah disimpan.',
            timer: 2500,
            showConfirmButton: false,
            timerProgressBar: true,
        });

        fetchActive();
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: err.response?.data?.message ?? 'Gagal mengunggah bukti.',
        });
    })
    .finally(() => {
        btn.disabled  = false;
        btn.innerHTML = originalHtml;
    });
}

/* =========================
   OPEN CAMERA
========================= */
function openCamera() {
    const video       = document.getElementById('camera');
    const captureBtn  = document.getElementById('captureBtn');
    const btnOpen     = document.getElementById('btnOpenCamera');
    const photoStatus = document.getElementById('photoStatus');

    navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } } })
    .then(stream => {
        cameraStream             = stream;
        video.srcObject          = stream;
        video.style.display      = 'block';
        captureBtn.style.display = 'inline-block';
        btnOpen.style.display    = 'none';
        photoStatus.textContent  = 'Arahkan kamera ke penerima, lalu tekan Ambil Foto.';
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Kamera Tidak Tersedia', 'Pastikan izin kamera sudah diberikan di browser.', 'error');
    });
}

/* =========================
   CAPTURE PHOTO
========================= */
function capturePhoto() {
    const video       = document.getElementById('camera');
    const canvas      = document.getElementById('canvas');
    const preview     = document.getElementById('previewImage');
    const photoStatus = document.getElementById('photoStatus');

    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);

    canvas.toBlob(function(blob) {
        const file         = new File([blob], 'bukti-terima.jpg', { type: 'image/jpeg' });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);

        document.getElementById('proofImage').files = dataTransfer.files;

        preview.src           = URL.createObjectURL(blob);
        preview.style.display = 'block';

        photoStatus.innerHTML = '<span class="text-success fw-bold"><i class="ph-check-circle me-1"></i>Foto berhasil diambil</span>';
    }, 'image/jpeg');

    if (cameraStream) {
        cameraStream.getTracks().forEach(t => t.stop());
        cameraStream = null;
    }

    video.style.display                                        = 'none';
    document.getElementById('captureBtn').style.display        = 'none';
    document.getElementById('btnOpenCamera').style.display     = 'inline-block';
    document.getElementById('btnOpenCamera').innerHTML         = '<i class="ph-camera me-1"></i> Ambil Ulang';
}

/* =========================
   INIT
========================= */
document.addEventListener('DOMContentLoaded', fetchActive);

</script>


<style>
/* ── Brand ── */
.bg-indigo   { background-color: #5c68e2 !important; }
.btn-indigo  { background-color: #5c68e2; color: #fff; border: none; }
.btn-indigo:hover { background-color: #4e59cf; color: #fff; }
.text-indigo { color: #5c68e2 !important; }

/* ── Card ── */
.active-delivery-card {
    background: #fff;
    border-left: 5px solid #5b5ce2;
    border-radius: 16px;
    padding: 18px;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
    height: 100%;
}

/* ── Header ── */
.delivery-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 14px;
}

.tracking-number {
    font-size: 17px;
    font-weight: 700;
    color: #4b4ded;
    margin-bottom: 4px;
    display: block;
    word-break: break-all;
}

.delivery-time {
    color: #888;
    font-size: 12px;
}

/* ── Status badge ── */
.status-badge.active {
    background: #d4f8df;
    color: #12a150;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
}

/* ── Destination ── */
.destination-name {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
}

.destination-address {
    color: #555;
    font-size: 13px;
    line-height: 1.5;
}

/* ── Vehicle info ── */
.vehicle-info {
    font-size: 13px;
    background: #f1f5ff;
    border-radius: 8px;
    padding: 6px 10px;
    display: inline-block;
}

/* ── Estimasi tiba ── */
.estimasi-info {
    font-size: 12px;
    color: #155724;
    background: #d1e7dd;
    border-radius: 8px;
    padding: 5px 10px;
    display: inline-block;
}

/* ── Delay badge ── */
.delay-badge {
    font-size: 11px;
    font-weight: 700;
    color: #856404;
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 8px;
    padding: 4px 10px;
    display: inline-block;
}

/* ── Buttons ── */
.btn-delivery {
    border-radius: 10px;
    padding: 10px;
    font-weight: 700;
    font-size: 14px;
}

.btn-delivery-sm {
    border-radius: 10px;
    padding: 10px 14px;
    font-weight: 700;
    font-size: 16px;
    flex-shrink: 0;
}

/* ── Issue option cards ── */
.issue-option-box {
    cursor: pointer;
    transition: all .2s;
    border-color: #dee2e6 !important;
}

.issue-option-box:hover {
    border-color: #adb5bd !important;
    background: #f8f9fa;
}

.issue-option-box.selected-delay {
    border-color: #ffc107 !important;
    background: #fffbeb;
}

.issue-option-box.selected-cannot {
    border-color: #dc3545 !important;
    background: #fff5f5;
}

/* ── Skeleton loading ── */
@keyframes shimmer {
    0%   { background-position: -400px 0; }
    100% { background-position:  400px 0; }
}

.skeleton-line {
    display: inline-block;
    border-radius: 6px;
    background: linear-gradient(90deg, #e8e8e8 25%, #f5f5f5 50%, #e8e8e8 75%);
    background-size: 800px 100%;
    animation: shimmer 1.4s infinite linear;
}
</style>

@endsection
