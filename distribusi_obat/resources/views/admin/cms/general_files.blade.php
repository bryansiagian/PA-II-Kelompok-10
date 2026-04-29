@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0">Manajemen Galeri & Dokumentasi</h4>
            <div class="text-muted">Kelola foto kegiatan dan dokumentasi yayasan untuk Landing Page</div>
        </div>
        <div class="ms-3">
            <button class="btn btn-indigo shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGallery">
                <i class="ph-image-plus me-2"></i> Unggah Foto Baru
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">
            <h5 class="mb-0 fw-bold"><i class="ph-aperture me-2 text-primary"></i>Katalog Dokumentasi</h5>
            <div class="ms-auto">
                <span class="badge bg-indigo text-white fw-bold px-3 shadow-sm">
                    <i class="ph-globe me-1"></i> Publik di Website
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover text-nowrap align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3">Preview & Judul</th>
                        <th>Kategori Kegiatan</th>
                        <th>Diupload Oleh</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="galleryTableBody">
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="spinner-border spinner-border-sm text-muted me-2"></div>
                            Sinkronisasi galeri foto...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL UNGGAH GALERI (Limitless Style) -->
<div class="modal fade" id="modalGallery" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-image-plus me-2"></i>Unggah Dokumentasi Baru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="galleryForm" onsubmit="saveGallery(event)">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Judul Foto / Kegiatan</label>
                        <input type="text" name="title" class="form-control border-light-subtle" placeholder="Contoh: Penyaluran Obat Wilayah Utara" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pilih Foto</label>
                        <input type="file" name="image" class="form-control border-light-subtle" accept="image/*" required>
                        <div class="form-text fs-xs text-muted">Format: JPG, PNG, WEBP (Maksimal 2MB).</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small">Keterangan Singkat (Opsional)</label>
                        <textarea name="description" class="form-control border-light-subtle" rows="2" placeholder="Tulis deskripsi singkat..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" id="btnSaveGallery" class="btn btn-indigo px-4 fw-bold">
                        <i class="ph-cloud-arrow-up me-2"></i>UNGGAH SEKARANG
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchGallery() {
        const tableBody = document.getElementById('galleryTableBody');
        axios.get('/api/cms/gallery').then(res => {
            let html = '';
            res.data.forEach(g => {
                html += `
                <tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3 position-relative">
                                <img src="/storage/${g.image_path}" class="rounded shadow-sm border" width="60" height="40" style="object-fit: cover;">
                            </div>
                            <div>
                                <div class="fw-bold text-dark">${g.title}</div>
                                <div class="fs-xs text-muted"><i class="ph-calendar me-1"></i>${new Date(g.created_at).toLocaleDateString('id-ID')}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-indigo bg-opacity-10 text-indigo border-0 py-1 px-2 fw-bold">
                            DOKUMENTASI
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="ph-user-circle me-2 text-muted fs-base"></i>
                            <span class="small">${g.creator ? g.creator.name : 'Administrator'}</span>
                        </div>
                    </td>
                    <td class="text-center pe-3">
                        <div class="d-inline-flex">
                            <button onclick="window.open('/storage/${g.image_path}')" class="btn btn-sm btn-light text-primary border-0 me-2" title="Lihat Fullsize">
                                <i class="ph-eye"></i>
                            </button>
                            <button onclick="deleteGallery(${g.id}, '${g.title}')" class="btn btn-sm btn-light text-danger border-0" title="Hapus Foto">
                                <i class="ph-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            });
            tableBody.innerHTML = html || '<tr><td colspan="4" class="text-center py-4 text-muted small">Belum ada foto dalam galeri.</td></tr>';
        });
    }

    function saveGallery(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const btn = document.getElementById('btnSaveGallery');

        btn.disabled = true;
        btn.innerHTML = '<i class="ph-spinner spinner me-2"></i> Mengunggah...';

        axios.post('/api/cms/gallery', formData).then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalGallery')).hide();
            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Foto kegiatan telah ditambahkan ke galeri', confirmButtonColor: '#4f46e5' });
            fetchGallery();
            e.target.reset();
        }).catch(err => {
            Swal.fire('Gagal', err.response.data.message || 'Terjadi kesalahan saat unggah foto', 'error');
        }).finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ph-cloud-arrow-up me-2"></i>UNGGAH SEKARANG';
        });
    }

    function deleteGallery(id, title) {
        Swal.fire({
            title: 'Hapus Dokumentasi?',
            text: `Foto "${title}" akan dihapus permanen dari server.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            confirmButtonColor: '#ef4444',
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light' }
        }).then(res => {
            if(res.isConfirmed) {
                axios.delete(`/api/cms/gallery/${id}`).then(() => {
                    Swal.fire('Terhapus', 'Foto telah dihapus dari galeri.', 'success');
                    fetchGallery();
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchGallery);
</script>

<style>
    /* Styling Dasar Limitless Indigo */
    .bg-indigo { background-color: #5c68e2 !important; }
    .btn-indigo { background-color: #5c68e2; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #4e59cf; color: #fff; }
    .text-indigo { color: #5c68e2 !important; }

    .card { border-radius: 0.5rem; }
    .table td { padding: 0.85rem 1rem; }
    .fs-base { font-size: 1rem; }

    /* Animasi Spinner */
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }

    /* Hover effect row */
    .table-hover tbody tr:hover { background-color: rgba(92, 104, 226, 0.02); }
</style>
@endsection
