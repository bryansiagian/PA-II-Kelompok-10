@extends('layouts.backoffice')

@section('page_title', 'Antrian Pesanan Produk')

@section('content')

<div class="container-fluid">

    {{-- ===================== HEADER ===================== --}}
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0 text-dark">Antrian Pesanan Logistik</h4>
            <div class="text-muted small">Validasi pengajuan obat, tinjau alamat tujuan, dan kelola distribusi unit mitra.</div>
        </div>
        <div class="ms-3 d-flex gap-2">
            <button class="btn btn-indigo shadow-sm rounded-pill px-4" onclick="openCreateOrderModal()">
                <i class="ph-plus-circle me-2"></i> Buat Pesanan
            </button>
            <button onclick="fetchOrders()" class="btn btn-light shadow-sm rounded-pill px-4">
                <i class="ph-arrow-clockwise me-2"></i> Refresh
            </button>
        </div>
    </div>

    {{-- ===================== TABLE ===================== --}}
    <div class="card shadow-sm border-0 rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3">ID & Waktu</th>
                        <th>Mitra Pemesan</th>
                        <th class="text-center">Total Item</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Pembayaran</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="orderTableBody">
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Menghubungkan ke server...</td>
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-info me-2"></i> Rincian Lengkap Pesanan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="detailContent"></div>
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
                <h6 class="modal-title fw-bold"><i class="ph-truck me-2"></i> Kirim Pesanan</h6>
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
                    <i class="ph-paper-plane-tilt me-2"></i> Kirim Sekarang
                </button>
            </div>
        </div>
    </div>
</div>


{{-- =====================================================
     MODAL CREATE ORDER
===================================================== --}}
<div class="modal fade" id="modalCreateOrder" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="ph-plus-circle me-2"></i> Buat Pesanan Manual</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                {{-- ── CUSTOMER ── --}}
                <div class="section-card mb-4">
                    <div class="section-label"><i class="ph-user-circle me-2"></i> Data Pemesan</div>
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" id="btnExistingCustomer" class="btn btn-sm btn-indigo rounded-pill px-3 active-toggle" onclick="toggleCustomerMode('existing')">Pilih Mitra</button>
                        <button type="button" id="btnNewCustomer" class="btn btn-sm btn-outline-indigo rounded-pill px-3" onclick="toggleCustomerMode('new')">+ Mitra Baru</button>
                    </div>
                    <div id="panelExistingCustomer">
                        <label class="field-label">Pilih Mitra (Customer)</label>
                        <select id="select_customer" class="form-select form-field">
                            <option value="">-- Pilih Mitra --</option>
                        </select>
                    </div>
                    <div id="panelNewCustomer" class="d-none">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="field-label">Nama Lengkap</label>
                                <input type="text" id="new_customer_name" class="form-control form-field" placeholder="Masukkan nama mitra baru">
                            </div>
                            <div class="col-md-6">
                                <label class="field-label">Email</label>
                                <input type="email" id="new_customer_email" class="form-control form-field" placeholder="email@domain.com">
                            </div>
                            <div class="col-md-6">
                                <label class="field-label">No. Telepon</label>
                                <input type="text" id="new_customer_phone" class="form-control form-field" placeholder="08xx-xxxx-xxxx">
                            </div>
                            <div class="col-12">
                                <label class="field-label">Alamat (opsional)</label>
                                <input type="text" id="new_customer_address" class="form-control form-field" placeholder="Alamat domisili mitra">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── HUB REGIONAL ── --}}
                <div class="section-card mb-4">
                    <div class="section-label"><i class="ph-map-pin me-2"></i> Alamat & Hub Regional (Sumatera Utara)</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="field-label">Provinsi</label>
                            <select id="select_province" class="form-select form-field" disabled>
                                <option value="12" selected>Sumatera Utara</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="field-label">Kabupaten / Kota</label>
                            <select id="select_regency" class="form-select form-field" onchange="onRegencyChange(this)">
                                <option value="" disabled selected>Memuat...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="field-label">Kecamatan</label>
                            <select id="select_district" class="form-select form-field" disabled onchange="onDistrictChange(this)">
                                <option value="" disabled selected>Pilih Kab/Kota dulu</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="field-label">Kelurahan / Desa</label>
                            <select id="select_village" class="form-select form-field" disabled>
                                <option value="" disabled selected>Pilih Kecamatan dulu</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="field-label">Detail Alamat</label>
                            <input type="text" id="input_address_detail" class="form-control form-field" placeholder="Nama jalan, nomor, RT/RW, gedung, dll.">
                        </div>
                    </div>
                </div>

                {{-- ── PRODUK ── --}}
                <div class="section-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="section-label mb-0"><i class="ph-pill me-2"></i> Daftar Produk Pesanan</div>
                        <button type="button" class="btn btn-sm btn-indigo rounded-pill px-3" onclick="addProductRow()">
                            <i class="ph-plus me-1"></i> Tambah Produk
                        </button>
                    </div>
                    <div id="productItemsContainer"></div>
                </div>

                {{-- ── METODE PENGIRIMAN & PEMBAYARAN ── --}}
                <div class="section-card mb-4">
                    <div class="section-label"><i class="ph-credit-card me-2"></i> Pengiriman & Pembayaran</div>
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="field-label">Metode Pengiriman</label>
                            <select id="input_request_type" class="form-select form-field">
                                <option value="delivery">🚚 Kirim Kurir</option>
                                <option value="self_pickup">🏢 Ambil Sendiri</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="field-label">Metode Pembayaran</label>
                            <div class="d-flex gap-2 mt-1">
                                {{-- Snap --}}
                                <div class="payment-method-card flex-fill" id="payMethodSnap" onclick="selectPaymentMethod('snap')">
                                    <div class="pay-icon"><i class="ph-device-mobile"></i></div>
                                    <div class="pay-label">Digital</div>
                                    <div class="pay-desc">Customer bayar via Midtrans</div>
                                </div>
                                {{-- Cash --}}
                                <div class="payment-method-card flex-fill" id="payMethodCash" onclick="selectPaymentMethod('cash')">
                                    <div class="pay-icon"><i class="ph-money"></i></div>
                                    <div class="pay-label">Tunai</div>
                                    <div class="pay-desc">Bayar di tempat / manual</div>
                                </div>
                            </div>
                            <input type="hidden" id="selected_payment_method" value="snap">
                        </div>

                    </div>

                    {{-- Info box berubah sesuai pilihan --}}
                    <div id="paymentMethodInfo" class="mt-3"></div>
                </div>

                {{-- ── CATATAN ── --}}
                <div class="mt-1">
                    <label class="field-label">Catatan Pesanan (opsional)</label>
                    <textarea id="input_notes" class="form-control form-field" rows="2" placeholder="Instruksi khusus, info tambahan..."></textarea>
                </div>

            </div>

            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnSimpanPesanan" class="btn btn-indigo text-white rounded-pill px-5 fw-bold" onclick="submitAdminOrder()">
                    <i class="ph-check-circle me-2"></i> Simpan Pesanan
                </button>
            </div>

        </div>
    </div>
