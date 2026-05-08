@extends('layouts.backoffice')

@section('page_title', 'Verifikasi Akun Baru')

@section('content')
<div class="container-fluid">

    <!-- HEADER -->
    <div class="d-flex align-items-center mb-4">
        <div class="flex-fill">
            <h3 class="fw-bold text-dark mb-1">
                Persetujuan Akun Baru
            </h3>

            <div class="text-muted">
                Menampilkan pendaftar yang telah memverifikasi email menggunakan OTP.
            </div>
        </div>

        <a href="/admin/users"
           class="btn btn-light border rounded-pill px-4 fw-semibold shadow-sm">
            <i class="ph-arrow-left me-2"></i>
            Kembali ke Daftar User
        </a>
    </div>

    <!-- CARD -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <!-- CARD HEADER -->
        <div class="card-header bg-white border-bottom py-3 px-4">
            <div class="d-flex align-items-center">

                <div>
                    <h5 class="fw-bold mb-0 d-flex align-items-center">
                        <i class="ph-user-circle-plus text-success me-2 fs-4"></i>
                        Antrian Terverifikasi OTP
                    </h5>
                </div>

                <div class="ms-auto">
                    <span class="verified-badge">
                        <i class="ph-check-circle me-1"></i>
                        EMAIL VERIFIED
                    </span>
                </div>

            </div>
        </div>

        <!-- TABLE -->
        <div class="table-responsive">
            <table class="table align-middle custom-table mb-0">

                <thead>
                    <tr>
                        <th class="ps-4">CALON PENGGUNA</th>
                        <th>PERAN</th>
                        <th>WAKTU VERIFIKASI</th>
                        <th class="text-center pe-4">AKSI</th>
                    </tr>
                </thead>

                <tbody id="pendingTableBody">

                    <tr>
                        <td colspan="4" class="text-center py-5">

                            <div class="spinner-border text-primary mb-3"></div>

                            <div class="fw-semibold text-muted">
                                Memuat data permintaan akun...
                            </div>

                        </td>
                    </tr>

                </tbody>

            </table>
        </div>
    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalDetailUser" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <!-- HEADER -->
            <div class="modal-header bg-indigo text-white border-0 py-3">

                <h5 class="modal-title fw-bold">
                    <i class="ph-user-focus me-2"></i>
                    Verifikasi Data Pengguna
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>

            </div>

            <!-- BODY -->
            <div class="modal-body p-4">

                <div id="userDetailContent"></div>

            </div>

            <!-- FOOTER -->
            <div class="modal-footer border-0 bg-light">

                <button type="button"
                        class="btn btn-light rounded-pill px-4 fw-semibold"
                        data-bs-dismiss="modal">
                    BATAL
                </button>

                <div id="footerActions" class="d-flex gap-2"></div>

            </div>

        </div>

    </div>
</div>

<script>
axios.defaults.headers.common['Authorization'] =
'Bearer ' + '{{ session('api_token') }}';

let pendingUsers = [];

function loadPending() {

    const tableBody = document.getElementById('pendingTableBody');

    axios.get('/api/users/pending')
    .then(res => {

        pendingUsers = res.data;

        let html = '';

        if (pendingUsers.length === 0) {

            html = `
            <tr>
                <td colspan="4" class="text-center py-5">

                    <i class="ph-smiley-blank text-muted opacity-25"
                       style="font-size:60px;"></i>

                    <div class="fw-bold mt-3 text-muted">
                        Tidak ada permintaan akun baru
                    </div>

                </td>
            </tr>
            `;

        } else {

            pendingUsers.forEach(u => {

                const roleName =
                    u.roles.length > 0
                    ? u.roles[0].name.toUpperCase()
                    : 'NO ROLE';

                const date =
                    new Date(u.email_verified_at)
                    .toLocaleString('id-ID', {
                        day:'2-digit',
                        month:'short',
                        hour:'2-digit',
                        minute:'2-digit'
                    });

                html += `
                <tr>

                    <!-- USER -->
                    <td class="ps-4 py-4">

                        <div class="d-flex align-items-center">

                            <div class="user-avatar me-3">
                                ${u.name.charAt(0)}
                            </div>

                            <div>

                                <div class="fw-bold text-dark fs-6">
                                    ${u.name}
                                </div>

                                <div class="small text-muted">
                                    ${u.email}
                                </div>

                            </div>

                        </div>

                    </td>

                    <!-- ROLE -->
                    <td>

                        <span class="role-badge">
                            ${roleName}
                        </span>

                    </td>

                    <!-- DATE -->
                    <td>

                        <div class="fw-semibold text-dark">
                            ${date}
                        </div>

                    </td>

                    <!-- ACTION -->
                    <td class="text-center pe-4">

                        <button
                            onclick="showFullProfile(${u.id})"
                            class="btn-review">

                            <i class="ph-magnifying-glass me-1"></i>
                            TINJAU

                        </button>

                    </td>

                </tr>
                `;
            });
        }

        tableBody.innerHTML = html;
    });
}

