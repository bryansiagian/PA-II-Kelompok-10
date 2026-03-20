<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Yayasan E-Pharma - Logistik Farmasi Terpadu</title>

    <!-- Favicons -->
    <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
    <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

    <!-- Fonts (MediNest Standard) -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&family=Ubuntu:wght@400;500;700&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">

    <!-- Core Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #3fbbc0;
            --secondary: #2c4964;
            --light-bg: #f4f9f9;
        }

        body {
            font-family: 'Roboto', sans-serif;
            color: #444;
            overflow-x: hidden;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Poppins', sans-serif;
            color: var(--secondary);
        }

        /* Header & Navigation */
        .header {
            height: 85px;
            transition: all 0.5s;
            z-index: 1030;
            background: #fff;
        }

        .sitename {
            font-size: 28px;
            font-weight: 700;
            color: var(--secondary);
            margin: 0;
        }

        .sitename span {
            color: var(--primary);
        }

        /* Navmenu Styling */
        .navmenu ul {
            margin: 0;
            padding: 0;
            display: flex;
            list-style: none;
            align-items: center;
        }

        .navmenu a {
            color: var(--secondary);
            padding: 10px 15px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
        }

        .navmenu a:hover,
        .navmenu .active {
            color: var(--primary);
        }

        /* Mobile Sidebar (Offcanvas) */
        .mobile-nav-toggle {
            font-size: 28px;
            color: var(--secondary);
            cursor: pointer;
            line-height: 0;
            border: none;
            background: none;
        }

        .offcanvas {
            width: 280px !important;
        }

        .offcanvas-title {
            font-weight: 700;
            color: var(--secondary);
        }

        .offcanvas-body .nav-link {
            color: var(--secondary);
            font-weight: 600;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .offcanvas-body .nav-link i {
            margin-right: 15px;
            color: var(--primary);
            font-size: 1.2rem;
        }

        /* Dropdown Profile */
        .dropdown-menu-profile {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 10px;
            min-width: 200px;
        }

        .dropdown-item {
            border-radius: 8px;
            padding: 10px;
            font-weight: 500;
            color: var(--secondary);
        }

        .dropdown-item:hover {
            background: var(--light-bg);
            color: var(--primary);
        }

        /* Section Title */
        .section-title {
            text-align: center;
            padding-bottom: 40px;
            margin-top: 60px;
        }

        .section-title h2 {
            font-size: 32px;
            font-weight: 700;
            position: relative;
            margin-bottom: 20px;
            padding-bottom: 20px;
        }

        .section-title h2::after {
            content: "";
            position: absolute;
            display: block;
            width: 50px;
            height: 3px;
            background: var(--primary);
            bottom: 0;
            left: calc(50% - 25px);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            background: #fff;
            padding-top: 120px;
            position: relative;
        }

        .hero-badge {
            background: #e8f9fa;
            color: var(--primary);
            padding: 7px 18px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .hero-title {
            font-size: 52px;
            font-weight: 700;
            line-height: 1.2;
        }

        .hero-title span {
            color: var(--primary);
        }

        .floating-card {
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: inline-flex;
            align-items: center;
            position: absolute;
            bottom: 30px;
            left: -20px;
            z-index: 2;
            border-left: 5px solid var(--primary);
        }

        .floating-card i {
            font-size: 28px;
            color: var(--primary);
            margin-right: 15px;
        }

        /* Cards MediNest Style */
        .medinest-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0px 2px 15px rgba(0, 0, 0, 0.08);
            transition: 0.4s;
            border: none;
            height: 100%;
            overflow: hidden;
            position: relative;
        }

        .medinest-card:hover {
            transform: translateY(-10px);
            box-shadow: 0px 12px 30px rgba(0, 0, 0, 0.12);
        }

        .gallery-img {
            height: 250px;
            width: 100%;
            object-fit: cover;
            transition: 0.5s;
            cursor: pointer;
        }

        .btn-medinest {
            background: var(--primary);
            color: white;
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 600;
            transition: 0.3s;
            border: none;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }

        .btn-medinest:hover {
            background: #329ea2;
            color: white;
            box-shadow: 0 5px 15px rgba(63, 187, 192, 0.4);
        }

        footer {
            background: #0c1825;
            color: #fff;
            padding: 60px 0 30px;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        /* Responsive Grid for Catalog (2 columns on mobile) */
        @media (max-width: 576px) {
            .section-title h2 {
                font-size: 24px;
            }

            .card-catalog-text {
                font-size: 13px !important;
            }

            .card-catalog-stock {
                font-size: 10px !important;
            }
        }
    </style>
</head>

<body class="index-page">

    <!-- Header -->
    <header id="header" class="header d-flex align-items-center fixed-top shadow-sm">
        <div class="container d-flex align-items-center justify-content-between">

            <a href="/" class="logo d-flex align-items-center text-decoration-none me-auto">
                <h1 class="sitename">E-<span>Pharma</span></h1>
            </a>

            <!-- Desktop Navigation -->
            <nav id="navmenu" class="navmenu d-none d-xl-block">
                <ul>
                    <li><a href="#hero" class="active">Beranda</a></li>
                    <li><a href="#home-about">Tentang</a></li>
                    <li><a href="#berita">Berita</a></li>
                    <li><a href="#katalog">Katalog</a></li>
                    <li><a href="#organisasi">Organisasi</a></li>
                    <li><a href="#galeri">Galeri</a></li>
                </ul>
            </nav>

            <div class="d-flex align-items-center">
                @auth
                    @role('customer')
                        <a href="/customer/cart" class="px-3 text-primary position-relative d-none d-xl-inline-block">
                            <i class="bi bi-cart3 fs-4"></i>
                            <span id="cartBadge"
                                class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle"
                                style="display:none">0</span>
                        </a>
                    @endrole

                    <div class="dropdown ms-2 ms-md-3">
                        <button class="btn btn-medinest dropdown-toggle shadow-sm px-2 px-md-3" type="button"
                            data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> <span
                                class="d-none d-sm-inline">{{ Auth::user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile mt-3">
                            <li><a class="dropdown-item py-2" href="/dashboard"><i
                                        class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard</a></li>
                            @role('customer')
                                <li><a class="dropdown-item py-2" href="/customer/history"><i
                                            class="bi bi-clock-history me-2 text-primary"></i> Pesanan Saya</a></li>
                            @endrole
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger fw-bold"><i
                                            class="bi bi-box-arrow-right me-2"></i> Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a class="btn-medinest shadow-sm ms-3 text-decoration-none" href="/login">Masuk</a>
                @endauth

                <button class="mobile-nav-toggle d-xl-none ms-2 ms-md-3" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#mobileSidebar">
                    <i class="bi bi-list"></i>
                </button>
            </div>

        </div>
    </header>

    <!-- Mobile Sidebar (Offcanvas) -->
    <div class="offcanvas offcanvas-start d-xl-none" tabindex="-1" id="mobileSidebar">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">E-<span>Pharma</span> Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <nav class="nav flex-column">
                <a class="nav-link" href="#hero"><i class="bi bi-house"></i> Beranda</a>
                <a class="nav-link" href="#home-about"><i class="bi bi-info-circle"></i> Tentang Kami</a>
                <a class="nav-link" href="#berita"><i class="bi bi-newspaper"></i> Berita Terbaru</a>
                <a class="nav-link" href="#katalog"><i class="bi bi-capsule"></i> Katalog Obat</a>
                <a class="nav-link" href="#organisasi"><i class="bi bi-people"></i> Struktur Organisasi</a>
                <a class="nav-link" href="#galeri"><i class="bi bi-images"></i> Galeri Foto</a>

                @auth @role('customer')
                <a class="nav-link text-primary" href="/customer/cart">
                    <i class="bi bi-cart3"></i> Keranjang Pesanan
                    <span class="badge bg-danger ms-2" id="mobileCartBadge" style="display:none">0</span>
                </a>
                @endrole @endauth
            </nav>
        </div>
    </div>

    <main class="main">

        <!-- Hero Section -->
        <section id="hero" class="hero">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-5 position-relative" data-aos="fade-right">
                        <img src="https://images.unsplash.com/photo-1587854680352-936b22b91030?auto=format&fit=crop&w=800&q=80"
                            class="img-fluid rounded-4 shadow-lg">
                        <div class="floating-card">
                            <i class="bi bi-truck"></i>
                            <div>
                                <div class="fw-bold">Pengiriman Cepat</div><small class="text-muted">Logistik Farmasi
                                    Terpadu</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7 ps-lg-5 mt-5 mt-lg-0" data-aos="fade-left">
                        <span class="hero-badge">E-PHARMA LOGISTICS HUB</span>
                        <h1 class="hero-title">Solusi Logistik Farmasi<br><span>Cepat & Transparan</span></h1>
                        <p class="text-muted fs-5 my-4">Mendukung distribusi sediaan farmasi yang aman dan efisien
                            untuk seluruh Fasilitas Kesehatan di Indonesia.</p>
                        <div class="d-flex gap-3">
                            <a href="#katalog" class="btn-medinest px-5 shadow py-3">Jelajahi Katalog</a>
                            <a href="#home-about"
                                class="btn btn-outline-secondary rounded-pill px-5 py-3 text-decoration-none">Tentang
                                Kami</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Profile Section (Teaser) -->
        <section id="home-about" class="py-5 bg-white">
            <div class="container" data-aos="fade-up">
                <div class="row gy-5 align-items-center">
                    <div class="col-lg-6">
                        <div class="position-relative">
                            <img src="https://images.unsplash.com/photo-1586015555751-63bb77f4322a?auto=format&fit=crop&w=800&q=80"
                                class="img-fluid rounded-4 shadow">
                            <div
                                class="p-4 bg-primary text-white rounded-4 position-absolute bottom-0 end-0 m-4 d-none d-md-block shadow-lg text-center">
                                <h3 class="text-white mb-0">24/7</h3><small>Sistem Terpadu</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 ps-lg-5">
                        <h2 class="fw-bold mb-4" id="aboutTitle">Profil E-Pharma</h2>
                        <div id="aboutExcerpt" class="text-muted lh-lg mb-4">Memuat profil yayasan...</div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 d-flex align-items-center bg-light cursor-pointer"
                                    onclick="showFullContent('history')">
                                    <i class="bi bi-clock-history fs-3 text-primary me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0">Sejarah</h6><small
                                            class="text-primary fw-bold">Detail <i
                                                class="bi bi-arrow-right"></i></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 d-flex align-items-center bg-light cursor-pointer"
                                    onclick="showFullContent('vision_mission')">
                                    <i class="bi bi-eye fs-3 text-primary me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0">Visi Misi</h6><small
                                            class="text-primary fw-bold">Detail <i
                                                class="bi bi-arrow-right"></i></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Berita -->
        <section id="berita" class="py-5" style="background: var(--light-bg);">
            <div class="container">
                <div class="section-title">
                    <h2>Berita & Kegiatan</h2>
                    <p>Informasi terbaru seputar kegiatan logistik farmasi nasional.</p>
                </div>
                <div id="postsContainer" class="row gy-4"></div>
            </div>
        </section>

        <!-- Katalog -->
        <section id="katalog" class="py-5">
            <div class="container">
                <div class="section-title">
                    <h2>Katalog Produk</h2>
                    <p>Pilih sediaan farmasi yang dibutuhkan oleh unit kesehatan Anda.</p>
                </div>
                <div id="productsContainer" class="row gx-3 gy-4"></div>
            </div>
        </section>

        <!-- Struktur Organisasi -->
        <section id="organisasi" class="py-5" style="background: var(--light-bg);">
            <div class="container">
                <div class="section-title">
                    <h2>Struktur Organisasi</h2>
                    <p>Tenaga profesional pengelola sistem distribusi logistik.</p>
                </div>
                <div id="orgContainer" class="row gy-4 justify-content-center"></div>
            </div>
        </section>

        <!-- Galeri -->
        <section id="galeri" class="py-5 bg-white">
            <div class="container">
                <div class="section-title">
                    <h2>Galeri Dokumentasi</h2>
                    <p>Koleksi foto kegiatan dan operasional E-Pharma.</p>
                </div>
                <div id="publicGalleryContainer" class="row gy-4"></div>
            </div>
        </section>

    </main>

    <!-- Modal Profile -->
    <div class="modal fade" id="contentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h4 class="fw-bold mb-0" id="modalContentTitle">Judul</h4><button type="button"
                        class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-muted lh-lg" id="modalContentBody"></div>
            </div>
        </div>
    </div>

    <!-- Modal Galeri (Album) -->
    <div class="modal fade" id="galleryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="fw-bold mb-0" id="galleryModalTitle">Album</h4>
                        <p class="text-muted small mb-0">Klik gambar untuk melihat</p>
                    </div><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="galleryModalBody" class="row gy-4"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4">
                    <h3 class="sitename text-white mb-3">E-<span>Pharma</span></h3>
                    <p id="footerAbout" class="opacity-75 small mt-3">Sistem logistik farmasi digital terpadu.</p>
                </div>
                <div class="col-lg-4">
                    <h5 class="fw-bold text-white mb-4">Kontak Kami</h5>
                    <div id="dynamicContactList" class="small opacity-75"></div>
                </div>
                <div class="col-lg-4">
                    <h5 class="fw-bold text-white mb-4">Tautan</h5>
                    <ul class="list-unstyled small opacity-75">
                        <li><a href="/login" class="text-white text-decoration-none d-block mb-2">Internal Login</a>
                        </li>
                        <li><a href="/register" class="text-white text-decoration-none d-block">Pendaftaran Mitra</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="text-center mt-5 pt-4 border-top border-secondary opacity-50 small">© 2024 Yayasan E-Pharma.
                All Rights Reserved.</div>
        </div>
    </footer>

    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>

    <script>
        // Konfigurasi Header Axios
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

        let globalProfiles = {};
        let allGalleries = [];

        document.addEventListener('DOMContentLoaded', () => {
            AOS.init({
                duration: 1000,
                once: true
            });
            fetchLandingPage();
            fetchCatalog();
        });

        function fetchLandingPage() {
            axios.get('/api/public/landing-page').then(res => {
                const d = res.data;
                globalProfiles = d.profiles || {};
                allGalleries = d.gallery || [];

                // 1. Profil Yayasan
                if (globalProfiles.about) {
                    document.getElementById('aboutTitle').innerText = globalProfiles.about.title;
                    document.getElementById('aboutExcerpt').innerText = globalProfiles.about.content.substring(0,
                        300) + '...';
                    document.getElementById('footerAbout').innerText = globalProfiles.about.content.substring(0,
                        150) + '...';
                }

                // 2. Kontak
                let contactHtml = '';
                Object.values(d.contacts || {}).forEach(i => {
                    contactHtml +=
                        `<p class="mb-2"><i class="bi bi-geo-alt text-primary me-2"></i><strong>${i.title}:</strong> ${i.value}</p>`;
                });
                document.getElementById('dynamicContactList').innerHTML = contactHtml;

                // 3. Berita & Kegiatan
                let postHtml = '';
                const combinedPosts = [...(d.news || []), ...(d.activities || [])];
                combinedPosts.forEach(p => {
                    const img = p.image ? `/storage/${p.image}` : 'https://placehold.co/600x400';
                    postHtml += `
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="medinest-card">
                        <img src="${img}" class="card-img-top" style="height:220px; object-fit:cover">
                        <div class="p-4">
                            <span class="text-primary small fw-bold text-uppercase">${p.category?.name || 'INFO'}</span>
                            <h5 class="fw-bold mt-2">${p.title}</h5>
                            <p class="small text-muted mb-0">${p.content.substring(0, 80)}...</p>
                        </div>
                    </div>
                </div>`;
                });
                document.getElementById('postsContainer').innerHTML = postHtml;

                // 4. Struktur Organisasi
                let orgHtml = '';
                d.organization.forEach(o => {
                    const img = o.photo ? `/storage/${o.photo}` :
                        `https://ui-avatars.com/api/?name=${encodeURIComponent(o.name)}&background=3fbbc0&color=fff`;
                    orgHtml += `
                <div class="col-lg-3 col-md-4 col-6" data-aos="zoom-in">
                    <div class="p-3 medinest-card mb-4 text-center">
                        <img src="${img}" class="rounded-circle mb-3 border border-4 border-white shadow-sm" width="100" height="100" style="object-fit:cover">
                        <h6 class="fw-bold mb-1 small">${o.name}</h6>
                        <p class="text-primary small mb-0 fw-bold" style="font-size:11px;">${o.position}</p>
                    </div>
                </div>`;
                });
                document.getElementById('orgContainer').innerHTML = orgHtml;

                // 5. Galeri
                let galHtml = '';
                allGalleries.forEach((g, idx) => {
                    if (g.files?.length > 0) {
                        galHtml += `
                    <div class="col-md-4 col-6" data-aos="fade-up">
                        <div class="medinest-card cursor-pointer" onclick="showGallery(${idx})">
                            <div class="position-relative overflow-hidden">
                                <img src="/${g.files[0].file_path}" class="gallery-img">
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-primary rounded-pill"><i class="bi bi-images me-1"></i> ${g.files.length}</span>
                                </div>
                            </div>
                            <div class="p-3 text-center bg-white border-top"><h6 class="mb-0 fw-bold small">${g.title}</h6></div>
                        </div>
                    </div>`;
                    }
                });
                document.getElementById('publicGalleryContainer').innerHTML = galHtml;
            });
        }

        function showGallery(index) {
            const gallery = allGalleries[index];
            if (!gallery) return;
            document.getElementById('galleryModalTitle').innerText = gallery.title;
            let photoHtml = '';
            gallery.files.forEach(f => {
                photoHtml +=
                    `<div class="col-md-4 col-6"><div class="medinest-card shadow-none border"><a href="/${f.file_path}" target="_blank"><img src="/${f.file_path}" class="img-fluid rounded-3" style="height:150px; width:100%; object-fit:cover"></a></div></div>`;
            });
            document.getElementById('galleryModalBody').innerHTML = photoHtml;
            new bootstrap.Modal(document.getElementById('galleryModal')).show();
        }

        function showFullContent(key) {
            const data = globalProfiles[key];
            if (!data) return;
            document.getElementById('modalContentTitle').innerText = data.title;
            document.getElementById('modalContentBody').innerHTML = data.content;
            new bootstrap.Modal(document.getElementById('contentModal')).show();
        }

        function fetchCatalog() {
            axios.get('/api/public/products').then(res => {

                let html = '';

                res.data.forEach(product => {

                    const img = product.image ?
                        '/images/' + product.image :
                        'https://via.placeholder.com/300x200?text=Produk';

                    html += `
            <div class="col-lg-3 col-md-4 col-6">
                <div class="medinest-card p-3 text-center h-100">

                    <img src="${img}"
                         class="img-fluid mb-3"
                         style="height:120px; object-fit:contain; width:100%;">

                    <h6 class="fw-bold">${product.name}</h6>

                    <p class="text-primary fw-bold">
                        Rp ${new Intl.NumberFormat('id-ID').format(product.price)}
                    </p>

                    <p class="small text-muted">
                        Stok: ${product.stock}
                    </p>

                    <button onclick="addToCart(${product.id}, '${product.name}')"
                            class="btn-medinest btn-sm w-100">
                        Pesan
                    </button>

                </div>
            </div>`;
                });

                document.getElementById('productsContainer').innerHTML = html;

            }).catch(err => {
                console.error(err);
            });
        }

        function addToCart(id, name) {

            axios.post('/api/cart', {
                    product_id: id
                })
                .then(res => {

                    Swal.fire({
                        toast: true,
                        position: 'bottom-end',
                        icon: 'success',
                        title: name + ' masuk keranjang',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    updateCartBadge();
                })
                .catch(err => {
                    Swal.fire('Error', err.response?.data?.message || 'Gagal menambahkan ke keranjang', 'error');
                    console.error(err);
                });
        }

        function updateCartBadge() {
            axios.get('/api/cart').then(res => {
                const count = res.data.length;
                const bD = document.getElementById('cartBadge');
                const bM = document.getElementById('mobileCartBadge');
                if (count > 0) {
                    if (bD) {
                        bD.innerText = count;
                        bD.style.display = 'inline-block';
                    }
                    if (bM) {
                        bM.innerText = count;
                        bM.style.display = 'inline-block';
                    }
                } else {
                    if (bD) bD.style.display = 'none';
                    if (bM) bM.style.display = 'none';
                }
            }).catch(() => {});
        }
    </script>
</body>

</html>
