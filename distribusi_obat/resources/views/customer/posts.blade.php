@extends('layouts.portal')

@section('content')

<style>
    :root {
        --primary: #00838f;
        --secondary: #2c4964;
        --hover-color: #006064;
    }

    .text-primary {
        color: var(--primary) !important;
    }

    .btn-outline-primary {
        border-color: var(--primary) !important;
        color: var(--primary) !important;
    }

    .btn-outline-primary:hover {
        background-color: var(--primary) !important;
        color: #fff !important;
        border-color: var(--primary) !important;
    }

    .spinner-border.text-primary {
        color: var(--primary) !important;
    }

    .news-header {
        background: #fff;
        border-bottom: 1px solid #eee;
        padding: 30px 0;
    }

    .post-card {
        transition: 0.3s;
        border-radius: 20px;
        overflow: hidden;
        height: 100%;
        border: none;
        background: #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .post-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.1);
    }

    .post-img {
        height: 220px;
        width: 100%;
        object-fit: cover;
    }

    .category-pill {
        cursor: pointer;
        transition: 0.3s;
        border: 1px solid #ddd;
        color: #666;
        font-size: 13px;
        padding: 6px 18px;
        border-radius: 30px;
        background: #fff;
        display: inline-block;
        margin-right: 5px;
        margin-bottom: 10px;
        font-weight: 500;
    }

    .category-pill:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .category-pill.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        box-shadow: 0 4px 10px rgba(0, 131, 143, 0.3);
    }
</style>

<!-- HEADER -->
<div class="news-header">
    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-6">
                <h2 class="fw-bold text-dark m-0">
                    Berita &
                    <span class="text-primary">Kegiatan</span>
                </h2>

                <p class="text-muted mb-0">
                    Informasi terbaru seputar distribusi farmasi dan aktivitas yayasan.
                </p>
            </div>

            <div class="col-lg-6 mt-3 mt-lg-0">

                <div class="input-group shadow-sm rounded-pill overflow-hidden border">

                    <span class="input-group-text bg-white border-0 ps-4">
                        <i class="bi bi-search text-primary"></i>
                    </span>

                    <input
                        type="text"
                        id="newsSearch"
                        class="form-control border-0 py-3 shadow-none"
                        placeholder="Cari judul berita atau kegiatan..."
                        onkeyup="filterPosts()"
                    >

                </div>

            </div>

        </div>

    </div>
</div>

<!-- CONTENT -->
<div class="container mt-4 mb-5">

    <!-- FILTER -->
    <div id="categoryContainer" class="mb-4 text-center text-lg-start">

        <div class="category-pill active"
             onclick="setCategory('', this)">
            Semua Postingan
        </div>

    </div>

    <!-- GRID -->
    <div id="postsGrid" class="row g-4">

        <div class="col-12 text-center py-5">

            <div class="spinner-border text-primary"></div>

            <p class="mt-2 text-muted small fw-bold">
                Memuat kabar terbaru...
            </p>

        </div>

    </div>

</div>

<script>

    axios.defaults.headers.common['Authorization'] =
        'Bearer ' + '{{ session('api_token') }}';

    let allPosts = [];
    let currentCategory = '';

    document.addEventListener('DOMContentLoaded', () => {

        fetchPostCategories();
        fetchAllPosts();

    });

    function fetchPostCategories() {

        axios.get('/api/public/post-categories')
        .then(res => {

            const container =
                document.getElementById('categoryContainer');

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

        axios.get('/api/public/posts')
        .then(res => {

            allPosts = res.data.filter(p => p.status == 1);

            renderPosts(allPosts);

        })
        .catch(err => {

            console.error(err);

            document.getElementById('postsGrid').innerHTML = `
                <div class="col-12 text-center py-5 text-muted">
                    Gagal memuat berita.
                </div>
            `;

        });

    }

    function renderPosts(data) {

        const grid = document.getElementById('postsGrid');

        let html = '';

        if (!data || data.length === 0) {

            grid.innerHTML = `
                <div class="col-12 text-center py-5 text-muted">
                    Tidak ada berita ditemukan.
                </div>
            `;

            return;
        }

        data.forEach(p => {

            const img = p.image
                ? `/storage/${p.image}`
                : 'https://placehold.co/600x400';

            const date =
                new Date(p.created_at)
                .toLocaleDateString(
                    'id-ID',
                    {
                        day:'numeric',
                        month:'long',
                        year:'numeric'
                    }
                );

            const catName =
                p.category?.name || 'Informasi';

            html += `
                <div class="col-lg-4 col-md-6">

                    <div class="post-card d-flex flex-column h-100">

                        <img src="${img}"
                             class="post-img"
                             alt="Thumbnail">

                        <div class="p-4 d-flex flex-column flex-grow-1">

                            <div class="d-flex align-items-center gap-3 mb-2"
                                 style="font-size:12px;">

                                <span class="text-muted">
                                    <i class="bi bi-calendar3 me-1 text-primary"></i>
                                    ${date}
                                </span>

                                <span class="text-primary fw-bold text-uppercase">
                                    ${catName}
                                </span>

                            </div>

                            <h5 class="fw-bold text-dark mb-3">
                                ${p.title}
                            </h5>

                            <p class="text-muted small mb-4">
                                ${p.content.substring(0, 100)}...
                            </p>

                            <div class="mt-auto">

                                <a href="/posts/${p.id}"
                                   class="btn btn-outline-primary btn-sm rounded-pill px-4 w-100 fw-bold">

                                    Baca Selengkapnya

                                </a>

                            </div>

                        </div>

                    </div>

                </div>
            `;
        });

        grid.innerHTML = html;
    }

    function setCategory(catName, element) {

        document
            .querySelectorAll('.category-pill')
            .forEach(el => el.classList.remove('active'));

        element.classList.add('active');

        currentCategory = catName;

        filterPosts();
    }

    function filterPosts() {

        const keyword =
            document.getElementById('newsSearch')
            .value
            .toLowerCase();

        const filtered = allPosts.filter(p => {

            const matchTitle =
                p.title.toLowerCase().includes(keyword);

            const matchCat =
                currentCategory === "" ||
                (
                    p.category &&
                    p.category.name === currentCategory
                );

            return matchTitle && matchCat;

        });

        renderPosts(filtered);
    }

</script>

@endsection
