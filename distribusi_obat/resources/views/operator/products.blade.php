@extends('layouts.backoffice')

@section('page_title', 'Inventaris & Lokasi Gudang')

@section('content')

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex align-items-center mb-3">

        <div class="flex-fill">

            <h4 class="fw-bold mb-0 text-dark">
                Manajemen Inventaris Produk
            </h4>

            <div class="text-muted small">
                Pantau stok, edit katalog, dan tentukan lokasi penyimpanan di gudang.
            </div>

        </div>

        <div class="ms-3 d-flex gap-2">

            <button
                class="btn btn-indigo shadow-sm rounded-pill px-4"
                onclick="openAddModal()">

                <i class="ph-plus-circle me-2"></i>
                Tambah Produk

            </button>

            <button
                class="btn btn-teal shadow-sm text-white rounded-pill px-4"
                data-bs-toggle="modal"
                data-bs-target="#modalStockIn">

                <i class="ph-arrows-down-up me-2"></i>
                Stock In

            </button>

        </div>

    </div>

    <!-- Statistik -->
    <div class="row mb-3">

        <div class="col-lg-4">

            <div class="card card-body bg-indigo text-white shadow-sm border-0 mb-3">

                <div class="d-flex align-items-center">

                    <div class="flex-fill">

                        <h4 class="mb-0 fw-bold" id="statTotalItems">0</h4>

                        <div class="text-uppercase fs-xs opacity-75">
                            Total Produk
                        </div>

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

                        <div class="text-uppercase fs-xs opacity-75">
                            Stok Rendah
                        </div>

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

                        <div class="text-uppercase fs-xs opacity-75">
                            Stok Kosong
                        </div>

                    </div>

                    <i class="ph-x-circle ph-2x opacity-75 ms-3"></i>

                </div>

            </div>

        </div>

    </div>

    <!-- Table -->
    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">

            <h5 class="mb-0 fw-bold">

                <i class="ph-list me-2 text-primary"></i>
                Katalog Inventaris

            </h5>

            <div class="ms-auto">

                <div class="input-group input-group-sm" style="width:250px;">

                    <span class="input-group-text bg-light border-0">
                        <i class="ph-magnifying-glass"></i>
                    </span>

                    <input
                        type="text"
                        id="tableSearch"
                        class="form-control bg-light border-0"
                        placeholder="Cari produk atau SKU...">

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
                        <th>Lokasi Rak</th>
                        <th class="text-center">Harga (Rp)</th>
                        <th class="text-center">Stok</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Aksi</th>

                    </tr>

                </thead>

                <tbody id="productTableBody">

                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            Memuat data...
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- MODAL PRODUCT -->
<div class="modal fade" id="modalProduct" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg rounded-3">

            <div class="modal-header bg-indigo text-white border-0 py-3">

                <h6 class="modal-title fw-bold" id="modalTitle">
                    Tambah Produk
                </h6>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <form id="formProduct" onsubmit="submitProduct(event)">

                <div class="modal-body p-4">

                    <input type="hidden" id="product_id" name="id">

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
                            <label class="form-label fw-bold small text-primary">Gudang Penyimpanan</label>
                            <select
                                name="warehouse_id"
                                id="form_warehouse_id"
                                class="form-select border-primary border-opacity-25"
                                onchange="loadRackOptions(this.value)"
                                required>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-primary">Lokasi Rak</label>
                            <select
                                name="rack_id"
                                id="form_rack_id"
                                class="form-select border-primary border-opacity-25"
                                required
                                disabled>
                                <option value="" selected disabled>-- Pilih Gudang Terlebih Dahulu --</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
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
                            <label class="form-label fw-bold small text-muted">Deskripsi Singkat</label>
                            <textarea name="description" id="form_description" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold small text-muted">Foto Produk</label>
                            <div class="mb-3">
                                <img
                                    id="previewImage"
                                    src="https://placehold.co/300x200?text=No+Image"
                                    class="img-thumbnail shadow-sm"
                                    style="width:160px;height:160px;object-fit:cover;border-radius:14px;">
                            </div>
                            <input
                                type="file"
                                id="form_image"
                                name="image"
                                class="form-control"
                                accept="image/*"
                                onchange="previewProductImage(event)">
                            <small class="text-muted">Pilih gambar produk baru jika ingin mengganti foto.</small>
                        </div>

                    </div>

                </div>

                <div class="modal-footer bg-light border-0">

                    <button
                        type="button"
                        class="btn btn-link text-muted fw-bold text-decoration-none"
                        data-bs-dismiss="modal">
                        BATAL
                    </button>

                    <button type="submit" id="btnSave" class="btn btn-indigo px-4 fw-bold shadow-sm">
                        SIMPAN DATA
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- STOCK IN -->
<div class="modal fade" id="modalStockIn" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg rounded-3">

            <div class="modal-header bg-teal text-white border-0 py-3">

                <h6 class="modal-title fw-bold">
                    <i class="ph-arrows-down-up me-2"></i>
                    Tambah Stok Manual
                </h6>

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

                <button
                    type="button"
                    class="btn btn-link text-muted fw-bold text-decoration-none"
                    data-bs-dismiss="modal">
                    BATAL
                </button>

                <button
                    type="button"
                    onclick="submitStockIn()"
                    class="btn btn-teal text-white px-4 fw-bold rounded-pill">
                    SIMPAN STOK
                </button>

            </div>

        </div>

    </div>