</div>


{{-- =====================================================
     JAVASCRIPT
===================================================== --}}
<script>

// ─── CONFIG ──────────────────────────────────────────
axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

const PROVINCE_ID = '12';
const API_WILAYAH = 'https://www.emsifa.com/api-wilayah-indonesia/api';

// ─── STATE ───────────────────────────────────────────
let productOptionsCache = [];
let customerMode        = 'existing';

// ─── PAYMENT METHOD SELECTOR ─────────────────────────

function selectPaymentMethod(method) {
    document.getElementById('selected_payment_method').value = method;

    document.getElementById('payMethodSnap').classList.toggle('active', method === 'snap');
    document.getElementById('payMethodCash').classList.toggle('active', method === 'cash');

    const infoEl = document.getElementById('paymentMethodInfo');
    if (method === 'snap') {
        infoEl.innerHTML = `
        <div class="alert alert-info border-0 rounded-3 py-2 px-3 mb-0 d-flex align-items-start gap-2">
            <i class="ph-info-circle fs-5 mt-1 flex-shrink-0"></i>
            <div class="small">
                <strong>Pembayaran Digital (Snap)</strong><br>
                Pesanan akan berstatus <span class="badge bg-warning text-dark">Awaiting Payment</span>.
                Customer mendapat notifikasi dan bisa bayar dari halaman <em>Riwayat Pesanan</em>.
            </div>
        </div>`;
    } else {
        infoEl.innerHTML = `
        <div class="alert alert-success border-0 rounded-3 py-2 px-3 mb-0 d-flex align-items-start gap-2">
            <i class="ph-check-circle fs-5 mt-1 flex-shrink-0"></i>
            <div class="small">
                <strong>Pembayaran Tunai</strong><br>
                Pesanan langsung berstatus <span class="badge bg-warning text-dark">Pending</span> dan siap diproses admin tanpa perlu pembayaran online.
            </div>
        </div>`;
    }
}

