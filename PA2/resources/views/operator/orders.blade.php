@extends('layouts.backoffice')

@section('page_title', 'Antrian Pesanan Produk')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0">Antrian Pesanan Logistik</h4>
            <div class="text-muted small">Validasi pengajuan obat dari unit kesehatan dan faskes mitra.</div>
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

    <!-- Table Section -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3">ID & Waktu</th>
                        <th>Mitra Pemesan (Unit)</th>
                        <th class="text-center">Total Item</th>
                        <th class="text-center">Metode</th>
                        <th class="text-center">Status</th>
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

<!-- MODAL: SIAP KIRIM & PILIH KURIR (REVISI BARU) -->
<div class="modal fade" id="modalReadyShipping" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-teal text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-truck me-2"></i>Konfirmasi Siap Kirim</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="shipping_order_id">
                <div class="mb-3">
                    <label class="small fw-bold text-muted mb-1">Tunjuk Kurir (Opsional)</label>
                    <select id="select_courier_shipping" class="form-select border-primary border-opacity-25">
                        <option value="">-- Biarkan Kosong (Masuk Bursa) --</option>
                    </select>
                    <small class="text-muted d-block mt-1">Jika kosong, semua kurir bisa mengambil tugas ini di bursa.</small>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button onclick="submitReadyShipping()" class="btn btn-teal text-white w-100 rounded-pill fw-bold">TERBITKAN RESI & KIRIM</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: DETAIL PESANAN LENGKAP -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-info me-2"></i>Rincian Lengkap Pesanan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="detailContent"></div>
            <div class="modal-footer border-0 bg-light">
                <div id="modalFooterActions" class="w-100 d-flex justify-content-between">
                    <button type="button" class="btn btn-link text-body fw-bold" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: BUAT PESANAN MANUAL -->