</div>

<script>

axios.defaults.headers.common['Authorization'] =
    'Bearer ' + '{{ session('api_token') }}';

const modalProduct = new bootstrap.Modal(document.getElementById('modalProduct'));

let allRacks = [];

// ─── Skeleton Helpers ─────────────────────────────────────────────────────────
const SKELETON_STAT = `<span class="skeleton-line" style="width:60px;height:28px;"></span>`;

function showSkeletons() {
    // Stat cards (latar warna → shimmer putih/transparan)
    document.getElementById('statTotalItems').innerHTML = SKELETON_STAT;
    document.getElementById('statLowStock').innerHTML   = SKELETON_STAT;
    document.getElementById('statOutOfStock').innerHTML = SKELETON_STAT;

    // Tabel produk — 8 baris skeleton
    let html = '';
    for (let i = 0; i < 8; i++) {
        html += `
        <tr>
            <td class="ps-3">
                <div class="d-flex align-items-center">
                    <span class="skeleton-circle me-3" style="width:45px;height:45px;flex-shrink:0;"></span>
                    <div>
                        <span class="skeleton-line" style="width:${100 + i * 12}px;height:13px;display:block;"></span>
                        <span class="skeleton-line mt-1" style="width:70px;height:10px;display:block;"></span>
                    </div>
                </div>
            </td>
            <td><span class="skeleton-line" style="width:70px;height:20px;border-radius:999px;"></span></td>
            <td><span class="skeleton-line" style="width:80px;height:13px;"></span></td>
            <td><span class="skeleton-line" style="width:90px;height:28px;border-radius:10px;"></span></td>
            <td class="text-center"><span class="skeleton-line" style="width:80px;height:13px;"></span></td>
            <td class="text-center"><span class="skeleton-line" style="width:30px;height:13px;"></span></td>
            <td class="text-center"><span class="skeleton-line" style="width:55px;height:22px;border-radius:999px;"></span></td>
            <td class="text-center pe-3"><span class="skeleton-line" style="width:60px;height:28px;border-radius:6px;"></span></td>
        </tr>`;
    }
    document.getElementById('productTableBody').innerHTML = html;
}

// ─── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', initPage);

