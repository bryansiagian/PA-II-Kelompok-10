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
                <h6 class="modal-title fw-bold"><i class="ph-check-circle me-2"></i>Konfirmasi Sampai Tujuan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formComplete" onsubmit="submitComplete(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="complete_delivery_id">

                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Nama Penerima</label>
                        <input type="text" name="receiver_name" class="form-control border-light-subtle" placeholder="Siapa yang menerima paket?" required>
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
    <label class="small fw-bold text-muted mb-1">
        Foto Bukti Terima
    </label>

    <!-- INPUT KAMERA -->
    <input
        type="file"
        name="image"
        id="proofImage"
        class="form-control border-light-subtle"
        accept="image/*"
        capture="environment"
        required
    >

    <!-- PREVIEW -->
    <div class="mt-3 text-center">
        <img
            id="previewImage"
            src=""
            style="
                display:none;
                width:100%;
                max-height:250px;
                object-fit:cover;
                border-radius:12px;
                border:1px solid #ddd;
            "
        >
    </div>

    <div class="small text-muted mt-2">
        HP: otomatis buka kamera.
        Laptop: pilih webcam/camera jika browser mendukung.
    </div>

    <!-- TOMBOL BUKA KAMERA -->
    <button
        type="button"
        class="btn btn-sm btn-primary mt-2"
        onclick="openCamera()"
    >
        📷 Gunakan Kamera
    </button>

    <!-- VIDEO CAMERA -->
    <div class="mt-3 text-center">
        <video
            id="camera"
            autoplay
            playsinline
            style="
                display:none;
                width:100%;
                border-radius:12px;
            "
        ></video>

        <canvas id="canvas" style="display:none;"></canvas>

        <button
            type="button"
            id="captureBtn"
            class="btn btn-success mt-2"
            style="display:none;"
            onclick="capturePhoto()"
        >
            Ambil Foto
        </button>
    </div>
</div>

                    <div class="mb-0">
                        <label class="small fw-bold text-muted mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="delivery_note" class="form-control border-light-subtle" rows="2" placeholder="Catatan lokasi penyerahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" id="btnSubmitComplete" class="btn btn-indigo px-4 fw-bold shadow-sm rounded-pill">KONFIRMASI SELESAI</button>
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

    const container =
        document.getElementById('activeList');

    axios.get('/api/deliveries/active')
    .then(res => {

        let html = '';
        const data = res.data;

        if(data.length === 0){

            html = `
            <div class="col-12 text-center py-5">
                Tidak ada tugas aktif
            </div>
            `;

        } else {

            data.forEach(d => {

                const rawStatus =
                    d.status ? d.status.name : 'Unknown';

                const statusName =
                    rawStatus.toLowerCase();

                const address =
                    d.order?.user?.address ||
                    'Alamat tidak tersedia';

                html += `
<div class="col-md-6 col-lg-5 mb-4">
    <div class="delivery-card active-delivery-card">

    <div class="delivery-header">
        <div>
            <span class="tracking-number">
                ${d.tracking_number}
            </span>

            <div class="delivery-time">
                <i class="fas fa-clock"></i>
                ${d.created_at ?? ''}
            </div>
        </div>

        <span class="status-badge active">
            AKTIF
        </span>
    </div>

    <div class="delivery-body">

        <div class="destination-info">
            <div class="destination-name">
                ${d.order?.user?.name || 'Customer'}
            </div>

            <div class="destination-address">
                <i class="fas fa-map-marker-alt"></i>
                ${address}
            </div>
        </div>

        <div class="delivery-actions">

            ${
                statusName === 'claimed'
                ?

                `
                <button
                    onclick="startShipping('${d.id}')"
                    class="btn btn-primary btn-delivery"
                >
                    MULAI PERJALANAN
                </button>
                `

                :

                `
                <button
                    onclick="openCompleteModal('${d.id}')"
                    class="btn btn-success btn-delivery"
                >
                    KONFIRMASI SAMPAI
                </button>
                `
            }

        </div>

    </div>
    </div>
</div>
`;
            });
        }

        container.innerHTML = html;
    })
    .catch(err => {

        console.error(err);

        container.innerHTML = `
        <div class="text-danger text-center py-5">
            Gagal memuat data
        </div>
        `;
    });
}

/* =========================
   START SHIPPING
========================= */
function startShipping(id){

    Swal.fire({
        title: 'Mulai perjalanan?',
        icon: 'question',
        showCancelButton: true
    })
    .then(res => {

        if(res.isConfirmed){

            axios.post(`/api/deliveries/start/${id}`)
            .then(() => {

                Swal.fire(
                    'Berhasil',
                    'Perjalanan dimulai',
                    'success'
                );

                fetchActive();
            });
        }
    });
}

