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
    <div id="activeList" class="row g-3">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-indigo spinner-border-sm" role="status"></div>
            <span class="ms-2 text-muted small fw-bold">Memuat tugas aktif...</span>
        </div>
    </div>

</div>


<!-- MODAL: SELESAIKAN PENGIRIMAN -->
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

                        <input
                            type="file"
                            name="image"
                            id="proofImage"
                            class="form-control border-light-subtle"
                            accept="image/*"
                            capture="environment"
                            required>

                        <div class="mt-3 text-center">
                            <img
                                id="previewImage"
                                src=""
                                style="display:none; width:100%; max-height:250px; object-fit:cover; border-radius:12px; border:1px solid #ddd;">
                        </div>

                        <div class="small text-muted mt-2">
                            HP: otomatis buka kamera.
                            Laptop: pilih webcam/camera jika browser mendukung.
                        </div>

                        <button type="button" class="btn btn-sm btn-primary mt-2" onclick="openCamera()">
                            📷 Gunakan Kamera
                        </button>

                        <div class="mt-3 text-center">
                            <video
                                id="camera"
                                autoplay
                                playsinline
                                style="display:none; width:100%; border-radius:12px;"></video>

                            <canvas id="canvas" style="display:none;"></canvas>

                            <button
                                type="button"
                                id="captureBtn"
                                class="btn btn-success mt-2"
                                style="display:none;"
                                onclick="capturePhoto()">
                                Ambil Foto
                            </button>
                        </div>
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
   FETCH ACTIVE DELIVERY
========================= */
function fetchActive() {

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

                // ── Alamat dari kolom order, bukan dari profil user ──
                const order = d.order ?? {};

                const village         = order.village          ?? '';
                const district        = order.district         ?? '';
                const regency         = order.regency          ?? '';
                const shippingAddress = order.shipping_address ?? '';

                const addressParts = [
                    shippingAddress,
                    village  ? `Kel. ${village}`  : '',
                    district ? `Kec. ${district}` : '',
                    regency,
                    regency  ? 'Sumatera Utara'   : '',
                ].filter(Boolean);

                const address = addressParts.length
                    ? addressParts.join(', ')
                    : 'Alamat tidak tersedia';

                // ── Waktu dibuat ──
                const createdAt = d.created_at
                    ? new Date(d.created_at).toLocaleString('id-ID', {
                        day: '2-digit', month: 'short', year: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                      })
                    : '-';

                // ── Tombol aksi ──
                const actionBtn = statusName === 'claimed'
                    ? `<button onclick="startShipping('${d.id}')" class="btn btn-primary btn-delivery">
                           <i class="ph-play-circle me-1"></i> MULAI PERJALANAN
                       </button>`
                    : `<button onclick="openCompleteModal('${d.id}')" class="btn btn-success btn-delivery">
                           <i class="ph-check-circle me-1"></i> KONFIRMASI SAMPAI
                       </button>`;

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
   START SHIPPING
========================= */
function startShipping(id) {
    Swal.fire({
        title: 'Mulai perjalanan?',
        text: 'Status pengiriman akan berubah ke Shipping.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Mulai',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#5c68e2',
    })
    .then(res => {
        if (res.isConfirmed) {
            axios.post(`/api/deliveries/start/${id}`)
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Perjalanan Dimulai',
                    text: 'Selamat bertugas!',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true,
                });
                fetchActive();
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: err.response?.data?.message ?? 'Terjadi kesalahan.',
                });
            });
        }
    });
}

/* =========================
   OPEN MODAL COMPLETE
========================= */
function openCompleteModal(id) {
    document.getElementById('formComplete').reset();
    document.getElementById('previewImage').style.display = 'none';
    document.getElementById('camera').style.display      = 'none';
    document.getElementById('captureBtn').style.display  = 'none';
    document.getElementById('complete_delivery_id').value = id;

    new bootstrap.Modal(document.getElementById('modalComplete')).show();
}

/* =========================
   SUBMIT COMPLETE
========================= */
function submitComplete(e) {
    e.preventDefault();

    const id       = document.getElementById('complete_delivery_id').value;
    const formData = new FormData(e.target);

    const btn          = document.getElementById('btnSubmitComplete');
    const originalHtml = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

    axios.post(`/api/deliveries/complete/${id}`, formData)
    .then(() => {
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
    const video      = document.getElementById('camera');
    const captureBtn = document.getElementById('captureBtn');

    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
    .then(stream => {
        cameraStream        = stream;
        video.srcObject     = stream;
        video.style.display = 'block';
        captureBtn.style.display = 'inline-block';
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Error', 'Kamera tidak dapat dibuka', 'error');
    });
}

/* =========================
   CAPTURE PHOTO
========================= */
function capturePhoto() {
    const video   = document.getElementById('camera');
    const canvas  = document.getElementById('canvas');
    const preview = document.getElementById('previewImage');

    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);

    canvas.toBlob(function(blob) {
        const file         = new File([blob], 'camera-photo.jpg', { type: 'image/jpeg' });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);

        document.getElementById('proofImage').files = dataTransfer.files;

        preview.src          = URL.createObjectURL(blob);
        preview.style.display = 'block';
    }, 'image/jpeg');

    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
    }

    video.style.display = 'none';
    document.getElementById('captureBtn').style.display = 'none';
}

/* =========================
   INIT
========================= */
document.addEventListener('DOMContentLoaded', function () {

    fetchActive();

    document.getElementById('proofImage').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const preview         = document.getElementById('previewImage');
            preview.src           = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    });
});

</script>


<style>
/* ── Brand ── */
.bg-indigo   { background-color: #5c68e2 !important; }
.btn-indigo  { background-color: #5c68e2; color: #fff; border: none; }
.btn-indigo:hover { background-color: #4e59cf; color: #fff; }

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

/* ── Button ── */
.btn-delivery {
    width: 100%;
    border-radius: 10px;
    padding: 10px;
    font-weight: 700;
    font-size: 14px;
}
</style>

@endsection
