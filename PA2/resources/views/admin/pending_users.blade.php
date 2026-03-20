@extends('layouts.backoffice')

@section('page_title', 'Verifikasi Akun Baru')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0 text-dark">Persetujuan Akun Baru</h4>
            <div class="text-muted small">Hanya menampilkan pendaftar yang telah memverifikasi alamat email via OTP.</div>
        </div>
        <div class="ms-3">
            <a href="/admin/users" class="btn btn-light btn-label rounded-pill fw-bold">
                <i class="ph-arrow-left me-2"></i> Kembali ke Daftar User
            </a>
        </div>
    </div>

    <!-- TABLE CARD -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">
            <h5 class="mb-0 fw-bold"><i class="ph-user-circle-plus me-2 text-success"></i>Antrian Terverifikasi OTP</h5>
            <div class="ms-auto">
                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 rounded-pill">
                    <i class="ph-check-circle me-1"></i> EMAIL VERIFIED
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3 py-3">Calon Pengguna</th>
                        <th>Peran</th>
                        <th>Waktu Verifikasi</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="pendingTableBody">
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="ph-spinner spinner text-indigo me-2"></div>
                            <span class="fw-bold text-muted">Mencari antrian baru...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: DETAIL PROFIL -->
<div class="modal fade" id="modalDetailUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-user-focus me-2"></i>Verifikasi Data Unit</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="userDetailContent"></div>
            </div>
            <div class="modal-footer bg-light border-0 py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                <div id="footerActions" class="d-flex gap-2"></div>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    let pendingUsers = [];

    function loadPending() {
        const tableBody = document.getElementById('pendingTableBody');

        axios.get('/api/users/pending').then(res => {
            pendingUsers = res.data;
            let html = '';

            if (pendingUsers.length === 0) {
                html = `<tr><td colspan="4" class="text-center py-5 text-muted">
                            <i class="ph-smiley-blank ph-3x opacity-25 mb-2"></i><br>
                            Tidak ada permintaan akun yang menunggu persetujuan.
                        </td></tr>`;
            } else {
                pendingUsers.forEach(u => {
                    const roleName = u.roles.length > 0 ? u.roles[0].name.toUpperCase() : 'NO ROLE';
                    const date = new Date(u.email_verified_at).toLocaleString('id-ID', {day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit'});

                    html += `
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-indigo text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 40px; height: 40px;">
                                    ${u.name.charAt(0)}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">${u.name}</div>
                                    <div class="fs-xs text-muted">${u.email}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-indigo bg-opacity-10 text-indigo px-3">${roleName}</span></td>
                        <td><div class="small text-muted fw-bold">${date}</div></td>
                        <td class="text-center pe-3">
                            <button onclick="showFullProfile(${u.id})" class="btn btn-indigo btn-sm rounded-pill px-3 fw-bold shadow-sm">
                                <i class="ph-magnifying-glass me-1"></i> TINJAU
                            </button>
                        </td>
                    </tr>`;
                });
            }
            tableBody.innerHTML = html;
        });
    }

    function showFullProfile(id) {
        const u = pendingUsers.find(user => user.id === id);
        const roleName = u.roles.length > 0 ? u.roles[0].name : 'No Role';

        let detailHtml = `
            <div class="text-center mb-4">
                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&background=5c68e2&color=fff&bold=true" class="rounded-circle shadow border border-4 border-white mb-2" width="80">
                <h5 class="fw-bold mb-0">${u.name}</h5>
                <div class="text-muted small">${u.email}</div>
            </div>

            <div class="mb-3">
                <label class="d-block fs-xs fw-bold text-muted text-uppercase mb-1">Alamat Unit / Lokasi</label>
                <div class="p-3 bg-light rounded-3 border-start border-indigo border-3 small">
                    ${u.address || 'Alamat tidak tersedia'}
                </div>
            </div>`;

        document.getElementById('userDetailContent').innerHTML = detailHtml;
        document.getElementById('footerActions').innerHTML = `
            <button onclick="decide(${u.id}, 'reject')" class="btn btn-light text-danger fw-bold rounded-pill px-3 btn-sm">TOLAK</button>
            <button onclick="decide(${u.id}, 'approve')" class="btn btn-indigo rounded-pill px-3 fw-bold btn-sm">SETUJUI</button>
        `;

        new bootstrap.Modal(document.getElementById('modalDetailUser')).show();
    }

    function decide(id, action) {
        Swal.fire({
            title: action === 'approve' ? 'Setujui Akun?' : 'Tolak Akun?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: action === 'approve' ? '#059669' : '#ef4444',
            confirmButtonText: 'Ya, Lanjutkan'
        }).then(result => {
            if(result.isConfirmed) {
                bootstrap.Modal.getInstance(document.getElementById('modalDetailUser')).hide();
                axios.post(`/api/users/${id}/${action}`).then(res => {
                    Swal.fire('Berhasil', res.data.message, 'success');
                    loadPending();
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', loadPending);
</script>

<style>
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
    .btn-indigo { background-color: #5c68e2; color: #fff; }
    .text-indigo { color: #5c68e2; }
</style>
@endsection
