@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0">Konten Berita & Kegiatan</h4>
            <div class="text-muted">Kelola publikasi informasi terbaru untuk Yayasan E-Pharma</div>
        </div>
        <div class="ms-3">
            <button class="btn btn-indigo shadow-sm" data-bs-toggle="modal" data-bs-target="#modalPost">
                <i class="ph-plus-circle me-2"></i> Buat Post Baru
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">
            <h5 class="mb-0 fw-bold"><i class="ph-article me-2 text-primary"></i>Daftar Postingan</h5>
            <div class="ms-auto">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <span class="input-group-text bg-light border-0"><i class="ph-magnifying-glass"></i></span>
                    <input type="text" class="form-control bg-light border-0" placeholder="Cari postingan...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover text-nowrap align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3" style="width: 150px;">Tanggal</th>
                        <th>Judul Postingan</th>
                        <th>Kategori</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="postTableBody">
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="spinner-border spinner-border-sm text-muted me-2"></div>
                            Memuat database konten...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH/EDIT POST (Limitless Style) -->
<div class="modal fade" id="modalPost" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-indigo text-white border-0">
                <h6 class="modal-title fw-bold" id="modalPostLabel"><i class="ph-plus-circle me-2"></i>Buat Konten Baru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="postForm" onsubmit="savePost(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="post_id" name="id">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold small">Judul Postingan</label>
                            <input type="text" name="title" id="post_title" class="form-control border-light-subtle" placeholder="Contoh: Penyaluran Obat ke Wilayah Pelosok" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Kategori</label>
                            <select name="post_category_id" id="post_category_id" class="form-select border-light-subtle" required>
                                <option value="" selected disabled>Pilih Kategori</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Isi Konten</label>
                        <textarea name="content" id="post_content" class="form-control border-light-subtle" rows="6" placeholder="Tulis narasi lengkap di sini..." required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Gambar Cover</label>
                            <input type="file" name="image" class="form-control border-light-subtle" accept="image/*">
                            <div class="form-text fs-xs text-muted">Format: JPG, PNG (Max 2MB)</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Status Publikasi</label>
                            <select name="status" id="post_status" class="form-select border-light-subtle">
                                <option value="0">Simpan sebagai Draft</option>
                                <option value="1">Langsung Publikasikan</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" id="btnSavePost" class="btn btn-indigo px-4 fw-bold">
                        <i class="ph-paper-plane-tilt me-2"></i>SIMPAN POSTINGAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Konfigurasi Axios Token
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function initPostsPage() {
        // Load Kategori
        axios.get('/api/post-categories').then(res => {
            const select = document.getElementById('post_category_id');
            res.data.forEach(cat => {
                select.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
            });
            loadPosts();
        });
    }

    function loadPosts() {
        const tableBody = document.getElementById('postTableBody');
        axios.get('/api/cms/posts').then(res => {
            let html = '';
            res.data.forEach(p => {
                const date = new Date(p.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });

                // Styling badge sesuai status
                const statusHtml = p.status == 1
                    ? `<span class="badge bg-success bg-opacity-10 text-success fw-bold"><i class="ph-check-circle me-1"></i>PUBLISHED</span>`
                    : `<span class="badge bg-warning bg-opacity-10 text-warning fw-bold"><i class="ph-clock me-1"></i>DRAFT</span>`;

                html += `
                <tr>
                    <td class="ps-3 text-muted small">${date}</td>
                    <td>
                        <div class="fw-bold text-dark">${p.title}</div>
                        <div class="fs-xs text-muted"><i class="ph-user-circle me-1"></i>${p.author ? p.author.name : 'Administrator'}</div>
                    </td>
                    <td><span class="badge bg-light text-indigo border-0 px-2">${p.category ? p.category.name : 'Uncategorized'}</span></td>
                    <td class="text-center">${statusHtml}</td>
                    <td class="text-center pe-3">
                        <div class="d-inline-flex">
                            <button onclick="editPost(${p.id})" class="btn btn-sm btn-light text-primary border-0 me-2" title="Edit">
                                <i class="ph-note-pencil fs-base"></i>
                            </button>
                            <button onclick="deletePost(${p.id}, '${p.title}')" class="btn btn-sm btn-light text-danger border-0" title="Hapus">
                                <i class="ph-trash fs-base"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            });
            tableBody.innerHTML = html || '<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data postingan.</td></tr>';
        });
    }

    // CRUD Logic tetap sama dengan penyesuaian visual feedback
    function savePost(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSavePost');
        const formData = new FormData(e.target);
        const postId = document.getElementById('post_id').value;

        btn.disabled = true;
        btn.innerHTML = '<i class="ph-spinner spinner me-2"></i> Memproses...';

        let url = '/api/cms/posts';
        if (postId) {
            url = `/api/cms/posts/${postId}`;
            formData.append('_method', 'PUT');
        }

        axios.post(url, formData).then(res => {
            bootstrap.Modal.getInstance(document.getElementById('modalPost')).hide();
            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data konten telah diperbarui', confirmButtonColor: '#4f46e5' });
            document.getElementById('postForm').reset();
            document.getElementById('post_id').value = '';
            loadPosts();
        }).catch(err => {
            Swal.fire('Gagal', err.response.data.message || 'Cek kembali inputan Anda', 'error');
        }).finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ph-paper-plane-tilt me-2"></i>SIMPAN POSTINGAN';
        });
    }

    function editPost(id) {
        axios.get(`/api/cms/posts/${id}`).then(res => {
            const p = res.data;
            document.getElementById('post_id').value = p.id;
            document.getElementById('post_title').value = p.title;
            document.getElementById('post_category_id').value = p.post_category_id;
            document.getElementById('post_content').value = p.content;
            document.getElementById('post_status').value = p.status;
            document.getElementById('modalPostLabel').innerHTML = '<i class="ph-note-pencil me-2"></i>Edit Postingan';
            new bootstrap.Modal(document.getElementById('modalPost')).show();
        });
    }

    function deletePost(id, title) {
        Swal.fire({
            title: 'Hapus Postingan?',
            text: `"${title}" akan dihapus permanen dari sistem.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#ef4444',
            buttonsStyling: true,
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light' }
        }).then(res => {
            if(res.isConfirmed) {
                axios.delete(`/api/cms/posts/${id}`).then(() => {
                    Swal.fire('Terhapus', 'Konten telah dihapus.', 'success');
                    loadPosts();
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', initPostsPage);
</script>

<style>
    /* Menyesuaikan warna indigo template Limitless */
    .bg-indigo { background-color: #5c68e2 !important; }
    .btn-indigo { background-color: #5c68e2; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #4e59cf; color: #fff; }
    .text-indigo { color: #5c68e2 !important; }

    .card { border-radius: 0.5rem; }
    .fs-base { font-size: 1rem; }
    .table td { padding: 0.75rem 1rem; }

    /* Animasi Spinner */
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
</style>
@endsection
