@extends('layouts.backoffice')

@section('page_title', 'Verifikasi Akun Baru')

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0 text-dark">Persetujuan Akun Baru</h4>
            <div class="text-muted small">Pendaftar yang telah memverifikasi email via OTP.</div>
        </div>
        <div class="ms-3 d-flex gap-2">
            <a href="/admin/users" class="btn btn-light shadow-sm rounded-pill px-4">
                <i class="ph-arrow-left me-2"></i> Kembali
            </a>
            <button onclick="loadPending()" class="btn btn-light shadow-sm rounded-pill px-4">
                <i class="ph-arrow-clockwise me-2"></i> Refresh
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3">Nama & Email</th>
                        <th>Role</th>
                        <th>Waktu Verifikasi</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="pendingTableBody">
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL DETAIL --}}
<div class="modal fade" id="modalDetailUser" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold">
                    <i class="ph-user-focus me-2"></i> Verifikasi Data Pengguna
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="userDetailContent"></div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <div id="footerActions" class="d-flex gap-2"></div>
            </div>
        </div>
    </div>
</div>

<script>
axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

let pendingUsers = [];

function showSkeletons() {
    let html = '';
    for (let i = 0; i < 6; i++) {
        html += `
        <tr>
            <td class="ps-3">
                <span class="skeleton-line d-block mb-1" style="width:${120+i*10}px;height:14px;"></span>
                <span class="skeleton-line d-block" style="width:${90+i*6}px;height:11px;"></span>
            </td>
            <td><span class="skeleton-line" style="width:80px;height:22px;border-radius:999px;"></span></td>
            <td><span class="skeleton-line" style="width:${100+i*8}px;height:13px;"></span></td>
            <td class="text-center pe-3">
                <span class="skeleton-line" style="width:80px;height:32px;border-radius:999px;"></span>
            </td>
        </tr>`;
    }
    document.getElementById('pendingTableBody').innerHTML = html;
}

function loadPending() {
    showSkeletons();
    axios.get('/api/users/pending').then(res => {
        pendingUsers = res.data;
        let html = '';

        if (!pendingUsers.length) {
            html = `<tr><td colspan="4" class="text-center py-5 text-muted">
                <i class="ph-smiley-blank d-block mb-2" style="font-size:2rem;opacity:.3;"></i>
                Tidak ada permintaan akun baru
            </td></tr>`;
        } else {
            pendingUsers.forEach(u => {
                const roleName = u.roles?.[0]?.name?.toUpperCase() ?? 'NO ROLE';
                const date = u.email_verified_at
                    ? new Date(u.email_verified_at).toLocaleString('id-ID', {
                        day: '2-digit', month: 'short', year: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                      })
                    : '-';

                html += `
                <tr>
                    <td class="ps-3">
                        <div class="fw-bold text-dark">${u.name}</div>
                        <div class="text-muted small">${u.email}</div>
                    </td>
                    <td>
                        <span class="badge bg-indigo bg-opacity-10 border border-indigo border-opacity-25 px-2 py-1">
                            ${roleName}
                        </span>
                    </td>
                    <td><div class="small text-muted">${date}</div></td>
                    <td class="text-center pe-3">
                        <button onclick="showFullProfile(${u.id})" class="btn btn-light btn-sm rounded-pill px-3 shadow-sm border">
                            <i class="ph-magnifying-glass me-1"></i> Tinjau
                        </button>
                    </td>
                </tr>`;
            });
        }

        document.getElementById('pendingTableBody').innerHTML = html;
    }).catch(() => {
        document.getElementById('pendingTableBody').innerHTML =
            `<tr><td colspan="4" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>`;
    });
}

function showFullProfile(id) {
    const u = pendingUsers.find(user => user.id === id);
    if (!u) return;

    const roleName = u.roles?.[0]?.name ?? 'No Role';

    const fieldRow = (icon, label, value) => `
        <div class="col-md-6 mb-3">
            <div class="small fw-bold text-muted text-uppercase mb-1" style="font-size:11px;">${label}</div>
            <div class="d-flex align-items-center gap-2 bg-light rounded-3 px-3 py-2">
                <i class="${icon} text-indigo flex-shrink-0"></i>
                <span class="fw-semibold text-dark small">${value || '<span class="text-muted fst-italic">Tidak diisi</span>'}</span>
            </div>
        </div>`;

    document.getElementById('userDetailContent').innerHTML = `
        <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                 style="width:56px;height:56px;font-size:22px;background:linear-gradient(135deg,#5B5FEF,#7C4DFF);">
                ${u.name.charAt(0)}
            </div>
            <div>
                <h5 class="fw-bold mb-1">${u.name}</h5>
                <div class="text-muted small">${u.email}</div>
                <span class="badge mt-1" style="background:#5B5FEF;">${roleName.toUpperCase()}</span>
            </div>
        </div>
        <div class="row">
            ${fieldRow('ph-phone', 'No. Telepon', u.phone)}
            ${fieldRow('ph-map-pin', 'Alamat', u.address)}
            ${fieldRow('ph-buildings', 'Kabupaten/Kota', u.regency)}
            ${fieldRow('ph-map-trifold', 'Kecamatan', u.district)}
            ${fieldRow('ph-house-line', 'Kelurahan/Desa', u.village)}
            ${fieldRow('ph-calendar-check', 'Verifikasi OTP', u.email_verified_at
                ? new Date(u.email_verified_at).toLocaleString('id-ID', {
                    day: '2-digit', month: 'long', year: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                  })
                : null)}
        </div>`;

    document.getElementById('footerActions').innerHTML = `
        <button onclick="decide(${u.id}, 'reject')" class="btn btn-light rounded-pill px-4 fw-bold text-danger border">
            <i class="ph-x-circle me-1"></i> Tolak
        </button>
        <button onclick="decide(${u.id}, 'approve')" class="btn btn-indigo rounded-pill px-4 fw-bold">
            <i class="ph-check-circle me-1"></i> Setujui
        </button>`;

    new bootstrap.Modal(document.getElementById('modalDetailUser')).show();
}

function decide(id, action) {
    Swal.fire({
        title: action === 'approve' ? 'Setujui Akun?' : 'Tolak Akun?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: action === 'approve' ? '#10b981' : '#ef4444',
        cancelButtonText: 'Batal',
        confirmButtonText: 'Ya, Lanjutkan',
        showLoaderOnConfirm: true,
        preConfirm: () => axios.post(`/api/users/${id}/${action}`).catch(err => {
            Swal.showValidationMessage(err.response?.data?.message ?? 'Terjadi kesalahan.');
        }),
        allowOutsideClick: () => !Swal.isLoading()
    }).then(result => {
        if (!result.isConfirmed) return;
        bootstrap.Modal.getInstance(document.getElementById('modalDetailUser'))?.hide();
        Swal.fire({
            icon: 'success',
            title: action === 'approve' ? 'Akun Disetujui' : 'Akun Ditolak',
            confirmButtonColor: '#5c6bc0'
        });
        loadPending();
    });
}

document.addEventListener('DOMContentLoaded', loadPending);
</script>

<style>
.btn-indigo { background-color: #5c6bc0; color: #fff; border: none; }
.btn-indigo:hover { background-color: #4a5ab0; color: #fff; }
.text-indigo { color: #5c6bc0; }
.bg-indigo { background-color: #5c6bc0 !important; }

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
