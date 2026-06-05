@extends('layouts.portal')

@section('content')

<section class="py-5 bg-light min-vh-100">
    <div class="container">

        <!-- Tombol Kembali -->
        <div class="mb-4">
            <a href="/posts" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i>
                Kembali ke Berita
            </a>
        </div>

        <!-- Content -->
        <div id="detailContent">
            <div class="text-center py-5">
                <div class="spinner-border text-info"></div>
                <div class="mt-3 text-muted">Memuat detail berita...</div>
            </div>
        </div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {

    function getBadgeColor(name) {
        const colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning text-dark', 'bg-danger', 'bg-secondary', 'bg-dark'];
        let hash = 0;
        for (let i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        return colors[Math.abs(hash) % colors.length];
    }

    axios.get('/api/public/posts/{{ $id }}')
    .then(res => {
        const p = res.data;

        const postType = p.category?.name || p.type || '';
        const badgeClass = postType ? getBadgeColor(postType) : 'bg-secondary';

        const image = p.image
            ? `/storage/${p.image}`
            : 'https://placehold.co/1200x600?text=No+Image';

        const tanggal = new Date(p.created_at).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });

        document.getElementById('detailContent').innerHTML = `
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">

                <!-- Gambar -->
                <div class="post-image-wrapper">
                    <img src="${image}" class="post-image" alt="${p.title}">
                </div>

                <!-- Content -->
                <div class="card-body p-lg-5 p-4">

                    <!-- Meta -->
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                        ${postType ? `<span class="badge ${badgeClass} px-3 py-2 rounded-pill">${postType}</span>` : ''}
                        <small class="text-muted">
                            <i class="bi bi-calendar-event me-1"></i>${tanggal}
                        </small>
                    </div>

                    <!-- Judul -->
                    <h1 class="post-title fw-bold mb-4">${p.title}</h1>

                    <!-- Isi -->
                    <div class="post-content text-secondary">${p.content}</div>

                </div>
            </div>
        `;
    })
    .catch(() => {
        document.getElementById('detailContent').innerHTML = `
            <div class="text-center py-5">
                <h4 class="text-danger fw-bold">Berita tidak ditemukan</h4>
            </div>
        `;
    });

});
</script>

<style>
.post-image-wrapper {
    width: 100%;
    height: 520px;
    overflow: hidden;
    background: #f1f5f9;
}

.post-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.post-title {
    font-size: 2.5rem;
    line-height: 1.3;
    color: #0f172a;
}

.post-content {
    font-size: 1.1rem;
    line-height: 2;
}

.post-content p {
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .post-image-wrapper { height: 240px; }
    .post-title { font-size: 1.8rem; line-height: 1.4; }
    .post-content { font-size: 1rem; line-height: 1.9; }
}
</style>

@endsection
