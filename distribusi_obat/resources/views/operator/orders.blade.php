@extends('layouts.backoffice')

@section('page_title', 'Antrian Pesanan Logistik')

@section('content')

<div class="container-fluid">

    <!-- HEADER -->
    <div class="d-flex align-items-center mb-4">
        <div class="flex-fill">
            <h4 class="fw-semibold mb-1">Antrian Pesanan Logistik</h4>
            <p class="text-muted mb-0 fs-sm">Validasi pengajuan obat, tinjau alamat tujuan, dan kelola distribusi unit mitra.</p>
        </div>
        <div class="ms-3 d-flex gap-2">
            <button class="btn btn-primary" onclick="openCreateOrderModal()">
                <i class="ph-plus-circle me-2"></i>Buat Pesanan
            </button>
            <button onclick="fetchOrders()" class="btn btn-light">
                <i class="ph-arrow-clockwise me-1"></i>Refresh
            </button>
        </div>
    </div>

    <!-- TABLE CARD -->
    <div class="card">
        <div class="card-header d-flex align-items-center py-2">
            <h6 class="mb-0 fw-semibold">
                <i class="ph-list-bullets me-2 text-primary"></i>Daftar Pesanan
            </h6>
            <span class="ms-auto badge bg-primary" id="orderCount">-</span>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-2 ps-3">ID & Waktu</th>
                        <th class="py-2">Mitra Pemesan</th>
                        <th class="py-2 text-center" style="width:90px">Item</th>
                        <th class="py-2 text-center" style="width:120px">Status</th>
                        <th class="py-2 text-center" style="width:200px">Aksi</th>
                    </tr>
                </thead>
                <tbody id="orderTableBody">
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="ph-spinner ph-spin me-1"></i> Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>


{{-- =====================================================
     MODAL DETAIL
===================================================== --}}
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="modal-title fw-semibold">
                    <i class="ph-receipt me-2"></i>Detail Pesanan
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="detailContent">
                <div class="text-center py-5 text-muted">
                    <i class="ph-spinner ph-spin fs-3 d-block mb-2"></i>Memuat...
                </div>
            </div>
            <div class="modal-footer py-2" id="detailFooter"></div>
        </div>
    </div>
</div>


{{-- =====================================================
     MODAL ASSIGN KURIR
===================================================== --}}
<div class="modal fade" id="modalShip" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold">
                    <i class="ph-truck me-2"></i>
                    Kirim Pesanan
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                <div class="mb-3">
                    <label class="field-label">Pilih Kurir</label>
                    <select id="select_courier" class="form-select form-field">
                        <option value="">-- Pilih Kurir --</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="field-label">Kendaraan</label>
                    <select id="select_vehicle" class="form-select form-field">
                        <option value="">-- Pilih Kendaraan --</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnKirimSekarang" class="btn btn-indigo rounded-pill px-5 fw-bold" onclick="submitShip()">
                    <i class="ph-paper-plane-tilt me-2"></i>
                    Kirim Sekarang
                </button>
            </div>

        </div>
    </div>
</div>


{{-- =====================================================
     MODAL CREATE ORDER
===================================================== --}}
<div class="modal fade" id="modalCreateOrder" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-semibold">
                    <i class="ph-plus-circle me-2"></i>Buat Pesanan Manual
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formCreateOrder" onsubmit="submitAdminOrder(event)">
                <div class="modal-body">

                    <!-- MITRA -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="ph-user me-1 text-primary"></i>Mitra Pemesan
                            <span class="text-danger">*</span>
                        </label>
                        <select id="select_customer" class="form-select" required>
                            <option value="">Memuat data mitra...</option>
                        </select>
                        <div class="form-text">Pilih unit kesehatan / klinik yang memesan.</div>
                    </div>

                    <hr>

                    <!-- PRODUK -->
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold mb-0">
                                <i class="ph-package me-1 text-primary"></i>Produk Pesanan
                                <span class="text-danger">*</span>
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addProductRow()">
                                <i class="ph-plus me-1"></i>Tambah Baris
                            </button>
                        </div>
                        <div id="productItemsContainer"></div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ph-x me-1"></i>Batal
                    </button>
                    <button type="submit" id="btnSimpan" class="btn btn-primary">
                        <i class="ph-check-circle me-1"></i>Simpan Pesanan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


{{-- =====================================================
     JAVASCRIPT
===================================================== --}}
<script>

axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

const PROVINCE_ID = '12'; // Sumatera Utara
const API_WILAYAH = 'https://www.emsifa.com/api-wilayah-indonesia/api';

// ── STATUS BADGE ──────────────────────────────
function statusBadge(name) {
    const map = {
        'Pending'  : 'bg-warning text-dark',
        'Processed': 'bg-primary',
        'Shipping' : 'bg-info text-dark',
        'Completed': 'bg-success',
        'Rejected' : 'bg-danger',
        'Cancelled': 'bg-secondary',
    };
    const icons = {
        'Pending'  : 'ph-clock',
        'Processed': 'ph-gear',
        'Shipping' : 'ph-truck',
        'Completed': 'ph-check-circle',
        'Rejected' : 'ph-x-circle',
        'Cancelled': 'ph-prohibit',
    };
    const cls  = map[name]  ?? 'bg-secondary';
    const icon = icons[name] ?? 'ph-question';
    return `<span class="badge ${cls}"><i class="${icon} me-1"></i>${name}</span>`;
}

// ── PRODUCT ROW ───────────────────────────────
function productRowHtml(optHtml) {
    return `
        <div class="product-row border rounded mb-2 p-2 bg-light">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <select class="form-select form-select-sm product-select" required>
                        ${optHtml || '<option value="">-- Pilih Produk --</option>'}
                    </select>
                </div>
                <div class="col-auto">
                    <div class="input-group input-group-sm" style="width:120px">
                        <span class="input-group-text bg-white">Qty</span>
                        <input type="number" class="form-control" min="1" value="1" required>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeProductRow(this)" title="Hapus baris">
                        <i class="ph-trash"></i>
                    </button>
                </div>
            </div>
        </div>`;
}

function addProductRow() {
    document.getElementById('productItemsContainer')
        .insertAdjacentHTML('beforeend', productRowHtml(productOptionsHtml));
}

function removeProductRow(btn) {
    if (document.querySelectorAll('.product-row').length <= 1) {
        Swal.fire({ icon: 'warning', title: 'Minimal 1 produk', confirmButtonColor: '#5c6bc0' });
        return;
    }
    btn.closest('.product-row').remove();
}

// ── OPEN MODAL CREATE ─────────────────────────
function openCreateOrderModal() {
    document.getElementById('formCreateOrder').reset();
    document.getElementById('productItemsContainer').innerHTML = productRowHtml();

    const btnSimpan = document.getElementById('btnSimpan');
    btnSimpan.disabled = false;
    btnSimpan.innerHTML = '<i class="ph-check-circle me-1"></i>Simpan Pesanan';

    // Load mitra
    const selCustomer = document.getElementById('select_customer');
    selCustomer.innerHTML = '<option value="">Memuat data mitra...</option>';
    selCustomer.disabled = true;

    axios.get('/api/users').then(res => {
        const customers = res.data.filter(u => u.roles?.[0]?.name === 'customer');
        let html = '<option value="">-- Pilih Mitra --</option>';
        if (!customers.length) {
            html += '<option value="" disabled>Tidak ada mitra terdaftar</option>';
        } else {
            customers.forEach(u => {
                const email = u.email ? ` — ${u.email}` : '';
                html += `<option value="${u.id}">${u.name}${email}</option>`;
            });
        }
        selCustomer.innerHTML = html;
        selCustomer.disabled = false;
    }).catch(() => {
        selCustomer.innerHTML = '<option value="">Gagal memuat mitra</option>';
        selCustomer.disabled = false;
    });

    // Load produk
    const firstSelect = document.querySelector('.product-select');
    firstSelect.innerHTML = '<option value="">Memuat produk...</option>';
    firstSelect.disabled = true;

    axios.get('/api/products').then(res => {
        productOptionsHtml = '<option value="">-- Pilih Produk --</option>';
        res.data.forEach(p => {
            const stokInfo = p.stock > 0 ? `Stok: ${p.stock}` : '⚠ Habis';
            productOptionsHtml += `<option value="${p.id}" ${p.stock == 0 ? 'disabled' : ''}>${p.name} (${stokInfo})</option>`;
        });
        firstSelect.innerHTML = productOptionsHtml;
        firstSelect.disabled = false;
    }).catch(() => {
        firstSelect.innerHTML = '<option value="">Gagal memuat produk</option>';
        firstSelect.disabled = false;
    });

    new bootstrap.Modal(document.getElementById('modalCreateOrder')).show();
}

