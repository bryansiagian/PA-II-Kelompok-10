@extends('layouts.portal')

@section('content')
<style>
    :root { --primary: #00838f; --secondary: #2c4964; }
    .search-section { background: #fff; border-bottom: 1px solid #eee; padding: 20px 0; }
    .product-card { transition: 0.3s; border-radius: 15px; overflow: hidden; height: 100%; border: 1px solid #eee; background: #fff; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
    .category-badge { font-size: 10px; background: #e8f9fa; color: var(--primary); padding: 4px 10px; border-radius: 50px; font-weight: 700; }

    /* Tombol Keranjang (Ikon) */
    .btn-cart-sm { width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid var(--primary); color: var(--primary); background: transparent; transition: 0.3s; }
    .btn-cart-sm:hover { background: var(--primary); color: #fff; }

    /* Tombol Pesan Langsung */
    .btn-order-sm { background: var(--primary); color: white !important; border-radius: 20px; padding: 5px 15px; font-size: 13px; font-weight: 600; border: none; transition: 0.3s; }
    .btn-order-sm:hover { background: #006064; box-shadow: 0 4px 10px rgba(0, 131, 143, 0.3); }

    .price-text { color: var(--secondary); font-weight: 800; font-size: 1rem; }

    /* Detail Modal Styling */
    #modalDetailImg { width: 100%; height: 300px; object-fit: contain; background: #f8f9fa; border-radius: 15px; }
    .detail-label { font-size: 12px; font-weight: 700; color: #999; text-transform: uppercase; margin-bottom: 2px; }
    .detail-value { font-weight: 600; color: var(--secondary); margin-bottom: 15px; }
    .btn-medinest { background: var(--primary); color: white !important; border-radius: 30px; padding: 12px; font-weight: 600; border: none; transition: 0.3s; }
    .btn-medinest:hover { background: #006064; }
</style>

<!-- Header & Search -->
<div class="search-section shadow-sm">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4">
                <h4 class="fw-bold m-0"><i class="bi bi-grid-fill me-2 text-info"></i>Katalog Produk</h4>
            </div>
            <div class="col-md-8">
                <div class="row g-2">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchInput" class="form-control bg-light border-0" placeholder="Cari nama produk atau SKU..." onkeyup="filterProducts()">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <select id="categoryFilter" class="form-select bg-light border-0" onchange="filterProducts()">
                            <option value="">Semua Kategori</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4 mb-5">
    <div id="productGrid" class="row gx-3 gy-4">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-info" role="status"></div>
            <p class="text-muted mt-2">Memuat data produk...</p>
        </div>
    </div>
</div>

<!-- MODAL DETAIL PRODUK -->
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 p-md-5 pt-0">
                <div class="row align-items-center">
                    <div class="col-md-5 mb-4 mb-md-0">
                        <img id="modalDetailImg" src="" alt="Produk">
                    </div>
                    <div class="col-md-7 ps-md-4">
                        <span id="modalDetailCategory" class="category-badge mb-2 d-inline-block">Kategori</span>
                        <h3 id="modalDetailName" class="fw-bold text-dark mb-1">Nama Produk</h3>
                        <p id="modalDetailSku" class="text-muted small mb-4">SKU: -------</p>

                        <div class="row">
                            <div class="col-6">
                                <div class="detail-label">Stok Tersedia</div>
                                <div id="modalDetailStock" class="detail-value">0 Unit</div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Satuan</div>
                                <div id="modalDetailUnit" class="detail-value">-</div>
                            </div>
                            <div class="col-12">
                                <div class="detail-label">Harga</div>
                                <div id="modalDetailPrice" class="fs-4 fw-bold text-primary mb-4">Rp 0</div>
                            </div>
                        </div>

                        <div class="detail-label">Deskripsi Produk</div>
                        <p id="modalDetailDesc" class="text-muted small mb-4">Tidak ada deskripsi tambahan.</p>

                        <div id="modalActionButtons" class="d-flex gap-2">
                            <!-- Diisi via JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

    // Cek status login dari Blade untuk digunakan di JavaScript
    const isLoggedIn = @json(auth()->check());
    const isCustomer = @auth @json(auth()->user()->hasRole('customer')) @else false @endauth;

    let allProducts = [];

    document.addEventListener('DOMContentLoaded', () => {
        fetchCategories();
        fetchProducts();
    });

    function fetchCategories() {
        axios.get('/api/public/product-categories').then(res => {
            let opt = '<option value="">Semua Kategori</option>';
            res.data.forEach(c => opt += `<option value="${c.name}">${c.name}</option>`);
            document.getElementById('categoryFilter').innerHTML = opt;
        });
    }

    function fetchProducts() {
        axios.get('/api/public/products').then(res => {
            allProducts = res.data;
            renderProducts(allProducts);
        });
    }

    function renderProducts(data) {
        const grid = document.getElementById('productGrid');
        let html = '';

        if (data.length === 0) {
            grid.innerHTML = '<div class="col-12 text-center py-5 text-muted">Produk tidak ditemukan.</div>';
            return;
        }

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
                                <button onclick="addToCart('${p.id}', '${p.name}')" class="btn-cart-sm" title="Tambah Keranjang">
                                    <i class="bi bi-cart-plus"></i>
                                </button>
                                <button onclick="quickOrder('${p.id}', '${p.name}')" class="btn-order-sm" title="Pesan Langsung">
                                    Pesan
                                </button>
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

    function filterProducts() {
        const keyword = document.getElementById('searchInput').value.toLowerCase();
        const category = document.getElementById('categoryFilter').value;
        const filtered = allProducts.filter(p => {
            const matchName = p.name.toLowerCase().includes(keyword) || (p.sku && p.sku.toLowerCase().includes(keyword));
            const matchCat = category === "" || (p.category && p.category.name === category);
            return matchName && matchCat;
        });
        renderProducts(filtered);
    }

    function openDetail(id) {
        const p = allProducts.find(item => item.id === id);
        if(!p) return;

        document.getElementById('modalDetailImg').src = p.image ? `/${p.image}` : 'https://placehold.co/400x300';
        document.getElementById('modalDetailName').innerText = p.name;
        document.getElementById('modalDetailSku').innerText = `SKU: ${p.sku || '-'}`;
        document.getElementById('modalDetailCategory').innerText = p.category?.name || 'Umum';
        document.getElementById('modalDetailStock').innerText = `${p.stock} ${p.unit}`;
        document.getElementById('modalDetailUnit').innerText = p.unit;
        document.getElementById('modalDetailPrice').innerText = `Rp${Number(p.price).toLocaleString()}`;
        document.getElementById('modalDetailDesc').innerText = p.description || 'Tidak ada deskripsi tambahan untuk produk ini.';

        const btnContainer = document.getElementById('modalActionButtons');
        if(isLoggedIn && isCustomer) {
            btnContainer.innerHTML = `
                <button onclick="addToCart('${p.id}', '${p.name}')" class="btn btn-outline-info rounded-pill px-4 fw-bold">Tambah Keranjang</button>
                <button onclick="quickOrder('${p.id}', '${p.name}')" class="btn btn-medinest flex-grow-1 rounded-pill shadow">Pesan Sekarang</button>
            `;
        } else {
            btnContainer.innerHTML = `<a href="/login" class="btn btn-medinest w-100 rounded-pill text-center">Masuk untuk Memesan</a>`;
        }

        new bootstrap.Modal(document.getElementById('productDetailModal')).show();
    }

    function addToCart(id, name) {
        axios.post('/api/cart', { product_id: id }).then(() => {
            Swal.fire({ toast:true, position:'bottom-end', icon:'success', title: name + ' masuk keranjang', showConfirmButton:false, timer:2000 });
            if(window.updateCartBadge) window.updateCartBadge();
        }).catch(() => Swal.fire('Gagal', 'Silakan login sebagai customer.', 'error'));
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
                }).catch(err => Swal.fire('Gagal', err.response?.data?.message || 'Sistem error', 'error'));
            }
        });
    }
</script>
@endsection