function showFullProfile(id) {

    const u = pendingUsers.find(user => user.id === id);

    const roleName =
        u.roles.length > 0
        ? u.roles[0].name
        : 'No Role';

    let detailHtml = `

        <div class="text-center mb-4">

            <img
                src="https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&background=5c68e2&color=fff"
                class="rounded-circle shadow-sm border border-4 border-white mb-3"
                width="90">

            <h5 class="fw-bold mb-1">
                ${u.name}
            </h5>

            <div class="text-muted small mb-2">
                ${u.email}
            </div>

            <span class="role-badge">
                ${roleName.toUpperCase()}
            </span>

        </div>

        <div class="mb-3">

            <label class="detail-label">
                Nomor Telepon / WhatsApp
            </label>

            <div class="detail-box">
                <i class="ph-phone me-2 text-indigo"></i>
                ${u.phone || 'Tidak tersedia'}
            </div>

        </div>

        <div>

            <label class="detail-label">
                Alamat Unit / Lokasi
            </label>

            <div class="detail-box">
                <i class="ph-map-pin me-2 text-indigo"></i>
                ${u.address || 'Alamat tidak tersedia'}
            </div>

        </div>
    `;

    document.getElementById('userDetailContent').innerHTML = detailHtml;

    document.getElementById('footerActions').innerHTML = `
        <button onclick="decide(${u.id}, 'reject')"
                class="btn btn-light-danger rounded-pill px-4 fw-bold">

            TOLAK

        </button>

        <button onclick="decide(${u.id}, 'approve')"
                class="btn btn-indigo rounded-pill px-4 fw-bold">

            SETUJUI

        </button>
    `;

    new bootstrap.Modal(
        document.getElementById('modalDetailUser')
    ).show();
}

function decide(id, action) {

    Swal.fire({
        title: action === 'approve'
            ? 'Setujui Akun?'
            : 'Tolak Akun?',

        icon: 'question',

        showCancelButton: true,

        confirmButtonColor:
            action === 'approve'
            ? '#10b981'
            : '#ef4444',

        confirmButtonText: 'Ya, Lanjutkan'

    }).then(result => {

        if(result.isConfirmed) {

            axios.post(`/api/users/${id}/${action}`)
            .then(res => {

                Swal.fire(
                    'Berhasil',
                    res.data.message,
                    'success'
                );

                loadPending();

            }).catch(err => {

                Swal.fire(
                    'Gagal',
                    'Terjadi kesalahan.',
                    'error'
                );
            });
        }
    });
}

document.addEventListener(
    'DOMContentLoaded',
    loadPending
);
</script>

<style>

body{
    background:#f5f7fb;
}

/* TABLE */

.custom-table thead th{
    background:#f8fafc;
    color:#64748b;
    font-size:12px;
    font-weight:700;
    letter-spacing:.7px;
    padding-top:18px;
    padding-bottom:18px;
    border-bottom:1px solid #edf2f7;
}

.custom-table tbody tr{
    transition:.2s;
}

.custom-table tbody tr:hover{
    background:#fafbff;
}

.custom-table td{
    border-bottom:1px solid #f1f5f9;
}

/* USER */

.user-avatar{
    width:50px;
    height:50px;
    border-radius:50%;
    background:linear-gradient(135deg,#5B5FEF,#7C4DFF);
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:700;
    font-size:18px;
    box-shadow:0 6px 14px rgba(92,95,239,.25);
}

/* ROLE BADGE */

.role-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;

    min-width:120px;

    padding:10px 18px;

    background:linear-gradient(135deg,#5B5FEF,#7C4DFF);

    color:white;

    border-radius:999px;

    font-size:12px;
    font-weight:700;
    letter-spacing:.5px;

    box-shadow:0 4px 12px rgba(92,95,239,.25);
}

/* VERIFIED BADGE */

.verified-badge{
    background:#dcfce7;
    color:#059669;
    padding:10px 16px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
}

/* BUTTON */

.btn-review{
    border:none;
    background:linear-gradient(135deg,#5B5FEF,#7C4DFF);
    color:white;
    padding:10px 22px;
    border-radius:999px;
    font-weight:700;
    font-size:13px;
    transition:.2s;
    box-shadow:0 4px 12px rgba(92,95,239,.25);
}

.btn-review:hover{
    transform:translateY(-1px);
    opacity:.95;
}

.btn-indigo{
    background:#5B5FEF;
    color:white;
}

.bg-indigo{
    background:#5B5FEF !important;
}

.text-indigo{
    color:#5B5FEF;
}

/* DETAIL */

.detail-label{
    display:block;
    font-size:12px;
    font-weight:700;
    color:#64748b;
    text-transform:uppercase;
    margin-bottom:8px;
}

.detail-box{
    background:#f8fafc;
    border-left:4px solid #5B5FEF;
    padding:14px;
    border-radius:12px;
    font-weight:600;
    color:#334155;
}

/* DANGER */

.btn-light-danger{
    background:#fee2e2;
    color:#dc2626;
}

</style>

@endsection
