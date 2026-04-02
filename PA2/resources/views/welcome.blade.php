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
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">

  <!-- Core Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    /* WARNA TEAL TUA SESUAI GAMBAR */
    :root {
      --primary: #00838f;
      --secondary: #2c4964;
      --light-bg: #f4f9f9;
    }

    body { font-family: 'Roboto', sans-serif; color: #444; overflow-x: hidden; }
    h1, h2, h3, h4, h5, h6 { font-family: 'Poppins', sans-serif; color: var(--secondary); }

    /* Override Bootstrap Colors agar menjadi Hijau Toska */
    .bg-primary { background-color: var(--primary) !important; }
    .text-primary { color: var(--primary) !important; }
    .btn-primary { background-color: var(--primary) !important; border-color: var(--primary) !important; }

    /* Header & Navigation */
    .header { height: 85px; transition: all 0.5s; z-index: 1030; background: #fff; }
    .sitename { font-size: 28px; font-weight: 700; color: var(--secondary); margin: 0; }
    .sitename span { color: var(--primary); }

    .navmenu ul { margin: 0; padding: 0; display: flex; list-style: none; align-items: center; }
    .navmenu a { color: var(--secondary); padding: 10px 15px; font-size: 15px; font-weight: 600; text-decoration: none; transition: 0.3s; }
    .navmenu a:hover, .navmenu .active { color: var(--primary); }

    /* Mobile Sidebar (Offcanvas) */
    .mobile-nav-toggle { font-size: 28px; color: var(--secondary); cursor: pointer; line-height: 0; border: none; background: none; }
    .offcanvas { width: 280px !important; }
    .offcanvas-title { font-weight: 700; color: var(--secondary); }
    .offcanvas-body .nav-link { color: var(--secondary); font-weight: 600; padding: 15px 0; border-bottom: 1px solid #eee; display: flex; align-items: center; text-decoration: none; }
    .offcanvas-body .nav-link i { margin-right: 15px; color: var(--primary); font-size: 1.2rem; }

    /* Dropdown User */
    .dropdown-menu-profile { border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-radius: 12px; padding: 10px; min-width: 200px; }
    .dropdown-item { border-radius: 8px; padding: 10px; font-weight: 500; color: var(--secondary); }
    .dropdown-item:hover { background: var(--light-bg); color: var(--primary); }

    /* Section Title */
    .section-title { text-align: center; padding-bottom: 40px; margin-top: 60px; }
    .section-title h2 { font-size: 32px; font-weight: 700; position: relative; margin-bottom: 20px; padding-bottom: 20px; }
    .section-title h2::after { content: ""; position: absolute; display: block; width: 50px; height: 3px; background: var(--primary); bottom: 0; left: calc(50% - 25px); }

    /* Hero Section
    .hero { min-height: 100vh; background: #fff; padding-top: 120px; position: relative; }
    .hero-badge { background: #e8f9fa; color: var(--primary); padding: 7px 18px; border-radius: 50px; font-weight: 600; font-size: 14px; display: inline-block; margin-bottom: 20px; }
    .hero-title { font-size: 52px; font-weight: 700; line-height: 1.2; }
    .hero-title span { color: var(--primary); }
    .floating-card { background: white; padding: 15px 25px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); display: inline-flex; align-items: center; position: absolute; bottom: 30px; left: -20px; z-index: 2; border-left: 5px solid var(--primary); }
    .floating-card i { font-size: 28px; color: var(--primary); margin-right: 15px; } */

    /* Cards Styling */
    .medinest-card { background: #fff; border-radius: 15px; box-shadow: 0px 2px 15px rgba(0, 0, 0, 0.08); transition: 0.4s; border: none; height: 100%; overflow: hidden; position: relative; }
    .medinest-card:hover { transform: translateY(-10px); box-shadow: 0px 12px 30px rgba(0, 0, 0, 0.12); }

    .btn-medinest {
      background: var(--primary) !important;
      color: white !important;
      border-radius: 25px;
      padding: 10px 28px;
      font-weight: 600;
      transition: 0.3s;
      border: none;
      text-decoration: none;
      display: inline-block;
      cursor: pointer;
    }
    .btn-medinest:hover { background: #006064 !important; box-shadow: 0 5px 15px rgba(0, 131, 143, 0.4); }

    .btn-cart-outline { border: 2px solid var(--primary); color: var(--primary); background: transparent; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; transition: 0.3s; cursor: pointer; }
    .btn-cart-outline:hover { background: var(--primary); color: white; }

    /* Profile Box Fix */
    .profile-item-box { transition: 0.3s; border: 1px solid #eee !important; }
    .profile-item-box:hover { background-color: #ffffff !important; border-color: var(--primary) !important; transform: translateY(-2px); }

    /* Detail Modal Styles */
    #modalDetailImg { width: 100%; height: 250px; object-fit: contain; background: #f8f9fa; border-radius: 15px; }
    .detail-label { font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; margin-bottom: 2px; }
    .detail-value { font-weight: 600; color: var(--secondary); margin-bottom: 12px; font-size: 14px; }

    footer { background: #0c1825; color: #fff; padding: 60px 0 30px; }
    .cursor-pointer { cursor: pointer; }

    @media (max-width: 576px) {
        .section-title h2 { font-size: 24px; }
        .card-catalog-text { font-size: 13px !important; }
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
      <!-- Desktop Nav -->
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
              <i class="bi bi-cart3 fs-4" style="color: var(--primary);"></i>
              <span id="cartBadge" class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="display:none">0</span>
            </a>
          @endrole
          <div class="dropdown ms-2 ms-md-3">
            <button class="btn btn-medinest dropdown-toggle shadow-sm px-2 px-md-3" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle me-1"></i> <span class="d-none d-sm-inline">{{ Auth::user()->name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile mt-3">
              <li><a class="dropdown-item py-2" href="/dashboard"><i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard</a></li>
              @role('customer')
                <li><a class="dropdown-item py-2" href="/customer/history"><i class="bi bi-clock-history me-2 text-primary"></i> Pesanan Saya</a></li>
              @endrole
              <li><hr class="dropdown-divider"></li>
              <li>
                <form action="{{ route('logout') }}" method="POST"> @csrf
                  <button type="submit" class="dropdown-item text-danger fw-bold"><i class="bi bi-box-arrow-right me-2"></i> Keluar</button>
                </form>
              </li>
            </ul>
          </div>
        @else
          <a class="btn-medinest shadow-sm ms-3 text-decoration-none" href="/login">Masuk</a>
        @endauth
        <button class="mobile-nav-toggle d-xl-none ms-2 ms-md-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
          <i class="bi bi-list"></i>
        </button>
      </div>
    </div>
  </header>

  <!-- Mobile Sidebar -->
  <div class="offcanvas offcanvas-start d-xl-none" tabindex="-1" id="mobileSidebar">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title">E-<span>Pharma</span> Menu</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <nav class="nav flex-column">
        <a class="nav-link" href="#hero"><i class="bi bi-house"></i> Beranda</a>
        <a class="nav-link" href="#home-about"><i class="bi bi-info-circle"></i> Tentang Kami</a>
        <a class="nav-link" href="#berita"><i class="bi bi-newspaper"></i> Berita</a>
        <a class="nav-link" href="#katalog"><i class="bi bi-capsule"></i> Katalog</a>
        <a class="nav-link" href="#organisasi"><i class="bi bi-people"></i> Organisasi</a>
        <a class="nav-link" href="#galeri"><i class="bi bi-images"></i> Galeri</a>
        @auth @role('customer')
          <a class="nav-link text-primary" href="/customer/cart">
            <i class="bi bi-cart3"></i> Keranjang <span class="badge bg-danger ms-2" id="mobileCartBadge" style="display:none">0</span>
          </a>
        @endrole @endauth
      </nav>
    </div>
  </div>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-5">
            <div class="hero-image" data-aos="fade-right" data-aos-delay="100">
              <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=800&q=80" alt="Logistics Hub" class="img-fluid main-image rounded-4 shadow-lg">
              <div class="floating-card emergency-card" data-aos="fade-up" data-aos-delay="300">
                <div class="card-content">
                  <i class="bi bi-truck"></i>
                  <div class="text">
                    <span class="label">Pengiriman Cepat</span>
                    <span class="number">Sistem Terintegrasi</span>
                  </div>
                </div>
              </div>
              <div class="floating-card stats-card" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-item">
                  <span class="number">500+</span>
                  <span class="label">SKU Produk</span>
                </div>
                <div class="stat-item">
                  <span class="number">98%</span>
                  <span class="label">On-Time Delivery</span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-7">
            <div class="hero-content" data-aos="fade-left" data-aos-delay="200">
              <div class="badge-container">
                <span class="hero-badge">E-PHARMA LOGISTICS HUB</span>
              </div>

              <h1 class="hero-title">Solusi Logistik Farmasi<br><span>Cepat & Transparan</span></h1>
              <p class="hero-description">Mendukung distribusi sediaan farmasi yang aman dan efisien untuk seluruh jaringan Fasilitas Kesehatan di Indonesia dengan sistem pengawasan digital terpadu.</p>

              <div class="hero-stats">
                <div class="stat-group">
                  <div class="stat">
                    <i class="bi bi-award"></i>
                    <div class="stat-text">
                      <span class="number">25+</span>
                      <span class="label">Tahun Pengalaman</span>
                    </div>
                  </div>
                  <div class="stat">
                    <i class="bi bi-people"></i>
                    <div class="stat-text">
                      <span class="number">100+</span>
                      <span class="label">Mitra Faskes</span>
                    </div>
                  </div>
                  <div class="stat">
                    <i class="bi bi-geo-alt"></i>
                    <div class="stat-text">
                      <span class="number">15</span>
                      <span class="label">Gudang Pusat</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="cta-section">
                <div class="cta-buttons">
                  <a href="#katalog" class="btn btn-primary">Jelajahi Katalog</a>
                  <a href="https://www.youtube.com/watch?v=Y7f98aduVJ8" class="btn btn-secondary glightbox">
                    <i class="bi bi-play-circle"></i>
                    Profil Video
                  </a>
                </div>

                <div class="quick-actions">
                  <a href="#katalog" class="action-link">
                    <i class="bi bi-calendar-check"></i>
                    <span>Cek Stok Ready</span>
                  </a>
                  <a href="https://wa.me/628123456789" class="action-link">
                    <i class="bi bi-chat-dots"></i>
                    <span>Chat Support</span>
                  </a>
                  <a href="/dashboard" class="action-link">
                    <i class="bi bi-file-medical"></i>
                    <span>Portal Dashboard</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="background-elements">
        <div class="bg-shape shape-1"></div>
        <div class="bg-shape shape-2"></div>
        <div class="bg-pattern"></div>
      </div>
    </section>


    <!-- Profile Section -->
    <section id="home-about" class="py-5 bg-white">
      <div class="container" data-aos="fade-up">
        <div class="row gy-5 align-items-center">
          <div class="col-lg-6">
              <div class="position-relative">
                  <img src="https://images.unsplash.com/photo-1586015555751-63bb77f4322a?auto=format&fit=crop&w=800&q=80" class="img-fluid rounded-4 shadow">
                  <div class="p-4 btn-medinest text-white rounded-4 position-absolute bottom-0 end-0 m-4 d-none d-md-block shadow-lg text-center">
                      <h3 class="text-white mb-0">24/7</h3><small>Sistem Terpadu</small>
                  </div>
              </div>
          </div>
          <div class="col-lg-6 ps-lg-5">
            <h2 class="fw-bold mb-4" id="aboutTitle">Profil E-Pharma</h2>
            <div id="aboutExcerpt" class="text-muted lh-lg mb-4">Memuat profil yayasan...</div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 d-flex align-items-center bg-light cursor-pointer profile-item-box" onclick="showFullContent('history')">
                        <i class="bi bi-clock-history fs-3 me-3" style="color: var(--primary); background: transparent !important;"></i>
                        <div>
                            <h6 class="fw-bold mb-0" style="color: var(--secondary);">Sejarah</h6>
                            <small class="fw-bold" style="color: var(--primary);">Detail <i class="bi bi-arrow-right"></i></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 d-flex align-items-center bg-light cursor-pointer profile-item-box" onclick="showFullContent('vision_mission')">
                        <i class="bi bi-eye fs-3 me-3" style="color: var(--primary); background: transparent !important;"></i>
                        <div>
                            <h6 class="fw-bold mb-0" style="color: var(--secondary);">Visi Misi</h6>
                            <small class="fw-bold" style="color: var(--primary);">Detail <i class="bi bi-arrow-right"></i></small>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Katalog -->
    <section id="katalog" class="py-5">
        <div class="container">
            <div class="section-title"><h2>Katalog Produk</h2><p>Pilih sediaan farmasi yang dibutuhkan oleh unit kesehatan Anda.</p></div>
            <div id="productsContainer" class="row gx-3 gy-4"></div>
            <div class="text-center mt-5">
                <a href="/customer/products" class="btn btn-medinest px-5 py-3 shadow">Lihat Semua Produk di Katalog</a>
            </div>
        </div>

    </section>

    <!-- Berita -->
    <section id="berita" class="py-5" style="background: var(--light-bg);">
        <div class="container">
            <div class="section-title"><h2>Berita & Kegiatan</h2><p>Informasi terbaru seputar kegiatan logistik farmasi nasional.</p></div>
            <div id="postsContainer" class="row gy-4"></div>
            <div class="text-center mt-5">
                <a href="/posts" class="btn btn-medinest px-5 py-3 shadow">Lihat Postingan Lainnya</a>
            </div>
        </div>
    </section>

    <!-- Struktur Organisasi -->
    <section id="organisasi" class="py-5">
        <div class="container">
            <div class="section-title"><h2>Struktur Organisasi</h2><p>Tenaga profesional pengelola sistem distribusi logistik.</p></div>
            <div id="orgContainer" class="row gy-4 justify-content-center"></div>
        </div>
    </section>

    <!-- Galeri -->
    <section id="galeri" class="py-5 bg-white">
        <div class="container">
            <div class="section-title"><h2>Galeri Dokumentasi</h2></div>
            <div id="publicGalleryContainer" class="row gy-4"></div>
        </div>
    </section>

  </main>

  <!-- Modal Detail Produk -->
  <div class="modal fade" id="productDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 p-md-5 pt-0">
                <div class="row align-items-center">
                    <div class="col-md-5 mb-4 mb-md-0 text-center">
                        <img id="modalDetailImg" src="" alt="Produk" class="img-fluid rounded-3" style="max-height: 250px; object-fit: contain;">
                    </div>
                    <div class="col-md-7 ps-md-4">
                        <span id="modalDetailCategory" class="badge bg-light text-primary rounded-pill mb-2 px-3">Kategori</span>
                        <h3 id="modalDetailName" class="fw-bold text-dark mb-1">Nama Produk</h3>
                        <p id="modalDetailSku" class="text-muted small mb-3">SKU: -------</p>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="small text-muted fw-bold text-uppercase">Stok Tersedia</div>
                                <div id="modalDetailStock" class="fw-bold text-dark">0 Unit</div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted fw-bold text-uppercase">Satuan</div>
                                <div id="modalDetailUnit" class="fw-bold text-dark">-</div>
                            </div>
                            <div class="col-12 mt-3">
                                <div class="small text-muted fw-bold text-uppercase">Harga Estimasi</div>
                                <div id="modalDetailPrice" class="fs-4 fw-bold text-primary">Rp 0</div>
                            </div>
                        </div>
                        <div class="small text-muted fw-bold text-uppercase mb-1">Deskripsi</div>
                        <p id="modalDetailDesc" class="text-muted small mb-4">Tidak ada deskripsi tambahan.</p>
                        <div id="modalActionButtons" class="d-flex gap-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

  <!-- Modal Profile -->
  <div class="modal fade" id="contentModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow rounded-4">
        <div class="modal-header border-0 pb-0"><h4 class="fw-bold mb-0" id="modalContentTitle">Judul</h4><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body p-4 text-muted lh-lg" id="modalContentBody"></div>
      </div>
  </div></div>

  <!-- Modal Galeri -->
  <div class="modal fade" id="galleryModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content border-0 shadow rounded-4">
        <div class="modal-header border-0 pb-0"><div><h4 class="fw-bold mb-0" id="galleryModalTitle">Album</h4><p class="text-muted small mb-0">Klik gambar untuk melihat</p></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body p-4"><div id="galleryModalBody" class="row gy-4"></div></div>
      </div>
  </div></div>

  <!-- Footer -->
  <footer>
    <div class="container text-center text-md-start">
      <div class="row gy-4">
        <div class="col-lg-4">
          <h3 class="sitename text-white mb-3">E-<span>Pharma</span></h3>
          <p id="footerAbout" class="opacity-75 small">Sistem logistik farmasi digital terpadu.</p>
        </div>
        <div class="col-lg-4">
          <h5 class="fw-bold text-white mb-4">Kontak Kami</h5>
          <div id="dynamicContactList" class="small opacity-75"></div>
        </div>
        <div class="col-lg-4">
          <h5 class="fw-bold text-white mb-4">Akses</h5>
          <ul class="list-unstyled small opacity-75">
            <li><a href="/login" class="text-white text-decoration-none d-block mb-2">Internal Login</a></li>
            <li><a href="/register" class="text-white text-decoration-none d-block">Pendaftaran Mitra</a></li>
          </ul>
        </div>
      </div>
      <div class="text-center mt-5 pt-4 border-top border-secondary opacity-50 small">© 2024 Yayasan E-Pharma.</div>
    </div>
  </footer>

  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>
  <script src="{{ asset('assets/vendor/glightbox/js/glightbox.min.js') }}"></script>

  <script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

    let globalProfiles = {};
    let allGalleries = [];
    let allProducts = [];

    document.addEventListener('DOMContentLoaded', () => {
        AOS.init({duration:1000, once:true});
        const glightbox = GLightbox({ selector: '.glightbox' });
        fetchLandingPage();
        fetchCatalog();
    });

    function fetchLandingPage() {
        axios.get('/api/public/landing-page').then(res => {
            const d = res.data;
            globalProfiles = d.profiles || {};
            allGalleries = d.gallery || [];

            if(globalProfiles.about) {
                document.getElementById('aboutExcerpt').innerText = globalProfiles.about.content.substring(0, 300) + '...';
                document.getElementById('footerAbout').innerText = globalProfiles.about.content.substring(0, 150) + '...';
            }

            let contactHtml = ''; Object.values(d.contacts || {}).forEach(i => contactHtml += `<p class="mb-2"><strong>${i.title}:</strong> ${i.value}</p>`);
            document.getElementById('dynamicContactList').innerHTML = contactHtml;

            // FIX WARNA BERITA (POSTS)
            let postHtml = '';
            const combinedPosts = [...(d.news || []), ...(d.activities || [])];
            combinedPosts.forEach(p => {
                const img = p.image ? `/storage/${p.image}` : 'https://placehold.co/600x400';
                postHtml += `
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="medinest-card">
                        <img src="${img}" class="card-img-top" style="height:220px; object-fit:cover">
                        <div class="p-4">
                            <span class="badge bg-primary mb-2">${p.category?.name || 'INFO'}</span>
                            <h5 class="fw-bold mt-2">${p.title}</h5>
                            <p class="small text-muted mb-3">${p.content.substring(0, 80)}...</p>
                            <a class="text-primary fw-bold small text-decoration-none cursor-pointer">Selengkapnya <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>`;
            });
            document.getElementById('postsContainer').innerHTML = postHtml;

            // FIX WARNA ORGANISASI
            let orgHtml = ''; d.organization.forEach(o => {
                const img = o.photo ? `/storage/${o.photo}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(o.name)}&background=00838f&color=fff`;
                orgHtml += `
                <div class="col-lg-3 col-md-4 col-6" data-aos="zoom-in">
                    <div class="p-3 medinest-card mb-4 text-center">
                        <img src="${img}" class="rounded-circle mb-3 border border-4 border-white shadow-sm" width="100" height="100" style="object-fit:cover">
                        <h6 class="fw-bold mb-1 small">${o.name}</h6>
                        <span class="badge bg-primary rounded-pill px-3" style="font-size:10px;">${o.position}</span>
                    </div>
                </div>`;
            });
            document.getElementById('orgContainer').innerHTML = orgHtml;

            let galHtml = ''; allGalleries.forEach((g, idx) => {
                if(g.files?.length > 0) {
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

    function fetchCatalog() {
        axios.get('/api/public/products').then(res => {
            allProducts = res.data;
            let html = ''; res.data.forEach(product => {
                const isCustomer = @auth @if(auth()->user()->hasRole('customer')) true @else false @endif @else false @endauth;
                const img = product.image ? `/${product.image}` : 'https://placehold.co/400x300';

                html += `
                <div class="col-lg-3 col-md-4 col-6" data-aos="fade-up">
                    <div class="medinest-card p-2 p-md-3 text-center h-100 d-flex flex-column justify-content-between">
                        <div class="cursor-pointer" onclick="openDetail('${product.id}')">
                            <div class="bg-light rounded-4 mb-2 p-1">
                                <img src="${img}" class="img-fluid rounded-3" style="height:120px; object-fit:contain; width:100%;">
                            </div>
                            <h6 class="fw-bold card-catalog-text mb-1">${product.name}</h6>
                            <p class="text-muted mb-2 card-catalog-stock">Stok: <span class="badge bg-light text-dark border p-1">${product.stock} ${product.unit}</span></p>
                        </div>

                        ${isCustomer
                            ? `<div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                                <button type="button" onclick="addToCart('${product.id}', '${product.name}')" class="btn-cart-outline" title="Tambah ke Keranjang">
                                    <i class="bi bi-cart-plus"></i>
                                </button>
                                <button type="button" onclick="quickOrder('${product.id}', '${product.name}')" class="btn-medinest flex-grow-1 py-2">
                                    Pesan
                                </button>
                               </div>`
                            : `<a href="/login" class="btn btn-outline-secondary btn-sm w-100 rounded-pill text-decoration-none py-1">Login</a>`
                        }
                    </div>
                </div>`;
            });
            document.getElementById('productsContainer').innerHTML = html;
            updateCartBadge();
        });
    }

    function openDetail(id) {
        const p = allProducts.find(item => item.id === id);
        if(!p) return;

        document.getElementById('modalDetailImg').src = p.image ? `/${p.image}` : 'https://placehold.co/400x300';
        document.getElementById('modalDetailName').innerText = p.name;
        document.getElementById('modalDetailSku').innerText = `SKU: ${p.sku}`;
        document.getElementById('modalDetailCategory').innerText = p.category?.name || 'Umum';
        document.getElementById('modalDetailStock').innerText = `${p.stock} ${p.unit}`;
        document.getElementById('modalDetailUnit').innerText = p.unit;
        document.getElementById('modalDetailPrice').innerText = `Rp${Number(p.price).toLocaleString()}`;
        document.getElementById('modalDetailDesc').innerText = p.description || 'Tidak ada deskripsi tambahan.';

        const isCustomer = @auth @if(auth()->user()->hasRole('customer')) true @else false @endif @else false @endauth;
        const actionContainer = document.getElementById('modalActionButtons');

        if(isCustomer) {
            actionContainer.innerHTML = `
                <button onclick="addToCart('${p.id}', '${p.name}')" class="btn btn-outline-info rounded-pill px-4 fw-bold"><i class="bi bi-cart-plus me-2"></i>Keranjang</button>
                <button onclick="quickOrder('${p.id}', '${p.name}')" class="btn btn-medinest flex-grow-1 rounded-pill shadow">Pesan Langsung</button>
            `;
        } else {
            actionContainer.innerHTML = `<a href="/login" class="btn btn-outline-secondary w-100 rounded-pill">Login untuk Memesan</a>`;
        }

        new bootstrap.Modal(document.getElementById('productDetailModal')).show();
    }

    function showGallery(index) {
        const gallery = allGalleries[index]; if (!gallery) return;
        document.getElementById('galleryModalTitle').innerText = gallery.title;
        let photoHtml = '';
        gallery.files.forEach(f => {
            photoHtml += `<div class="col-md-4 col-6"><div class="medinest-card shadow-none border"><a href="/${f.file_path}" target="_blank"><img src="/${f.file_path}" class="img-fluid rounded-3" style="height:150px; width:100%; object-fit:cover"></a></div></div>`;
        });
        document.getElementById('galleryModalBody').innerHTML = photoHtml;
        new bootstrap.Modal(document.getElementById('galleryModal')).show();
    }

    function showFullContent(key) {
        const data = globalProfiles[key]; if(!data) return;
        document.getElementById('modalContentTitle').innerText = data.title;
        document.getElementById('modalContentBody').innerHTML = data.content;
        new bootstrap.Modal(document.getElementById('contentModal')).show();
    }

    function addToCart(id, name) {
        axios.post('/api/cart', { product_id: id }).then(() => {
            Swal.fire({ toast:true, position:'bottom-end', icon:'success', title:name+' masuk keranjang', showConfirmButton:false, timer:2000 });
            updateCartBadge();
        }).catch(err => Swal.fire('Gagal', err.response?.data?.message || 'Sistem error', 'error'));
    }

    function quickOrder(id, name) {
        Swal.fire({
            title: 'Pesan Sekarang?',
            text: `Kirim permintaan 1 unit ${name}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00838f',
            confirmButtonText: 'Ya, Kirim'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post('/api/orders/quick', { product_id: id }).then(() => {
                    Swal.fire('Berhasil!', 'Pesanan diproses.', 'success').then(() => window.location.href = '/customer/history');
                });
            }
        });
    }

    function updateCartBadge() {
        axios.get('/api/cart').then(res => {
            const count = res.data.length;
            const bD = document.getElementById('cartBadge');
            const bM = document.getElementById('mobileCartBadge');
            if (count > 0) {
                if(bD){ bD.innerText = count; bD.style.display = 'inline-block'; }
                if(bM){ bM.innerText = count; bM.style.display = 'inline-block'; }
            } else {
                if(bD) bD.style.display = 'none';
                if(bM) bM.style.display = 'none';
            }
        }).catch(() => {});
    }
  </script>
</body>
</html>