function initPage() {
    showSkeletons(); // tampilkan skeleton sebelum semua fetch

    axios.get('/api/product-categories')
        .then(res => {
            let opt = '<option value="" selected disabled>-- Pilih Kategori --</option>';
            res.data
                .filter(c => c.active == 1)  // hanya tampilkan yang aktif
                .forEach(c => {
                    opt += `<option value="${c.id}">${c.name}</option>`;
                });
            document.getElementById('form_category_id').innerHTML = opt;
        });

    axios.get('/api/inventory/warehouses')
        .then(res => {
            let opt = '<option value="" selected disabled>-- Pilih Gudang --</option>';
            res.data.forEach(w => {
                opt += `<option value="${w.id}">${w.name} (${w.code})</option>`;
            });
            document.getElementById('form_warehouse_id').innerHTML = opt;
        });

    axios.get('/api/inventory/racks')
        .then(res => {
            allRacks = res.data;
        });

    fetchProducts();
}

// ─── Rack Options ─────────────────────────────────────────────────────────────
function loadRackOptions(warehouseId, selectedRackId = null) {
    const rackSelect = document.getElementById('form_rack_id');
    rackSelect.disabled = false;

    const filteredRacks = allRacks.filter(r => r.warehouse_id == warehouseId);

    if (filteredRacks.length === 0) {
        rackSelect.innerHTML = '<option value="" disabled selected>Belum ada rak di gudang ini</option>';
        return;
    }

    let html = '<option value="" selected disabled>-- Pilih Rak --</option>';
    filteredRacks.forEach(r => {
        html += `<option value="${r.id}" ${selectedRackId == r.id ? 'selected' : ''}>${r.name}</option>`;
    });
    rackSelect.innerHTML = html;
}