// ── SUBMIT ORDER ─────────────────────────────
function submitAdminOrder(e) {
    e.preventDefault();

    const customerId = document.getElementById('select_customer').value;
    if (!customerId) {
        Swal.fire({ icon: 'warning', title: 'Pilih mitra terlebih dahulu', confirmButtonColor: '#5c6bc0' });
        return;
    }

    let products = [];
    document.querySelectorAll('.product-row').forEach(row => {
        const sel = row.querySelector('.product-select');
        const qty = row.querySelector('input[type="number"]');
        if (sel.value && qty.value) {
            products.push({ product_id: parseInt(sel.value), quantity: parseInt(qty.value) });
        }
    });

    if (!products.length) {
        Swal.fire({ icon: 'warning', title: 'Pilih minimal 1 produk', confirmButtonColor: '#5c6bc0' });
        return;
    }

    const btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    btn.innerHTML = '<i class="ph-spinner ph-spin me-1"></i>Menyimpan...';

    axios.post('/api/admin/orders', {
        customer_id:  parseInt(customerId),
        request_type: 'delivery',
        notes:        'Pesanan Manual oleh Admin',
        products
    }).then(() => {
        bootstrap.Modal.getInstance(document.getElementById('modalCreateOrder')).hide();
        Swal.fire({
            icon: 'success',
            title: 'Pesanan Dibuat!',
            text: 'Pesanan manual berhasil disimpan.',
            confirmButtonColor: '#5c6bc0',
            timer: 2500,
            timerProgressBar: true,
            showConfirmButton: false,
        });
        fetchOrders();
    }).catch(err => {
        let msg = err.response?.data?.message ?? 'Terjadi kesalahan';
        if (err.response?.data?.errors) msg = Object.values(err.response.data.errors)[0][0];
        Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: msg, confirmButtonColor: '#d33' });
    }).finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ph-check-circle me-1"></i>Simpan Pesanan';
    });
}

// ── FETCH ORDERS ──────────────────────────────
function fetchOrders() {
    document.getElementById('orderTableBody').innerHTML =
        `<tr><td colspan="5" class="text-center py-4 text-muted">
            <i class="ph-spinner ph-spin me-1"></i> Memuat data...
        </td></tr>`;

    axios.get('/api/orders').then(res => {
        const orders = res.data;
        document.getElementById('orderCount').textContent = orders.length + ' pesanan';

        if (!orders.length) {
            document.getElementById('orderTableBody').innerHTML =
                `<tr><td colspan="5" class="text-center py-5 text-muted">
                    <i class="ph-inbox fs-3 d-block mb-2 opacity-25"></i>Belum ada pesanan
                </td></tr>`;
            return;
        }

        let html = '';
        orders.forEach(order => {
            const statusName = order.status?.name ?? 'Unknown';
            const totalItem  = order.items?.length ?? 0;
            const createdAt  = new Date(order.created_at).toLocaleString('id-ID');
            const shortId    = order.id.toString().length > 13
                ? '#' + order.id.toString().substring(0, 13) + '…'
                : '#' + order.id;

            let aksi = '';

            if (statusName === 'Pending') {
                aksi = `
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-light" onclick="showOrderDetail('${order.id}')" title="Detail">
                            <i class="ph-eye"></i>
                        </button>
                        <button class="btn btn-success" onclick="updateOrderStatus('${order.id}','approve')">
                            <i class="ph-check me-1"></i>Terima
                        </button>
                        <button class="btn btn-danger" onclick="updateOrderStatus('${order.id}','reject')">
                            <i class="ph-x me-1"></i>Tolak
                        </button>
                    </div>`;
            } else {
                aksi = `
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-light" onclick="showOrderDetail('${order.id}')" title="Detail">
                            <i class="ph-eye me-1"></i>Detail
                        </button>
                    </div>`;
            }

            html += `
                <tr>
                    <td class="ps-3 py-2">
                        <a href="#" class="fw-semibold text-primary text-decoration-none font-monospace small"
                            onclick="showOrderDetail('${order.id}');return false;"
                            title="${order.id}">${shortId}</a>
                        <div class="text-muted fs-sm mt-1">
                            <i class="ph-clock me-1"></i>${createdAt}
                        </div>
                    </td>
                    <td class="py-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:28px;height:28px;font-size:.72rem;font-weight:700;">
                                ${(order.user?.name ?? '?')[0].toUpperCase()}
                            </div>
                            <span class="fw-semibold">${order.user?.name ?? '-'}</span>
                        </div>
                    </td>
                    <td class="text-center py-2">
                        <span class="badge bg-light text-dark border">${totalItem} item</span>
                    </td>
                    <td class="text-center py-2">
                        ${statusBadge(statusName)}
                    </td>
                    <td class="text-center py-2">
                        ${aksi}
                    </td>
                </tr>`;
        });

        document.getElementById('orderTableBody').innerHTML = html;

    }).catch(() => {
        document.getElementById('orderTableBody').innerHTML =
            `<tr><td colspan="5" class="text-center py-4 text-danger">
                <i class="ph-warning-circle me-1"></i>Gagal memuat data
            </td></tr>`;
    });
}

