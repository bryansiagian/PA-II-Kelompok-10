<!DOCTYPE html>
<html lang="id" dir="ltr">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>E-Pharma - Control Panel</title>

	<!-- Global stylesheets -->
	<link href="{{ asset('admin/assets/fonts/inter/inter.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('admin/assets/icons/phosphor/styles.min.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('admin/assets/css/ltr/all.min.css') }}" id="stylesheet" rel="stylesheet" type="text/css">
	<!-- /global stylesheets -->

    <style>
        /* ============================================================
        ULTIMATE FIX: SWEETALERT2 FOR LIMITLESS TEMPLATE
        ============================================================ */

        /* 1. Perbaikan Container Utama */
        .swal2-popup {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
            border-radius: 1rem !important;
            padding: 2.5rem 1.5rem !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        }

        /* 2. Perbaikan Ikon dan Animasi (Fix Skewed Checkmark) */
        .swal2-icon {
            border-width: 4px !important;
            zoom: 0.8; /* Mengecilkan sedikit agar lebih elegan */
            margin-top: 0.5rem !important;
            margin-bottom: 1.5rem !important;
        }

        /* FIX KHUSUS: Garis centang yang miring/berantakan */
        .swal2-icon.swal2-success [class^='swal2-success-line'] {
            height: 5px !important;
            background-color: #a5dc86 !important;
        }

        .swal2-icon.swal2-success .swal2-success-ring {
            width: 100% !important;
            height: 100% !important;
            box-sizing: content-box !important; /* Paksa balik ke standar */
        }

        /* 3. Perbaikan Judul & Teks */
        .swal2-title {
            font-size: 1.4rem !important;
            font-weight: 800 !important;
            color: #1e293b !important;
            margin-bottom: 0.5rem !important;
        }

        .swal2-html-container {
            font-size: 0.95rem !important;
            color: #64748b !important;
            line-height: 1.6 !important;
        }

        /* 4. Perbaikan Tombol (Limitless Style) */
        .swal2-actions {
            margin-top: 2rem !important;
            gap: 10px !important;
        }

        .swal2-styled.swal2-confirm {
            background-color: #5c6bc0 !important; /* Indigo asli Limitless */
            color: #fff !important;
            border-radius: 0.5rem !important;
            padding: 0.6rem 2.5rem !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            border: none !important;
            transition: all 0.2s !important;
        }

        .swal2-styled.swal2-confirm:hover {
            background-color: #3f51b5 !important;
            transform: translateY(-1px) !important;
        }

        .swal2-styled.swal2-cancel {
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            border-radius: 0.5rem !important;
            padding: 0.6rem 2.5rem !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
        }

        /* 5. Loading Spinner Fix */
        .swal2-loader {
            border-color: #5c6bc0 transparent #5c6bc0 transparent !important;
        }


        /* Laravel Active Link Fix */
        .nav-link.active { background-color: rgba(var(--primary-rgb), .1); color: var(--primary) !important; }
    </style>
	<!-- Core JS files -->
	<script src="{{ asset('admin/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
	<!-- /core JS files -->

	<!-- Theme JS files -->
	<script src="{{ asset('admin/assets/js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<!-- /theme JS files -->

    <script>
        // Global Axios Configuration
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    </script>
</head>

