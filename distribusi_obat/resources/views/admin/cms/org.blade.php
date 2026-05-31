@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Struktur Organisasi</h4>
            <p class="text-muted small mb-0">Kelola daftar pengurus dan hierarki organisasi yayasan.</p>
        </div>
        <button class="btn btn-indigo rounded-pill px-4 shadow-sm" onclick="openOrgModal()">
            <i class="ph-plus-circle me-2"></i> Tambah Pengurus
        </button>
    </div>

    <div id="orgList" class="row g-4"></div>
</div>

<!-- MODAL ORG (TAMBAH & EDIT) -->
<div class="modal fade" id="modalOrg" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalTitle">Tambah Struktur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="orgForm" onsubmit="saveOrg(event)">
                <input type="hidden" name="org_id" id="org_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small fw-bold mb-1">Nama Lengkap</label>
                        <input type="text" name="name" id="form_name" class="form-control bg-light border-0"
                               placeholder="Masukkan nama beserta gelar" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold mb-1">Jabatan</label>
                        <input type="text" name="position" id="form_position" class="form-control bg-light border-0"
                               placeholder="Contoh: Ketua Yayasan" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold mb-1">Urutan (Order)</label>
                        <input type="number" name="order" id="form_order" class="form-control bg-light border-0"
                               value="0" required>
                        <small class="text-muted">Semakin kecil angkanya, semakin awal muncul di halaman depan.</small>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold mb-1">Foto Profil</label>
                        <input type="file" name="photo" id="form_photo" class="form-control bg-light border-0" accept="image/*">
                        <div id="photoInfo" class="mt-2 d-none">
                            <p class="small text-info mb-1"><i class="ph-info me-1"></i> Biarkan kosong jika tidak ingin mengubah foto.</p>
                            <img id="currentPhotoPreview" src="" class="rounded-circle border" width="60" height="60" style="object-fit: cover;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-link text-body" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmit" class="btn btn-indigo rounded-pill px-4 fw-bold">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    /* =========================
       SKELETON
    ========================= */
    function showSkeletons() {
        let html = '';
        for (let i = 0; i < 8; i++) {
            html += `
            <div class="col-md-3 text-center">
                <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                    <div class="d-flex justify-content-center mb-3">
                        <span class="skeleton-line" style="width:100px;height:100px;border-radius:50%;"></span>
                    </div>
                    <span class="skeleton-line d-block mx-auto mb-2" style="width:${100 + i * 8}px;height:14px;"></span>
                    <span class="skeleton-line d-block mx-auto mb-3" style="width:${70 + i * 5}px;height:12px;"></span>
                    <span class="skeleton-line d-block mx-auto" style="width:80px;height:24px;border-radius:999px;"></span>
                </div>
            </div>`;
        }
        document.getElementById('orgList').innerHTML = html;
    }

    /* =========================
       LOAD ORG
    ========================= */
    function loadOrg() {
        showSkeletons();

        axios.get('/api/cms/org').then(res => {
            let html = '';
            if (res.data.length === 0) {
                html = `
                <div class="col-12 text-center py-5">
                    <i class="ph-users-four opacity-25 mb-2" style="font-size: 4rem;"></i>
                    <p class="text-muted">Belum ada data pengurus.</p>
                </div>`;
            } else {
                res.data.forEach(o => {
                    const img = o.photo
                        ? `/storage/${o.photo}`
                        : `https://ui-avatars.com/api/?name=${encodeURIComponent(o.name)}&background=5c6bc0&color=fff`;
                    html += `
                    <div class="col-md-3 text-center">
                        <div class="card border-0 shadow-sm p-4 rounded-4 h-100 medinest-card">
                            <div class="position-absolute top-0 end-0 m-2">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-icon btn-sm rounded-pill shadow-none" data-bs-toggle="dropdown">
                                        <i class="ph-dots-three-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                        <li><a class="dropdown-item py-2" onclick="editOrg(${o.id})"><i class="ph-pencil me-2 text-primary"></i> Edit</a></li>
                                        <li><a class="dropdown-item py-2 text-danger" onclick="deleteOrg(${o.id})"><i class="ph-trash me-2"></i> Hapus</a></li>
                                    </ul>
                                </div>
                            </div>
                            <img src="${img}"
                                 class="rounded-circle mb-3 mx-auto border border-4 border-white shadow-sm"
                                 width="100" height="100" style="object-fit:cover">
                            <h6 class="fw-bold mb-1 text-indigo">${o.name}</h6>
                            <p class="text-muted small mb-0 fw-bold">${o.position}</p>
                            <span class="badge bg-light text-indigo border rounded-pill mt-2">Urutan: ${o.order}</span>
                        </div>
                    </div>`;
                });
            }
            document.getElementById('orgList').innerHTML = html;
        }).catch(() => {
            document.getElementById('orgList').innerHTML = `
            <div class="col-12 text-center py-5 text-danger">
                <i class="ph-warning-circle fs-1 d-block mb-2 opacity-50"></i>
                Gagal memuat data struktur organisasi.
            </div>`;
        });
    }

    /* =========================
       OPEN MODAL
    ========================= */
    function openOrgModal() {
        document.getElementById('orgForm').reset();
        document.getElementById('org_id').value = '';
        document.getElementById('modalTitle').innerText = 'Tambah Struktur';
        document.getElementById('btnSubmit').innerText  = 'Simpan Data';
        document.getElementById('photoInfo').classList.add('d-none');
        document.getElementById('form_photo').required = true;
        new bootstrap.Modal(document.getElementById('modalOrg')).show();
    }

    /* =========================
       EDIT
    ========================= */
    function editOrg(id) {
        axios.get('/api/cms/org').then(res => {
            const o = res.data.find(item => item.id === id);
            if (!o) return;

            document.getElementById('org_id').value       = o.id;
            document.getElementById('form_name').value    = o.name;
            document.getElementById('form_position').value = o.position;
            document.getElementById('form_order').value   = o.order;

            document.getElementById('modalTitle').innerText = 'Edit Pengurus';
            document.getElementById('btnSubmit').innerText  = 'Update Data';

            document.getElementById('form_photo').required = false;
            document.getElementById('photoInfo').classList.remove('d-none');
            const preview = o.photo
                ? `/storage/${o.photo}`
                : `https://ui-avatars.com/api/?name=${encodeURIComponent(o.name)}`;
            document.getElementById('currentPhotoPreview').src = preview;

            new bootstrap.Modal(document.getElementById('modalOrg')).show();
        });
    }

    /* =========================
       SAVE
    ========================= */
    function saveOrg(e) {
        e.preventDefault();
        const id       = document.getElementById('org_id').value;
        const btn      = document.getElementById('btnSubmit');
        const formData = new FormData(e.target);
        const originalHtml = btn.innerHTML;

        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';

        let url = '/api/cms/org';
        if (id) {
            formData.append('_method', 'PUT');
            url = `/api/cms/org/${id}`;
        }

        axios.post(url, formData)
            .then(() => {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data organisasi diperbarui', timer: 1500, showConfirmButton: false });
                bootstrap.Modal.getInstance(document.getElementById('modalOrg')).hide();
                loadOrg();
            })
            .catch(err => {
                Swal.fire('Gagal', err.response?.data?.message || 'Terjadi kesalahan', 'error');
            })
            .finally(() => {
                btn.disabled  = false;
                btn.innerHTML = originalHtml;
            });
    }

    /* =========================
       DELETE
    ========================= */
    function deleteOrg(id) {
        Swal.fire({
            title: 'Hapus Pengurus?',
            text: 'Data akan dihapus permanen dari struktur organisasi.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef5350',
            cancelButtonColor: '#f1f5f9',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (res.isConfirmed) {
                axios.delete(`/api/cms/org/${id}`).then(() => {
                    Swal.fire({ icon: 'success', title: 'Terhapus', timer: 1000, showConfirmButton: false });
                    loadOrg();
                }).catch(e => {
                    Swal.fire('Gagal', e.response?.data?.message || 'Terjadi kesalahan', 'error');
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', loadOrg);
</script>

<style>
    .medinest-card { transition: 0.3s; }
    .medinest-card:hover { transform: translateY(-5px); }
    .btn-indigo       { background-color: #5c6bc0; color: #fff; }
    .btn-indigo:hover { background-color: #3f51b5; color: #fff; }
    .text-indigo      { color: #5c6bc0; }

    /* ── Skeleton loading ──────────────────────────────────────────────────── */
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