// ── UPDATE STATUS ─────────────────────────────
function updateOrderStatus(orderId, action) {
    const isApprove = action === 'approve';
    Swal.fire({
        icon:               isApprove ? 'question' : 'warning',
        title:              isApprove ? 'Terima Pesanan?' : 'Tolak Pesanan?',
        text:               isApprove
                                ? 'Stok produk akan dikurangi dan pesanan diteruskan ke logistik.'
                                : 'Pesanan akan ditolak dan tidak dapat diproses kembali.',
        showCancelButton:   true,
        confirmButtonColor: isApprove ? '#2e7d32' : '#c62828',
        cancelButtonColor:  '#546e7a',
        confirmButtonText:  isApprove ? 'Ya, Terima' : 'Ya, Tolak',
        cancelButtonText:   'Batal',
    }).then(r => {
        if (!r.isConfirmed) return;

        try {
            bootstrap.Modal.getInstance(document.getElementById('modalDetail'))?.hide();
        } catch(e) {}

        axios.post(`/api/orders/${orderId}/${isApprove ? 'approve' : 'reject'}`)
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: isApprove ? 'Pesanan Diterima!' : 'Pesanan Ditolak',
                    confirmButtonColor: '#5c6bc0',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true,
                });
                fetchOrders();
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: err.response?.data?.message ?? 'Terjadi kesalahan',
                    confirmButtonColor: '#d33'
                });
            });
    });
}

// ── SHOW DETAIL ───────────────────────────────
function showOrderDetail(orderId) {
    document.getElementById('detailContent').innerHTML =
        `<div class="text-center py-5 text-muted">
            <i class="ph-spinner ph-spin fs-3 d-block mb-2"></i>Memuat detail...
        </div>`;
    document.getElementById('detailFooter').innerHTML = '';

    new bootstrap.Modal(document.getElementById('modalDetail')).show();

    axios.get('/api/orders').then(res => {
        const order = res.data.find(o => o.id == orderId);
        if (!order) return;

        const statusName = order.status?.name ?? 'Unknown';
        const createdAt  = new Date(order.created_at).toLocaleString('id-ID');

        let itemsHtml = '';
        let grandTotal = 0;
        (order.items ?? []).forEach(item => {
            const sub = item.quantity * item.price_at_order;
            grandTotal += sub;
            itemsHtml += `
                <tr>
                    <td>${item.product?.name ?? '-'}</td>
                    <td class="text-center">${item.quantity}</td>
                    <td class="text-end text-nowrap">Rp ${Number(item.price_at_order).toLocaleString('id-ID')}</td>
                    <td class="text-end fw-semibold text-nowrap">Rp ${Number(sub).toLocaleString('id-ID')}</td>
                </tr>`;
        });

        document.getElementById('detailContent').innerHTML = `
            <div class="p-3 bg-light border-bottom">
                <div class="row g-2">
                    <div class="col-sm-8">
                        <div class="fs-sm text-muted mb-1">ID Pesanan</div>
                        <div class="fw-semibold font-monospace small text-break">${order.id}</div>
                    </div>
                    <div class="col-sm-4 text-sm-end">
                        <div class="fs-sm text-muted mb-1">Status</div>
                        ${statusBadge(statusName)}
                        <div class="text-muted fs-sm mt-1">${createdAt}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="fs-sm text-muted mb-1"><i class="ph-user me-1"></i>Mitra</div>
                        <div class="fw-semibold">${order.user?.name ?? '-'}</div>
                        <div class="text-muted fs-sm">${order.user?.email ?? '-'}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="fs-sm text-muted mb-1"><i class="ph-clock me-1"></i>Waktu Pesan</div>
                        <div class="fw-semibold">${createdAt}</div>
                    </div>
                    ${order.shipping_address ? `
                    <div class="col-12">
                        <div class="fs-sm text-muted mb-1"><i class="ph-map-pin me-1"></i>Alamat Pengiriman</div>
                        <div class="fw-semibold">${order.shipping_address}</div>
                    </div>` : ''}
                    ${order.notes ? `
                    <div class="col-12">
                        <div class="fs-sm text-muted mb-1"><i class="ph-note me-1"></i>Catatan</div>
                        <div>${order.notes}</div>
                    </div>` : ''}
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th class="text-center" style="width:60px">Qty</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>${itemsHtml}</tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-semibold">Total</td>
                            <td class="text-end fw-bold text-primary">
                                Rp ${Number(grandTotal).toLocaleString('id-ID')}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>`;

        if (statusName === 'Pending') {
            document.getElementById('detailFooter').innerHTML = `
                <button class="btn btn-danger me-auto" onclick="updateOrderStatus('${order.id}','reject')">
                    <i class="ph-x-circle me-1"></i>Tolak
                </button>
                <button class="btn btn-success" onclick="updateOrderStatus('${order.id}','approve')">
                    <i class="ph-check-circle me-1"></i>Terima & Proses
                </button>`;
        }
    });
}

