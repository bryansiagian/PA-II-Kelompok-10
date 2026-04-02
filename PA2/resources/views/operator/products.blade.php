@extends('layouts.backoffice')

@section('page_title', 'Inventaris & Lokasi Gudang')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0 text-dark">Manajemen Inventaris Produk</h4>
            <div class="text-muted small">Pantau stok, edit katalog, dan tentukan lokasi penyimpanan di gudang.</div>
        </div>
        <div class="ms-3 d-flex gap-2">
            <button class="btn btn-indigo shadow-sm rounded-pill px-4" onclick="openAddModal()">
                <i class="ph-plus-circle me-2"></i> Tambah Produk
            </button>
            <button class="btn btn-teal shadow-sm text-white rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalStockIn">
                <i class="ph-arrows-down-up me-2"></i> Stock In
            </button>
        </div>
    </div>

    <!-- Statistik Row -->
    <div class="row mb-3">
        <div class="col-lg-4">
            <div class="card card-body bg-indigo text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="statTotalItems">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Total Produk</div>
                    </div>
                    <i class="ph-package ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-body bg-warning text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="statLowStock">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Stok Rendah</div>
                    </div>
                    <i class="ph-warning-circle ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-body bg-pink text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="statOutOfStock">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Stok Kosong</div>
                    </div>
                    <i class="ph-x-circle ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">
            <h5 class="mb-0 fw-bold"><i class="ph-list me-2 text-primary"></i>Katalog Inventaris</h5>
            <div class="ms-auto">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <span class="input-group-text bg-light border-0"><i class="ph-magnifying-glass"></i></span>
                    <input type="text" id="tableSearch" class="form-control bg-light border-0" placeholder="Cari produk atau SKU...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3">Produk & SKU</th>
                        <th>Kategori</th>
                        <th>Gudang</th>
                        <th class="text-center">Harga (Rp)</th>
                        <th class="text-center">Stok</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    <tr><td colspan="7" class="text-center py-5 text-muted">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: TAMBAH/EDIT PRODUK -->
<div class="modal fade" id="modalProduct" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold" id="modalTitle">Tambah Produk</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formProduct" onsubmit="submitProduct(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="product_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Nama Produk</label>
                            <input type="text" name="name" id="form_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">SKU / Kode</label>
                            <input type="text" name="sku" id="form_sku" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Kode Produk Internal</label>
                            <input type="text" name="product_code" id="form_product_code" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Kategori</label>
                            <select name="product_category_id" id="form_category_id" class="form-select" required></select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Satuan (Unit)</label>
                            <input type="text" name="unit" id="form_unit" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-primary">Pilih Gudang</label>
                            <select name="warehouse_id" id="form_warehouse_id" class="form-select" required></select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small text-muted">Harga Satuan (Rp)</label>
                            <input type="number" name="price" id="form_price" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3" id="initialStockSection">
                            <label class="form-label fw-bold small text-muted">Stok Awal</label>
                            <input type="number" name="stock" id="form_stock" class="form-control" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small text-muted">Min. Stok</label>
                            <input type="number" name="min_stock" id="form_min_stock" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold small text-muted">Deskripsi</label>
                            <textarea name="description" id="form_description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold small text-muted">Foto Produk</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-info d-none" id="editImageNote">Kosongkan jika tidak ingin mengubah foto.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" id="btnSave" class="btn btn-indigo px-4 fw-bold shadow-sm">SIMPAN DATA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: STOCK IN -->
