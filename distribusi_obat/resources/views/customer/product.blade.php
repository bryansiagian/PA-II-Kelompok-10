@extends('layouts.portal')

@section('content')
<style>
    :root {
      --primary: #00838f;
      --secondary: #2c4964;
      --hover-color: #006064;
    }

    .search-section { background: #fff; border-bottom: 1px solid #eee; padding: 20px 0; }
    .product-card { transition: 0.3s; border-radius: 15px; overflow: hidden; height: 100%; border: 1px solid #eee; background: #fff; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
    .category-badge { font-size: 10px; background: #e0f2f1; color: var(--primary); padding: 4px 10px; border-radius: 50px; font-weight: 700; }

    .btn-cart-sm { width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid var(--primary); color: var(--primary); background: transparent; transition: 0.3s; }
    .btn-cart-sm:hover { background: var(--primary); color: #fff; }

    .btn-order-sm { background: var(--primary); color: white !important; border-radius: 20px; padding: 5px 15px; font-size: 13px; font-weight: 600; border: none; transition: 0.3s; }
    .btn-order-sm:hover { background: var(--hover-color); box-shadow: 0 4px 10px rgba(0, 131, 143, 0.3); }

    .price-text { color: var(--secondary); font-weight: 800; font-size: 1rem; }

    #modalDetailImg { width: 100%; height: 300px; object-fit: contain; background: #f8f9fa; border-radius: 15px; }
    .detail-label { font-size: 12px; font-weight: 700; color: #999; text-transform: uppercase; margin-bottom: 2px; }
    .detail-value { font-weight: 600; color: var(--secondary); margin-bottom: 15px; }

    .btn-medinest { background: var(--primary) !important; color: white !important; border-radius: 30px; padding: 12px 25px; font-weight: 600; border: none; transition: 0.3s; }
    .btn-medinest:hover { background: var(--hover-color) !important; box-shadow: 0 5px 15px rgba(0, 131, 143, 0.3); }

    .btn-outline-info { border-color: var(--primary) !important; color: var(--primary) !important; }
    .btn-outline-info:hover { background-color: var(--primary) !important; color: white !important; border-color: var(--primary) !important; }

    .qty-control { background: #f8f9fa; border-radius: 30px; padding: 5px; border: 1px solid #eee; display: inline-flex; align-items: center; }
    .btn-qty { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #dee2e6; color: var(--primary); font-weight: bold; cursor: pointer; }
    .btn-qty:hover { background: var(--primary); color: #fff; }

    /* ── Skeleton loading ──────────────────────────────────────────────────── */
    @keyframes shimmer {
        0%   { background-position: -600px 0; }
        100% { background-position:  600px 0; }
    }
    .sk {
        display: inline-block;
        border-radius: 8px;
        background: linear-gradient(90deg, #e0e0e0 25%, #efefef 50%, #e0e0e0 75%);
        background-size: 1200px 100%;
        animation: shimmer 1.5s infinite linear;
    }
    .sk-block { display: block; }
</style>

<!-- Header & Search -->
<div class="search-section shadow-sm">
    <div class="container">
        <div class="mb-3">
            <a href="/dashboard" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                <i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda
            </a>
        </div>
        <div class="row align-items-center">
            <div class="col-md-4 text-center text-md-start">
                <h4 class="fw-bold m-0" style="color: var(--secondary);">
                    <i class="bi bi-grid-fill me-2" style="color: var(--primary);"></i>Katalog Produk
                </h4>
            </div>
            <div class="col-md-8 mt-3 mt-md-0">
                <div class="row g-2">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0">
                                <i class="bi bi-search" style="color: var(--primary);"></i>
                            </span>
                            <input type="text" id="searchInput"
                                   class="form-control bg-light border-0 shadow-none"
                                   placeholder="Cari nama produk atau SKU..."
                                   onkeyup="debounceFilter()">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <select id="categoryFilter" class="form-select bg-light border-0 shadow-none" onchange="filterProducts()">
                            <option value="">Semua Kategori</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4 mb-5">
    <div id="productGrid" class="row gx-3 gy-4"></div>
</div>

<!-- MODAL DETAIL PRODUK -->
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 p-md-5 pt-0">
                <div class="row align-items-start">
                    <div class="col-md-5 mb-4 mb-md-0 text-center">
                        <img id="modalDetailImg" src="" alt="Produk">
                    </div>
                    <div class="col-md-7 ps-md-4">
                        <span id="modalDetailCategory" class="category-badge mb-2 d-inline-block">Kategori</span>
                        <h3 id="modalDetailName" class="fw-bold text-dark mb-1">Nama Produk</h3>
                        <div id="modalDetailPrice" class="fs-4 fw-bold mb-3" style="color: var(--primary);">Rp 0</div>

                        <div class="row g-2 mb-3 border-top pt-3">
                            <div class="col-6">
                                <div class="detail-label">Stok Tersedia</div>
                                <div id="modalDetailStock" class="fw-bold text-dark">-</div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Satuan</div>
                                <div id="modalDetailUnit" class="fw-bold text-dark">-</div>
                            </div>
                        </div>

                        <!-- FORM QUICK ORDER -->
                        <div id="quickOrderForm" class="bg-light p-3 rounded-3 mb-3 d-none">
                            <h6 class="fw-bold small text-muted text-uppercase mb-3 border-bottom pb-2">Konfirmasi Logistik Cepat (Sumut)</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="detail-label">Kab/Kota</label>
                                    <select id="quick_regency" class="form-select form-select-sm" onchange="fetchDistricts(this.value)">
                                        <option value="" disabled selected>Pilih Kab/Kota</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="detail-label">Kecamatan</label>
                                    <select id="quick_district" class="form-select form-select-sm" onchange="fetchVillages(this.value)" disabled>
                                        <option value="">Pilih Kecamatan</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="detail-label">Kelurahan</label>
                                    <select id="quick_village" class="form-select form-select-sm" disabled>
                                        <option value="">Pilih Kelurahan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="detail-label">Alamat Pengiriman</label>
                                <div>
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="radio" name="q_addr_type" id="q_addr_profile" value="profile" checked onchange="toggleQuickAddrInput()">
                                        <label class="form-check-label small fw-bold" for="q_addr_profile">Gunakan Alamat Akun</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="q_addr_type" id="q_addr_custom" value="custom" onchange="toggleQuickAddrInput()">
                                        <label class="form-check-label small fw-bold" for="q_addr_custom">Input Alamat Baru</label>
                                    </div>
                                    <textarea id="quick_shipping_address" class="form-control form-control-sm d-none border-0 shadow-sm" rows="2" placeholder="Nama jalan, nomor gedung..."></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label class="detail-label">Jumlah</label>
                                    <div class="qty-control">
                                        <button type="button" class="btn-qty" onclick="changeQuickQty(-1)"><i class="bi bi-dash"></i></button>
                                        <input type="number" id="quick_qty" class="form-control text-center border-0 bg-transparent fw-bold" style="width: 45px;" value="1" readonly>
                                        <button type="button" class="btn-qty" onclick="changeQuickQty(1)"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="detail-label">Metode</label>
                                    <select id="quick_request_type" class="form-select form-select-sm">
                                        <option value="delivery">🚚 Kirim</option>
                                        <option value="self_pickup">🏢 Ambil</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="detail-label">Nomor Telepon Pemesan</label>
                                <input type="text" id="quick_phone" class="form-control form-control-sm shadow-sm"
                                    placeholder="Contoh: 08123456789">
                            </div>
                            <div class="mt-3">
                                <label class="detail-label">Catatan Tambahan</label>
                                <textarea id="quick_notes" class="form-control form-control-sm shadow-sm" rows="2" placeholder="Contoh: Unit Gawat Darurat..."></textarea>
                            </div>
                        </div>

                        <div id="modalActionButtons" class="d-flex gap-2 mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Loading Overlay -->
<div id="paymentOverlay">
    <div class="overlay-card">
        <div class="spinner-border" style="width:3rem;height:3rem;color:var(--primary);" role="status"></div>
        <h5>Membuka Halaman Pembayaran</h5>
        <p>Jangan tutup halaman ini...</p>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

    const isLoggedIn    = @json(auth()->check());
    const isCustomer    = @auth @json(auth()->user()->hasRole('customer')) @else false @endauth;
    const PROVINCE_ID   = '12';

    let allProducts      = [];
    let searchTimeout    = null;
    let maxStockCurrent  = 0;
    let detailModalInstance;

    document.addEventListener('DOMContentLoaded', () => {
        detailModalInstance = new bootstrap.Modal(document.getElementById('productDetailModal'));
        showSkeletons();
        fetchCategories();
        fetchProducts();
        if (isCustomer) fetchRegencies();
    });

    /* =========================
       SKELETON
    ========================= */
    function showSkeletons(count = 8) {
        let html = '';
        for (let i = 0; i < count; i++) {
            html += `
            <div class="col-lg-3 col-md-4 col-6">
                <div class="product-card p-3 d-flex flex-column h-100 shadow-sm">
                    <div class="bg-light rounded-4 mb-2 p-1 text-center" style="height:140px;">
                        <span class="sk sk-block h-100 w-100" style="border-radius:12px;"></span>
                    </div>
                    <span class="sk mb-2" style="width:60px;height:18px;border-radius:50px;"></span>
                    <span class="sk sk-block mb-1" style="width:85%;height:14px;"></span>
                    <span class="sk sk-block mb-3" style="width:55%;height:12px;"></span>
                    <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between">
                        <span class="sk" style="width:70px;height:16px;"></span>
                        <span class="sk" style="width:80px;height:32px;border-radius:20px;"></span>
                    </div>
                </div>
            </div>`;
        }
        document.getElementById('productGrid').innerHTML = html;
    }

    /* =========================
       API WILAYAH
    ========================= */
    async function fetchRegencies() {
        try {
            const data = await (await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${PROVINCE_ID}.json`)).json();
            let html = '<option value="" selected disabled>Pilih Kab/Kota</option>';
            data.forEach(item => { html += `<option value="${item.id}" data-name="${item.name}">${item.name}</option>`; });
            document.getElementById('quick_regency').innerHTML = html;
        } catch (e) { console.error('Gagal muat kabupaten:', e); }
    }

    async function fetchDistricts(regencyId) {
        const sel = document.getElementById('quick_district');
        sel.disabled = true; sel.innerHTML = '<option>Memuat...</option>';
        try {
            const data = await (await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${regencyId}.json`)).json();
            let html = '<option value="" selected disabled>Pilih Kecamatan</option>';
            data.forEach(item => { html += `<option value="${item.id}" data-name="${item.name}">${item.name}</option>`; });
            sel.innerHTML = html; sel.disabled = false;
        } catch (e) { console.error('Gagal muat kecamatan:', e); }
    }

    async function fetchVillages(districtId) {
        const sel = document.getElementById('quick_village');
        sel.disabled = true; sel.innerHTML = '<option>Memuat...</option>';
        try {
            const data = await (await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${districtId}.json`)).json();
            let html = '<option value="" selected disabled>Pilih Kelurahan/Desa</option>';
            data.forEach(item => { html += `<option value="${item.id}" data-name="${item.name}">${item.name}</option>`; });
            sel.innerHTML = html; sel.disabled = false;
        } catch (e) { console.error('Gagal muat kelurahan:', e); }
    }

    function toggleQuickAddrInput() {
        const isCustom = document.getElementById('q_addr_custom').checked;
        document.getElementById('quick_shipping_address').classList.toggle('d-none', !isCustom);
    }

    function changeQuickQty(val) {
        const input = document.getElementById('quick_qty');
        const next  = parseInt(input.value) + val;
        if (next >= 1 && next <= maxStockCurrent) input.value = next;
    }

    /* =========================
       KATALOG
    ========================= */
    // ✅ MODIFIKASI: tampilkan skeleton dulu, baru filter setelah delay
    function debounceFilter() {
        clearTimeout(searchTimeout);
        showSkeletons(); // langsung tampil skeleton saat user mengetik
        searchTimeout = setTimeout(() => filterProducts(), 500);
    }

    function fetchCategories() {
        axios.get('/api/product-categories').then(res => {
            let opt = '<option value="">Semua Kategori</option>';
            res.data.forEach(c => opt += `<option value="${c.id}">${c.name}</option>`);
            document.getElementById('categoryFilter').innerHTML = opt;
        });
    }

    function fetchProducts() {
        axios.get('/api/products')
            .then(res => {
                allProducts = res.data;
                renderProducts(allProducts);
            })
            .catch(() => {
                document.getElementById('productGrid').innerHTML =
                    '<div class="col-12 text-center py-5 text-danger">Gagal memuat produk.</div>';
            });
    }

    function renderProducts(data) {
        const grid = document.getElementById('productGrid');
        if (data.length === 0) {
            grid.innerHTML = '<div class="col-12 text-center py-5 text-muted">Produk tidak ditemukan.</div>';
            return;
        }
        let html = '';
        data.forEach(p => {
            const img = p.image ? `/${p.image}` : 'https://placehold.co/400x300';
            html += `
            <div class="col-lg-3 col-md-4 col-6">
                <div class="product-card p-3 d-flex flex-column h-100 shadow-sm">
                    <div class="cursor-pointer mb-2" onclick="openDetail('${p.id}')">
                        <div class="bg-light rounded-4 mb-2 p-1 text-center">
                            <img src="${img}" class="img-fluid rounded-3" style="height:140px; object-fit:contain;">
                        </div>
                        <span class="category-badge mb-1 d-inline-block">${p.category?.name || 'Umum'}</span>
                        <h6 class="fw-bold text-dark mb-1 text-truncate">${p.name}</h6>
                        <p class="text-muted small mb-0">Stok: ${p.stock} ${p.unit}</p>
                    </div>
                    <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between">
                        <span class="price-text small">Rp${Number(p.price).toLocaleString()}</span>
                        <div class="d-flex gap-1">
                            ${isLoggedIn && isCustomer ? `
                                <button onclick="addToCart('${p.id}', '${p.name}')" class="btn-cart-sm" title="Tambah Keranjang"><i class="bi bi-cart-plus"></i></button>
                                <button onclick="openDetail('${p.id}')" class="btn-order-sm">Pesan</button>
                            ` : `
                                <a href="/login" class="btn btn-link p-0 text-primary small text-decoration-none fw-bold">Masuk</a>
                            `}
                        </div>
                    </div>
                </div>
            </div>`;
        });
        grid.innerHTML = html;
    }

    // ✅ MODIFIKASI: pakai setTimeout(0) agar skeleton sempat dirender browser sebelum JS memproses filter
    function filterProducts() {
        showSkeletons();
        setTimeout(() => {
            const keyword    = document.getElementById('searchInput').value.toLowerCase();
            const categoryId = document.getElementById('categoryFilter').value;
            const filtered   = allProducts.filter(p => {
                const matchName = p.name.toLowerCase().includes(keyword) || (p.sku && p.sku.toLowerCase().includes(keyword));
                const matchCat  = categoryId === '' || String(p.product_category_id) === String(categoryId);
                return matchName && matchCat;
            });
            renderProducts(filtered);
        }, 0);
    }

    /* =========================
       MODAL DETAIL
    ========================= */
    function openDetail(id) {
        const p = allProducts.find(item => String(item.id) === String(id));
        if (!p) return;
        maxStockCurrent = p.stock;

        document.getElementById('modalDetailImg').src              = p.image ? `/${p.image}` : 'https://placehold.co/400x300';
        document.getElementById('modalDetailName').innerText       = p.name;
        document.getElementById('modalDetailCategory').innerText   = p.category?.name || 'Umum';
        document.getElementById('modalDetailStock').innerText      = `${p.stock} ${p.unit}`;
        document.getElementById('modalDetailUnit').innerText       = p.unit;
        document.getElementById('modalDetailPrice').innerText      = `Rp ${Number(p.price).toLocaleString('id-ID')}`;

        document.getElementById('quick_qty').value                 = 1;
        document.getElementById('quick_notes').value               = '';
        document.getElementById('quick_regency').value             = '';
        document.getElementById('quick_district').innerHTML        = '<option value="">Pilih Kecamatan</option>';
        document.getElementById('quick_village').innerHTML         = '<option value="">Pilih Kelurahan</option>';
        document.getElementById('quick_district').disabled         = true;
        document.getElementById('quick_village').disabled          = true;
        document.getElementById('q_addr_profile').checked          = true;
        document.getElementById('quick_shipping_address').value    = '';
        document.getElementById('quick_shipping_address').classList.add('d-none');

        const actionBtn = document.getElementById('modalActionButtons');
        const formDiv   = document.getElementById('quickOrderForm');

        if (isLoggedIn && isCustomer) {
            formDiv.classList.remove('d-none');
            actionBtn.innerHTML = `
                <button onclick="addToCart('${p.id}', '${p.name}')" class="btn btn-outline-info rounded-pill px-4 fw-bold">Keranjang</button>
                <button onclick="quickOrder('${p.id}', '${p.name}')" class="btn btn-medinest flex-grow-1 rounded-pill shadow">Kirim Pesanan</button>`;
        } else {
            formDiv.classList.add('d-none');
            actionBtn.innerHTML = `<a href="/login" class="btn btn-medinest w-100 rounded-pill text-center text-white text-decoration-none py-2">Masuk untuk Memesan</a>`;
        }

        detailModalInstance.show();
    }

    /* =========================
       CART
    ========================= */
    function addToCart(id, name) {
        axios.post('/api/cart', { product_id: id }).then(() => {
            Swal.fire({ toast: true, position: 'bottom-end', icon: 'success', title: name + ' masuk keranjang', showConfirmButton: false, timer: 2000 });
            if (window.updateCartBadge) window.updateCartBadge();
        });
    }

    /* =========================
       QUICK ORDER + MIDTRANS
    ========================= */
    function quickOrder(id, name) {
        const regSel  = document.getElementById('quick_regency');
        const distSel = document.getElementById('quick_district');
        const villSel = document.getElementById('quick_village');

        const payload = {
            product_id:          id,
            quantity:            document.getElementById('quick_qty').value,
            notes:               document.getElementById('quick_notes').value,
            request_type:        document.getElementById('quick_request_type').value,
            regency:             regSel.options[regSel.selectedIndex]?.getAttribute('data-name'),
            district:            distSel.options[distSel.selectedIndex]?.getAttribute('data-name'),
            village:             villSel.options[villSel.selectedIndex]?.getAttribute('data-name'),
            use_profile_address: document.getElementById('q_addr_profile').checked ? 1 : 0,
            shipping_address:    document.getElementById('quick_shipping_address').value,
            phone_order: document.getElementById('quick_phone').value,
        };

        if (!payload.regency || !payload.district || !payload.village) {
            return Swal.fire('Peringatan', 'Lengkapi data wilayah (Kabupaten, Kecamatan, Kelurahan).', 'warning');
        }

        Swal.fire({
            title: 'Kirim Pesanan Kilat?',
            text: 'Permintaan stok akan segera diproses.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00838f',
            confirmButtonText: 'Ya, Kirim'
        }).then(result => {
            if (!result.isConfirmed) return;

            detailModalInstance.hide();
            document.getElementById('paymentOverlay').classList.add('show');

            axios.post('/api/orders/quick', payload)
                .then(res => {
                    const snapToken = res.data.snap_token;
                    document.getElementById('paymentOverlay').classList.remove('show');

                    if (!snapToken) {
                        Swal.fire('Perhatian', 'Pesanan dibuat tapi gagal membuat token pembayaran. Silakan bayar dari halaman riwayat.', 'warning')
                            .then(() => window.location.href = '/customer/history');
                        return;
                    }

                    snap.pay(snapToken, {
                        onSuccess: function() {
                            Swal.fire({ icon: 'success', title: 'Pembayaran Berhasil!', text: 'Pesanan sedang menunggu konfirmasi admin.', confirmButtonColor: '#00838f' })
                                .then(() => window.location.href = '/customer/history');
                        },
                        onPending: function() {
                            Swal.fire({ icon: 'info', title: 'Pembayaran Pending', text: 'Selesaikan pembayaran sesegera mungkin.', confirmButtonColor: '#00838f' })
                                .then(() => window.location.href = '/customer/history');
                        },
                        onError: function() {
                            Swal.fire('Pembayaran Gagal', 'Silakan coba lagi dari halaman riwayat.', 'error')
                                .then(() => window.location.href = '/customer/history');
                        },
                        onClose: function() {
                            Swal.fire({ icon: 'warning', title: 'Pembayaran Dibatalkan', text: 'Pesanan tersimpan. Bayar kapan saja dari Riwayat Pesanan.', confirmButtonColor: '#00838f' })
                                .then(() => window.location.href = '/customer/history');
                        }
                    });
                })
                .catch(err => {
                    document.getElementById('paymentOverlay').classList.remove('show');
                    Swal.fire('Gagal', err.response?.data?.message || 'Error', 'error');
                });
        });
    }
</script>

<style>
    #paymentOverlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.55);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 16px;
    }
    #paymentOverlay.show { display: flex; }
    #paymentOverlay .overlay-card {
        background: #fff;
        border-radius: 20px;
        padding: 36px 48px;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    #paymentOverlay .overlay-card h5 { color: var(--secondary); font-weight: 700; margin-top: 16px; margin-bottom: 6px; }
    #paymentOverlay .overlay-card p  { color: #888; font-size: 14px; margin: 0; }
</style>
@push('scripts')
  @role('customer')
    <script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
  @endrole
@endpush
@endsection
