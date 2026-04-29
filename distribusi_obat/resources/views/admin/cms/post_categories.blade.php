@extends('layouts.backoffice')

@section('page_title', 'Kategori Postingan')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Manajemen Kategori Post</h4>
            <div class="text-muted small">Kelola pengelompokan konten berita dan kegiatan untuk Landing Page.</div>
        </div>
        <button class="btn btn-indigo rounded-pill px-4 shadow-sm fw-bold mt-3 mt-sm-0" onclick="openAddModal()">
            <i class="ph-plus-circle me-2"></i> Tambah Kategori
        </button>
    </div>

    <!-- TABLE CARD -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
            <h6 class="mb-0 fw-bold"><i class="ph-tag me-2 text-indigo"></i>Daftar Kategori Aktif</h6>
            <div class="ms-auto">
                <span class="badge bg-indigo text-white rounded-pill px-3 shadow-sm fw-bold">
                <i class="ph-database me-1"></i> Database Terhubung
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3 py-3">Nama Kategori</th>
                        <th class="text-center">Jumlah Postingan</th>
                        <th>Dibuat Oleh</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="categoryTableBody">
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="ph-spinner spinner text-indigo me-2"></div> Memuat data kategori...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: TAMBAH/EDIT KATEGORI (Limitless Style)
     ========================================== -->
<div class="modal fade" id="modalCategory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalTitle">Kategori Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="cat_id">
                <div class="mb-3">
                    <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Nama Kategori</label>
                    <div class="form-control-feedback form-control-feedback-start">
                        <input type="text" id="cat_name" class="form-control border-0 bg-light py-2 shadow-none" placeholder="Contoh: Berita Utama" required>
                        <div class="form-control-feedback-icon"><i class="ph-tag"></i></div>
                    </div>
                </div>
                <div class="alert alert-info border-0 small mb-0 mt-3">
                    <i class="ph-info me-2"></i> Nama kategori ini akan muncul sebagai label filter di Landing Page publik.
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 d-flex justify-content-between p-3">
                <button type="button" class="btn btn-link text-muted text-decoration-none fw-semibold" data-bs-dismiss="modal">Batal</button>
                <button onclick="saveCategory()" class="btn btn-indigo rounded-pill px-4 fw-bold shadow-sm">
                    Simpan Data
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Header Token Global
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchCategories() {
        const tableBody = document.getElementById('categoryTableBody');

        axios.get('/api/cms/post-categories')
            .then(res => {
                let html = '';
                const categories = res.data;

                if (!categories || categories.length === 0) {
                    html = '<tr><td colspan="4" class="text-center py-5 text-muted italic small">Belum ada kategori postingan yang dibuat.</td></tr>';
                } else {
                    categories.forEach(cat => {
                        const jumlahPost = cat.posts_count || 0;
                        const creator = cat.creator ? cat.creator.name : 'Administrator';

                        html += `
                        <tr class="border-bottom">
                            <td class="ps-3 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-indigo text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 32px; height: 32px; font-size: 10px; font-weight: bold;">
                                        ${cat.name.charAt(0).toUpperCase()}
                                    </div>
                                    <div class="fw-bold text-dark">${cat.name}</div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill px-3 py-2 fw-bold" style="background:#eef2ff; color:#1e293b; border:1px solid #c7d2fe; font-size: 11px;">
                                <i class="ph-file-text me-1"></i> ${jumlahPost} Postingan
                                </span>
                            </td>
                            <td>
                                <div class="small text-muted fw-semibold">
                                    <i class="ph-user-circle me-1"></i> ${creator}
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button onclick="openEditModal(${cat.id}, '${cat.name.replace(/'/g, "\\'")}')" class="btn btn-light btn-icon btn-sm rounded-pill shadow-sm border text-indigo" title="Edit Kategori">
                                        <i class="ph-pencil-line"></i>
                                    </button>
                                    <button onclick="deleteCategory(${cat.id}, '${cat.name.replace(/'/g, "\\'")}')" class="btn btn-light btn-icon btn-sm rounded-pill shadow-sm border text-danger" title="Hapus Kategori">
                                        <i class="ph-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    });
                }
                tableBody.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-danger">Gagal memuat data dari server.</td></tr>';
            });
    }

    function openAddModal() {
        document.getElementById('cat_id').value = '';
        document.getElementById('cat_name').value = '';
        document.getElementById('modalTitle').innerText = 'Tambah Kategori Baru';
        new bootstrap.Modal(document.getElementById('modalCategory')).show();
    }

    function openEditModal(id, name) {
        document.getElementById('cat_id').value = id;
        document.getElementById('cat_name').value = name;
        document.getElementById('modalTitle').innerText = 'Edit Informasi Kategori';
        new bootstrap.Modal(document.getElementById('modalCategory')).show();
    }

    function saveCategory() {
        const id = document.getElementById('cat_id').value;
        const name = document.getElementById('cat_name').value;

        if(!name) return Swal.fire('Gagal', 'Nama kategori wajib diisi.', 'warning');

        Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        const request = id
            ? axios.put(`/api/cms/post-categories/${id}`, { name })
            : axios.post('/api/cms/post-categories', { name });

        request.then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalCategory')).hide();
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Kategori telah diperbarui.', timer: 1500, showConfirmButton: false });
            fetchCategories();
        }).catch(err => {
            Swal.fire('Error', err.response.data.message || 'Gagal menyimpan kategori.', 'error');
        });
    }

    function deleteCategory(id, name) {
        Swal.fire({
            title: 'Hapus Kategori?',
            text: `Kategori "${name}" akan dihapus secara permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(res => {
            if(res.isConfirmed) {
                Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                axios.delete(`/api/cms/post-categories/${id}`)
                    .then(() => {
                        Swal.fire('Terhapus!', 'Kategori telah dihapus.', 'success');
                        fetchCategories();
                    })
                    .catch(err => {
                        Swal.fire('Gagal', err.response.data.message || 'Kategori mungkin masih digunakan.', 'error');
                    });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchCategories);
</script>

<style>
/* PRIMARY COLOR */
.bg-indigo {
    background: linear-gradient(135deg, #5c6bc0, #3f51b5) !important;
}
.text-indigo {
    color: #5c6bc0 !important;
}

/* BUTTON */
.btn-indigo {
    background: linear-gradient(135deg, #5c6bc0, #3f51b5);
    color: #fff;
    border: none;
}
.btn-indigo:hover {
    background: linear-gradient(135deg, #3f51b5, #303f9f);
    color: #fff;
}

/* BADGE FIX (INI YANG PENTING) */
.badge {
    font-weight: 600;
}

.badge.bg-indigo {
    color: #fff !important;
}

.badge i {
    color: #1e293b !important;
}

/* FIX BADGE JUMLAH POST */
.badge.text-indigo {
    background-color: rgba(92, 107, 192, 0.1) !important;
    color: #3f51b5 !important;
    border: 1px solid rgba(92, 107, 192, 0.25);
}

/* ICON BUTTON */
.btn.text-indigo i {
    color: #5c6bc0 !important;
}

.btn.text-danger i {
    color: #dc3545 !important;
}

/* TABLE IMPROVEMENT */
.table td {
    padding: 0.75rem 1.25rem;
}

.table tbody tr:hover {
    background-color: #f8fafc;
}

/* TEXT GLOBAL BIAR GA PUDAR */
.card,
.table {
    color: #2c3e50;
}

/* INPUT MODAL */
.form-control {
    color: #2c3e50 !important;
}

/* PLACEHOLDER */
::placeholder {
    color: #999 !important;
}
</style>
@endsection
