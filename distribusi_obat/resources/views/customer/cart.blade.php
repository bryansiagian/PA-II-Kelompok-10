@extends('layouts.portal')

@section('content')
<style>
    :root {
        --primary: #00838f;
        --secondary: #2c4964;
        --hover-color: #006064;
    }

    .page-header { background: transparent; padding: 30px 0; border-bottom: none; margin-bottom: 20px; }
    .section-heading { color: var(--secondary); font-weight: 700; position: relative; padding-bottom: 15px; font-family: 'Poppins', sans-serif; font-size: 2rem; }
    .section-heading::after { content: ""; position: absolute; width: 50px; height: 4px; background: var(--primary); bottom: 0; left: 0; border-radius: 2px; }

    .card-cart { border: none; border-radius: 15px; background: #fff; border-left: 5px solid var(--primary); box-shadow: 0 2px 12px rgba(0,0,0,0.04); transition: 0.3s; margin-bottom: 15px; }
    .card-cart:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }

    .qty-control { background: #f8f9fa; border-radius: 30px; padding: 5px; border: 1px solid #eee; display: inline-flex; align-items: center; justify-content: center; }
    .btn-qty { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #dee2e6; color: var(--primary); font-weight: bold; cursor: pointer; transition: 0.2s; padding: 0; }
    .btn-qty:hover { background: var(--primary); color: #fff; border-color: var(--primary); }

    .card-summary { border: none; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); overflow: hidden; z-index: 10; }
    .btn-medinest { background: var(--primary); color: white !important; border-radius: 30px; padding: 12px 25px; font-weight: 600; border: none; width: 100%; transition: 0.3s; }
    .btn-medinest:hover:not(:disabled) { background: var(--hover-color); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 131, 143, 0.3); }
    .btn-medinest:disabled { background: #a5d8da; cursor: not-allowed; }

    .text-teal { color: var(--primary) !important; }
    .detail-label { font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; margin-bottom: 5px; display: block; }
    .address-box { background-color: #f8fcfc; border: 1px solid #e0eeee; border-radius: 15px; }

    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

    .ongkir-box { background: #f0fafa; border: 1px solid #c8e6e8; border-radius: 12px; padding: 10px 14px; }

    #paymentOverlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.55); z-index: 9999;
        align-items: center; justify-content: center;
        flex-direction: column; gap: 16px;
    }
    #paymentOverlay.show { display: flex; }
    #paymentOverlay .overlay-card {
        background: #fff; border-radius: 20px; padding: 36px 48px;
        text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    #paymentOverlay .overlay-card h5 { color: var(--secondary); font-weight: 700; margin-top: 16px; margin-bottom: 6px; }
    #paymentOverlay .overlay-card p { color: #888; font-size: 14px; margin: 0; }

    /* ── Modal Konfirmasi ── */
    #confirmOrderModal .modal-content { border-radius: 20px; border: none; overflow: hidden; }
    #confirmOrderModal .modal-header { background: #f0fafa; border-bottom: 1px solid #d8eeef; padding: 20px 24px 16px; }
    #confirmOrderModal .modal-body { background: #f8fcfc; padding: 20px 24px; }
    #confirmOrderModal .modal-footer { background: #f0fafa; border-top: 1px solid #d8eeef; padding: 16px 24px; }
    #confirmOrderModal .table thead th { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; color: #888; font-weight: 700; border-bottom: 1px solid #e8f0f1; background: #f0fafa; }
    #confirmOrderModal .table tbody td { vertical-align: middle; border-color: #f0f4f5; }
    #confirmOrderModal .confirm-block { background: #fff; border-radius: 14px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); padding: 14px 16px; margin-bottom: 12px; }
    .btn-pay-now { background: var(--primary); color: #fff !important; border-radius: 30px; padding: 10px 28px; font-weight: 600; border: none; transition: 0.3s; }
    .btn-pay-now:hover { background: var(--hover-color); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,131,143,0.3); }
    .btn-back-modal { border-radius: 30px; padding: 10px 24px; font-weight: 600; }
</style>

<!-- Midtrans Snap JS -->
<script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>

<!-- Payment Loading Overlay -->
<div id="paymentOverlay">
    <div class="overlay-card">
        <div class="spinner-border text-teal" style="width:3rem;height:3rem;" role="status"></div>
        <h5>Membuka Halaman Pembayaran</h5>
        <p>Jangan tutup halaman ini...</p>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     MODAL KONFIRMASI PESANAN (Detail Pesanan)
══════════════════════════════════════════════════ -->
<div class="modal fade" id="confirmOrderModal" tabindex="-1" aria-labelledby="confirmOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="confirmOrderModalLabel" style="color: var(--secondary);">
                        <i class="bi bi-receipt-cutoff me-2" style="color:var(--primary);"></i>Konfirmasi Pesanan
                    </h5>
                    <p class="text-muted small mb-0 mt-1">Periksa kembali sebelum melanjutkan ke pembayaran</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <!-- Detail Pesanan: Tabel Produk (nama, qty, subtotal) -->
                <div class="confirm-block p-0 overflow-hidden">
                    <div class="px-3 py-2 border-bottom" style="background:#f0fafa;">
                        <span class="fw-bold small" style="color:var(--secondary);">
                            <i class="bi bi-box-seam me-1"></i> Daftar Sediaan
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3 py-2">Produk</th>
                                    <th class="text-center py-2">Qty</th>
                                    <th class="text-end py-2">Harga Satuan</th>
                                    <th class="text-end pe-3 py-2">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="confirmOrderItems">
                                <!-- Diisi JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Info Pengiriman -->
                <div class="confirm-block">
                    <div class="row g-3">
                        <div class="col-6">
                            <span class="detail-label"><i class="bi bi-geo-alt me-1"></i>Tujuan</span>
                            <div class="small fw-semibold text-dark lh-sm" id="confirmDestination">—</div>
                        </div>
                        <div class="col-6">
                            <span class="detail-label"><i class="bi bi-truck me-1"></i>Metode</span>
                            <div class="small fw-semibold text-dark" id="confirmMethod">—</div>
                        </div>
                        <div class="col-12" id="confirmPhoneRow" style="display:none;">
                            <span class="detail-label"><i class="bi bi-telephone me-1"></i>No. Telepon</span>
                            <div class="small fw-semibold text-dark" id="confirmPhone">—</div>
                        </div>
                        <div class="col-12" id="confirmNotesRow" style="display:none;">
                            <span class="detail-label"><i class="bi bi-chat-left-text me-1"></i>Catatan</span>
                            <div class="small fw-semibold text-dark" id="confirmNotes">—</div>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Biaya: Subtotal & Total -->
                <div class="confirm-block mb-0">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Total Item</span>
                        <span class="small fw-semibold" id="confirmItemCount">—</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Subtotal Produk</span>
                        <span class="small fw-semibold" id="confirmSubtotal">—</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Ongkos Kirim</span>
                        <span class="small fw-semibold" id="confirmShipping">—</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold" style="color:var(--secondary);">Total Pembayaran</span>
                        <span class="fw-bold fs-5" style="color:var(--primary);" id="confirmTotal">—</span>
                    </div>
                </div>

            </div>

            <div class="modal-footer gap-2">
                <button type="button" class="btn btn-outline-secondary btn-back-modal" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </button>
                <button type="button" class="btn btn-pay-now" id="btnConfirmPay">
                    <i class="bi bi-credit-card me-2"></i>Bayar Sekarang
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Trigger tersembunyi (fallback reliable untuk munculkan modal) -->
<button id="btnTriggerModal" data-bs-toggle="modal" data-bs-target="#confirmOrderModal" style="display:none;"></button>

<!-- ══════════════════════════════════════════════════
     PAGE CONTENT
══════════════════════════════════════════════════ -->
<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h2 class="section-heading">Keranjang Permintaan</h2>
                <p class="text-muted">Tinjau daftar sediaan dan tentukan lokasi tujuan distribusi.</p>
            </div>
            <div class="col-md-5 text-md-end">
                <button id="btnClearCart" onclick="clearCart()" class="btn btn-outline-danger rounded-pill px-4 bg-white shadow-sm" style="display: none;">
                    <i class="bi bi-trash3 me-1"></i> Kosongkan
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row g-4">
        <!-- LIST ITEM -->
        <div class="col-lg-7">
            <div id="cartItemsContainer">
                <div class="text-center py-5">
                    <div class="spinner-border text-teal" role="status"></div>
                </div>
            </div>
        </div>

        <!-- SUMMARY & LOGISTICS -->
        <div class="col-lg-5">
            <div class="card card-summary sticky-top" style="top: 100px;">
                <div class="p-4 bg-light border-bottom text-center">
                    <h5 class="fw-bold m-0" style="color: var(--secondary);">Konfirmasi Logistik</h5>
                </div>
                <div class="card-body p-4">

                    <!-- WILAYAH SUMATERA UTARA -->
                    <div class="mb-4">
                        <label class="detail-label text-teal"><i class="bi bi-geo-alt-fill me-1"></i> Hub Regional Lokal (Sumut)</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <select id="regency" class="form-select form-select-sm shadow-sm" onchange="fetchDistricts(this.value)">
                                    <option value="" selected disabled>Pilih Kab/Kota</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select id="district" class="form-select form-select-sm shadow-sm" onchange="fetchVillages(this.value)" disabled>
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                            <div class="col-md-12 mt-2">
                                <select id="village" class="form-select form-select-sm shadow-sm" disabled>
                                    <option value="">Pilih Kelurahan/Desa</option>
                                </select>
                            </div>
                        </div>

                        <!-- ONGKIR DISPLAY -->
                        <div class="ongkir-box mt-3">
                            <label class="detail-label mb-1"><i class="bi bi-truck me-1"></i> Estimasi Ongkos Kirim</label>
                            <div id="shippingRateDisplay" class="fw-bold text-muted small">— Pilih wilayah hingga kelurahan</div>
                        </div>
                    </div>

                    <!-- ALAMAT TUJUAN -->
                    <div class="mb-4">
                        <label class="detail-label">Alamat Pengiriman</label>
                        <div class="address-box p-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="addr_type" id="addr_profile" value="profile" checked onchange="toggleAddrInput()">
                                <label class="form-check-label small fw-bold" for="addr_profile">Gunakan Alamat Akun</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="addr_type" id="addr_custom" value="custom" onchange="toggleAddrInput()">
                                <label class="form-check-label small fw-bold" for="addr_custom">Input Alamat Pengiriman Baru</label>
                            </div>
                            <textarea id="shipping_address" class="form-control form-control-sm mt-3 d-none border-0 shadow-sm" rows="3" placeholder="Masukkan nama jalan, nomor bangunan, blok..."></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="detail-label">Metode</label>
                            <select id="request_type" class="form-select form-select-sm">
                                <option value="delivery">Kirim Kurir</option>
                                <option value="self_pickup">Ambil Sendiri</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="detail-label">Total Item</label>
                            <div class="fw-bold fs-5 text-teal" id="totalQty">0</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="detail-label">Nomor Telepon Pemesan</label>
                        <input type="text" id="phone_order" class="form-control form-control-sm shadow-sm"
                            placeholder="Contoh: 08123456789">
                    </div>

                    <div class="mb-4">
                        <label class="detail-label">Catatan Tambahan</label>
                        <textarea id="checkoutNotes" class="form-control form-control-sm" rows="2" placeholder="Contoh: Unit Gawat Darurat..."></textarea>
                    </div>

                    <button id="btnCheckout" class="btn btn-medinest shadow-sm mb-3" disabled>
                        Lanjut ke Pembayaran <i class="bi bi-credit-card ms-2"></i>
                    </button>

                    <p class="text-muted text-center small mb-0">
                        <i class="bi bi-shield-lock me-1"></i> Pembayaran aman via Midtrans
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const apiToken = '{{ session('api_token') }}';
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + apiToken;

    const PROVINCE_ID = '12';

    // State global
    let _cartData      = [];
    let _checkoutData  = null;
    let _shippingCost  = 0;

    document.addEventListener('DOMContentLoaded', () => {
        fetchCart();
        fetchRegencies();
        document.getElementById('request_type').addEventListener('change', fetchShippingRate);

        // Tombol Bayar Sekarang di dalam modal
        document.getElementById('btnConfirmPay').addEventListener('click', submitCheckout);

        document.getElementById('btnCheckout').addEventListener('click', processCheckout);
    });

    // ── Wilayah ──────────────────────────────────────────────────

    async function fetchRegencies() {
        try {
            const res  = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${PROVINCE_ID}.json`);
            const data = await res.json();
            let html   = '<option value="" selected disabled>Pilih Kab/Kota</option>';
            data.forEach(item => {
                html += `<option value="${item.id}" data-name="${item.name}">${item.name}</option>`;
            });
            document.getElementById('regency').innerHTML = html;
        } catch (e) { console.error('Gagal muat kabupaten:', e); }
    }

    async function fetchDistricts(regencyId) {
        const distSel = document.getElementById('district');
        const villSel = document.getElementById('village');

        distSel.disabled = true;
        distSel.innerHTML = '<option>Memuat...</option>';
        villSel.disabled  = true;
        villSel.innerHTML = '<option value="" disabled selected>Pilih Kelurahan/Desa</option>';
        resetShippingDisplay();

        try {
            const res  = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${regencyId}.json`);
            const data = await res.json();
            let html   = '<option value="" selected disabled>Pilih Kecamatan</option>';
            data.forEach(item => {
                html += `<option value="${item.id}" data-name="${item.name}">${item.name}</option>`;
            });
            distSel.innerHTML = html;
            distSel.disabled  = false;
        } catch (e) { console.error('Gagal muat kecamatan:', e); }
    }

    async function fetchVillages(districtId) {
        const villSel = document.getElementById('village');
        villSel.disabled = true;
        villSel.innerHTML = '<option>Memuat...</option>';
        resetShippingDisplay();

        try {
            const res  = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${districtId}.json`);
            const data = await res.json();
            let html   = '<option value="" selected disabled>Pilih Kelurahan/Desa</option>';
            data.forEach(item => {
                html += `<option value="${item.id}" data-name="${item.name}">${item.name}</option>`;
            });
            villSel.innerHTML = html;
            villSel.disabled  = false;
            villSel.addEventListener('change', fetchShippingRate);
        } catch (e) { console.error('Gagal muat kelurahan:', e); }
    }

    function resetShippingDisplay() {
        _shippingCost = 0;
        document.getElementById('shippingRateDisplay').innerHTML = '— Pilih wilayah hingga kelurahan';
    }

    // ── Shipping Rate ─────────────────────────────────────────────

    async function fetchShippingRate() {
        const regSel  = document.getElementById('regency');
        const distSel = document.getElementById('district');
        const villSel = document.getElementById('village');
        const display = document.getElementById('shippingRateDisplay');
        const reqType = document.getElementById('request_type').value;

        if (!regSel.value || !distSel.value || !villSel.value) return;

        if (reqType === 'self_pickup') {
            _shippingCost = 0;
            display.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Gratis (Ambil Sendiri)</span>`;
            return;
        }

        display.innerHTML = `<span class="text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Menghitung...</span>`;

        try {
            const res  = await axios.get('/api/shipping-rate', {
                params: {
                    regency_id:   regSel.value,
                    district_id:  distSel.value,
                    village_id:   villSel.value,
                    request_type: reqType,
                }
            });

            _shippingCost = res.data.rate || 0;

            display.innerHTML = _shippingCost === 0
                ? `<span class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Gratis</span>`
                : `<span class="fw-bold" style="color:var(--primary);">Rp ${_shippingCost.toLocaleString('id-ID')}</span>`;

        } catch (e) {
            _shippingCost = 0;
            display.innerHTML = `<span class="text-danger small"><i class="bi bi-exclamation-circle me-1"></i>Gagal menghitung ongkir</span>`;
        }
    }

    // ── UI Helpers ────────────────────────────────────────────────

    function toggleAddrInput() {
        const isCustom = document.getElementById('addr_custom').checked;
        document.getElementById('shipping_address').classList.toggle('d-none', !isCustom);
    }

    // ── Cart ──────────────────────────────────────────────────────

    function fetchCart() {
        const container   = document.getElementById('cartItemsContainer');
        const btnCheckout = document.getElementById('btnCheckout');

        axios.get('/api/cart').then(res => {
            _cartData = res.data || [];
            let html  = '';
            let totalQty = 0;

            if (_cartData.length === 0) {
                btnCheckout.disabled = true;
                html = `
                <div class="text-center py-5 bg-white rounded-4 border p-5">
                    <i class="bi bi-cart-x text-muted opacity-25" style="font-size:5rem;color:#00838f;"></i>
                    <h4 class="fw-bold mt-3">Keranjang Kosong</h4>
                    <a href="/customer/products" class="btn btn-medinest px-5 mt-2 w-auto">Buka Katalog</a>
                </div>`;
            } else {
                btnCheckout.disabled = false;
                _cartData.forEach(item => {
                    const product = item.product || {};
                    totalQty += parseInt(item.quantity);
                    html += `
                    <div class="card card-cart">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-3 col-md-2">
                                    <img src="${product.image ? '/'+product.image : 'https://placehold.co/100'}" class="img-fluid rounded-3">
                                </div>
                                <div class="col-9 col-md-5">
                                    <h6 class="fw-bold text-dark mb-1">${product.name}</h6>
                                    <span class="badge badge-teal">Stok: ${product.stock}</span>
                                </div>
                                <div class="col-8 col-md-3 mt-3 mt-md-0">
                                    <div class="qty-control">
                                        <button class="btn-qty" onclick="changeQty('${item.id}', ${parseInt(item.quantity)-1}, ${product.stock})"><i class="bi bi-dash"></i></button>
                                        <input type="number" class="form-control text-center border-0 bg-transparent fw-bold" style="width:50px;" value="${item.quantity}" readonly>
                                        <button class="btn-qty" onclick="changeQty('${item.id}', ${parseInt(item.quantity)+1}, ${product.stock})"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 text-end">
                                    <button class="btn btn-link text-danger p-0" onclick="deleteItem('${item.id}')"><i class="bi bi-trash fs-5"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
            }

            container.innerHTML = html;
            document.getElementById('totalQty').innerText = totalQty;
        });
    }

    function changeQty(id, qty, max) {
        if (qty < 1) return deleteItem(id);
        if (qty > max) return Swal.fire('Stok Terbatas', `Sisa stok ${max} unit.`, 'warning');
        axios.put(`/api/cart/${id}`, { quantity: qty }).then(() => fetchCart());
    }

    function deleteItem(id) {
        axios.delete(`/api/cart/${id}`).then(() => fetchCart());
    }

    // ── STEP 1: Validasi → Render Modal → Tampilkan ───────────────

    function processCheckout() {
        console.log('processCheckout dipanggil');
        const regSel  = document.getElementById('regency');
        const distSel = document.getElementById('district');
        const villSel = document.getElementById('village');

        const regencyName  = regSel.options[regSel.selectedIndex]  ?.getAttribute('data-name') || '';
        const districtName = distSel.options[distSel.selectedIndex] ?.getAttribute('data-name') || '';
        const villageName  = villSel.options[villSel.selectedIndex] ?.getAttribute('data-name') || '';

        if (!regencyName || !districtName || !villageName) {
            return Swal.fire('Peringatan', 'Mohon pilih lokasi hingga tingkat Kelurahan.', 'warning');
        }

        // Simpan payload checkout
        _checkoutData = {
            regency:             regencyName,
            district:            districtName,
            village:             villageName,
            regency_id:          regSel.value,
            district_id:         distSel.value,
            village_id:          villSel.value,
            use_profile_address: document.getElementById('addr_profile').checked ? 1 : 0,
            shipping_address:    document.getElementById('shipping_address').value,
            phone_order:         document.getElementById('phone_order').value,
            request_type:        document.getElementById('request_type').value,
            notes:               document.getElementById('checkoutNotes').value,
        };

        // ── Render tabel produk (nama, qty, subtotal) ──
        let itemsHtml  = '';
        let subtotal   = 0;
        let totalItems = 0;

        _cartData.forEach(item => {
            const product    = item.product || {};
            const qty        = parseInt(item.quantity);
            const price      = parseFloat(product.price || 0);
            const lineTotal  = qty * price;
            subtotal        += lineTotal;
            totalItems      += qty;

            itemsHtml += `
            <tr>
                <td class="ps-3 py-2">
                    <div class="d-flex align-items-center gap-2">
                        <img src="${product.image ? '/'+product.image : 'https://placehold.co/40x40'}"
                             width="38" height="38" class="rounded-2 border"
                             style="object-fit:cover; flex-shrink:0;">
                        <span class="fw-semibold text-dark" style="font-size:0.85rem;">${product.name}</span>
                    </div>
                </td>
                <td class="text-center py-2">
                    <span class="badge rounded-pill px-3 py-1 fw-bold" style="background:#e8f5f6;color:var(--primary);font-size:0.85rem;">${qty}</span>
                </td>
                <td class="text-end py-2 text-muted small">
                    ${price > 0 ? 'Rp ' + price.toLocaleString('id-ID') : '—'}
                </td>
                <td class="text-end pe-3 py-2 fw-semibold" style="color:var(--secondary);">
                    ${lineTotal > 0 ? 'Rp ' + lineTotal.toLocaleString('id-ID') : '—'}
                </td>
            </tr>`;
        });

        document.getElementById('confirmOrderItems').innerHTML = itemsHtml;

        // ── Render info pengiriman ──
        document.getElementById('confirmDestination').innerHTML =
            `${villageName}, Kec. ${districtName}<br><small class="text-muted">${regencyName}</small>`;

        document.getElementById('confirmMethod').innerText =
            _checkoutData.request_type === 'self_pickup' ? 'Ambil Sendiri' : 'Kirim Kurir';

        const phone = _checkoutData.phone_order.trim();
        const phoneRow = document.getElementById('confirmPhoneRow');
        if (phone) {
            document.getElementById('confirmPhone').innerText = phone;
            phoneRow.style.display = 'block';
        } else {
            phoneRow.style.display = 'none';
        }

        const notes = _checkoutData.notes.trim();
        const notesRow = document.getElementById('confirmNotesRow');
        if (notes) {
            document.getElementById('confirmNotes').innerText = notes;
            notesRow.style.display = 'block';
        } else {
            notesRow.style.display = 'none';
        }

        // ── Render ringkasan biaya (subtotal & total) ──
        const grandTotal = subtotal + _shippingCost;

        document.getElementById('confirmItemCount').innerText = `${totalItems} item`;

        document.getElementById('confirmSubtotal').innerHTML = subtotal > 0
            ? `Rp ${subtotal.toLocaleString('id-ID')}`
            : `<span class="text-muted">—</span>`;

        document.getElementById('confirmShipping').innerHTML = _shippingCost === 0
            ? `<span class="text-success fw-semibold">Gratis</span>`
            : `Rp ${_shippingCost.toLocaleString('id-ID')}`;

        document.getElementById('confirmTotal').innerHTML = grandTotal > 0
            ? `Rp ${grandTotal.toLocaleString('id-ID')}`
            : `<span class="text-muted fs-6">(Dihitung saat proses)</span>`;

        // ── Tampilkan modal via hidden trigger (paling reliable) ──
        document.getElementById('btnTriggerModal').click();
    }

    // ── STEP 2: Submit → Midtrans ─────────────────────────────────

    function submitCheckout() {
        if (!_checkoutData) return;

        // Blur dulu tombol yang fokus, supaya tidak konflik dengan aria-hidden saat modal ditutup
        if (document.activeElement) document.activeElement.blur();

        // Tutup modal
        const modalEl = document.getElementById('confirmOrderModal');
        const modal   = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();

        // Tunggu modal selesai menutup baru tampilkan overlay & panggil Midtrans
        modalEl.addEventListener('hidden.bs.modal', function onHidden() {
            modalEl.removeEventListener('hidden.bs.modal', onHidden);
            document.getElementById('paymentOverlay').classList.add('show');

            axios.post('/api/orders', _checkoutData)
                .then(res => {
                    document.getElementById('paymentOverlay').classList.remove('show');

                    const snapToken = res.data.snap_token;
                    if (!snapToken) {
                        Swal.fire('Perhatian', 'Pesanan dibuat tapi gagal membuat token pembayaran. Silakan bayar dari halaman riwayat.', 'warning')
                            .then(() => window.location.href = '/customer/history');
                        return;
                    }

                    snap.pay(snapToken, {
                        onSuccess() {
                            Swal.fire({ icon: 'success', title: 'Pembayaran Berhasil!', text: 'Pesanan sedang menunggu konfirmasi admin.', confirmButtonColor: '#00838f' })
                                .then(() => window.location.href = '/customer/history');
                        },
                        onPending() {
                            Swal.fire({ icon: 'info', title: 'Pembayaran Pending', text: 'Selesaikan pembayaran sesegera mungkin.', confirmButtonColor: '#00838f' })
                                .then(() => window.location.href = '/customer/history');
                        },
                        onError() {
                            Swal.fire('Pembayaran Gagal', 'Silakan coba lagi dari halaman riwayat pesanan.', 'error')
                                .then(() => window.location.href = '/customer/history');
                        },
                        onClose() {
                            Swal.fire({ icon: 'warning', title: 'Pembayaran Dibatalkan', text: 'Pesanan tersimpan. Bayar kapan saja dari Riwayat Pesanan.', confirmButtonColor: '#00838f' })
                                .then(() => window.location.href = '/customer/history');
                        }
                    });
                })
                .catch(err => {
                    document.getElementById('paymentOverlay').classList.remove('show');
                    Swal.fire('Gagal', err.response?.data?.message || 'Error sistem', 'error');
                });
        }, { once: true });
    }
</script>
@endsection
