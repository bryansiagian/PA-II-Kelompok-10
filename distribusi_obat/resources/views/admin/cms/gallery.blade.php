@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Manajemen Galeri Media</h4>
            <p class="text-muted small mb-0">Kelola album foto dan video kegiatan yayasan.</p>
        </div>
        <button class="btn btn-indigo rounded-pill px-4 shadow-sm" onclick="openCreateModal()">
            <i class="ph-plus-circle me-2"></i> Buat Album Baru
        </button>
    </div>

    <div id="galleryList" class="row g-4">
        <!-- Render via JS -->
    </div>
</div>

<!-- MODAL GALERI (DINAMIS: TAMBAH/EDIT) -->
<div class="modal fade" id="modalGallery" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalTitle">Album Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="galleryForm" onsubmit="saveGallery(event)">
                <input type="hidden" name="gallery_id" id="gallery_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small fw-bold mb-1">Nama Album / Kegiatan</label>
                        <input type="text" name="title" id="form_title" class="form-control border-0 bg-light" placeholder="Contoh: Dokumentasi Baksos 2024" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold mb-1">Unggah Foto</label>
                        <input type="file" name="files[]" id="form_files" class="form-control border-0 bg-light" accept="image/*" multiple>
                        <div id="editNote" class="text-info small mt-1 d-none">
                            <i class="ph-info me-1"></i> Biarkan kosong jika tidak ingin menambah foto baru.
                        </div>
                    </div>
                    <div id="currentImagesPreview" class="d-flex flex-wrap gap-2 mt-2">
                        <!-- Preview gambar lama jika sedang edit -->
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-link text-body" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSave" class="btn btn-indigo rounded-pill px-4 py-2 fw-bold">
                        <i class="ph-check-circle me-2"></i> <span id="btnText">Simpan Album</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchGalleries() {
        axios.get('/api/cms/galleries').then(res => {
            let html = '';
            res.data.forEach(g => {
                const cover = g.files.length > 0 ? `/${g.files[0].file_path}` : 'https://placehold.co/400x300?text=No+Media';
                html += `
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="position-relative">
                            <img src="${cover}" class="card-img-top" style="height:200px; object-fit:cover">
                            <span class="badge bg-indigo position-absolute bottom-0 end-0 m-2 rounded-pill">
                                <i class="ph-image me-1"></i> ${g.files.length} Media
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1 text-indigo">${g.title}</h6>
                            <p class="small text-muted mb-3"><i class="ph-calendar me-1"></i> ${new Date(g.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'})}</p>

                            <div class="d-flex gap-2">
                                <button onclick="editGallery(${g.id})" class="btn btn-sm btn-indigo rounded-pill px-3 flex-grow-1">
                                    <i class="ph-pencil me-1"></i> Edit
                                </button>
                                <button onclick="deleteGallery(${g.id})" class="btn btn-sm btn-light text-danger rounded-pill px-3">
                                    <i class="ph-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            document.getElementById('galleryList').innerHTML = html || `
                <div class="col-12 text-center py-5">
                    <i class="ph-image-square opacity-25 mb-2" style="font-size: 4rem;"></i>
                    <p class="text-muted">Belum ada album galeri.</p>
                </div>`;
        });
    }

    function openCreateModal() {
        document.getElementById('galleryForm').reset();
        document.getElementById('gallery_id').value = '';
        document.getElementById('modalTitle').innerText = 'Album Baru';
        document.getElementById('btnText').innerText = 'Simpan Album';
        document.getElementById('editNote').classList.add('d-none');
        document.getElementById('form_files').required = true;
        document.getElementById('currentImagesPreview').innerHTML = '';
        new bootstrap.Modal(document.getElementById('modalGallery')).show();
    }

    function editGallery(id) {
        axios.get(`/api/cms/galleries`).then(res => {
            const data = res.data.find(g => g.id === id);
            if (!data) return;

            // Reset dan set mode edit
            document.getElementById('gallery_id').value = data.id;
            document.getElementById('form_title').value = data.title;
            document.getElementById('modalTitle').innerText = 'Edit Album';
            document.getElementById('btnText').innerText = 'Update Album';
            document.getElementById('editNote').classList.remove('d-none');
            document.getElementById('form_files').required = false;

            // Preview Foto saat ini
            let previewHtml = '';
            data.files.forEach(f => {
                previewHtml += `<img src="/${f.file_path}" class="rounded border shadow-sm" style="width:60px; height:60px; object-fit:cover">`;
            });
            document.getElementById('currentImagesPreview').innerHTML = previewHtml;

            new bootstrap.Modal(document.getElementById('modalGallery')).show();
        });
    }

    function saveGallery(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSave');
        const id = document.getElementById('gallery_id').value;
        const formData = new FormData(e.target);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';

        let url = '/api/cms/galleries';

        // Laravel Hack: Gunakan POST dengan _method PUT untuk pengiriman file via Axios
        if (id) {
            url = `/api/cms/galleries/${id}`;
            formData.append('_method', 'PUT');
        }

        axios.post(url, formData)
            .then(res => {
                bootstrap.Modal.getInstance(document.getElementById('modalGallery')).hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: id ? 'Album berhasil diperbarui' : 'Album telah dipublikasikan',
                    confirmButtonColor: '#5c6bc0'
                });
                fetchGalleries();
                e.target.reset();
            })
            .catch(err => {
                Swal.fire('Gagal', err.response?.data?.message || 'Terjadi kesalahan sistem', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = `<i class="ph-check-circle me-2"></i> ${id ? 'Update Album' : 'Simpan Album'}`;
            });
    }

    function deleteGallery(id) {
        Swal.fire({
            title: 'Hapus Album?',
            text: "Seluruh media di dalam album ini akan terhapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef5350',
            cancelButtonColor: '#f1f5f9',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(res => {
            if(res.isConfirmed) {
                axios.delete(`/api/cms/galleries/${id}`).then(() => {
                    Swal.fire({ icon: 'success', title: 'Terhapus', timer: 1500, showConfirmButton: false });
                    fetchGalleries();
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchGalleries);
</script>
@endsection