/* =========================
   OPEN MODAL COMPLETE
========================= */
function openCompleteModal(id){

    document.getElementById('formComplete').reset();

    document.getElementById(
        'complete_delivery_id'
    ).value = id;

    new bootstrap.Modal(
        document.getElementById('modalComplete')
    ).show();
}

/* =========================
   SUBMIT COMPLETE
========================= */
function submitComplete(e){

    e.preventDefault();

    const id =
        document.getElementById(
            'complete_delivery_id'
        ).value;

    const formData =
        new FormData(e.target);

    axios.post(
        `/api/deliveries/complete/${id}`,
        formData
    )
    .then(() => {

        bootstrap.Modal
        .getInstance(
            document.getElementById('modalComplete')
        )
        .hide();

        Swal.fire(
            'Berhasil',
            'Pengiriman selesai',
            'success'
        );

        fetchActive();
    })
    .catch(err => {

        console.error(err);

        Swal.fire(
            'Error',
            'Gagal upload',
            'error'
        );
    });
}

/* =========================
   OPEN CAMERA
========================= */
function openCamera(){

    const video =
        document.getElementById('camera');

    const captureBtn =
        document.getElementById('captureBtn');

    navigator.mediaDevices
    .getUserMedia({
        video: {
            facingMode: "environment"
        }
    })

    .then(stream => {

        cameraStream = stream;

        video.srcObject = stream;

        video.style.display = 'block';

        captureBtn.style.display =
            'inline-block';
    })

    .catch(err => {

        console.error(err);

        Swal.fire(
            'Error',
            'Kamera tidak dapat dibuka',
            'error'
        );
    });
}

/* =========================
   CAPTURE PHOTO
========================= */
function capturePhoto(){

    const video =
        document.getElementById('camera');

    const canvas =
        document.getElementById('canvas');

    const preview =
        document.getElementById('previewImage');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    const ctx = canvas.getContext('2d');

    ctx.drawImage(video, 0, 0);

    canvas.toBlob(function(blob){

        const file = new File(
            [blob],
            "camera-photo.jpg",
            {
                type: "image/jpeg"
            }
        );

        const dataTransfer =
            new DataTransfer();

        dataTransfer.items.add(file);

        document.getElementById(
            'proofImage'
        ).files = dataTransfer.files;

        preview.src =
            URL.createObjectURL(blob);

        preview.style.display = 'block';

    }, 'image/jpeg');

    if(cameraStream){

        cameraStream
        .getTracks()
        .forEach(track => track.stop());
    }

    video.style.display = 'none';

    document.getElementById(
        'captureBtn'
    ).style.display = 'none';
}

/* =========================
   PREVIEW FILE
========================= */
document.addEventListener(
'DOMContentLoaded',
function(){

    fetchActive();

    const input =
        document.getElementById('proofImage');

    if(input){

        input.addEventListener(
        'change',
        function(e){

            const file =
                e.target.files[0];

            if(file){

                const preview =
                    document.getElementById(
                        'previewImage'
                    );

                preview.src =
                    URL.createObjectURL(file);

                preview.style.display =
                    'block';
            }
        });
    }
});

</script>

<style>
    .bg-indigo { background-color: #5c68e2 !important; }
    .btn-indigo { background-color: #5c68e2; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #4e59cf; color: #fff; }
    .border-start-primary { border-left: 5px solid #5c68e2 !important; }
    .border-start-warning { border-left: 5px solid #ffb300 !important; }
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }

    .active-delivery-card{
    background:#fff;
    border-left:5px solid #5b5ce2;
    border-radius:16px;
    padding:18px;
    margin-bottom:16px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
}

.delivery-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:12px;
}

.tracking-number{
    font-size:18px;
    font-weight:700;
    color:#4b4ded;
    margin-bottom:4px;
    display:block;
}

.delivery-time{
    color:#777;
    font-size:12px;
}

.status-badge.active{
    background:#d4f8df;
    color:#12a150;
    padding:5px 12px;
    border-radius:20px;
    font-size:11px;
    font-weight:600;
}

.destination-name{
    font-size:16px;
    font-weight:700;
    margin-bottom:4px;
}

.destination-address{
    color:#666;
    font-size:13px;
    margin-bottom:14px;
}

.btn-delivery{
    width:100%;
    border-radius:10px;
    padding:10px;
    font-weight:700;
    font-size:14px;
}

.delivery-list{
    display:flex;
    flex-wrap:wrap;
    gap:20px;
}
</style>
@endsection
