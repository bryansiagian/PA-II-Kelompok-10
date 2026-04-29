@extends('layouts.portal')

@section('content')
<style>
    :root {
        --primary: #00838f;
        --secondary: #2c4964;
        --hover-color: #006064;
    }

    /* Override Bootstrap Primary untuk Konsistensi Hijau */
    .text-primary { color: var(--primary) !important; }
    .btn-outline-primary { border-color: var(--primary) !important; color: var(--primary) !important; }
    .btn-outline-primary:hover { background-color: var(--primary) !important; color: #fff !important; border-color: var(--primary) !important; }
    .spinner-border.text-primary { color: var(--primary) !important; }

    .news-header { background: #fff; border-bottom: 1px solid #eee; padding: 30px 0; }
    .post-card { transition: 0.3s; border-radius: 20px; overflow: hidden; height: 100%; border: none; background: #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .post-card:hover { transform: translateY(-8px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
    .post-img { height: 220px; width: 100%; object-fit: cover; }

    .category-pill { cursor: pointer; transition: 0.3s; border: 1px solid #ddd; color: #666; font-size: 13px; padding: 6px 18px; border-radius: 30px; background: #fff; display: inline-block; margin-right: 5px; margin-bottom: 10px; font-weight: 500; }
    .category-pill:hover { border-color: var(--primary); color: var(--primary); }
    .category-pill.active { background: var(--primary); color: #fff; border-color: var(--primary); box-shadow: 0 4px 10px rgba(0, 131, 143, 0.3); }

    /* Modal Styling */
    .modal-content { border-radius: 25px; border: none; }
    #modalPostImg { width: 100%; height: 350px; object-fit: cover; border-radius: 20px; margin-bottom: 20px; }
    .post-content-full { line-height: 1.8; color: #555; font-size: 1.05rem; }
    .post-meta { font-size: 0.85rem; color: #999; display: flex; gap: 15px; margin-bottom: 15px; }

    .cursor-pointer { cursor: pointer; }
</style>

<!-- Header & Search -->
<div class="news-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="fw-bold text-dark m-0">Berita & <span class="text-primary">Kegiatan</span></h2>
                <p class="text-muted mb-0">Informasi terbaru seputar distribusi farmasi dan aktivitas yayasan.</p>
            </div>
            <div class="col-lg-6 mt-3 mt-lg-0">
                <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                    <span class="input-group-text bg-white border-0 ps-4"><i class="bi bi-search text-primary"></i></span>
                    <input type="text" id="newsSearch" class="form-control border-0 py-3 shadow-none" placeholder="Cari judul berita atau kegiatan..." onkeyup="filterPosts()">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4 mb-5">
    <!-- Filter Kategori -->
    <div id="categoryContainer" class="mb-4 text-center text-lg-start">
        <div class="category-pill active" onclick="setCategory('', this)">Semua Postingan</div>
    </div>

    <!-- Grid Berita -->
    <div id="postsGrid" class="row g-4">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted small fw-bold">Memuat kabar terbaru...</p>
        </div>
    </div>
</div>

<!-- MODAL DETAIL BERITA -->
<div class="modal fade" id="postDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-0 pb-0 pe-4 pt-4">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 p-md-5 pt-0">
                <img id="modalPostImg" src="" alt="Thumbnail" class="border">
                <div class="post-meta mt-3">
                    <span><i class="bi bi-calendar3 me-1 text-primary"></i> <span id="modalPostDate"></span></span>
                    <span><i class="bi bi-tag me-1 text-primary"></i> <span id="modalPostCategory" class="text-primary fw-bold"></span></span>
                    <span><i class="bi bi-person me-1 text-primary"></i> <span id="modalPostAuthor">Admin</span></span>
                </div>
                <h2 id="modalPostTitle" class="fw-bold text-dark mb-4">Judul Berita</h2>
                <div id="modalPostContent" class="post-content-full"></div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    let allPosts = [];
    let currentCategory = '';

    document.addEventListener('DOMContentLoaded', () => {
        fetchPostCategories();
        fetchAllPosts();
    });

    function fetchPostCategories() {
        axios.get('/api/public/post-categories').then(res => {
            const container = document.getElementById('categoryContainer');
            res.data.forEach(cat => {
                const span = document.createElement('div');
                span.className = 'category-pill';
                span.innerText = cat.name;
                span.onclick = () => setCategory(cat.name, span);
                container.appendChild(span);
            });
        });
    }

    function fetchAllPosts() {
        // Gunakan rute publik
        axios.get('/api/public/posts').then(res => {
            // FIX: Gunakan comparison yang lebih fleksibel (==)
            // Dan cek status '1' (Published). Kita asumsikan 'active' sudah difilter di Backend.
            allPosts = res.data.filter(p => p.status == 1);

            console.log("Data diterima:", res.data); // Debugging
            renderPosts(allPosts);
        }).catch(err => {
            console.error(err);
            document.getElementById('postsGrid').innerHTML = '<p class="text-center text-muted">Gagal memuat berita.</p>';
        });
    }

    function renderPosts(data) {
        const grid = document.getElementById('postsGrid');
        let html = '';

        if (!data || data.length === 0) {
            grid.innerHTML = '<div class="col-12 text-center py-5 text-muted">Tidak ada berita yang ditemukan.</div>';
            return;
        }

        data.forEach(p => {
            const img = p.image ? `/storage/${p.image}` : 'https://placehold.co/600x400';
            const date = new Date(p.created_at).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' });
            const catName = p.category?.name || 'Informasi';

            html += `
            <div class="col-lg-4 col-md-6">
                <div class="post-card d-flex flex-column h-100">
                    <img src="${img}" class="post-img" alt="Thumbnail">
                    <div class="p-4 d-flex flex-column flex-grow-1">
                        <div class="post-meta mb-2" style="font-size: 11px;">
                            <span><i class="bi bi-calendar3 me-1 text-primary"></i> ${date}</span>
                            <span class="text-primary fw-bold text-uppercase">${catName}</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-3">${p.title}</h5>
                        <p class="text-muted small mb-4">${p.content.substring(0, 100)}...</p>
                        <div class="mt-auto">
                            <button onclick="openPostDetail('${p.id}')" class="btn btn-outline-primary btn-sm rounded-pill px-4 w-100 fw-bold">
                                Baca Selengkapnya
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        grid.innerHTML = html;
    }

    function setCategory(catName, element) {
        document.querySelectorAll('.category-pill').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
        currentCategory = catName;
        filterPosts();
    }

    function filterPosts() {
        const keyword = document.getElementById('newsSearch').value.toLowerCase();
        const filtered = allPosts.filter(p => {
            const matchTitle = p.title.toLowerCase().includes(keyword);
            const matchCat = currentCategory === "" || (p.category && p.category.name === currentCategory);
            return matchTitle && matchCat;
        });
        renderPosts(filtered);
    }

    function openPostDetail(id) {
        const p = allPosts.find(item => item.id == id);
        if(!p) return;

        const date = new Date(p.created_at).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' });

        document.getElementById('modalPostImg').src = p.image ? `/storage/${p.image}` : 'https://placehold.co/600x400';
        document.getElementById('modalPostTitle').innerText = p.title;
        document.getElementById('modalPostDate').innerText = date;
        document.getElementById('modalPostCategory').innerText = p.category?.name || 'INFO';
        document.getElementById('modalPostAuthor').innerText = p.author?.name || 'Administrator';
        document.getElementById('modalPostContent').innerHTML = p.content;

        const modal = new bootstrap.Modal(document.getElementById('postDetailModal'));
        modal.show();
    }
</script>
@endsection