<div class="modal fade" id="modalStockIn" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-teal text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-arrows-down-up me-2"></i>Tambah Stok Manual</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="stockInForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Pilih Produk</label>
                        <select id="selectProductStock" class="form-select" required></select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted">Jumlah Penambahan</label>
                        <input type="number" id="inputQty" class="form-control py-2" min="1" placeholder="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                <button type="button" onclick="submitStockIn()" class="btn btn-teal text-white px-4 fw-bold rounded-pill">SIMPAN STOK</button>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    const modalProduct = new bootstrap.Modal(document.getElementById('modalProduct'));

    document.addEventListener('DOMContentLoaded', initPage);

    function initPage() {
        axios.get('/api/product-categories').then(res => {
            let opt = '<option value="" selected disabled>-- Pilih Kategori --</option>';
            res.data.forEach(c => opt += `<option value="${c.id}">${c.name}</option>`);
            document.getElementById('form_category_id').innerHTML = opt;
        });

        axios.get('/api/inventory/warehouses').then(res => {
            let opt = '<option value="" selected disabled>-- Pilih Gudang --</option>';
            res.data.forEach(w => opt += `<option value="${w.id}">${w.name} (${w.code})</option>`);
            document.getElementById('form_warehouse_id').innerHTML = opt;
        });

        fetchProducts();
    }

    function fetchProducts() {
        const tableBody = document.getElementById('productTableBody');
        const stockSelect = document.getElementById('selectProductStock');

        axios.get('/api/products').then(res => {
            const products = res.data;
            let html = '';
            let options = '<option value="" selected disabled>-- Pilih Produk --</option>';
            let low = 0, out = 0;

            if (products.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-5">Katalog kosong.</td></tr>';
                return;
            }

            products.forEach(p => {
                if (p.stock <= 0) out++; else if (p.stock <= p.min_stock) low++;
                let badge = p.stock <= 0 ? 'bg-danger' : (p.stock <= p.min_stock ? 'bg-warning text-dark' : 'bg-success');
                let imgUrl = p.image ? `/${p.image}` : 'https://placehold.co/200x200?text=No+Image';

                html += `
                <tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center">
                            <img src="${imgUrl}" class="rounded shadow-sm me-3 border" style="width: 45px; height: 45px; object-fit: cover;">
                            <div>
                                <div class="fw-bold text-dark text-truncate" style="max-width: 180px;">${p.name}</div>
                                <div class="fs-xs text-muted font-monospace">SKU: ${p.sku} | CODE: ${p.product_code || '-'}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-light text-primary border-0">${p.category?.name || '-'}</span></td>
                    <td><div class="small fw-bold"><i class="ph-warehouse me-1 text-muted"></i>${p.warehouse?.name || 'N/A'}</div></td>
                    <td class="text-center fw-semibold">Rp ${Number(p.price).toLocaleString('id-ID')}</td>
                    <td class="text-center fw-bold text-indigo fs-base">${p.stock}</td>
                    <td class="text-center"><span class="badge ${badge} px-2 py-1 rounded-pill">${p.stock <= 0 ? 'HABIS' : (p.stock <= p.min_stock ? 'RENDAH' : 'AMAN')}</span></td>
                    <td class="text-center pe-3">
                        <div class="d-inline-flex">
                            <button onclick="openEditModal('${p.id}')" class="btn btn-sm btn-light text-primary border-0 me-2 shadow-sm"><i class="ph-note-pencil"></i></button>
                            <button onclick="confirmDelete('${p.id}', '${p.name}')" class="btn btn-sm btn-light text-danger border-0 shadow-sm"><i class="ph-trash"></i></button>
                        </div>
                    </td>
                </tr>`;
                options += `<option value="${p.id}">${p.name} (Stok: ${p.stock})</option>`;
            });

            tableBody.innerHTML = html;
            if(stockSelect) stockSelect.innerHTML = options;
            document.getElementById('statTotalItems').innerText = products.length;
            document.getElementById('statLowStock').innerText = low;
            document.getElementById('statOutOfStock').innerText = out;
        });
    }

    function openAddModal() {
        document.getElementById('formProduct').reset();
        document.getElementById('product_id').value = '';
        document.getElementById('modalTitle').innerText = 'Daftarkan Produk Baru';
        document.getElementById('initialStockSection').classList.remove('d-none');
        document.getElementById('editImageNote').classList.add('d-none');
        modalProduct.show();
    }

    function openEditModal(id) {
        axios.get(`/api/products/${id}`).then(res => {
            const p = res.data;
            document.getElementById('product_id').value = p.id;
            document.getElementById('form_name').value = p.name;
            document.getElementById('form_sku').value = p.sku;
            document.getElementById('form_product_code').value = p.product_code || '';
            document.getElementById('form_category_id').value = p.product_category_id;
            document.getElementById('form_warehouse_id').value = p.warehouse_id;
            document.getElementById('form_unit').value = p.unit;
            document.getElementById('form_price').value = p.price;
            document.getElementById('form_min_stock').value = p.min_stock;
            document.getElementById('form_description').value = p.description || '';

            document.getElementById('modalTitle').innerText = 'Edit Informasi Produk';
            document.getElementById('initialStockSection').classList.add('d-none'); // Hidden saat edit
            document.getElementById('editImageNote').classList.remove('d-none');

            modalProduct.show();
        });
    }

    function submitProduct(event) {
        event.preventDefault();
        const id = document.getElementById('product_id').value;
        const btn = document.getElementById('btnSave');
        const formData = new FormData(document.getElementById('formProduct'));

        btn.disabled = true;
        btn.innerHTML = '<i class="ph-spinner spinner me-2"></i> Memproses...';

        let url = '/api/products';
        if (id) {
            url = `/api/products/${id}`;
            formData.append('_method', 'PUT');
            // TIPS: Anda bisa menghapus field 'stock' dari FormData di sini jika ingin lebih aman di sisi client
            formData.delete('stock');
        }

        axios.post(url, formData).then(() => {
            modalProduct.hide();
            Swal.fire({ icon: 'success', title: 'Berhasil', timer: 1500, showConfirmButton: false });
            fetchProducts();
        }).catch(err => {
            Swal.fire('Gagal', err.response?.data?.message || 'Terjadi kesalahan', 'error');
        }).finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'SIMPAN DATA';
        });
    }

    function submitStockIn() {
        const productId = document.getElementById('selectProductStock').value;
        const qty = document.getElementById('inputQty').value;
        axios.post('/api/products/stock-in', { product_id: productId, quantity: qty }).then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalStockIn')).hide();
            Swal.fire({ icon: 'success', title: 'Stok Diperbarui', timer: 1500, showConfirmButton: false });
            fetchProducts();
        });
    }

    function confirmDelete(id, name) {
        Swal.fire({ title: 'Hapus Produk?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444' })
        .then(result => { if (result.isConfirmed) axios.delete(`/api/products/${id}`).then(() => fetchProducts()); });
    }

    document.getElementById('tableSearch').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        let rows = document.querySelectorAll('#productTableBody tr');
        rows.forEach(row => { row.style.display = row.innerText.toLowerCase().includes(val) ? "" : "none"; });
    });
</script>

<style>
    .bg-indigo { background-color: #5c6bc0 !important; }
    .bg-teal { background-color: #26a69a !important; }
    .btn-indigo { background-color: #5c6bc0; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #3f51b5; color: #fff; }
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
</style>
@endsection
