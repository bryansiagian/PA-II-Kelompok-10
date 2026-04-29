<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Portal E-Pharma - Unit Kesehatan Terpadu</title>

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
  <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">

  <!-- Core Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
        --primary: #3fbbc0; /* Hijau Toska / Teal */
        --secondary: #2c4964; /* Navy MediNest */
        --light-bg: #f1f7f8;
        --hover-color: #329ea2; /* Hijau Toska Tua */
    }

    body {
      background-color: var(--light-bg);
      padding-top: 100px;
      font-family: 'Roboto', sans-serif;
      color: #444;
    }

    /* HEADER */
    .header {
      background: #fff;
      box-shadow: 0px 2px 15px rgba(0, 0, 0, 0.1);
      padding: 10px 0;
      z-index: 1030;
      height: 85px;
      display: flex;
      align-items: center;
    }

    .sitename { font-size: 24px; font-weight: 700; color: var(--secondary); margin: 0; }
    .sitename span { color: var(--primary); }

    /* Navmenu Styling */
    .navmenu ul { margin: 0; padding: 0; display: flex; list-style: none; align-items: center; }

    /* Tombol Profil Hijau Toska */
    .btn-profile {
        background: var(--primary);
        color: white;
        border-radius: 25px;
        padding: 8px 18px;
        font-weight: 600;
        border: none;
        transition: 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-profile:hover {
        background: var(--hover-color);
        color: white;
        box-shadow: 0 4px 12px rgba(63, 187, 192, 0.3);
    }

    /* Dropdown Profile Styling */
    .dropdown-menu-profile {
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border-radius: 12px;
        padding: 10px;
        min-width: 200px;
        margin-top: 15px !important;
    }
    .dropdown-item {
        border-radius: 8px;
        padding: 10px 15px;
        font-weight: 500;
        color: var(--secondary);
        transition: 0.2s;
    }
    .dropdown-item i {
        color: var(--primary);
        margin-right: 10px;
        font-size: 1.1rem;
    }
    .dropdown-item:hover {
        background-color: var(--light-bg);
        color: var(--primary);
    }

    /* Badge Alignment */
    #cartBadge, #mobileCartBadge {
        font-size: 10px;
        background-color: #ff4d4d;
        border: 2px solid #fff;
    }

    /* Sidebar Mobile (Offcanvas) */
    .mobile-nav-toggle { font-size: 28px; color: var(--secondary); cursor: pointer; border: none; background: none; }
    .offcanvas { width: 280px !important; border-right: none; }
    .offcanvas-header { background: #fff; border-bottom: 1px solid #eee; }
    .offcanvas-title { font-weight: 700; color: var(--secondary); }
    .offcanvas-title span { color: var(--primary); }

    .offcanvas-body .nav-link {
        color: var(--secondary);
        font-weight: 600;
        padding: 15px 10px;
        border-bottom: 1px solid #f8f9fa;
        display: flex;
        align-items: center;
        text-decoration: none;
        transition: 0.3s;
    }
    .offcanvas-body .nav-link i { margin-right: 15px; color: var(--primary); font-size: 1.2rem; }
    .offcanvas-body .nav-link:hover {
        background: var(--light-bg);
        color: var(--primary);
        padding-left: 15px;
    }

    /* Search Box (Desktop Only) */
    .search-box .input-group {
        background: #f0f4f4;
        border-radius: 25px;
        padding: 2px 15px;
        border: 1px solid transparent;
        transition: 0.3s;
    }
    .search-box .input-group:focus-within {
        border-color: var(--primary);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(63, 187, 192, 0.1);
    }

    footer { background: #fff; border-top: 1px solid #dee2e6; padding: 25px 0; }

    @keyframes bounceCart {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.3); }
    }
    .animate-cart { animation: bounceCart 0.5s ease-in-out; }

    @media (max-width: 768px) {
        body { padding-top: 85px; }
        .header { height: 75px; }
        .sitename { font-size: 20px; }
    }
  </style>

  <script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
  </script>
</head>