// ─── Fetch Products ───────────────────────────────────────────────────────────
function fetchProducts() {
    showSkeletons(); // tampilkan skeleton setiap kali fetch dijalankan ulang

    const tableBody   = document.getElementById('productTableBody');
    const stockSelect = document.getElementById('selectProductStock');

    axios.get('/api/products')
        .then(res => {
            const products = res.data;

            let html    = '';
            let options = '<option value="" selected disabled>-- Pilih Produk --</option>';
            let low     = 0;
            let out     = 0;

            if (products.length === 0) {
                tableBody.innerHTML =
                    '<tr><td colspan="8" class="text-center py-5 text-muted small">Katalog masih kosong.</td></tr>';
                document.getElementById('statTotalItems').innerText = 0;
                document.getElementById('statLowStock').innerText   = 0;
                document.getElementById('statOutOfStock').innerText = 0;
                return;
            }

            products.forEach(p => {
                if (p.stock <= 0) {
                    out++;
                } else if (p.stock <= p.min_stock) {
                    low++;
                }

                const badge =
                    p.stock <= 0
                    ? 'bg-danger'
                    : (p.stock <= p.min_stock ? 'bg-warning text-dark' : 'bg-success');

                const imgUrl = p.image
                    ? `/${p.image}`
                    : 'https://placehold.co/200x200?text=No+Image';

                const rackDisplay = p.rack
                    ? `<span class="rack-badge rack-filled">${p.rack.name}</span>`
                    : `<span class="rack-badge rack-empty">BELUM DISET</span>`;

                html += `
                <tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center">
                            <img
                                src="${imgUrl}"
                                class="rounded shadow-sm me-3 border"
                                style="width:45px;height:45px;object-fit:cover;">
                            <div>
                                <div class="fw-bold text-dark text-truncate" style="max-width:180px;">
                                    ${p.name}
                                </div>
                                <div class="fs-xs text-muted font-monospace">SKU: ${p.sku}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-light text-primary border-0">
                            ${p.category?.name || '-'}
                        </span>
                    </td>
                    <td>
                        <div class="small fw-bold text-dark">${p.warehouse?.name || 'N/A'}</div>
                    </td>
                    <td>${rackDisplay}</td>
                    <td class="text-center fw-semibold">Rp ${Number(p.price).toLocaleString('id-ID')}</td>
                    <td class="text-center fw-bold text-indigo fs-base">${p.stock}</td>
                    <td class="text-center">
                        <span class="badge ${badge} px-2 py-1 rounded-pill text-uppercase">
                            ${p.stock <= 0 ? 'Habis' : (p.stock <= p.min_stock ? 'Rendah' : 'Aman')}
                        </span>
                    </td>
                    <td class="text-center pe-3">
                        <div class="d-inline-flex">
                            <button
                                onclick="openEditModal('${p.id}')"
                                class="btn btn-sm btn-light text-primary border-0 me-2 shadow-sm">
                                <i class="ph-note-pencil"></i>
                            </button>
                            <button
                                onclick="confirmDelete('${p.id}', '${p.name}')"
                                class="btn btn-sm btn-light text-danger border-0 shadow-sm">
                                <i class="ph-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;

                options += `<option value="${p.id}">${p.name} (Stok: ${p.stock})</option>`;
            });

            tableBody.innerHTML  = html;
            stockSelect.innerHTML = options;

            document.getElementById('statTotalItems').innerText = products.length;
            document.getElementById('statLowStock').innerText   = low;
            document.getElementById('statOutOfStock').innerText = out;
        })
        .catch(() => {
            tableBody.innerHTML =
                '<tr><td colspan="8" class="text-center py-5 text-muted small">Gagal memuat data produk.</td></tr>';
        });
}

// ─── Modal: Tambah ────────────────────────────────────────────────────────────
function openAddModal() {
    document.getElementById('formProduct').reset();
    document.getElementById('product_id').value = '';
    document.getElementById('modalTitle').innerText = 'Daftarkan Produk Baru';
    document.getElementById('initialStockSection').classList.remove('d-none');
    document.getElementById('previewImage').src = 'https://placehold.co/300x200?text=No+Image';
    modalProduct.show();
}

// ─── Modal: Edit ──────────────────────────────────────────────────────────────
function openEditModal(id) {
    axios.get(`/api/products/${id}`)
        .then(res => {
            const p = res.data;

            document.getElementById('product_id').value         = p.id;
            document.getElementById('form_name').value          = p.name;
            document.getElementById('form_sku').value           = p.sku;
            document.getElementById('form_product_code').value  = p.product_code || '';
            document.getElementById('form_category_id').value   = p.product_category_id;
            document.getElementById('form_warehouse_id').value  = p.warehouse_id;
            document.getElementById('form_unit').value          = p.unit;
            document.getElementById('form_price').value         = p.price;
            document.getElementById('form_min_stock').value     = p.min_stock;
            document.getElementById('form_description').value   = p.description || '';

            loadRackOptions(p.warehouse_id, p.rack_id);

            document.getElementById('previewImage').src =
                p.image ? `/${p.image}` : 'https://placehold.co/300x200?text=No+Image';

            document.getElementById('modalTitle').innerText = 'Edit Informasi Produk';
            document.getElementById('initialStockSection').classList.add('d-none');

            modalProduct.show();
        })
        .catch(() => {
            Swal.fire('Error', 'Gagal memuat data produk.', 'error');
        });
}

// ─── Preview Gambar ───────────────────────────────────────────────────────────
function previewProductImage(event) {
    const file = event.target.files[0];
    if (file) {
        document.getElementById('previewImage').src = URL.createObjectURL(file);
    }
}

// ─── Submit Product ───────────────────────────────────────────────────────────
function submitProduct(event) {
    event.preventDefault();

    const id  = document.getElementById('product_id').value;
    const btn = document.getElementById('btnSave');
    const formData = new FormData(document.getElementById('formProduct'));

    btn.disabled  = true;
    btn.innerHTML = '<i class="ph-spinner spinner me-2"></i> Memproses...';

    let url = '/api/products';

    if (id) {
        url = `/api/products/${id}`;
        formData.append('_method', 'PUT');
        formData.delete('stock');
    }

    axios.post(url, formData)
        .then(() => {
            modalProduct.hide();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Inventaris diperbarui',
                timer: 1500,
                showConfirmButton: false
            });
            fetchProducts();
        })
        .catch(err => {
            Swal.fire('Gagal', err.response?.data?.message || 'Terjadi kesalahan sistem.', 'error');
        })
        .finally(() => {
            btn.disabled  = false;
            btn.innerHTML = 'SIMPAN DATA';
        });
}

// ─── Submit Stock In ──────────────────────────────────────────────────────────
function submitStockIn() {
    const productId = document.getElementById('selectProductStock').value;
    const qty       = document.getElementById('inputQty').value;

    if (!productId || !qty) {
        return Swal.fire('Peringatan', 'Lengkapi data.', 'warning');
    }

    axios.post('/api/products/stock-in', { product_id: productId, quantity: qty })
        .then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalStockIn')).hide();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Stok telah diperbarui.',
                timer: 1500,
                showConfirmButton: false
            });
            document.getElementById('stockInForm').reset();
            fetchProducts();
        });
}

// ─── Confirm Delete ───────────────────────────────────────────────────────────
function confirmDelete(id, name) {
    Swal.fire({
        title: 'Hapus Produk?',
        text: `Yakin menghapus ${name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Hapus'
    }).then(result => {
        if (result.isConfirmed) {
            axios.delete(`/api/products/${id}`)
                .then(() => {
                    Swal.fire('Terhapus', 'Produk dihapus.', 'success');
                    fetchProducts();
                });
        }
    });
}

// ─── Search Filter ────────────────────────────────────────────────────────────
document.getElementById('tableSearch').addEventListener('keyup', function () {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#productTableBody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
    });
});

</script>

<style>

.bg-indigo  { background-color: #5c6bc0 !important; }
.bg-teal    { background-color: #26a69a !important; }
.text-indigo { color: #5c6bc0 !important; }

.btn-indigo       { background-color: #5c6bc0; color: #fff; border: none; }
.btn-indigo:hover { background-color: #3f51b5; color: #fff; }

.spinner { animation: rotation 2s infinite linear; display: inline-block; }

@keyframes rotation {
    from { transform: rotate(0deg); }
    to   { transform: rotate(359deg); }
}

/* RACK BADGE */
.rack-badge {
    display: inline-block;
    min-width: 90px;
    text-align: center;
    padding: 7px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .3px;
}

.rack-filled { background: #5c6bc0; color: #fff; }
.rack-empty  { background: #fef3c7; color: #92400e; }

/* ── Skeleton loading ──────────────────────────────────────────────────────── */
@keyframes shimmer {
    0%   { background-position: -400px 0; }
    100% { background-position:  400px 0; }
}

.skeleton-line,
.skeleton-circle {
    display: inline-block;
    border-radius: 6px;
    /* Pada latar berwarna (stat cards) → shimmer putih transparan */
    background: linear-gradient(90deg, rgba(255,255,255,.15) 25%, rgba(255,255,255,.35) 50%, rgba(255,255,255,.15) 75%);
    background-size: 800px 100%;
    animation: shimmer 1.4s infinite linear;
}

/* Pada latar putih (tabel) → shimmer abu-abu */
td .skeleton-line,
td .skeleton-circle {
    background: linear-gradient(90deg, #e8e8e8 25%, #f5f5f5 50%, #e8e8e8 75%);
    background-size: 800px 100%;
    animation: shimmer 1.4s infinite linear;
}

.skeleton-circle {
    border-radius: 50% !important;
    background: linear-gradient(90deg, #e8e8e8 25%, #f5f5f5 50%, #e8e8e8 75%);
    background-size: 800px 100%;
    animation: shimmer 1.4s infinite linear;
}

</style>

@endsection
