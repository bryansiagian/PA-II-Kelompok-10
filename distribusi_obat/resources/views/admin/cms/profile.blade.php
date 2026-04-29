@extends('layouts.backoffice')

@section('page_title', 'Manajemen Profil & Kontak')

@section('content')
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>

<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold text-primary mb-1">Pengaturan Konten Website</h4>
        <div class="text-muted small">Kelola narasi yayasan dan informasi informasi penting pada Landing Page.</div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-bottom py-3">
            <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i> Editor Konten Terstruktur</h6>
        </div>

        <div class="card-body p-4">
            <ul class="nav nav-pills mb-4 bg-light p-2 rounded-pill justify-content-center custom-tab">
                <li class="nav-item"><button class="nav-link active rounded-pill fw-bold px-4" data-bs-toggle="pill" data-bs-target="#tab-about">Tentang Kami</button></li>
                <li class="nav-item ms-2"><button class="nav-link rounded-pill fw-bold px-4" data-bs-toggle="pill" data-bs-target="#tab-history">Sejarah</button></li>
                <li class="nav-item ms-2"><button class="nav-link rounded-pill fw-bold px-4" data-bs-toggle="pill" data-bs-target="#tab-vision">Visi & Misi</button></li>
            </ul>

            <div class="tab-content">
                <!-- ABOUT -->
                <div class="tab-pane fade show active" id="tab-about">
                    <form onsubmit="updateProfileContent(event, 'about')">
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Judul Seksi</label>
                            <input type="text" id="about_title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Isi Konten</label>
                            <textarea id="about_content" class="editor-target"></textarea>
                        </div>
                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Update Profil</button>
                        </div>
                    </form>
                </div>

                <!-- HISTORY -->
                <div class="tab-pane fade" id="tab-history">
                    <form onsubmit="updateProfileContent(event, 'history')">
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Judul Sejarah</label>
                            <input type="text" id="history_title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Isi Konten</label>
                            <textarea id="history_content" class="editor-target"></textarea>
                        </div>
                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Simpan Sejarah</button>
                        </div>
                    </form>
                </div>

                <!-- VISION & MISSION (REVISED) -->
                <div class="tab-pane fade" id="tab-vision">
                    <form onsubmit="updateVisionMission(event)">
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase text-muted">Judul Utama</label>
                            <input type="text" id="vision_mission_title" class="form-control" placeholder="Contoh: Visi & Misi Kami" required>
                        </div>

                        <div class="bg-light p-3 rounded-4 mb-4">
                            <label class="small fw-bold text-primary text-uppercase mb-2"><i class="bi bi-eye me-1"></i> Kalimat Visi</label>
                            <textarea id="vision_text" class="form-control" rows="2" placeholder="Tuliskan visi utama yayasan..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="small fw-bold text-uppercase text-primary mb-2"><i class="bi bi-list-check me-1"></i> Poin-Poin Misi (Akan Berurutan Angka)</label>
                            <div id="misi-container"></div>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill mt-2" onclick="addMisiRow()">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Poin Misi
                            </button>
                        </div>

                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Simpan Visi Misi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
let editorInstances = {};

document.addEventListener('DOMContentLoaded', () => {
    const textareas = ['about_content', 'history_content'];
    const editorPromises = textareas.map(id => {
        return ClassicEditor.create(document.querySelector('#' + id), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'undo', 'redo'],
        }).then(editor => { editorInstances[id] = editor; });
    });

    Promise.all(editorPromises).then(() => { loadCmsData(); });
});

function loadCmsData() {
    axios.get('/api/public/landing-page').then(res => {
        const profiles = res.data.profiles;
        // About & History
        ['about','history'].forEach(k => {
            if(profiles[k]) {
                document.getElementById(k + '_title').value = profiles[k].title;
                if(editorInstances[k + '_content']) editorInstances[k + '_content'].setData(profiles[k].content);
            }
        });

        // Vision Mission Logic
        if(profiles['vision_mission']) {
            document.getElementById('vision_mission_title').value = profiles['vision_mission'].title;
            const rawContent = profiles['vision_mission'].content;

            // Mencoba memisahkan Visi dan Misi dari HTML yang tersimpan
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = rawContent;

            const visionP = tempDiv.querySelector('.vision-text');
            if(visionP) document.getElementById('vision_text').value = visionP.innerText;

            const misiItems = tempDiv.querySelectorAll('ol li');
            const container = document.getElementById('misi-container');
            container.innerHTML = '';
            if(misiItems.length > 0) {
                misiItems.forEach(item => addMisiRow(item.innerText));
            } else { addMisiRow(); }
        } else { addMisiRow(); }
    });
}

function addMisiRow(val = '') {
    const container = document.getElementById('misi-container');
    const index = container.children.length + 1;
    const div = document.createElement('div');
    div.className = 'input-group mb-2 misi-row';
    div.innerHTML = `
        <span class="input-group-text bg-white border-end-0 fw-bold text-primary">${index}</span>
        <input type="text" class="form-control border-start-0 misi-input" value="${val}" placeholder="Tuliskan poin misi..." required>
        <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove(); reorderMisi();"><i class="bi bi-trash"></i></button>
    `;
    container.appendChild(div);
}

function reorderMisi() {
    document.querySelectorAll('.misi-row .input-group-text').forEach((span, i) => span.innerText = i + 1);
}

function updateVisionMission(event) {
    event.preventDefault();
    const title = document.getElementById('vision_mission_title').value;
    const vision = document.getElementById('vision_text').value;
    const misiInputs = document.querySelectorAll('.misi-input');

    // Susun HTML secara manual agar rapi di Welcome Page
    let htmlContent = `
        <div class="mb-4">
            <h5 class="fw-bold text-primary"><i class="bi bi-eye me-2"></i>Visi</h5>
            <p class="vision-text bg-light p-3 rounded-3 border-start border-4 border-primary">${vision}</p>
        </div>
        <div>
            <h5 class="fw-bold text-primary"><i class="bi bi-list-ol me-2"></i>Misi</h5>
            <ol class="misi-list">
    `;

    misiInputs.forEach(input => {
        if(input.value.trim() !== '') htmlContent += `<li class="mb-2">${input.value}</li>`;
    });

    htmlContent += `</ol></div>`;

    sendUpdate('vision_mission', title, htmlContent, event.submitter);
}

function updateProfileContent(event, key) {
    event.preventDefault();
    const title = document.getElementById(key + '_title').value;
    const content = editorInstances[key + '_content'].getData();
    sendUpdate(key, title, content, event.submitter);
}

function sendUpdate(key, title, content, btn) {
    const formData = new FormData();
    formData.append('key', key);
    formData.append('title', title);
    formData.append('content', content);
    formData.append('_method', 'PUT');

    btn.disabled = true;
    Swal.fire({title:'Menyimpan...', allowOutsideClick:false, didOpen:()=>Swal.showLoading()});
    axios.post('/api/cms/profile', formData)
        .then(() => {
            Swal.fire({icon:'success', title:'Konten Diperbarui', timer:1500, showConfirmButton:false});
            loadCmsData();
        })
        .catch(() => Swal.fire('Error','Gagal memperbarui data','error'))
        .finally(()=> btn.disabled=false);
}
</script>

<style>
.custom-tab .nav-link { background: #e2e8f0; color: #475569 !important; margin: 0 5px; }
.custom-tab .nav-link.active { background: #00838f !important; color: #fff !important; }
.ck-editor__editable { min-height: 200px; }
</style>
@endsection