<div class="modal fade" id="modalCreateOrder" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-plus-circle me-2"></i>Buat Pesanan Manual</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCreateOrder" onsubmit="submitAdminOrder(event)">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Pilih Mitra (Customer)</label>
                        <select name="customer_id" id="select_customer" class="form-select" required></select>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="small fw-bold text-muted mb-1">Pilih Produk</label>
                            <select name="product_id" id="select_product" class="form-select" required></select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small fw-bold text-muted mb-1">Kuantitas</label>
                            <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Metode Pengiriman</label>
                        <select name="request_type" id="form_request_type" class="form-select" onchange="toggleCourierSelect()">
                            <option value="delivery">Kurir Logistik Internal</option>
                            <option value="self_pickup">Ambil Sendiri di Gudang</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="courier_select_group">
                        <label class="small fw-bold text-primary mb-1">Tunjuk Kurir (Opsional)</label>
                        <select name="courier_id" id="select_courier" class="form-select border-primary border-opacity-25 shadow-none">
                            <option value="">-- Masukkan ke Bursa Tugas --</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold text-muted mb-1">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="submit" class="btn btn-indigo w-100 rounded-pill fw-bold shadow-sm">SIMPAN PESANAN</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchOrders() {
        const tableBody = document.getElementById('orderTableBody');
        axios.get('/api/orders').then(res => {
            const orders = res.data;
            let html = '';
            if (!orders || orders.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted small">Belum ada antrian pesanan.</td></tr>';
                return;
            }
            orders.forEach(o => {
                const date = new Date(o.created_at).toLocaleString('id-ID', {day:'numeric', month:'short', hour:'2-digit', minute:'2-digit'});
                const statusName = o.status ? o.status.name : 'Unknown';
                const statusConfig = getStatusBadge(statusName.toLowerCase());
                const isPickup = o.product_order_delivery_id == 2;
                const deliveryId = o.delivery ? o.delivery.id : null;

                html += `
                <tr>
                    <td class="ps-3">
                        <div class="fw-bold text-indigo">#${o.id.substring(0,8)}</div>
                        <div class="fs-xs text-muted"><i class="ph-clock me-1"></i>${date}</div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">${o.user?.name || 'Customer'}</div>
                        <div class="fs-xs text-muted">${o.user?.email || '-'}</div>
                    </td>
                    <td class="text-center fw-bold">${o.items ? o.items.length : 0} Item</td>
                    <td class="text-center">
                        <span class="badge ${isPickup ? 'bg-orange bg-opacity-10 text-orange' : 'bg-info bg-opacity-10 text-info'} px-2 py-1">
                            <i class="${isPickup ? 'ph-storefront' : 'ph-truck'} me-1"></i> ${isPickup ? 'PICKUP' : 'DELIVERY'}
                        </span>
                    </td>
                    <td class="text-center"><span class="badge ${statusConfig.class} rounded-pill px-2 py-1">${statusName.toUpperCase()}</span></td>
                    <td class="text-center pe-3">
                        <div class="d-inline-flex gap-2">
                            <button onclick="viewDetail('${o.id}')" class="btn btn-sm btn-light text-indigo border-0 shadow-sm" title="Buka Rincian"><i class="ph-eye"></i></button>
                            ${deliveryId ? `<a href="/operator/tracking/${deliveryId}" class="btn btn-sm btn-light text-success border-0 shadow-sm"><i class="ph-map-pin-line"></i></a>` : ''}

                            ${statusName === 'Pending' ? `
                                <button onclick="approveOrder('${o.id}')" class="btn btn-sm btn-indigo rounded-pill px-3">Setujui</button>
                            ` : ''}

                            ${statusName === 'Processed' && !isPickup ? `
                                <button onclick="openShippingModal('${o.id}')" class="btn btn-sm btn-teal text-white rounded-pill px-3 shadow-sm">Siap Kirim</button>
                            ` : ''}

                            ${statusName === 'Processed' && isPickup ? `
                                <button onclick="completePickup('${o.id}')" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">Selesai Ambil</button>
                            ` : ''}
                        </div>
                    </td>
                </tr>`;
            });
            tableBody.innerHTML = html;
        });
    }

    // FUNGSI BARU: BUKA MODAL KIRIM DENGAN OPSI KURIR
    function openShippingModal(id) {
        document.getElementById('shipping_order_id').value = id;

        // Load data Kurir untuk dropdown
        axios.get('/api/users').then(res => {
            let opt = '<option value="">-- Biarkan Kosong (Masuk Bursa) --</option>';
            res.data.filter(u => u.roles && u.roles[0].name === 'courier').forEach(u => {
                opt += `<option value="${u.id}">${u.name}</option>`;
            });
            document.getElementById('select_courier_shipping').innerHTML = opt;
            new bootstrap.Modal(document.getElementById('modalReadyShipping')).show();
        });
    }

    // FUNGSI BARU: SUBMIT SIAP KIRIM
    function submitReadyShipping() {
        const id = document.getElementById('shipping_order_id').value;
        const courierId = document.getElementById('select_courier_shipping').value; // Mengambil ID kurir dari dropdown

        Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        // Mengirim courier_id ke backend
        axios.post(`/api/deliveries/ready/${id}`, {
            courier_id: courierId
        })
        .then(res => {
            bootstrap.Modal.getInstance(document.getElementById('modalReadyShipping')).hide();

            let message = courierId
                ? 'Kurir berhasil ditugaskan! Pesanan masuk ke tugas aktif kurir.'
                : 'Pesanan masuk ke Bursa Tugas.';

            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                confirmButtonColor: '#3fbbc0'
            });
            fetchOrders();
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Gagal', 'Terjadi kesalahan sistem saat menerbitkan resi.', 'error');
        });
    }

    function viewDetail(id) {
        const modalBody = document.getElementById('detailContent');
        const footerActions = document.getElementById('modalFooterActions');
        modalBody.innerHTML = '<div class="text-center p-5"><i class="ph-spinner spinner fs-1 text-indigo"></i></div>';
        new bootstrap.Modal(document.getElementById('modalDetail')).show();

        axios.get('/api/orders').then(res => {
            const o = res.data.find(order => order.id === id);
            if (!o) return;

            const isPickup = o.product_order_delivery_id == 2;
            const statusName = o.status ? o.status.name : 'Unknown';

            let itemsHtml = `
                <div class="p-3 bg-light border-bottom">
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <label class="fs-xs fw-bold text-muted text-uppercase mb-1">Informasi Pemesan</label>
                            <div class="fw-bold text-dark">${o.user.name}</div>
                            <div class="small text-muted mb-1"><i class="ph-envelope me-1"></i>${o.user.email}</div>
                            <div class="small text-muted mt-2 border-top pt-2">
                                <i class="ph-map-pin me-1 text-danger"></i>${o.user.address || 'Alamat tidak diatur'}
                            </div>
                        </div>
                        <div class="col-md-6 ps-md-4 mt-3 mt-md-0">
                            <label class="fs-xs fw-bold text-muted text-uppercase mb-1">Metode & Catatan</label>
                            <div class="mb-2">
                                <span class="badge ${isPickup ? 'bg-orange text-white' : 'bg-info text-white'} px-3 rounded-pill">
                                    ${isPickup ? 'Ambil Sendiri di Gudang' : 'Kirim via Kurir Internal'}
                                </span>
                            </div>
                            <div class="p-2 bg-white rounded border small text-muted">
                                <i class="ph-note me-1"></i>Catatan: ${o.notes || 'N/A'}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light text-uppercase fs-xs">
                            <tr><th class="ps-3">Produk</th><th class="text-center">QTY</th><th class="text-end pe-3">Harga</th></tr>
                        </thead>
                        <tbody>`;

            o.items.forEach(item => {
                itemsHtml += `
                    <tr>
                        <td class="ps-3 py-2">
                            <div class="fw-bold text-dark">${item.product?.name || 'Produk'}</div>
                            <div class="fs-xs text-muted">SKU: ${item.product?.sku}</div>
                        </td>
                        <td class="text-center fw-bold text-indigo">x ${item.quantity}</td>
                        <td class="text-end pe-3 small">Rp${Number(item.price_at_order).toLocaleString()}</td>
                    </tr>`;
            });

            itemsHtml += `</tbody></table></div>
                <div class="p-3 border-top bg-light text-end">
                    <div class="fs-xs text-muted text-uppercase">Total Tagihan</div>
                    <h4 class="fw-bold text-indigo mb-0">Rp${Number(o.total).toLocaleString()}</h4>
                </div>`;

            modalBody.innerHTML = itemsHtml;

            // Footer Actions dinamis
            if (isPickup && statusName === 'Processed') {
                footerActions.innerHTML = `<button type="button" class="btn btn-link text-body fw-bold" data-bs-dismiss="modal">Tutup</button>
                    <button onclick="completePickup('${o.id}')" class="btn btn-success px-4 rounded-pill shadow-sm fw-bold">KONFIRMASI SELESAI</button>`;
            } else if (!isPickup && statusName === 'Processed') {
                footerActions.innerHTML = `<button type="button" class="btn btn-link text-body fw-bold" data-bs-dismiss="modal">Tutup</button>
                    <button onclick="openShippingModal('${o.id}')" class="btn btn-teal text-white px-4 rounded-pill shadow-sm fw-bold">SIAP KIRIM</button>`;
            } else {
                footerActions.innerHTML = `<button type="button" class="btn btn-link text-body fw-bold" data-bs-dismiss="modal">Tutup</button>`;
            }
        });
    }

    function approveOrder(id) {
        Swal.fire({ title: 'Setujui Pesanan?', text: "Stok akan otomatis terpotong.", icon: 'question', showCancelButton: true, confirmButtonColor: '#5c6bc0' })
        .then(result => { if (result.isConfirmed) axios.post(`/api/orders/${id}/approve`).then(() => { Swal.fire('Berhasil', 'Pesanan disetujui.', 'success'); fetchOrders(); }); });
    }

    function completePickup(id) {
        axios.post(`/api/orders/${id}/complete-pickup`).then(() => { Swal.fire('Selesai!', 'Status Completed.', 'success'); fetchOrders(); });
    }

    function openCreateOrderModal() {
        axios.get('/api/users').then(res => {
            let opt = '<option value="" selected disabled>-- Pilih Mitra --</option>';
            res.data.filter(u => u.roles && u.roles[0].name === 'customer').forEach(u => opt += `<option value="${u.id}">${u.name}</option>`);
            document.getElementById('select_customer').innerHTML = opt;
        });
        axios.get('/api/products').then(res => {
            let opt = '<option value="" selected disabled>-- Pilih Produk --</option>';
            res.data.forEach(p => opt += `<option value="${p.id}">${p.name} (Stok: ${p.stock})</option>`);
            document.getElementById('select_product').innerHTML = opt;
        });
        axios.get('/api/users').then(res => {
            let opt = '<option value="">-- Masukkan ke Bursa Tugas --</option>';
            res.data.filter(u => u.roles && u.roles[0].name === 'courier').forEach(u => opt += `<option value="${u.id}">${u.name}</option>`);
            document.getElementById('select_courier').innerHTML = opt;
        });
        new bootstrap.Modal(document.getElementById('modalCreateOrder')).show();
    }

    function toggleCourierSelect() {
        const type = document.getElementById('form_request_type').value;
        document.getElementById('courier_select_group').classList.toggle('d-none', type === 'self_pickup');
    }

    function submitAdminOrder(e) {
        e.preventDefault();
        axios.post('/api/admin/orders', Object.fromEntries(new FormData(e.target))).then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalCreateOrder')).hide();
            Swal.fire('Berhasil!', 'Pesanan manual dibuat.', 'success');
            fetchOrders();
            e.target.reset();
        });
    }

    function getStatusBadge(status) {
        switch(status) {
            case 'pending':   return { class: 'bg-warning text-dark' };
            case 'processed': return { class: 'bg-info text-white' };
            case 'shipping':  return { class: 'bg-primary text-white' };
            case 'completed': return { class: 'bg-success text-white' };
            default:          return { class: 'bg-dark text-white' };
        }
    }

    document.addEventListener('DOMContentLoaded', fetchOrders);
</script>

<style>
    .bg-teal { background-color: #26a69a !important; }
    .btn-teal { background-color: #26a69a; color: #fff; }
    .btn-teal:hover { background-color: #00897b; color: #fff; }
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
    .btn-indigo { background-color: #5c6bc0; color: #fff; }
    .text-orange { color: #f59e0b; }
</style>
@endsection