// Init default
document.addEventListener('DOMContentLoaded', () => {
    selectPaymentMethod('snap');
    fetchOrders();
});

// ─── WILAYAH ─────────────────────────────────────────

async function fetchRegencies() {
    try {
        const res  = await fetch(`${API_WILAYAH}/regencies/${PROVINCE_ID}.json`);
        const data = await res.json();
        let html = '<option value="" disabled selected>Pilih Kab/Kota</option>';
        data.forEach(item => { html += `<option value="${item.id}" data-name="${item.name}">${item.name}</option>`; });
        document.getElementById('select_regency').innerHTML = html;
    } catch (e) { console.error('Gagal muat kabupaten:', e); }
}

async function fetchDistricts(regencyId) {
    const el = document.getElementById('select_district');
    el.disabled = true; el.innerHTML = '<option>Memuat kecamatan...</option>';
    try {
        const data = await (await fetch(`${API_WILAYAH}/districts/${regencyId}.json`)).json();
        let html = '<option value="" disabled selected>Pilih Kecamatan</option>';
        data.forEach(item => { html += `<option value="${item.id}" data-name="${item.name}">${item.name}</option>`; });
        el.innerHTML = html; el.disabled = false;
    } catch (e) { console.error('Gagal muat kecamatan:', e); }
}

async function fetchVillages(districtId) {
    const el = document.getElementById('select_village');
    el.disabled = true; el.innerHTML = '<option>Memuat kelurahan...</option>';
    try {
        const data = await (await fetch(`${API_WILAYAH}/villages/${districtId}.json`)).json();
        let html = '<option value="" disabled selected>Pilih Kelurahan/Desa</option>';
        data.forEach(item => { html += `<option value="${item.id}" data-name="${item.name}">${item.name}</option>`; });
        el.innerHTML = html; el.disabled = false;
    } catch (e) { console.error('Gagal muat kelurahan:', e); }
}

function onRegencyChange(sel) {
    const districtEl = document.getElementById('select_district');
    const villageEl  = document.getElementById('select_village');
    districtEl.innerHTML = '<option value="" disabled selected>Pilih Kecamatan</option>';
    districtEl.disabled  = true;
    villageEl.innerHTML  = '<option value="" disabled selected>Pilih Kecamatan dulu</option>';
    villageEl.disabled   = true;
    if (sel.value) fetchDistricts(sel.value);
}

function onDistrictChange(sel) {
    const villageEl = document.getElementById('select_village');
    villageEl.innerHTML = '<option value="" disabled selected>Pilih Kelurahan/Desa</option>';
    villageEl.disabled  = true;
    if (sel.value) fetchVillages(sel.value);
}

// ─── CUSTOMER MODE ───────────────────────────────────

function toggleCustomerMode(mode) {
    customerMode = mode;
    const panelExisting = document.getElementById('panelExistingCustomer');
    const panelNew      = document.getElementById('panelNewCustomer');
    const btnExisting   = document.getElementById('btnExistingCustomer');
    const btnNew        = document.getElementById('btnNewCustomer');

    if (mode === 'existing') {
        panelExisting.classList.remove('d-none'); panelNew.classList.add('d-none');
        btnExisting.classList.add('active-toggle'); btnNew.classList.remove('active-toggle');
    } else {
        panelExisting.classList.add('d-none'); panelNew.classList.remove('d-none');
        btnNew.classList.add('active-toggle'); btnExisting.classList.remove('active-toggle');
    }
}

// ─── PRODUCT ROWS ────────────────────────────────────

function buildProductOptionsHtml() {
    let html = '<option value="">-- Pilih Produk --</option>';
    productOptionsCache.forEach(p => {
        html += `<option value="${p.id}">${p.name} (Stok: ${p.stock})</option>`;
    });
    return html;
}

function addProductRow() {
    const container = document.getElementById('productItemsContainer');
    const row = document.createElement('div');
    row.className = 'product-item card border-0 shadow-sm mb-3';
    row.innerHTML = `
        <div class="card-body p-3">
            <div class="row align-items-end g-3">
                <div class="col-md-7">
                    <label class="field-label">Produk</label>
                    <select class="form-select form-field product-select" required>${buildProductOptionsHtml()}</select>
                </div>
                <div class="col-md-3">
                    <label class="field-label">Kuantitas</label>
                    <input type="number" class="form-control form-field product-qty" min="1" value="1" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger w-100 btn-delete-row" onclick="removeProductRow(this)"><i class="ph-trash"></i></button>
                </div>
            </div>
        </div>`;
    container.appendChild(row);
}