<body>

	<!-- Main navbar -->
	<div class="navbar navbar-dark navbar-expand-lg navbar-static border-bottom border-bottom-white border-opacity-10">
		<div class="container-fluid">
			<div class="d-flex d-lg-none me-2">
				<button type="button" class="navbar-toggler sidebar-mobile-main-toggle rounded-pill">
					<i class="ph-list"></i>
				</button>
			</div>

			<div class="navbar-brand flex-1 flex-lg-0">
    <a href="{{ url('/dashboard') }}" class="d-inline-flex align-items-center text-white text-decoration-none">
        <!-- Ikon Logo (Menggunakan Ikon Obat) -->
        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
            <i class="ph-pill text-indigo fs-4"></i>
        </div>

        <!-- Teks Brand -->
        <span class="d-none d-sm-inline-block fw-bold fs-5 tracking-tight">E-PHARMA</span>
    </a>
    </div>

			<ul class="nav flex-row">
				<li class="nav-item d-lg-none">
					<a href="#navbar_search" class="navbar-nav-link navbar-nav-link-icon rounded-pill" data-bs-toggle="collapse">
						<i class="ph-magnifying-glass"></i>
					</a>
				</li>

				<li class="nav-item nav-item-dropdown-lg dropdown">
					<a href="#" class="navbar-nav-link navbar-nav-link-icon rounded-pill" data-bs-toggle="dropdown">
						<i class="ph-squares-four"></i>
					</a>

					<div class="dropdown-menu dropdown-menu-scrollable-sm wmin-lg-600 p-0">
						<div class="d-flex align-items-center border-bottom p-3">
							<h6 class="mb-0">Browse apps</h6>
							<a href="#" class="ms-auto">
								View all
								<i class="ph-arrow-circle-right ms-1"></i>
							</a>
						</div>

						<div class="row row-cols-1 row-cols-sm-2 g-0">
							<div class="col">
								<button type="button" class="dropdown-item text-wrap h-100 align-items-start border-end-sm border-bottom p-3">
									<div>
										<img src="../../../admin/assets/images/demo/logos/1.svg" class="h-40px mb-2" alt="">
										<div class="fw-semibold my-1">Customer data platform</div>
										<div class="text-muted">Unify customer data from multiple sources</div>
									</div>
								</button>
							</div>

							<div class="col">
								<button type="button" class="dropdown-item text-wrap h-100 align-items-start border-bottom p-3">
									<div>
										<img src="../../../admin/assets/images/demo/logos/2.svg" class="h-40px mb-2" alt="">
										<div class="fw-semibold my-1">Data catalog</div>
										<div class="text-muted">Discover, inventory, and organize data admin/assets</div>
									</div>
								</button>
							</div>

							<div class="col">
								<button type="button" class="dropdown-item text-wrap h-100 align-items-start border-end-sm border-bottom border-bottom-sm-0 rounded-bottom-start p-3">
									<div>
										<img src="../../../admin/assets/images/demo/logos/3.svg" class="h-40px mb-2" alt="">
										<div class="fw-semibold my-1">Data governance</div>
										<div class="text-muted">The collaboration hub and data marketplace</div>
									</div>
								</button>
							</div>

							<div class="col">
								<button type="button" class="dropdown-item text-wrap h-100 align-items-start rounded-bottom-end p-3">
									<div>
										<img src="../../../admin/assets/images/demo/logos/4.svg" class="h-40px mb-2" alt="">
										<div class="fw-semibold my-1">Data privacy</div>
										<div class="text-muted">Automated provisioning of non-production datasets</div>
									</div>
								</button>
							</div>
						</div>
					</div>
				</li>

				<li class="nav-item nav-item-dropdown-lg dropdown ms-lg-2">
					<a href="#" class="navbar-nav-link navbar-nav-link-icon rounded-pill" data-bs-toggle="dropdown" data-bs-auto-close="outside">
						<i class="ph-chats"></i>
						<span class="badge bg-yellow text-black position-absolute top-0 end-0 translate-middle-top zindex-1 rounded-pill mt-1 me-1">8</span>
					</a>

					<div class="dropdown-menu wmin-lg-400 p-0">
						<div class="d-flex align-items-center p-3">
							<h6 class="mb-0">Messages</h6>
							<div class="ms-auto">
								<a href="#" class="text-body">
									<i class="ph-plus-circle"></i>
								</a>
								<a href="#search_messages" class="collapsed text-body ms-2" data-bs-toggle="collapse">
									<i class="ph-magnifying-glass"></i>
								</a>
							</div>
						</div>

						<div class="collapse" id="search_messages">
							<div class="px-3 mb-2">
								<div class="form-control-feedback form-control-feedback-start">
									<input type="text" class="form-control" placeholder="Search messages">
									<div class="form-control-feedback-icon">
										<i class="ph-magnifying-glass"></i>
									</div>
								</div>
							</div>
						</div>

						<div class="dropdown-menu-scrollable pb-2">
							<a href="#" class="dropdown-item align-items-start text-wrap py-2">
								<div class="status-indicator-container me-3">
									<img src="../../../admin/assets/images/demo/users/face10.jpg" class="w-40px h-40px rounded-pill" alt="">
									<span class="status-indicator bg-warning"></span>
								</div>

								<div class="flex-1">
									<span class="fw-semibold">James Alexander</span>
									<span class="text-muted float-end fs-sm">04:58</span>
									<div class="text-muted">who knows, maybe that would be the best thing for me...</div>
								</div>
							</a>

							<a href="#" class="dropdown-item align-items-start text-wrap py-2">
								<div class="status-indicator-container me-3">
									<img src="../../../admin/assets/images/demo/users/face3.jpg" class="w-40px h-40px rounded-pill" alt="">
									<span class="status-indicator bg-success"></span>
								</div>

								<div class="flex-1">
									<span class="fw-semibold">Margo Baker</span>
									<span class="text-muted float-end fs-sm">12:16</span>
									<div class="text-muted">That was something he was unable to do because...</div>
								</div>
							</a>

							<a href="#" class="dropdown-item align-items-start text-wrap py-2">
								<div class="status-indicator-container me-3">
									<img src="../../../admin/assets/images/demo/users/face24.jpg" class="w-40px h-40px rounded-pill" alt="">
									<span class="status-indicator bg-success"></span>
								</div>
								<div class="flex-1">
									<span class="fw-semibold">Jeremy Victorino</span>
									<span class="text-muted float-end fs-sm">22:48</span>
									<div class="text-muted">But that would be extremely strained and suspicious...</div>
								</div>
							</a>

							<a href="#" class="dropdown-item align-items-start text-wrap py-2">
								<div class="status-indicator-container me-3">
									<img src="../../../admin/assets/images/demo/users/face4.jpg" class="w-40px h-40px rounded-pill" alt="">
									<span class="status-indicator bg-grey"></span>
								</div>
								<div class="flex-1">
									<span class="fw-semibold">Beatrix Diaz</span>
									<span class="text-muted float-end fs-sm">Tue</span>
									<div class="text-muted">What a strenuous career it is that I've chosen...</div>
								</div>
							</a>

							<a href="#" class="dropdown-item align-items-start text-wrap py-2">
								<div class="status-indicator-container me-3">
									<img src="../../../admin/assets/images/demo/users/face25.jpg" class="w-40px h-40px rounded-pill" alt="">
									<span class="status-indicator bg-danger"></span>
								</div>
								<div class="flex-1">
									<span class="fw-semibold">Richard Vango</span>
									<span class="text-muted float-end fs-sm">Mon</span>
									<div class="text-muted">Other travelling salesmen live a life of luxury...</div>
								</div>
							</a>
						</div>

						<div class="d-flex border-top py-2 px-3">
							<a href="#" class="text-body">
								<i class="ph-checks me-1"></i>
								Dismiss all
							</a>
							<a href="#" class="text-body ms-auto">
								View all
								<i class="ph-arrow-circle-right ms-1"></i>
							</a>
						</div>
					</div>
				</li>
			</ul>

			<div class="navbar-collapse justify-content-center flex-lg-1 order-2 order-lg-1 collapse" id="navbar_search">
				<div class="navbar-search flex-fill position-relative mt-2 mt-lg-0 mx-lg-3">
					<div class="form-control-feedback form-control-feedback-start flex-grow-1" data-color-theme="dark">
						<input type="text" class="form-control bg-transparent rounded-pill" placeholder="Search" data-bs-toggle="dropdown">
						<div class="form-control-feedback-icon">
							<i class="ph-magnifying-glass"></i>
						</div>
						<div class="dropdown-menu w-100" data-color-theme="light">
							<button type="button" class="dropdown-item">
								<div class="text-center w-32px me-3">
									<i class="ph-magnifying-glass"></i>
								</div>
								<span>Search <span class="fw-bold">"in"</span> everywhere</span>
							</button>

							<div class="dropdown-divider"></div>

							<div class="dropdown-menu-scrollable-lg">
								<div class="dropdown-header">
									Contacts
									<a href="#" class="float-end">
										See all
										<i class="ph-arrow-circle-right ms-1"></i>
									</a>
								</div>

								<div class="dropdown-item cursor-pointer">
									<div class="me-3">
										<img src="../../../admin/assets/images/demo/users/face3.jpg" class="w-32px h-32px rounded-pill" alt="">
									</div>

									<div class="d-flex flex-column flex-grow-1">
										<div class="fw-semibold">Christ<mark>in</mark>e Johnson</div>
										<span class="fs-sm text-muted">c.johnson@awesomecorp.com</span>
									</div>

									<div class="d-inline-flex">
										<a href="#" class="text-body ms-2">
											<i class="ph-user-circle"></i>
										</a>
									</div>
								</div>

								<div class="dropdown-item cursor-pointer">
									<div class="me-3">
										<img src="../../../admin/assets/images/demo/users/face24.jpg" class="w-32px h-32px rounded-pill" alt="">
									</div>

									<div class="d-flex flex-column flex-grow-1">
										<div class="fw-semibold">Cl<mark>in</mark>ton Sparks</div>
										<span class="fs-sm text-muted">c.sparks@awesomecorp.com</span>
									</div>

									<div class="d-inline-flex">
										<a href="#" class="text-body ms-2">
											<i class="ph-user-circle"></i>
										</a>
									</div>
								</div>

								<div class="dropdown-divider"></div>

								<div class="dropdown-header">
									Clients
									<a href="#" class="float-end">
										See all
										<i class="ph-arrow-circle-right ms-1"></i>
									</a>
								</div>

								<div class="dropdown-item cursor-pointer">
									<div class="me-3">
										<img src="../../../admin/assets/images/brands/adobe.svg" class="w-32px h-32px rounded-pill" alt="">
									</div>

									<div class="d-flex flex-column flex-grow-1">
										<div class="fw-semibold">Adobe <mark>In</mark>c.</div>
										<span class="fs-sm text-muted">Enterprise license</span>
									</div>

									<div class="d-inline-flex">
										<a href="#" class="text-body ms-2">
											<i class="ph-briefcase"></i>
										</a>
									</div>
								</div>

								<div class="dropdown-item cursor-pointer">
									<div class="me-3">
										<img src="../../../admin/assets/images/brands/holiday-inn.svg" class="w-32px h-32px rounded-pill" alt="">
									</div>

									<div class="d-flex flex-column flex-grow-1">
										<div class="fw-semibold">Holiday-<mark>In</mark>n</div>
										<span class="fs-sm text-muted">On-premise license</span>
									</div>

									<div class="d-inline-flex">
										<a href="#" class="text-body ms-2">
											<i class="ph-briefcase"></i>
										</a>
									</div>
								</div>

								<div class="dropdown-item cursor-pointer">
									<div class="me-3">
										<img src="../../../admin/assets/images/brands/ing.svg" class="w-32px h-32px rounded-pill" alt="">
									</div>

									<div class="d-flex flex-column flex-grow-1">
										<div class="fw-semibold"><mark>IN</mark>G Group</div>
										<span class="fs-sm text-muted">Perpetual license</span>
									</div>

									<div class="d-inline-flex">
										<a href="#" class="text-body ms-2">
											<i class="ph-briefcase"></i>
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>

					<a href="#" class="navbar-nav-link align-items-center justify-content-center w-40px h-32px rounded-pill position-absolute end-0 top-50 translate-middle-y p-0 me-1" data-bs-toggle="dropdown" data-bs-auto-close="outside">
						<i class="ph-faders-horizontal"></i>
					</a>

					<div class="dropdown-menu w-100 p-3">
						<div class="d-flex align-items-center mb-3">
							<h6 class="mb-0">Search options</h6>
							<a href="#" class="text-body rounded-pill ms-auto">
								<i class="ph-clock-counter-clockwise"></i>
							</a>
						</div>

						<div class="mb-3">
							<label class="d-block form-label">Category</label>
							<label class="form-check form-check-inline">
								<input type="checkbox" class="form-check-input" checked>
								<span class="form-check-label">Invoices</span>
							</label>
							<label class="form-check form-check-inline">
								<input type="checkbox" class="form-check-input">
								<span class="form-check-label">Files</span>
							</label>
							<label class="form-check form-check-inline">
								<input type="checkbox" class="form-check-input">
								<span class="form-check-label">Users</span>
							</label>
						</div>

						<div class="mb-3">
							<label class="form-label">Addition</label>
							<div class="input-group">
								<select class="form-select w-auto flex-grow-0">
									<option value="1" selected>has</option>
									<option value="2">has not</option>
								</select>
								<input type="text" class="form-control" placeholder="Enter the word(s)">
							</div>
						</div>

						<div class="mb-3">
							<label class="form-label">Status</label>
							<div class="input-group">
								<select class="form-select w-auto flex-grow-0">
									<option value="1" selected>is</option>
									<option value="2">is not</option>
								</select>
								<select class="form-select">
									<option value="1" selected>Active</option>
									<option value="2">Inactive</option>
									<option value="3">New</option>
									<option value="4">Expired</option>
									<option value="5">Pending</option>
								</select>
							</div>
						</div>

						<div class="d-flex">
							<button type="button" class="btn btn-light">Reset</button>

							<div class="ms-auto">
								<button type="button" class="btn btn-light">Cancel</button>
								<button type="button" class="btn btn-primary ms-2">Apply</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<ul class="nav flex-row justify-content-end order-1 order-lg-2">
				<li class="nav-item ms-lg-2">
					<a href="#" class="navbar-nav-link navbar-nav-link-icon rounded-pill" data-bs-toggle="offcanvas" data-bs-target="#notifications">
						<i class="ph-bell"></i>
						<span class="badge bg-yellow text-black position-absolute top-0 end-0 translate-middle-top zindex-1 rounded-pill mt-1 me-1">2</span>
					</a>
				</li>

				<li class="nav-item nav-item-dropdown-lg dropdown ms-lg-2">
					<a href="#" class="navbar-nav-link align-items-center rounded-pill p-1" data-bs-toggle="dropdown">
						<div class="status-indicator-container">
							<img src="../../../admin/assets/images/demo/users/face11.jpg" class="w-32px h-32px rounded-pill" alt="">
							<span class="status-indicator bg-success"></span>
						</div>
						<span class="d-none d-lg-inline-block mx-lg-2">{{ Auth::user()->name }}</span>
					</a>

					<div class="dropdown-menu dropdown-menu-end">
						{{-- <a href="{{ route('profile.index') }}" class="dropdown-item">
                        <i class="ph-user-circle me-2"></i>
                         My profile
                        </a> --}}
                        {{-- <div class="dropdown-divider"></div> --}}
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="ph-sign-out me-2"></i>
                                Keluar (Logout)
                            </button>
                        </form>
					</div>
				</li>
			</ul>
		</div>
	</div>
	<!-- /main navbar -->


	<!-- Page content -->
	<div class="page-content">

		<!-- Main sidebar -->
		<div class="sidebar sidebar-dark sidebar-main sidebar-expand-lg">

			<!-- Sidebar content -->
			<div class="sidebar-content">

				<!-- Sidebar header -->
				<div class="sidebar-section">
					<div class="sidebar-section-body d-flex justify-content-center">
						<h5 class="sidebar-resize-hide flex-grow-1 my-auto">Navigasi Utama</h5>

						<div>
							<button type="button" class="btn btn-flat-white btn-icon btn-sm rounded-pill border-transparent sidebar-control sidebar-main-resize d-none d-lg-inline-flex">
								<i class="ph-arrows-left-right"></i>
							</button>

							<button type="button" class="btn btn-flat-white btn-icon btn-sm rounded-pill border-transparent sidebar-mobile-main-toggle d-lg-none">
								<i class="ph-x"></i>
							</button>
						</div>
					</div>
				</div>
				<!-- /sidebar header -->


				<!-- Main navigation -->
                <div class="sidebar-section">
                    <ul class="nav nav-sidebar" data-nav-type="accordion">

                        <!-- DASHBOARD MODULE -->
                        <li class="nav-item-header pt-0">
                            <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">Utama</div>
                            <i class="ph-dots-three sidebar-resize-show"></i>
                        </li>
                        <li class="nav-item">
                            <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                                <i class="ph-house"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        @can('view reports')
                        <li class="nav-item">
                            <a href="/reports" class="nav-link {{ request()->is('reports') ? 'active' : '' }}">
                                <i class="ph-chart-line"></i>
                                <span>Analytics</span>
                            </a>
                        </li>
                        @endcan

                        <!-- VERIFICATION MODULE -->
                        @can('manage users')
                        <li class="nav-item-header">
                            <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">Verifikasi</div>
                            <i class="ph-dots-three sidebar-resize-show"></i>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/users/pending" class="nav-link {{ request()->is('admin/users/pending') ? 'active' : '' }}">
                                <i class="ph-user-plus"></i>
                                <span>Permintaan Akun</span>
                            </a>
                        </li>
                        @endcan

                        <!-- SYSTEM CONTROL -->
                        @role('admin')
                        <li class="nav-item-header">
                            <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">Kontrol Sistem</div>
                            <i class="ph-dots-three sidebar-resize-show"></i>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/users" class="nav-link {{ request()->is('admin/users') ? 'active' : '' }}">
                                <i class="ph-users-three"></i>
                                <span>Kelola Pengguna</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/logs" class="nav-link {{ request()->is('admin/logs') ? 'active' : '' }}">
                                <i class="ph-scroll"></i>
                                <span>Log Sistem</span>
                            </a>
                        </li>

                        <!-- CMS MODULE -->
                        <li class="nav-item-header">
                            <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">Manajemen Konten</div>
                            <i class="ph-dots-three sidebar-resize-show"></i>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/cms/profile" class="nav-link {{ request()->is('admin/cms/profile') ? 'active' : '' }}">
                                <i class="ph-buildings"></i>
                                <span>Profile Yayasan</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/cms/org" class="nav-link {{ request()->is('admin/cms/org') ? 'active' : '' }}">
                                <i class="ph-users"></i> <!-- DIGANTI: dari hierarchy ke users (pasti ada) -->
                                <span>Struktur Organisasi</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/cms/post-categories" class="nav-link {{ request()->is('admin/cms/post-categories') ? 'active' : '' }}">
                                <i class="ph-bookmarks"></i>
                                <span>Kategori Post</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/cms/posts" class="nav-link {{ request()->is('admin/cms/posts*') ? 'active' : '' }}">
                                <i class="ph-article"></i>
                                <span>Berita & Kegiatan</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/cms/contacts" class="nav-link {{ request()->is('admin/cms/contacts') ? 'active' : '' }}">
                                <i class="ph-phone"></i>
                                <span>Kontak & Sosmed</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/cms/gallery" class="nav-link {{ request()->is('admin/cms/gallery') ? 'active' : '' }}">
                                <i class="ph-camera"></i> <!-- DIGANTI: dari images ke camera (pasti ada) -->
                                <span>Galeri</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/cms/files" class="nav-link {{ request()->is('admin/cms/files') ? 'active' : '' }}">
                                <i class="ph-file-arrow-up"></i>
                                <span>Dokumen & File</span>
                            </a>
                        </li>
                        @endrole

                        <!-- WAREHOUSE MODULE -->
                        @can('manage inventory')
                        <li class="nav-item-header">
                            <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">Gudang & Stok</div>
                            <i class="ph-dots-three sidebar-resize-show"></i>
                        </li>
                        <li class="nav-item">
                            <a href="/operator/products" class="nav-link {{ request()->is('operator/products*') ? 'active' : '' }}">
                                <i class="ph-package"></i>
                                <span>Katalog Produk</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/operator/categories" class="nav-link {{ request()->is('operator/categories*') ? 'active' : '' }}">
                                <i class="ph-list"></i> <!-- DIGANTI: dari tags ke list (pasti ada) -->
                                <span>Kategori Produk</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/operator/orders" class="nav-link {{ request()->is('operator/orders*') ? 'active' : '' }}">
                                <i class="ph-shopping-cart"></i>
                                <span>Antrian Pesanan</span>
                            </a>
                        </li>

                        <li class="nav-item-header">
                            <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">Infrastruktur</div>
                            <i class="ph-dots-three sidebar-resize-show"></i>
                        </li>
                        <li class="nav-item">
                            <a href="/operator/warehouses" class="nav-link {{ request()->is('operator/warehouses*') ? 'active' : '' }}">
                                <i class="ph-buildings"></i> <!-- DIGANTI: disamakan dengan Profile Yayasan (pasti muncul) -->
                                <span>Daftar Gudang</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/operator/racks" class="nav-link {{ request()->is('operator/racks*') ? 'active' : '' }}">
                                <i class="ph-buildings"></i> <!-- DIGANTI: disamakan dengan Profile Yayasan (pasti muncul) -->
                                <span>Daftar Rak</span>
                            </a>
                        </li>
                        @endcan

                        <!-- COURIER MODULE -->
                        @role('courier')
                        <li class="nav-item-header">
                            <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">Logistik</div>
                            <i class="ph-dots-three sidebar-resize-show"></i>
                        </li>
                        <li class="nav-item">
                            <a href="/courier/available" class="nav-link {{ request()->is('courier/available') ? 'active' : '' }}">
                                <i class="ph-megaphone"></i>
                                <span>Bursa Tugas</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/courier/active" class="nav-link {{ request()->is('courier/active') ? 'active' : '' }}">
                                <i class="ph-truck"></i>
                                <span>Tugas Aktif</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/courier/history" class="nav-link {{ request()->is('courier/history') ? 'active' : '' }}">
                                <i class="ph-clock-counter-clockwise"></i>
                                <span>Riwayat Selesai</span>
                            </a>
                        </li>
                        @endrole

                    </ul>
                </div>

			</div>
			<!-- /sidebar content -->

		</div>
		<!-- /main sidebar -->


		<!-- Main content -->
		<div class="content-wrapper">

			<!-- Inner content -->
			<div class="content-inner">

				<!-- Page header -->
				<div class="page-header">
					{{-- <div class="page-header-content d-lg-flex">
						<div class="d-flex p-3">
							<h4 class="page-title mb-0">
								Backoffice - <span class="fw-normal">@yield('page_title', 'Dashboard')</span>
							</h4>
						</div>
					</div> --}}

					{{-- <div class="page-header-content d-lg-flex border-top">
						<div class="d-flex">
							<div class="breadcrumb py-2">
								<a href="/dashboard" class="breadcrumb-item"><i class="ph-house me-2"></i> Home</a>
								<span class="breadcrumb-item active">@yield('page_title', 'Dashboard')</span>
							</div>
						</div>
					</div> --}}
				</div>
				<!-- /page header -->


				<!-- Content area -->
				<div class="content">
					@yield('content')
				</div>
				<!-- /content area -->


				<!-- Footer -->
				<div class="navbar navbar-sm navbar-footer border-top p-3">
					<div class="container-fluid">
						<span>&copy; 2026 <a href="#" class="text-indigo fw-bold text-decoration-none">Yayasan Satriabudi Dharma Setia</a></span>
						<ul class="nav">
							<li class="nav-item">
								<span class="text-muted small">Kelompok 10</span>
							</li>
						</ul>
					</div>
				</div>
				<!-- /footer -->

			</div>
			<!-- /inner content -->

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

</body>
</html>
