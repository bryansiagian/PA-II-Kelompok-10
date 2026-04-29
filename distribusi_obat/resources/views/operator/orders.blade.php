@extends('layouts.backoffice')

@section('page_title', 'Antrian Pesanan Produk')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
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

<!-- MODAL: SIAP KIRIM & PILIH KENDARAAN + KURIR -->
<div class="modal fade" id="modalReadyShipping" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-teal text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-truck me-2"></i>Konfirmasi Siap Kirim</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="shipping_order_id">

                <div class="mb-3">
                    <label class="small fw-bold text-dark mb-1 text-uppercase">1. Pilih Jenis Armada</label>
                    <select id="select_vehicle_shipping" class="form-select border-primary" onchange="filterCourierByVehicle()">
                        <option value="1">Sepeda Motor (Muatan Kecil)</option>
                        <option value="2">Mobil / Van (Muatan Besar)</option>
                    </select>
                </div>

                <div class="mb-0">
                    <label class="small fw-bold text-dark mb-1 text-uppercase">2. Tunjuk Kurir (Opsional)</label>
                    <select id="select_courier_shipping" class="form-select border-light">
                        <option value="">-- Masukkan ke Bursa Tugas --</option>
                    </select>
                    <small class="text-muted mt-1 d-block" id="courier_info_text">Menampilkan kurir yang tersedia untuk armada ini.</small>
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
                        <select name="request_type" id="form_request_type" class="form-select" onchange="toggleCourierSelectManual()">
                            <option value="delivery">Kurir Logistik Internal</option>
                            <option value="self_pickup">Ambil Sendiri di Gudang</option>
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

    let allCouriers = [];

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
                        <div class="fs-xs text-muted"><i class="bi bi-geo-alt me-1"></i>${o.regency || '-'}</div>
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

    async function openShippingModal(id) {
        document.getElementById('shipping_order_id').value = id;
        const courierSelect = document.getElementById('select_courier_shipping');
        courierSelect.innerHTML = '<option value="">Memuat data kurir...</option>';

        try {
            const resOrder = await axios.get('/api/orders');
            const o = resOrder.data.find(order => order.id === id);
            if(o) document.getElementById('select_vehicle_shipping').value = o.product_order_type_id;

            const resUser = await axios.get('/api/users');
            allCouriers = resUser.data.filter(u => u.roles && u.roles.some(r => r.name === 'courier'));

            filterCourierByVehicle();
            new bootstrap.Modal(document.getElementById('modalReadyShipping')).show();
        } catch (error) {
            Swal.fire('Error', 'Gagal mengambil data kurir', 'error');
        }
    }

    function filterCourierByVehicle() {
        const vehicleTypeId = document.getElementById('select_vehicle_shipping').value;
        const courierSelect = document.getElementById('select_courier_shipping');
        const targetType = (vehicleTypeId == '2') ? 'car' : 'motorcycle';

        const filtered = allCouriers.filter(c => c.courier_detail && c.courier_detail.vehicle_type === targetType);
        let html = '<option value="">-- Masukkan ke Bursa Tugas --</option>';

        if (filtered.length > 0) {
            filtered.forEach(c => html += `<option value="${c.id}">${c.name} [${c.courier_detail.vehicle_plate}]</option>`);
            document.getElementById('courier_info_text').innerHTML = `<span class="text-success fw-bold"><i class="ph-check-circle"></i> Tersedia ${filtered.length} kurir.</span>`;
        } else {
            document.getElementById('courier_info_text').innerHTML = `<span class="text-danger fw-bold"><i class="ph-warning"></i> Tidak ada kurir ${targetType} yang stand-by.</span>`;
        }
        courierSelect.innerHTML = html;
    }

    function submitReadyShipping() {
        const id = document.getElementById('shipping_order_id').value;
        const courierId = document.getElementById('select_courier_shipping').value;
        const typeId = document.getElementById('select_vehicle_shipping').value;

        Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        axios.post(`/api/deliveries/ready/${id}`, { courier_id: courierId, product_order_type_id: typeId }).then(res => {
            bootstrap.Modal.getInstance(document.getElementById('modalReadyShipping')).hide();
            Swal.fire('Berhasil!', 'Nomor resi diterbitkan.', 'success');
            fetchOrders();
        });
    }

    // MODIFIKASI FUNGSI VIEW DETAIL
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
            const displayAddress = o.shipping_address ? o.shipping_address : (o.user.address || 'Alamat tidak diatur');

            let itemsHtml = `
            <div class="p-3 bg-light border-bottom">
                <div class="row">
                    <div class="col-md-6 border-end">
                        <label class="fs-xs fw-bold text-muted text-uppercase mb-1">Informasi Pemesan</label>
                        <div class="fw-bold text-dark">${o.user.name}</div>
                        <div class="small text-muted mb-1"><i class="ph-envelope me-1"></i>${o.user.email}</div>
                        <label class="fs-xs fw-bold text-muted text-uppercase mt-2 mb-1">Tujuan Wilayah</label>
                        <div class="p-2 bg-white rounded border border-dashed mb-2">
                            <div class="fw-bold text-indigo small"><i class="bi bi-geo-fill me-1"></i>${o.regency || '-'}</div>
                            <div class="text-muted small">${o.district || '-'}, ${o.village || '-'}</div>
                        </div>
                    </div>
                    <div class="col-md-6 ps-md-4 mt-3 mt-md-0">
                        <label class="fs-xs fw-bold text-muted text-uppercase mb-1">Alamat Pengiriman</label>
                        <div class="p-2 bg-white rounded border small text-dark mb-3">
                            <i class="ph-map-pin me-1 text-danger"></i>${displayAddress}
                        </div>
                        <span class="badge ${isPickup ? 'bg-orange text-white' : 'bg-info text-white'} px-3 rounded-pill mb-2">${isPickup ? 'Ambil Sendiri' : 'Kirim Kurir'}</span>
                        <div class="small text-muted">Catatan: ${o.notes || 'N/A'}</div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light fs-xs">
                        <tr>
                            <th class="ps-3">Produk</th>
                            <th class="text-center">Lokasi (Gudang - Rak)</th>
                            <th class="text-center">QTY</th>
                            <th class="text-end pe-3">Harga</th>
                        </tr>
                    </thead>
                    <tbody>`;

            o.items.forEach(item => {
                // Logic pengambilan data gudang & rak dari produk
                const warehouseName = item.product?.warehouse?.name || 'N/A';
                const rackName = item.product?.rack?.name || 'N/A';

                itemsHtml += `
                <tr>
                    <td class="ps-3 py-2">
                        <div class="fw-bold text-dark">${item.product?.name || 'Produk'}</div>
                        <div class="fs-xs text-muted">SKU: ${item.product?.sku}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-indigo bg-opacity-10 text-indigo border border-indigo border-opacity-25 px-2">
                            <i class="ph-archive me-1"></i> ${warehouseName} - ${rackName}
                        </span>
                    </td>
                    <td class="text-center fw-bold text-indigo">x ${item.quantity}</td>
                    <td class="text-end pe-3 small">Rp${Number(item.price_at_order).toLocaleString()}</td>
                </tr>`;
            });

            itemsHtml += `</tbody></table></div><div class="p-3 border-top bg-light text-end"><h4 class="fw-bold text-indigo mb-0">Rp${Number(o.total).toLocaleString()}</h4></div>`;
            modalBody.innerHTML = itemsHtml;

            if (isPickup && statusName === 'Processed') {
                footerActions.innerHTML = `<button type="button" class="btn btn-link text-body fw-bold" data-bs-dismiss="modal">Tutup</button><button onclick="completePickup('${o.id}')" class="btn btn-success px-4 rounded-pill shadow-sm fw-bold">KONFIRMASI SELESAI</button>`;
            } else if (!isPickup && statusName === 'Processed') {
                footerActions.innerHTML = `<button type="button" class="btn btn-link text-body fw-bold" data-bs-dismiss="modal">Tutup</button><button onclick="openShippingModal('${o.id}')" class="btn btn-teal text-white px-4 rounded-pill shadow-sm fw-bold">SIAP KIRIM</button>`;
            } else {
                footerActions.innerHTML = `<button type="button" class="btn btn-link text-body fw-bold" data-bs-dismiss="modal">Tutup</button>`;
            }
        });
    }

    function approveOrder(id) {
        Swal.fire({ title: 'Setujui Pesanan?', icon: 'question', showCancelButton: true, confirmButtonColor: '#5c6bc0' }).then(result => { if (result.isConfirmed) axios.post(`/api/orders/${id}/approve`).then(() => { fetchOrders(); }); });
    }

    function completePickup(id) {
        axios.post(`/api/orders/${id}/complete-pickup`).then(() => { fetchOrders(); });
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
        new bootstrap.Modal(document.getElementById('modalCreateOrder')).show();
    }

    function submitAdminOrder(e) {
        e.preventDefault();
        axios.post('/api/admin/orders', Object.fromEntries(new FormData(e.target))).then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalCreateOrder')).hide();
            fetchOrders();
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
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
    .btn-indigo { background-color: #5c6bc0; color: #fff; }
    .text-orange { color: #f59e0b; }
</style>
@endsection