function removeProductRow(button) {
    const rows = document.querySelectorAll('.product-item');
    if (rows.length <= 1) {
        Swal.fire({ icon: 'warning', title: 'Minimal 1 Produk', confirmButtonColor: '#5c6bc0' });
        return;
    }
    button.closest('.product-item').remove();
}

// ─── HELPERS ─────────────────────────────────────────

function setButtonLoading(btn, isLoading, originalHtml) {
    if (isLoading) {
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Memproses...`;
    } else {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

// ─── OPEN MODAL ──────────────────────────────────────

function openCreateOrderModal() {
    toggleCustomerMode('existing');
    document.getElementById('new_customer_name').value    = '';
    document.getElementById('new_customer_email').value   = '';
    document.getElementById('new_customer_phone').value   = '';
    document.getElementById('new_customer_address').value = '';
    document.getElementById('input_address_detail').value = '';
    document.getElementById('input_notes').value          = '';

    document.getElementById('select_regency').innerHTML  = '<option>Memuat...</option>';
    document.getElementById('select_district').innerHTML = '<option disabled selected>Pilih Kab/Kota dulu</option>';
    document.getElementById('select_district').disabled  = true;
    document.getElementById('select_village').innerHTML  = '<option disabled selected>Pilih Kecamatan dulu</option>';
    document.getElementById('select_village').disabled   = true;

    document.getElementById('productItemsContainer').innerHTML = '';

    selectPaymentMethod('snap');

    axios.get('/api/users').then(res => {
        let html = '<option value="">-- Pilih Mitra --</option>';
        res.data
            .filter(u => u.roles && u.roles[0]?.name === 'customer')
            .forEach(u => { html += `<option value="${u.id}">${u.name}</option>`; });
        document.getElementById('select_customer').innerHTML = html;
    });

    axios.get('/api/products').then(res => {
        productOptionsCache = res.data;
        addProductRow();
    });

    fetchRegencies();
    new bootstrap.Modal(document.getElementById('modalCreateOrder')).show();
}

// ─── SUBMIT ORDER ────────────────────────────────────

async function submitAdminOrder() {
    const btn          = document.getElementById('btnSimpanPesanan');
    const originalHtml = btn.innerHTML;

    const products = [];
    document.querySelectorAll('.product-item').forEach(row => {
        const productId = row.querySelector('.product-select')?.value;
        const qty       = row.querySelector('input[type="number"]')?.value;
        if (productId && productId !== '' && qty) {
            products.push({ product_id: productId, quantity: parseInt(qty) });
        }
    });

    if (products.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Produk belum dipilih', text: 'Silakan pilih minimal satu produk.', confirmButtonColor: '#5c6bc0' });
        return;
    }

    let customerId = null;
    if (customerMode === 'existing') {
        customerId = document.getElementById('select_customer').value;
        if (!customerId) {
            Swal.fire({ icon: 'warning', title: 'Mitra belum dipilih', text: 'Silakan pilih mitra pemesan.', confirmButtonColor: '#5c6bc0' });
            return;
        }
    } else {
        const name  = document.getElementById('new_customer_name').value.trim();
        const email = document.getElementById('new_customer_email').value.trim();
        const phone = document.getElementById('new_customer_phone').value.trim();
        if (!name || !email) {
            Swal.fire({ icon: 'warning', title: 'Data Mitra Tidak Lengkap', text: 'Nama dan email wajib diisi untuk mitra baru.', confirmButtonColor: '#5c6bc0' });
            return;
        }
        try {
            const res  = await axios.post('/api/customers', { name, email, phone, address: document.getElementById('new_customer_address').value.trim() });
            customerId = res.data.id;
            window._newCustomerPassword = res.data.plain_password;
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Gagal Mendaftar Mitra Baru', text: e.response?.data?.message ?? 'Terjadi kesalahan.', confirmButtonColor: '#d33' });
            return;
        }
    }

    const regencySel  = document.getElementById('select_regency');
    const districtSel = document.getElementById('select_district');
    const villageSel  = document.getElementById('select_village');
    const address = {
        regency:  regencySel.options[regencySel.selectedIndex]?.dataset?.name   ?? '',
        district: districtSel.options[districtSel.selectedIndex]?.dataset?.name ?? '',
        village:  villageSel.options[villageSel.selectedIndex]?.dataset?.name    ?? '',
        detail:   document.getElementById('input_address_detail').value.trim(),
    };

    const paymentMethod = document.getElementById('selected_payment_method').value;

    const payload = {
        customer_id:    customerId,
        request_type:   document.getElementById('input_request_type').value,
        payment_method: paymentMethod,
        notes:          document.getElementById('input_notes').value.trim(),
        address,
        products
    };

    setButtonLoading(btn, true);

    try {
        await axios.post('/api/admin/orders', payload);

        setButtonLoading(btn, false, originalHtml);
        bootstrap.Modal.getInstance(document.getElementById('modalCreateOrder')).hide();

        const paymentNote = paymentMethod === 'snap'
            ? '<br><small class="text-muted">Customer akan mendapat notifikasi untuk menyelesaikan pembayaran.</small>'
            : '<br><small class="text-muted">Pesanan langsung masuk antrian (Pending).</small>';

        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            html: `Pesanan berhasil dibuat.${paymentNote}<br><br>
                ${window._newCustomerPassword
                    ? `<b>Password mitra baru:</b> <code>${window._newCustomerPassword}</code><br>
                       <small class="text-muted">Sampaikan ke mitra, password hanya ditampilkan sekali.</small>`
                    : ''}`,
            confirmButtonColor: '#5c6bc0'
        });
        window._newCustomerPassword = null;
        fetchOrders();

    } catch (error) {
        setButtonLoading(btn, false, originalHtml);
        let msg = 'Terjadi kesalahan';
        if (error.response?.data?.errors) {
            const firstKey = Object.keys(error.response.data.errors)[0];
            msg = error.response.data.errors[firstKey][0] ?? msg;
        } else if (error.response?.data?.message) {
            msg = error.response.data.message;
        }
        Swal.fire({ icon: 'error', title: 'Gagal Menyimpan Pesanan', text: msg, confirmButtonColor: '#d33' });
    }
}

// ─── FETCH ORDERS ────────────────────────────────────

const trackingBaseUrl = "{{ route('operator.tracking', '__id__') }}".replace('__id__', '');

const payBadgeMap = {
    'unpaid':   { cls: 'bg-secondary',        label: 'Belum Bayar' },
    'paid':     { cls: 'bg-success',           label: 'Lunas' },
    'cash':     { cls: 'bg-info text-dark',    label: 'Tunai' },
    'refunded': { cls: 'bg-danger',            label: 'Refund' },
};

function fetchOrders() {
    axios.get('/api/orders')
        .then(res => {
            const orders = res.data;

            if (!orders.length) {
                document.getElementById('orderTableBody').innerHTML = `
                    <tr><td colspan="6" class="text-center py-5 text-muted">Tidak ada data pesanan</td></tr>`;
                return;
            }

            const badgeMap = {
                'Awaiting Payment': 'bg-secondary',
                'Pending':          'bg-warning text-dark',
                'Processed':        'bg-info text-dark',
                'Completed':        'bg-success',
                'Cancelled':        'bg-danger',
                'Rejected':         'bg-danger',
            };

            let html = '';
            orders.forEach(order => {
                const createdAt  = new Date(order.created_at).toLocaleString('id-ID');
                const totalItem  = order.items?.length ?? 0;
                const statusName = order.status?.name ?? 'Unknown';
                const badge      = badgeMap[statusName] ?? 'bg-secondary';

                const payStatus = order.payment_status ?? 'unpaid';
                const payBadge  = payBadgeMap[payStatus] ?? { cls: 'bg-secondary', label: payStatus };

                // Pesanan dianggap sudah dibayar kalau payment_status = 'paid' atau 'cash'
                const isPaid = payStatus === 'paid' || payStatus === 'cash';

                let actionHtml = '';
                if (statusName === 'Pending') {
                    if (isPaid) {
                        // Sudah bayar — tampilkan Setujui & Tolak
                        actionHtml = `
                            <button class="btn btn-indigo btn-sm rounded-pill px-3" onclick="approveOrder('${order.id}')">
                                <i class="ph-paper-plane-tilt me-1"></i> Setujui
                            </button>
                            <button class="btn btn-danger btn-sm rounded-pill px-3" onclick="rejectOrder('${order.id}')">
                                <i class="ph-x-circle me-1"></i> Tolak
                            </button>`;
                    } else {
                        // Belum bayar — tampilkan label saja, bukan tombol aksi
                        actionHtml = `
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2" style="font-size:.75rem;">
                                <i class="ph-clock me-1"></i> Menunggu Pembayaran
                            </span>`;
                    }
                } else if (statusName === 'Processed') {
                    actionHtml = `
                        <button class="btn btn-warning btn-sm rounded-pill px-3" onclick="openShipModal('${order.id}')">
                            <i class="ph-truck me-1"></i> Kirim
                        </button>`;
                } else if (statusName === 'Shipping' || statusName === 'Completed') {
                    const deliveryId = order.delivery?.id ?? null;
                    actionHtml = deliveryId
                        ? `<a href="${trackingBaseUrl}${deliveryId}" class="btn btn-success btn-sm rounded-pill px-3"><i class="ph-map-pin me-1"></i> Lacak</a>`
                        : `<span class="text-muted small">Belum ada kurir</span>`;
                }

                html += `
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold text-primary">#${order.id.substring(0, 8)}...</div>
                            <small class="text-muted">${createdAt}</small>
                        </td>
                        <td><div class="fw-semibold">${order.user?.name ?? '-'}</div></td>
                        <td class="text-center"><span class="fw-bold">${totalItem}</span></td>
                        <td class="text-center"><span class="badge ${badge}">${statusName}</span></td>
                        <td class="text-center"><span class="badge ${payBadge.cls}">${payBadge.label}</span></td>
                        <td class="text-center pe-3">
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <button class="btn btn-light btn-sm rounded-circle" onclick="showOrderDetail('${order.id}')" title="Lihat Detail">
                                    <i class="ph-eye"></i>
                                </button>
                                ${actionHtml}
                            </div>
                        </td>
                    </tr>`;
            });

            document.getElementById('orderTableBody').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('orderTableBody').innerHTML = `
                <tr><td colspan="6" class="text-center py-5 text-danger">Gagal mengambil data pesanan</td></tr>`;
        });
}

function approveOrder(orderId) {
    Swal.fire({
        title: 'Setujui Pesanan?',
        text: 'Stok akan dikurangi dan email konfirmasi dikirim ke customer.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#5c6bc0',
        showLoaderOnConfirm: true,
        preConfirm: () => axios.post(`/api/orders/${orderId}/approve`).catch(err => {
            Swal.showValidationMessage(err.response?.data?.message ?? 'Terjadi kesalahan.');
        }),
        allowOutsideClick: () => !Swal.isLoading()
    }).then(result => {
        if (!result.isConfirmed) return;
        Swal.fire({ icon: 'success', title: 'Pesanan Disetujui', text: 'Email konfirmasi berhasil dikirim.', confirmButtonColor: '#5c6bc0' });
        fetchOrders();
    });
}

function rejectOrder(orderId) {
    Swal.fire({
        title: 'Tolak Pesanan?',
        text: 'Jika sudah dibayar, refund akan otomatis diproses ke Midtrans.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tolak',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33',
        showLoaderOnConfirm: true,
        preConfirm: () => axios.post(`/api/orders/${orderId}/reject`).catch(err => {
            Swal.showValidationMessage(err.response?.data?.message ?? 'Terjadi kesalahan.');
        }),
        allowOutsideClick: () => !Swal.isLoading()
    }).then(result => {
        if (!result.isConfirmed) return;
        Swal.fire({ icon: 'success', title: 'Pesanan Ditolak', confirmButtonColor: '#5c6bc0' });
        fetchOrders();
    });
}

// ─── SHOW DETAIL ─────────────────────────────────────

function showOrderDetail(orderId) {
    axios.get('/api/orders').then(res => {
        const order = res.data.find(o => o.id === orderId);
        if (!order) return;

        let itemsHtml = '';
        let grandTotal = 0;
        (order.items ?? []).forEach(item => {
            const subtotal = item.quantity * Number(item.price_at_order);
            grandTotal += subtotal;
            itemsHtml += `
                <tr>
                    <td class="ps-3">${item.product?.name ?? '-'}</td>
                    <td class="text-center">${item.quantity}</td>
                    <td class="text-end">Rp ${Number(item.price_at_order).toLocaleString('id-ID')}</td>
                    <td class="text-end pe-3 fw-semibold">Rp ${subtotal.toLocaleString('id-ID')}</td>
                </tr>`;
        });

        const village  = order.village          ?? '-';
        const district = order.district         ?? '-';
        const regency  = order.regency          ?? '-';
        const detail   = order.shipping_address ?? '-';

        const badgeMap = {
            'Awaiting Payment': 'bg-secondary',
            'Pending':          'bg-warning text-dark',
            'Processed':        'bg-info text-dark',
            'Shipping':         'bg-primary',
            'Completed':        'bg-success',
            'Cancelled':        'bg-danger',
            'Rejected':         'bg-danger',
        };
        const statusName = order.status?.name ?? 'Unknown';
        const badge      = badgeMap[statusName] ?? 'bg-secondary';

        const payStatus = order.payment_status ?? 'unpaid';
        const payBadge  = payBadgeMap[payStatus] ?? { cls: 'bg-secondary', label: payStatus };

        const notesHtml = order.notes?.trim()
            ? `<div class="alert alert-light border-start border-4 border-indigo mb-0 py-2 px-3">
                   <small class="text-muted fw-bold text-uppercase d-block mb-1">Catatan</small>
                   ${order.notes}
               </div>`
            : '';

        const paidAtHtml = order.paid_at
            ? `<div class="text-muted small mt-1"><i class="ph-check-circle text-success me-1"></i>Dibayar: ${new Date(order.paid_at).toLocaleString('id-ID')}</div>`
            : '';

        document.getElementById('detailContent').innerHTML = `
            <div class="px-4 pt-4 pb-3 border-bottom d-flex align-items-start justify-content-between gap-3">
                <div>
                    <div class="fw-bold fs-6 text-dark mb-1">Pesanan #${order.id}</div>
                    <div class="text-muted small"><i class="ph-clock me-1"></i>${new Date(order.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</div>
                </div>
                <div class="text-end">
                    <span class="badge ${badge} rounded-pill px-3 py-2 d-block mb-1">${statusName}</span>
                    <span class="badge ${payBadge.cls} rounded-pill px-2 py-1" style="font-size:11px;">${payBadge.label}</span>
                    ${paidAtHtml}
                </div>
            </div>

            <div class="row g-0">
                <div class="col-md-6 border-end p-4">
                    <div class="detail-section-label"><i class="ph-user-circle me-1"></i> Mitra Pemesan</div>
                    <div class="fw-semibold text-dark">${order.user?.name ?? '-'}</div>
                    <div class="text-muted small">${order.user?.email ?? ''}</div>
                    ${order.user?.phone ? `<div class="text-muted small"><i class="ph-phone me-1"></i>${order.user.phone}</div>` : ''}
                </div>
                <div class="col-md-6 p-4">
                    <div class="detail-section-label"><i class="ph-map-pin me-1"></i> Alamat Pengiriman</div>
                    <div class="small text-dark lh-base">
                        ${detail !== '-' ? `<div class="fw-semibold mb-1">${detail}</div>` : ''}
                        <div class="text-muted">${village !== '-' ? `Kel. ${village}, ` : ''}${district !== '-' ? `Kec. ${district}` : ''}</div>
                        <div class="text-muted">${regency !== '-' ? `${regency}, ` : ''}Sumatera Utara</div>
                    </div>
                </div>
            </div>

            <div class="px-4 pb-3 border-top">
                <div class="detail-section-label mt-3"><i class="ph-pill me-1"></i> Daftar Produk</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-uppercase fw-bold text-muted" style="font-size:.72rem;">
                                <th class="ps-3">Produk</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end pe-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>${itemsHtml}</tbody>
                        <tfoot class="border-top">
                            <tr>
                                <td colspan="3" class="text-end fw-bold ps-3">Total</td>
                                <td class="text-end fw-bold pe-3 text-primary">Rp ${grandTotal.toLocaleString('id-ID')}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            ${notesHtml ? `<div class="px-4 pb-4 border-top pt-3">${notesHtml}</div>` : ''}
        `;

        new bootstrap.Modal(document.getElementById('modalDetail')).show();
    });
}

// ─── SHIP MODAL ──────────────────────────────────────

let activeShipOrderId = null;

function openShipModal(orderId) {
    activeShipOrderId = orderId;
    axios.get('/api/couriers/with-status').then(res => {
        let html = '<option value="">-- Pilih Kurir --</option>';
        res.data.forEach(u => {
            const busyLabel = u.is_busy ? ' (Aktif)' : '';
            html += `<option value="${u.id}">${u.name}${busyLabel}</option>`;
        });
        document.getElementById('select_courier').innerHTML = html;
    });

    axios.get('/api/vehicles/with-status').then(res => {
        let html = '<option value="">-- Pilih Kendaraan --</option>';
        res.data.forEach(v => {
            const busyLabel = v.is_busy
                ? ` (Dipakai: ${v.courier_name})`
                : '';
            html += `<option value="${v.id}">${v.brand} ${v.subtype} - ${v.plate_number} (${v.color})${busyLabel}</option>`;
        });
        document.getElementById('select_vehicle').innerHTML = html;
    });
    new bootstrap.Modal(document.getElementById('modalShip')).show();
}

function submitShip() {
    const courierId = document.getElementById('select_courier').value;
    const vehicleId = document.getElementById('select_vehicle').value;
    if (!courierId) { Swal.fire({ icon: 'warning', title: 'Kurir belum dipilih', confirmButtonColor: '#5c6bc0' }); return; }
    if (!vehicleId) { Swal.fire({ icon: 'warning', title: 'Kendaraan belum dipilih', confirmButtonColor: '#5c6bc0' }); return; }

    const btn = document.getElementById('btnKirimSekarang');
    const originalHtml = btn.innerHTML;
    setButtonLoading(btn, true);

    axios.post(`/api/deliveries/ready/${activeShipOrderId}`, { courier_id: courierId, vehicle_id: vehicleId })
        .then(() => {
            setButtonLoading(btn, false, originalHtml);
            bootstrap.Modal.getInstance(document.getElementById('modalShip')).hide();
            Swal.fire({ icon: 'success', title: 'Pesanan Dikirim', text: 'Kurir telah ditugaskan.', confirmButtonColor: '#5c6bc0' });
            fetchOrders();
        })
        .catch(err => {
            setButtonLoading(btn, false, originalHtml);
            Swal.fire({ icon: 'error', title: 'Gagal', text: err.response?.data?.message ?? 'Terjadi kesalahan.', confirmButtonColor: '#d33' });
        });
}

</script>


{{-- =====================================================
     CSS
===================================================== --}}
<style>

.bg-indigo  { background: #5c6bc0 !important; }
.btn-indigo { background: #5c6bc0; color: #fff; border: none; }
.btn-indigo:hover { background: #4a5ab0; color: #fff; }
.btn-outline-indigo { border: 2px solid #5c6bc0; color: #5c6bc0; background: transparent; }
.btn-outline-indigo:hover, .btn-outline-indigo.active-toggle { background: #5c6bc0; color: #fff; }
.active-toggle { background: #5c6bc0 !important; color: #fff !important; }

.section-card { background: #f8fafc; border: 1px solid #e8edf3; border-radius: 18px; padding: 20px; }
.section-label { font-size: .78rem; font-weight: 700; text-transform: uppercase; color: #5c6bc0; letter-spacing: .04em; margin-bottom: 14px; }
.detail-section-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; color: #5c6bc0; letter-spacing: .05em; margin-bottom: 8px; }

.field-label { display: block; font-size: .75rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: .04em; margin-bottom: 6px; }
.form-field { border-radius: 14px; min-height: 50px; border: 1px solid #dbe4ee; padding: 10px 14px; font-size: .95rem; }
.form-field:focus { border-color: #5c6bc0; box-shadow: 0 0 0 3px rgba(92, 107, 192, .15); }

#select_province { background-color: #f1f5f9; color: #475569; cursor: not-allowed; opacity: 1; }

.product-item { border-radius: 18px !important; background: #fff !important; border: 1px solid #e8edf3 !important; }
.btn-delete-row { height: 50px; border-radius: 14px; }

/* Payment method cards */
.payment-method-card {
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px 10px;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    background: #fff;
}
.payment-method-card:hover { border-color: #5c6bc0; background: #f5f3ff; }
.payment-method-card.active { border-color: #5c6bc0; background: #eef0fb; box-shadow: 0 0 0 3px rgba(92,107,192,.15); }
.payment-method-card .pay-icon { font-size: 24px; color: #5c6bc0; margin-bottom: 4px; }
.payment-method-card .pay-label { font-weight: 700; font-size: .85rem; color: #2d3748; }
.payment-method-card .pay-desc { font-size: .72rem; color: #94a3b8; margin-top: 2px; }

#modalCreateOrder .modal-dialog { max-width: 1100px; }
#modalCreateOrder .modal-content { border-radius: 24px; overflow: hidden; }
#modalCreateOrder .modal-body { max-height: 72vh; overflow-y: auto; padding: 28px !important; }
#modalCreateOrder .modal-body::-webkit-scrollbar { width: 7px; }
#modalCreateOrder .modal-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 20px; }

@media (max-width: 768px) {
    #modalCreateOrder .modal-dialog { margin: 12px; max-width: 100%; }
    #modalCreateOrder .modal-body { max-height: 75vh; padding: 18px !important; }
}

</style>

@endsection
