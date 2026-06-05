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
        --primary: #3fbbc0;
        --secondary: #2c4964;
        --light-bg: #f1f7f8;
        --hover-color: #329ea2;
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

    .navmenu ul { margin: 0; padding: 0; display: flex; list-style: none; align-items: center; }

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

    .dropdown-menu-profile {
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border-radius: 12px;
        padding: 10px;
        min-width: 220px;
        margin-top: 15px !important;
    }
    .dropdown-item {
        border-radius: 8px;
        padding: 10px 15px;
        font-weight: 500;
        color: var(--secondary);
        transition: 0.2s;
    }
    .dropdown-item i { color: var(--primary); margin-right: 10px; font-size: 1.1rem; }
    .dropdown-item:hover { background-color: var(--light-bg); color: var(--primary); }

    /* Badge notif di dropdown */
    .notif-badge {
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 10px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-left: auto;
        flex-shrink: 0;
        animation: pulse-notif 2s infinite;
    }
    @keyframes pulse-notif {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        50%       { box-shadow: 0 0 0 5px rgba(239, 68, 68, 0); }
    }

    /* Badge di tombol profil (dot merah kecil) */
    .profile-notif-dot {
        width: 10px;
        height: 10px;
        background: #ef4444;
        border-radius: 50%;
        border: 2px solid white;
        position: absolute;
        top: 2px;
        right: 2px;
        display: none;
        animation: pulse-notif 2s infinite;
    }

    #cartBadge, #mobileCartBadge {
        font-size: 10px;
        background-color: #ff4d4d;
        border: 2px solid #fff;
    }

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
    .offcanvas-body .nav-link:hover { background: var(--light-bg); color: var(--primary); padding-left: 15px; }

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
    axios.defaults.headers.common['X-CSRF-TOKEN']  = '{{ csrf_token() }}';
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
          {{-- Tombol profil dengan dot merah jika ada notif --}}
          <button class="btn-profile shadow-sm dropdown-toggle position-relative" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle fs-5"></i>
            <span class="d-none d-sm-inline">{{ Auth::user()->name }}</span>
            {{-- Dot merah kecil di tombol — ditampilkan via JS --}}
            <span id="profileNotifDot" class="profile-notif-dot"></span>
          </button>

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile">
            <li>
              <a class="dropdown-item py-2" href="{{ route('customer.profile') }}">
                <i class="bi bi-person-badge me-2"></i> Profil Saya
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="/dashboard">
                <i class="bi bi-speedometer2"></i> Dashboard
              </a>
            </li>
            <li>
              {{-- Item Riwayat Pesanan dengan badge notif --}}
              <a class="dropdown-item d-flex align-items-center" href="/customer/history">
                <i class="bi bi-clock-history"></i>
                Riwayat Pesanan
                {{-- Badge angka — ditampilkan via JS jika ada awaiting_payment --}}
                <span id="historyNotifBadge" class="notif-badge d-none">0</span>
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form action="/logout" method="POST">
                @csrf
                <button type="submit" class="dropdown-item text-danger fw-bold">
                  <i class="bi bi-box-arrow-right"></i> Keluar
                </button>
              </form>
            </li>
          </ul>
        </div>
        @else
        <a href="/login" class="btn-profile text-decoration-none px-4">
            <i class="bi bi-box-arrow-in-right me-2"></i> Masuk
        </a>
        @endauth

        <!-- Mobile Toggle Button -->
        <button class="mobile-nav-toggle d-md-none ms-3" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarPortal">
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
        <a class="nav-link d-flex align-items-center" href="/customer/history">
          <i class="bi bi-clock-history"></i>
          Riwayat Pesanan
          <span id="mobileHistoryNotifBadge" class="notif-badge d-none ms-auto">0</span>
        </a>
        <a class="nav-link" href="{{ route('customer.manual_request') }}">
          <i class="bi bi-pencil-square"></i> Request Baru
        </a>
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
    <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
        <p class="text-muted small mb-0">
            © 2026 <strong style="color: var(--secondary);">Yayasan Satriabudi Dharma Setia</strong> |
            <span style="color: var(--primary);">E-Pharma Logistics</span>
        </p>
        <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('images/logo-it-del.jpg') }}" alt="Logo IT Del" style="height:28px; width:auto; object-fit:contain; opacity:.85;">
            <img src="{{ asset('images/logo-ysds.avif') }}" alt="Logo YSDS" style="height:28px; width:auto; object-fit:contain; opacity:.85;">
        </div>
    </div>
  </footer>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

  <script>
    // ============================================================
    // UPDATE CART BADGE
    // ============================================================
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
        }).catch(() => {});
    }

    // ============================================================
    // UPDATE NOTIF BADGE — cek order awaiting_payment milik customer
    // ============================================================
    function updateNotifBadge() {
        @auth
        @role('customer')
        axios.get('/api/orders').then(res => {
            // Hitung order dengan status 'Awaiting Payment'
            const count = (res.data || []).filter(o =>
                o.status && o.status.name === 'Awaiting Payment'
            ).length;

            const badge       = document.getElementById('historyNotifBadge');
            const mobileBadge = document.getElementById('mobileHistoryNotifBadge');
            const dot         = document.getElementById('profileNotifDot');

            if (count > 0) {
                if (badge)       { badge.innerText = count;       badge.classList.remove('d-none'); }
                if (mobileBadge) { mobileBadge.innerText = count; mobileBadge.classList.remove('d-none'); }
                if (dot)         { dot.style.display = 'block'; }
            } else {
                if (badge)       { badge.classList.add('d-none'); }
                if (mobileBadge) { mobileBadge.classList.add('d-none'); }
                if (dot)         { dot.style.display = 'none'; }
            }
        }).catch(() => {});
        @endrole
        @endauth
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateCartBadge();
        updateNotifBadge();

        // Refresh badge setiap 60 detik (polling ringan)
        setInterval(updateNotifBadge, 60000);
    });
  </script>
  @stack('scripts')
</body>
</html>