<body>

  <!-- HEADER -->
  <header id="header" class="header fixed-top">
    <div class="container d-flex align-items-center justify-content-between">

      <!-- LOGO -->
      <a href="/dashboard" class="logo d-flex align-items-center text-decoration-none">
        <h1 class="sitename">E-<span>Pharma</span></h1>
      </a>

      <!-- SEARCH BOX (HIDDEN ON MOBILE) -->
      {{-- <div class="search-box d-none d-lg-block mx-4 flex-grow-1" style="max-width: 400px;">
        <div class="input-group">
            <span class="input-group-text bg-transparent border-0 pe-0"><i class="bi bi-search text-muted"></i></span>
            <input id="globalSearchInput" class="form-control border-0 shadow-none bg-transparent" type="search" placeholder="Cari sediaan obat...">
        </div>
      </div> --}}

      <!-- ACTION BUTTONS -->
      <div class="d-flex align-items-center">
        <!-- Cart Icon (Desktop Only) -->
        <a href="{{ route('customer.cart') }}" class="position-relative p-2 me-2 d-none d-md-inline-block">
          <i class="bi bi-cart3 fs-4" style="color: var(--secondary);"></i>
          <span id="cartBadge" class="badge rounded-pill position-absolute top-0 start-100 translate-middle" style="display: none;">0</span>
        </a>

        @auth
        <!-- Profile Dropdown -->
        <div class="dropdown">
          <button class="btn-profile shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle fs-5"></i>
            <span class="d-none d-sm-inline">{{ Auth::user()->name }}</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile">
            <li><a class="dropdown-item py-2" href="{{ route('customer.profile') }}"><i class="bi bi-person-badge me-2"></i> Profil Saya</a></li>
            <li><a class="dropdown-item" href="/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a class="dropdown-item" href="/customer/history"><i class="bi bi-clock-history"></i> Riwayat Pesanan</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form action="/logout" method="POST">
                @csrf
                <button type="submit" class="dropdown-item text-danger fw-bold"><i class="bi bi-box-arrow-right"></i> Keluar</button>
              </form>
            </li>
          </ul>
        </div>
        @else
        <!-- Tampilkan tombol Masuk jika guest -->
        <a href="/login" class="btn-profile text-decoration-none px-4">
            <i class="bi bi-box-arrow-in-right me-2"></i> Masuk
        </a>
        @endauth
        <!-- Mobile Toggle Button -->
        <button class="mobile-nav-toggle d-md-none ms-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarPortal">
          <i class="bi bi-list"></i>
        </button>
      </div>

    </div>
  </header>

  <!-- Sidebar Mobile (Offcanvas) -->
  <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="mobileSidebarPortal">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">E-<span>Pharma</span> Menu</h5>
      <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
      <nav class="nav flex-column">
        <a class="nav-link" href="/dashboard"><i class="bi bi-house"></i> Dashboard</a>
        <a class="nav-link" href="/"><i class="bi bi-grid"></i> Katalog Produk</a>

        @role('customer')
        <a class="nav-link text-primary" href="{{ route('customer.cart') }}">
          <i class="bi bi-cart3"></i> Keranjang
          <span id="mobileCartBadge" class="badge bg-danger ms-2" style="display:none">0</span>
        </a>
        <a class="nav-link" href="/customer/history"><i class="bi bi-clock-history"></i> Riwayat Pesanan</a>
        <a class="nav-link" href="{{ route('customer.manual_request') }}"><i class="bi bi-pencil-square"></i> Request Baru</a>
        @endrole
      </nav>
    </div>
  </div>

  <!-- CONTENT AREA -->
  <main id="main">
    @yield('content')
  </main>

  <!-- FOOTER -->
  <footer>
    <div class="container text-center">
      <p class="text-muted small mb-0">© 2026 <strong style="color: var(--secondary);">Yayasan Satriabudi Dharma Setia</strong> | <span style="color: var(--primary);">E-Pharma Logistics</span></p>
    </div>
  </footer>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

  <script>
    function updateCartBadge() {
        const bD = document.getElementById('cartBadge');
        const bM = document.getElementById('mobileCartBadge');
        axios.get('/api/cart').then(res => {
            const count = res.data.length;
            if (count > 0) {
                if(bD){ bD.innerText = count; bD.style.display = 'block'; bD.classList.add('animate-cart'); }
                if(bM){ bM.innerText = count; bM.style.display = 'inline-block'; }
            } else {
                if(bD) bD.style.display = 'none';
                if(bM) bM.style.display = 'none';
            }
        }).catch(err => console.error(err));
    }
    document.addEventListener('DOMContentLoaded', updateCartBadge);
  </script>
</body>
</html>
