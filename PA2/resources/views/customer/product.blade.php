@extends('layouts.portal')

@section('content')
<style>
    .search-section { background: #fff; border-bottom: 1px solid #eee; padding: 20px 0; }
    .product-card { transition: 0.3s; border-radius: 15px; overflow: hidden; height: 100%; border: 1px solid #eee; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
    .category-badge { font-size: 10px; background: #e8f9fa; color: #3fbbc0; padding: 4px 10px; border-radius: 50px; font-weight: 700; }
    .btn-cart { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #3fbbc0; color: #3fbbc0; background: transparent; transition: 0.3s; }
    .btn-cart:hover { background: #3fbbc0; color: #fff; }
    .price-text { color: #2c4964; font-weight: 800; font-size: 1.1rem; }

    /* Detail Modal Styling */
    #modalDetailImg { width: 100%; height: 300px; object-fit: contain; background: #f8f9fa; border-radius: 15px; }
    .detail-label { font-size: 12px; font-weight: 700; color: #999; text-transform: uppercase; margin-bottom: 2px; }
    .detail-value { font-weight: 600; color: #2c4964; margin-bottom: 15px; }
</style>

<!-- Header -->
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
                            <!-- Diisi via JS -->
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4 mb-5">
    <div id="productGrid" class="row gx-3 gy-4">
        <!-- JS Render -->
    </div>
</div>

<!-- MODAL DETAIL PRODUK -->
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 p-md-5">
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
                                <div id="modalDetailPrice" class="price-text mb-4">Rp 0</div>
                            </div>
                        </div>

                        <div class="detail-label">Deskripsi Produk</div>
                        <p id="modalDetailDesc" class="text-muted small mb-4">Tidak ada deskripsi.</p>

                        <div class="d-flex gap-2">
                            <button id="modalAddToCartBtn" class="btn btn-outline-info rounded-pill px-4 fw-bold">
                                <i class="bi bi-cart-plus me-2"></i>Tambah Keranjang
                            </button>
                            <button id="modalQuickOrderBtn" class="btn btn-medinest flex-grow-1 rounded-pill shadow">
                                Pesan Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    let allProducts = [];

    document.addEventListener('DOMContentLoaded', () => {
        fetchCategories();
        fetchProducts();
    });

    function fetchCategories() {
        axios.get('/api/product-categories').then(res => {
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
        let html = '';
        data.forEach(p => {
            const img = p.image ? `/${p.image}` : 'https://placehold.co/400x300';
            html += `
            <div class="col-lg-3 col-md-4 col-6 product-item" data-name="${p.name.toLowerCase()}" data-category="${p.category?.name || ''}">
                <div class="product-card bg-white p-3 d-flex flex-column justify-content-between shadow-sm">
                    <div class="cursor-pointer" onclick="openDetail('${p.id}')">
                        <div class="bg-light rounded-4 mb-2 p-1 text-center">
                            <img src="${img}" class="img-fluid rounded-3" style="height:140px; object-fit:contain;">
                        </div>
                        <span class="category-badge mb-1 d-inline-block">${p.category?.name || 'Umum'}</span>
                        <h6 class="fw-bold text-dark mb-1 text-truncate">${p.name}</h6>
                        <p class="text-muted small mb-3">Stok: ${p.stock} ${p.unit}</p>
                    </div>
                    <div class="d-flex align-items-center justify-content-between border-top pt-3">
                        <span class="price-text small">Rp${Number(p.price).toLocaleString()}</span>
                        <button onclick="addToCart('${p.id}', '${p.name}')" class="btn-cart" title="Tambah ke Keranjang">
                            <i class="bi bi-cart-plus"></i>
                        </button>
                    </div>
                </div>
            </div>`;
        });
        document.getElementById('productGrid').innerHTML = html || '<div class="text-center py-5">Produk tidak ditemukan.</div>';
    }

    function filterProducts() {
        const keyword = document.getElementById('searchInput').value.toLowerCase();
        const category = document.getElementById('categoryFilter').value;

        const filtered = allProducts.filter(p => {
            const matchName = p.name.toLowerCase().includes(keyword) || p.sku.toLowerCase().includes(keyword);
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
        document.getElementById('modalDetailSku').innerText = `SKU: ${p.sku}`;
        document.getElementById('modalDetailCategory').innerText = p.category?.name || 'Umum';
        document.getElementById('modalDetailStock').innerText = `${p.stock} ${p.unit}`;
        document.getElementById('modalDetailUnit').innerText = p.unit;
        document.getElementById('modalDetailPrice').innerText = `Rp${Number(p.price).toLocaleString()}`;
        document.getElementById('modalDetailDesc').innerText = p.description || 'Tidak ada deskripsi tambahan untuk produk ini.';

        // Update Action Buttons di Modal
        document.getElementById('modalAddToCartBtn').onclick = () => addToCart(p.id, p.name);
        document.getElementById('modalQuickOrderBtn').onclick = () => quickOrder(p.id, p.name);

        new bootstrap.Modal(document.getElementById('productDetailModal')).show();
    }

    function addToCart(id, name) {
        axios.post('/api/cart', { product_id: id }).then(() => {
            Swal.fire({ toast:true, position:'bottom-end', icon:'success', title: name + ' masuk keranjang', showConfirmButton:false, timer:2000 });
            if(window.updateCartBadge) window.updateCartBadge();
        });
    }

    function quickOrder(id, name) {
        Swal.fire({
            title: 'Pesan Sekarang?',
            text: `Kirim permintaan 1 unit ${name}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3fbbc0',
            confirmButtonText: 'Ya, Kirim'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post('/api/orders/quick', { product_id: id })
                    .then(() => {
                        Swal.fire('Berhasil!', 'Pesanan diproses.', 'success')
                            .then(() => window.location.href = '/customer/history');
                    });
            }
        });
    }
</script>
@endsection
