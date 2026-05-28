@extends('layouts.backoffice')

@section('page_title', 'Manajemen Galeri Media')

@section('content')
<div class="container-fluid">

    {{-- ===================== HEADER ===================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Manajemen Galeri Media</h4>
            <p class="text-muted small mb-0">Kelola album foto dan video YouTube kegiatan yayasan.</p>
        </div>
        <button class="btn btn-indigo rounded-pill px-4 shadow-sm" onclick="openCreateModal()">
            <i class="ph-plus-circle me-2"></i> Buat Album Baru
        </button>
    </div>

    {{-- ===================== GALLERY GRID ===================== --}}
    <div id="galleryList" class="row g-4">
        <div class="col-12 text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm text-indigo me-2"></div>
            Memuat galeri...
        </div>
    </div>

</div>


{{-- =====================================================
     MODAL TAMBAH / EDIT ALBUM
===================================================== --}}
<div class="modal fade" id="modalGallery" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalTitle">
                    <i class="ph-image-square me-2"></i> Album Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                <input type="hidden" id="gallery_id">

                {{-- Judul Album --}}
                <div class="mb-4">
                    <label class="field-label">Nama Album / Kegiatan</label>
                    <input type="text" id="form_title" class="form-control form-field"
                           placeholder="Contoh: Dokumentasi Baksos 2024" required>
                </div>

                {{-- Tab Foto / Video --}}
                <ul class="nav nav-tabs mb-3" id="mediaTab">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold" id="tab-foto" onclick="switchTab('foto')">
                            <i class="ph-image me-1"></i> Foto
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold" id="tab-video" onclick="switchTab('video')">
                            <i class="ph-youtube-logo me-1"></i> Video YouTube
                        </button>
                    </li>
                </ul>

                {{-- Panel Foto --}}
                <div id="panelFoto">
                    <div class="upload-area border-2 border-dashed rounded-3 p-4 text-center position-relative"
                         id="uploadArea"
                         ondragover="event.preventDefault(); this.classList.add('drag-over')"
                         ondragleave="this.classList.remove('drag-over')"
                         ondrop="handleDrop(event)">
                        <i class="ph-upload-simple text-indigo" style="font-size:2rem;"></i>
                        <div class="fw-semibold text-dark mt-2">Klik atau drag foto ke sini</div>
                        <div class="text-muted small">JPG, PNG, WEBP — maks. 2MB per file</div>
                        <input type="file" id="form_files" accept="image/*" multiple
                               class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                               style="cursor:pointer;" onchange="previewNewFiles(this)">
                    </div>
                    <div id="newFilesPreview" class="d-flex flex-wrap gap-2 mt-3"></div>
                </div>

                {{-- Panel Video YouTube --}}
                <div id="panelVideo" class="d-none">
                    <label class="field-label">Link YouTube (satu per baris)</label>
                    <textarea id="form_youtube_urls" class="form-control form-field"
                              rows="4"
                              placeholder="https://www.youtube.com/watch?v=xxxx&#10;https://youtu.be/yyyy"></textarea>
                    <div class="text-muted small mt-1">Tempel satu URL YouTube per baris. Format pendek (youtu.be) juga didukung.</div>
                    <div id="youtubePreview" class="d-flex flex-wrap gap-2 mt-3"></div>
                </div>

                {{-- Media yang Sudah Ada (Edit Mode) --}}
                <div id="existingFilesSection" class="d-none mt-4">
                    <label class="field-label">Media Saat Ini</label>
                    <div id="existingFilesPreview" class="d-flex flex-wrap gap-2"></div>
                </div>

            </div>

            <div class="modal-footer border-0 bg-light py-2">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none"
                        data-bs-dismiss="modal">BATAL</button>
                <button id="btnSave" onclick="saveGallery()"
                        class="btn btn-indigo px-4 fw-bold shadow-sm rounded-pill">
                    <i class="ph-check-circle me-2"></i>
                    <span id="btnText">Simpan Album</span>
                </button>
            </div>

        </div>
    </div>
</div>


{{-- =====================================================
     JAVASCRIPT
===================================================== --}}
<script>
axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
axios.defaults.headers.common['X-CSRF-TOKEN']  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let modalInstance    = null;
let newFilesSelected = [];

document.addEventListener('DOMContentLoaded', () => {
    modalInstance = new bootstrap.Modal(document.getElementById('modalGallery'));
    document.getElementById('form_youtube_urls').addEventListener('input', previewYoutube);
    fetchGalleries();
});

// ─── TAB SWITCH ──────────────────────────────────────

function switchTab(tab) {
    document.getElementById('panelFoto').classList.toggle('d-none', tab !== 'foto');
    document.getElementById('panelVideo').classList.toggle('d-none', tab !== 'video');
    document.getElementById('tab-foto').classList.toggle('active', tab === 'foto');
    document.getElementById('tab-video').classList.toggle('active', tab === 'video');
    if (tab === 'video') previewYoutube();
}

// ─── YOUTUBE HELPER ──────────────────────────────────

function getYoutubeId(url) {
    if (!url) return null;
    const patterns = [
        /youtube\.com\/watch\?v=([^&]+)/,
        /youtu\.be\/([^?]+)/,
        /youtube\.com\/embed\/([^?]+)/,
    ];
    for (const p of patterns) {
        const m = url.match(p);
        if (m) return m[1];
    }
    return null;
}

function previewYoutube() {
    const raw  = document.getElementById('form_youtube_urls').value;
    const urls = raw.split('\n').map(u => u.trim()).filter(Boolean);
    const container = document.getElementById('youtubePreview');

    if (!urls.length) { container.innerHTML = ''; return; }

    let html = '';
    urls.forEach(url => {
        const vid = getYoutubeId(url);
        if (vid) {
            html += `
            <div class="position-relative" style="width:120px;">
                <img src="https://img.youtube.com/vi/${vid}/mqdefault.jpg"
                     class="rounded-2 border" style="width:120px;height:68px;object-fit:cover;">
                <div class="position-absolute top-50 start-50 translate-middle">
                    <i class="ph-youtube-logo text-danger" style="font-size:1.8rem;"></i>
                </div>
            </div>`;
        } else {
            html += `<div class="text-danger small py-2 w-100"><i class="ph-warning me-1"></i>URL tidak valid: ${url}</div>`;
        }
    });
    container.innerHTML = html;
}

// ─── FETCH & RENDER ──────────────────────────────────

function fetchGalleries() {
    document.getElementById('galleryList').innerHTML = `
        <div class="col-12 text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm text-indigo me-2"></div> Memuat...
        </div>`;

    axios.get('/api/cms/galleries')
        .then(res => {
            const galleries = res.data;

            if (!galleries.length) {
                document.getElementById('galleryList').innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="ph-images opacity-25 mb-2" style="font-size:4rem;"></i>
                        <p class="text-muted">Belum ada album galeri.</p>
                    </div>`;
                return;
            }

            let html = '';
            galleries.forEach(g => {
                const firstImg = g.files.find(f => f.file_type === 'image');
                const firstVid = g.files.find(f => f.file_type === 'video');
                let cover;
                if (firstImg) {
                    cover = `/storage/${firstImg.file_path}`;
                } else if (firstVid) {
                    const vid = getYoutubeId(firstVid.youtube_url);
                    cover = vid ? `https://img.youtube.com/vi/${vid}/mqdefault.jpg` : 'https://placehold.co/400x300?text=Video';
                } else {
                    cover = 'https://placehold.co/400x300?text=No+Media';
                }

                const photoCount = g.files.filter(f => f.file_type === 'image').length;
                const videoCount = g.files.filter(f => f.file_type === 'video').length;

                let thumbs = '';
                g.files.slice(0, 4).forEach((f, i) => {
                    const isLast = i === 3 && g.files.length > 4;
                    let thumbSrc;
                    if (f.file_type === 'image') {
                        thumbSrc = `/storage/${f.file_path}`;
                    } else {
                        const vid = getYoutubeId(f.youtube_url);
                        thumbSrc = vid ? `https://img.youtube.com/vi/${vid}/mqdefault.jpg` : 'https://placehold.co/48x48?text=YT';
                    }
                    thumbs += `
                        <div class="position-relative" style="width:48px;height:48px;flex-shrink:0;">
                            <img src="${thumbSrc}" class="rounded-2 w-100 h-100" style="object-fit:cover;"
                                 onerror="this.src='https://placehold.co/48x48?text=?'">
                            ${f.file_type === 'video' ? `<div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center rounded-2" style="background:rgba(0,0,0,.35);"><i class="ph-youtube-logo text-white" style="font-size:14px;"></i></div>` : ''}
                            ${isLast ? `<div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center rounded-2" style="background:rgba(0,0,0,.5);color:#fff;font-size:12px;font-weight:700;">+${g.files.length - 3}</div>` : ''}
                        </div>`;
                });

                html += `
                <div class="col-md-4 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">
                        <div class="position-relative">
                            <img src="${cover}" class="card-img-top" style="height:180px;object-fit:cover;"
                                 onerror="this.src='https://placehold.co/400x300?text=No+Media'">
                            <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-1">
                                ${photoCount > 0 ? `<span class="badge rounded-pill px-2 py-1" style="background:rgba(0,0,0,.6);font-size:.7rem;"><i class="ph-image me-1"></i>${photoCount}</span>` : ''}
                                ${videoCount > 0 ? `<span class="badge rounded-pill px-2 py-1" style="background:rgba(255,0,0,.75);font-size:.7rem;"><i class="ph-youtube-logo me-1"></i>${videoCount}</span>` : ''}
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="fw-bold text-dark mb-1">${g.title}</div>
                            <div class="text-muted small mb-3">
                                <i class="ph-calendar me-1"></i>
                                ${new Date(g.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'})}
                            </div>
                            ${thumbs ? `<div class="d-flex gap-1 mb-3">${thumbs}</div>` : ''}
                            <div class="d-flex gap-2">
                                <button onclick="editGallery(${g.id})"
                                        class="btn btn-sm btn-indigo rounded-pill px-3 flex-grow-1">
                                    <i class="ph-pencil me-1"></i> Edit
                                </button>
                                <button onclick="deleteGallery(${g.id})"
                                        class="btn btn-sm btn-light text-danger rounded-pill px-3">
                                    <i class="ph-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
            });

            document.getElementById('galleryList').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('galleryList').innerHTML = `
                <div class="col-12 text-center py-5 text-danger">Gagal memuat galeri.</div>`;
        });
}

// ─── OPEN CREATE MODAL ───────────────────────────────

function openCreateModal() {
    resetModal();
    document.getElementById('modalTitle').innerHTML = '<i class="ph-image-square me-2"></i> Album Baru';
    document.getElementById('btnText').innerText    = 'Simpan Album';
    document.getElementById('existingFilesSection').classList.add('d-none');
    switchTab('foto');
    modalInstance.show();
}

// ─── OPEN EDIT MODAL ─────────────────────────────────

function editGallery(id) {
    axios.get('/api/cms/galleries')
        .then(res => {
            const data = res.data.find(g => g.id === id);
            if (!data) return;

            resetModal();
            document.getElementById('gallery_id').value = data.id;
            document.getElementById('form_title').value = data.title;
            document.getElementById('modalTitle').innerHTML = '<i class="ph-pencil me-2"></i> Edit Album';
            document.getElementById('btnText').innerText    = 'Update Album';
            switchTab('foto');

            const section = document.getElementById('existingFilesSection');
            const preview = document.getElementById('existingFilesPreview');

            if (data.files.length > 0) {
                let html = '';
                data.files.forEach(f => {
                    if (f.file_type === 'image') {
                        html += `
                        <div class="position-relative existing-file-item" id="file-${f.id}">
                            <img src="/storage/${f.file_path}" class="rounded-2 border"
                                 style="width:80px;height:80px;object-fit:cover;"
                                 onerror="this.src='https://placehold.co/80x80?text=?'">
                            <button type="button" onclick="deleteGalleryFile(${f.id})"
                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle d-flex align-items-center justify-content-center p-0"
                                    style="width:22px;height:22px;transform:translate(40%,-40%);">
                                <i class="ph-x" style="font-size:10px;"></i>
                            </button>
                        </div>`;
                    } else {
                        const vid   = getYoutubeId(f.youtube_url);
                        const thumb = vid ? `https://img.youtube.com/vi/${vid}/mqdefault.jpg` : 'https://placehold.co/80x80?text=YT';
                        html += `
                        <div class="position-relative existing-file-item" id="file-${f.id}">
                            <img src="${thumb}" class="rounded-2 border"
                                 style="width:80px;height:80px;object-fit:cover;">
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center rounded-2"
                                 style="background:rgba(0,0,0,.3);">
                                <i class="ph-youtube-logo text-danger" style="font-size:1.4rem;"></i>
                            </div>
                            <button type="button" onclick="deleteGalleryFile(${f.id})"
                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle d-flex align-items-center justify-content-center p-0"
                                    style="width:22px;height:22px;transform:translate(40%,-40%);z-index:2;">
                                <i class="ph-x" style="font-size:10px;"></i>
                            </button>
                        </div>`;
                    }
                });
                preview.innerHTML = html;
                section.classList.remove('d-none');
            } else {
                section.classList.add('d-none');
            }

            modalInstance.show();
        });
}

// ─── PREVIEW FILE BARU ───────────────────────────────

function previewNewFiles(input) {
    newFilesSelected = Array.from(input.files);
    renderNewFilesPreview();
}

function handleDrop(event) {
    event.preventDefault();
    document.getElementById('uploadArea').classList.remove('drag-over');
    const dropped = Array.from(event.dataTransfer.files).filter(f => f.type.startsWith('image/'));
    newFilesSelected = [...newFilesSelected, ...dropped];
    renderNewFilesPreview();
}

function renderNewFilesPreview() {
    const container = document.getElementById('newFilesPreview');
    if (!newFilesSelected.length) { container.innerHTML = ''; return; }

    let html = '';
    newFilesSelected.forEach((f, i) => {
        const url = URL.createObjectURL(f);
        html += `
        <div class="position-relative" id="new-file-${i}">
            <img src="${url}" class="rounded-2 border" style="width:80px;height:80px;object-fit:cover;">
            <button type="button" onclick="removeNewFile(${i})"
                    class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle d-flex align-items-center justify-content-center p-0"
                    style="width:22px;height:22px;transform:translate(40%,-40%);">
                <i class="ph-x" style="font-size:10px;"></i>
            </button>
            <div class="text-muted mt-1" style="font-size:9px;max-width:80px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${f.name}</div>
        </div>`;
    });
    container.innerHTML = html;
}

function removeNewFile(index) {
    newFilesSelected.splice(index, 1);
    renderNewFilesPreview();
}

// ─── SAVE GALLERY ────────────────────────────────────

function saveGallery() {
    const btn          = document.getElementById('btnSave');
    const originalHtml = btn.innerHTML;
    const id           = document.getElementById('gallery_id').value;
    const title        = document.getElementById('form_title').value.trim();
    const youtubeUrls  = document.getElementById('form_youtube_urls').value.trim();

    if (!title) {
        Swal.fire({ icon: 'warning', title: 'Nama album wajib diisi', confirmButtonColor: '#5c6bc0' });
        return;
    }

    if (!id && newFilesSelected.length === 0 && !youtubeUrls) {
        Swal.fire({ icon: 'warning', title: 'Tambahkan minimal 1 foto atau 1 link YouTube', confirmButtonColor: '#5c6bc0' });
        return;
    }

    const formData = new FormData();
    formData.append('title', title);
    newFilesSelected.forEach(f => formData.append('files[]', f));
    if (youtubeUrls) formData.append('youtube_urls', youtubeUrls);

    const url = id ? `/api/cms/galleries/${id}` : '/api/cms/galleries';

    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

    axios.post(url, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
        .then(() => {
            modalInstance.hide();
            Swal.fire({
                icon: 'success',
                title: id ? 'Album diperbarui' : 'Album dibuat',
                timer: 1500,
                showConfirmButton: false,
            });
            fetchGalleries();
        })
        .catch(err => {
            Swal.fire('Gagal', err.response?.data?.message ?? 'Terjadi kesalahan', 'error');
        })
        .finally(() => {
            btn.disabled  = false;
            btn.innerHTML = originalHtml;
        });
}

// ─── DELETE GALLERY FILE ─────────────────────────────

function deleteGalleryFile(fileId) {
    Swal.fire({
        title: 'Hapus media ini?',
        text: 'Media akan dihapus permanen dari album.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return axios.delete(`/api/cms/gallery-files/${fileId}`)
                .catch(err => {
                    Swal.showValidationMessage(err.response?.data?.message ?? 'Gagal menghapus media.');
                });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then(result => {
        if (!result.isConfirmed) return;
        const el = document.getElementById(`file-${fileId}`);
        if (el) el.remove();
        const remaining = document.querySelectorAll('.existing-file-item');
        if (remaining.length === 0) {
            document.getElementById('existingFilesSection').classList.add('d-none');
        }
        Swal.fire({ icon: 'success', title: 'Media dihapus', timer: 1000, showConfirmButton: false });
    });
}

// ─── DELETE GALLERY ──────────────────────────────────

function deleteGallery(id) {
    Swal.fire({
        title: 'Hapus Album?',
        text: 'Seluruh media di dalam album ini akan terhapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return axios.delete(`/api/cms/galleries/${id}`)
                .catch(err => {
                    Swal.showValidationMessage(err.response?.data?.message ?? 'Gagal menghapus album.');
                });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then(result => {
        if (!result.isConfirmed) return;
        Swal.fire({ icon: 'success', title: 'Album dihapus', timer: 1200, showConfirmButton: false });
        fetchGalleries();
    });
}

// ─── RESET MODAL ─────────────────────────────────────

function resetModal() {
    newFilesSelected = [];
    document.getElementById('gallery_id').value               = '';
    document.getElementById('form_title').value               = '';
    document.getElementById('form_files').value               = '';
    document.getElementById('form_youtube_urls').value        = '';
    document.getElementById('newFilesPreview').innerHTML      = '';
    document.getElementById('youtubePreview').innerHTML       = '';
    document.getElementById('existingFilesPreview').innerHTML = '';
}
</script>


{{-- =====================================================
     CSS
===================================================== --}}
<style>
.bg-indigo  { background: #5c6bc0 !important; }
.btn-indigo { background: #5c6bc0; color: #fff; border: none; }
.btn-indigo:hover { background: #4a5ab0; color: #fff; }
.text-indigo { color: #5c6bc0 !important; }

.field-label {
    display: block;
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: .04em;
    margin-bottom: 6px;
}
.form-field {
    border-radius: 10px;
    min-height: 44px;
    border: 1px solid #dbe4ee;
    padding: 10px 14px;
    font-size: .9rem;
}
.form-field:focus {
    border-color: #5c6bc0;
    box-shadow: 0 0 0 3px rgba(92,107,192,.15);
    outline: none;
}
.upload-area {
    border: 2px dashed #c3cfe2 !important;
    border-radius: 12px;
    background: #f8faff;
    transition: all .2s;
    cursor: pointer;
}
.upload-area:hover,
.upload-area.drag-over {
    border-color: #5c6bc0 !important;
    background: #eef0fb;
}
.upload-area.drag-over { transform: scale(1.01); }

.nav-tabs .nav-link {
    color: #64748b;
    border: none;
    border-bottom: 2px solid transparent;
    border-radius: 0;
    padding: .5rem 1rem;
}
.nav-tabs .nav-link.active {
    color: #5c6bc0;
    border-bottom-color: #5c6bc0;
    background: transparent;
}
.nav-tabs { border-bottom: 1px solid #e2e8f0; }
</style>

@endsection