let activeShipOrderId = null;

function submitShip() {
    const courierId = document.getElementById('select_courier').value;
    const vehicleId = document.getElementById('select_vehicle').value;

    if (!courierId) {
        Swal.fire({ icon: 'warning', title: 'Kurir belum dipilih', confirmButtonColor: '#5c6bc0' });
        return;
    }

    if (!vehicleId) {
        Swal.fire({ icon: 'warning', title: 'Kendaraan belum dipilih', confirmButtonColor: '#5c6bc0' });
        return;
    }

    const btn          = document.getElementById('btnKirimSekarang');
    const originalHtml = btn.innerHTML;
    setButtonLoading(btn, true);

    axios.post(`/api/deliveries/ready/${activeShipOrderId}`, {
        courier_id: courierId,
        vehicle_id: vehicleId
    })
    .then(() => {
        setButtonLoading(btn, false, originalHtml);

        bootstrap.Modal.getInstance(
            document.getElementById('modalShip')
        ).hide();

        Swal.fire({
            icon: 'success',
            title: 'Pesanan Dikirim',
            text: 'Kurir telah ditugaskan dan status berubah ke Shipping.',
            confirmButtonColor: '#5c6bc0'
        });

        fetchOrders();
    })
    .catch(err => {
        setButtonLoading(btn, false, originalHtml);

        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: err.response?.data?.message ?? 'Terjadi kesalahan.',
            confirmButtonColor: '#d33'
        });
    });
}

function openShipModal(orderId) {
    activeShipOrderId = orderId;

    // Load kurir
    axios.get('/api/users').then(res => {
        let html = '<option value="">-- Pilih Kurir --</option>';
        res.data
            .filter(u => u.roles && u.roles[0]?.name === 'courier')
            .forEach(u => {
                html += `<option value="${u.id}">${u.name}</option>`;
            });
        document.getElementById('select_courier').innerHTML = html;
    });

    // Load kendaraan dari vehicles table
    axios.get('/api/vehicles').then(res => {
        let html = '<option value="">-- Pilih Kendaraan --</option>';
        res.data.forEach(v => {
            html += `<option value="${v.id}">${v.brand} ${v.subtype} - ${v.plate_number} (${v.color})</option>`;
        });
        document.getElementById('select_vehicle').innerHTML = html;
    });

    new bootstrap.Modal(document.getElementById('modalShip')).show();
}

// ─── INIT ────────────────────────────────────────────
fetchOrders();

</script>


{{-- =====================================================
     CSS
===================================================== --}}
<style>
.table > tbody > tr > td { vertical-align: middle; }
.font-monospace { font-family: 'SFMono-Regular', Consolas, monospace; }
.product-row { transition: background .15s; }
.product-row:hover { background: #f1f5f9 !important; }
.badge.bg-light { border: 1px solid #dee2e6; }
</style>

@endsection
