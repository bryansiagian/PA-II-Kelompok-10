@extends('layouts.portal')

@section('content')
<style>
    :root {
      --primary: #00838f;
      --secondary: #2c4964;
      --hover-color: #006064;
    }

    .page-header {
        background: transparent;
        padding: 30px 0;
        margin-bottom: 25px;
    }
    .section-heading {
        color: var(--secondary);
        font-weight: 700;
        position: relative;
        padding-bottom: 15px;
        font-size: 1.5rem;
    }
    .section-heading::after {
        content: "";
        position: absolute;
        display: block;
        width: 50px;
        height: 3px;
        background: var(--primary);
        bottom: 0;
        left: 0;
    }
    .card-history {
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease;
        background: #fff;
        border-left: 5px solid var(--primary);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .card-history:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    /* Card awaiting payment — border kuning */
    .card-history.awaiting {
        border-left-color: #f59e0b;
        background: #fffbeb;
    }

    .req-number {
        color: var(--primary);
        font-family: 'Ubuntu', sans-serif;
        font-size: 1.1rem;
    }
    .badge-status {
        font-size: 0.7rem;
        padding: 8px 12px;
        font-weight: 700;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
    }
    .btn-medinest {
        background: var(--primary);
        color: white !important;
        border-radius: 25px;
        padding: 8px 20px;
        transition: 0.3s;
        font-weight: 600;
        border: none;
    }
    .btn-medinest:hover {
        background: var(--hover-color);
        box-shadow: 0 4px 12px rgba(0, 131, 143, 0.3);
    }

    /* Tombol Bayar Sekarang */
    .btn-pay {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white !important;
        border-radius: 25px;
        padding: 8px 20px;
        transition: 0.3s;
        font-weight: 700;
        border: none;
        animation: pulse-pay 2s infinite;
    }
    .btn-pay:hover {
        background: linear-gradient(135deg, #d97706, #b45309);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.5);
        animation: none;
    }
    @keyframes pulse-pay {
        0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
        50%       { box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
    }

    .text-accent { color: var(--primary) !important; }
    .btn-outline-info { border-color: var(--primary); color: var(--primary); }
    .btn-outline-info:hover { background-color: var(--primary); border-color: var(--primary); color: #fff; }

    /* Badge payment status kecil */
    .payment-badge {
        font-size: 0.65rem;
        padding: 4px 8px;
        border-radius: 20px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    @media (max-width: 768px) {
        .page-header { padding: 20px 0; }
        .section-heading { font-size: 1.25rem; }
        .btn-group-mobile {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            width: 100%;
        }
        .btn-group-mobile .btn,
        .btn-group-mobile button,
        .btn-group-mobile a {
            border-radius: 10px !important;
            margin: 0 !important;
            width: 100%;
            text-align: center;
        }
        /* Tombol bayar full width di mobile */
        .btn-pay-wrapper { grid-column: 1 / -1; }
    }
</style>

<!-- Header Section -->
<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12 col-md-6 mb-3 mb-md-0 text-center text-md-start">
                <h2 class="section-heading d-inline-block d-md-block">Riwayat Permintaan</h2>
                <p class="text-muted small mb-0">Pantau distribusi logistik unit Anda secara real-time.</p>
            </div>
            <div class="col-12 col-md-6 text-center text-md-end">
                <button onclick="fetchHistory()" class="btn btn-white btn-sm rounded-pill px-4 border shadow-sm">
                    <i class="bi bi-arrow-clockwise me-1 text-accent"></i> Sinkronisasi
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Loading State -->
    <div id="loadingHistory" class="text-center py-5">
        <div class="spinner-border" role="status" style="width: 2.5rem; height: 2.5rem; color: var(--primary);"></div>
        <p class="mt-3 text-muted small fw-bold">Menghubungkan ke Server...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyHistory" class="text-center py-5 d-none bg-white rounded-4 shadow-sm border">
        <i class="bi bi-clipboard2-pulse opacity-25" style="font-size: 4rem; color: var(--primary);"></i>
        <h4 class="fw-bold mt-3" style="color: var(--secondary);">Belum Ada Permintaan</h4>
        <p class="text-muted small">Unit Anda belum melakukan pemesanan stok produk.</p>
        <a href="/customer/products" class="btn btn-medinest mt-2 px-5 shadow">Mulai Pesan</a>
    </div>

    <!-- History List -->
    <div id="historyList" class="row">
        <!-- JS Render -->
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header border-light">
                <h6 class="fw-bold mb-0" style="color: var(--secondary);">
                    <i class="bi bi-file-earmark-medical me-2 text-accent"></i>Rincian Item
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="detailContent"></div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill w-100" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     SCRIPT MIDTRANS SNAP
     ============================================================ --}}
<script src="{{ config('midtrans.snap_url') }}"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
    axios.defaults.headers.common['X-CSRF-TOKEN']  = '{{ csrf_token() }}';

    // ============================================================
    // FETCH HISTORY
    // ============================================================
    function fetchHistory() {
        const listContainer = document.getElementById('historyList');
        const loading       = document.getElementById('loadingHistory');
        const empty         = document.getElementById('emptyHistory');

        loading.classList.remove('d-none');
        listContainer.innerHTML = '';
        empty.classList.add('d-none');

        axios.get('/api/orders')
            .then(res => {
                loading.classList.add('d-none');
                const data = res.data;

                if (!data || data.length === 0) {
                    empty.classList.remove('d-none');
                    return;
                }

                let html = '';
                data.forEach(req => {
                    const statusName   = req.status ? req.status.name : 'Unknown';
                    const statusConfig = getStatusBadge(statusName.toLowerCase());

                    const date = new Date(req.created_at).toLocaleDateString('id-ID', {
                        day: 'numeric', month: 'short', year: 'numeric'
                    });

                    const isPickup   = req.product_order_delivery_id == 2;
                    const isAwaiting = statusName === 'Awaiting Payment';
                    let   statusText = statusName.toUpperCase();

                    if (statusName === 'Processed' && isPickup) {
                        statusText = 'SIAP DIAMBIL';
                    }

                    const canCancel  = statusName === 'Pending' || isAwaiting;
                    const deliveryId = req.delivery ? req.delivery.id : null;

                    // ---- Badge payment status ----
                    const payBadge     = getPaymentBadge(req.payment_status);
                    const payBadgeHtml = `
                        <span class="payment-badge ${payBadge.class} ms-1">
                            ${payBadge.icon} ${payBadge.label}
                        </span>`;

                    // ---- Badge estimasi tiba ----
                    const estimasiBadge = req.estimated_delivery_start && req.estimated_delivery_end ? `
                        <span class="payment-badge bg-light border text-dark ms-1">
                            <i class="bi bi-calendar-check text-accent"></i>
                            ${new Date(req.estimated_delivery_start).toLocaleDateString('id-ID', {day:'numeric', month:'short'})}
                            &ndash;
                            ${new Date(req.estimated_delivery_end).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'})}
                        </span>` : '';

                    // ---- Total ----
                    const totalHtml = `
                        <div class="small text-muted text-uppercase mb-1 fw-bold" style="font-size:9px;">Total</div>
                        <div class="fw-bold small text-accent">Rp${Number(req.total).toLocaleString('id-ID')}</div>`;

                    // ---- Tombol aksi ----
                    let actionHtml = `
                        <button onclick="viewDetail('${req.id}')" class="btn btn-outline-info rounded-pill px-3">
                            <i class="bi bi-eye"></i> Detail
                        </button>`;

                    if (isAwaiting) {
                        actionHtml += `
                        <div class="btn-pay-wrapper">
                            <button onclick="bayarSekarang('${req.id}')"
                                    class="btn btn-pay w-100"
                                    id="btnPay_${req.id}">
                                <i class="bi bi-credit-card me-1"></i> Bayar Sekarang
                            </button>
                        </div>`;
                    }

                    if ((statusName === 'Shipping' || statusName === 'Completed') && deliveryId) {
                        actionHtml += `
                        <a href="/customer/tracking/${deliveryId}" class="btn btn-medinest shadow-sm">
                            <i class="bi bi-geo-alt"></i> Lacak
                        </a>`;
                    }

                    if (canCancel) {
                        actionHtml += `
                        <button onclick="cancelRequest('${req.id}', '${statusName}')"
                                class="btn btn-outline-danger rounded-pill px-3">
                            <i class="bi bi-trash"></i> Batal
                        </button>`;
                    }

                    const awaitingAlert = isAwaiting ? `
                        <div class="alert alert-warning py-2 px-3 mb-3 small rounded-3 d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-circle-fill text-warning fs-5"></i>
                            <span>Segera selesaikan pembayaran Anda untuk memproses pesanan ini.</span>
                        </div>` : '';

                    html += `
                    <div class="col-12 mb-3" id="card_${req.id}">
                        <div class="card card-history ${isAwaiting ? 'awaiting' : ''}">
                            <div class="card-body p-3 p-md-4">
                                ${awaitingAlert}
                                <div class="row align-items-center">
                                    <div class="col-6 col-md-2">
                                        <div class="small text-muted text-uppercase mb-1 fw-bold" style="font-size: 9px;">ID Pesanan</div>
                                        <h6 class="req-number fw-bold mb-0 text-truncate">#${req.id.substring(0,8)}...</h6>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <div class="small text-muted text-uppercase mb-1 fw-bold" style="font-size: 9px;">Tanggal</div>
                                        <div class="text-dark fw-bold small">
                                            <i class="bi bi-calendar3 me-1 text-accent"></i>${date}
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-2 my-3 my-md-0">
                                        ${totalHtml}
                                    </div>
                                    <div class="col-6 col-md-3 my-3 my-md-0 text-center text-md-start">
                                        <div class="small text-muted text-uppercase mb-1 fw-bold" style="font-size: 9px;">Status</div>
                                        <div class="d-flex align-items-center flex-wrap gap-1">
                                            <span class="badge rounded-pill badge-status ${statusConfig.class}">
                                                ${statusConfig.icon} ${statusText}
                                            </span>
                                            ${payBadgeHtml}
                                        </div>
                                        ${req.estimated_delivery_start && req.estimated_delivery_end ? `
                                            <div class="mt-1 d-flex align-items-center gap-1" style="font-size:10px; color:#555;">
                                                <i class="bi bi-calendar-check text-accent"></i>
                                                <span class="fw-semibold">
                                                    ${new Date(req.estimated_delivery_start).toLocaleDateString('id-ID', {day:'numeric', month:'short'})}
                                                    &ndash;
                                                    ${new Date(req.estimated_delivery_end).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'})}
                                                </span>
                                            </div>` : ''}
                                    </div>
                                    <div class="col-12 col-md-3 text-center text-md-end">
                                        <div class="btn-group-mobile d-flex flex-wrap gap-2 justify-content-end">
                                            ${actionHtml}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });

                listContainer.innerHTML = html;
            })
            .catch(err => {
                loading.classList.add('d-none');
                console.error(err);
            });
    }

    // ============================================================
    // BAYAR SEKARANG — ambil Snap token lalu tampilkan popup
    // ============================================================
    function bayarSekarang(orderId) {
        const btn = document.getElementById('btnPay_' + orderId);
        const originalHtml = btn.innerHTML;

        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memuat...';

        axios.get('/api/payment/token/' + orderId)
            .then(res => {
                const snapToken = res.data.snap_token;

                window.snap.pay(snapToken, {
                    onSuccess: function(result) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pembayaran Berhasil!',
                            text: 'Pesanan Anda sedang diproses oleh admin.',
                            confirmButtonColor: 'var(--primary)',
                        }).then(() => fetchHistory());
                    },
                    onPending: function(result) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Menunggu Pembayaran',
                            text: 'Silakan selesaikan pembayaran sesuai instruksi.',
                            confirmButtonColor: 'var(--primary)',
                        }).then(() => fetchHistory());
                    },
                    onError: function(result) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Pembayaran Gagal',
                            text: 'Silakan coba lagi.',
                            confirmButtonColor: 'var(--primary)',
                        });
                        btn.disabled  = false;
                        btn.innerHTML = originalHtml;
                    },
                    onClose: function() {
                        btn.disabled  = false;
                        btn.innerHTML = originalHtml;
                    }
                });
            })
            .catch(err => {
                Swal.fire('Gagal', err.response?.data?.message ?? 'Tidak dapat memuat pembayaran.', 'error');
                btn.disabled  = false;
                btn.innerHTML = originalHtml;
            });
    }

    // ============================================================
    // HELPER: STATUS BADGE
    // ============================================================
    function getStatusBadge(status) {
        switch(status) {
            case 'pending':           return { class: 'bg-warning text-dark',   icon: '<i class="bi bi-hourglass-split me-1"></i>' };
            case 'processed':         return { class: 'bg-info text-white',     icon: '<i class="bi bi-check2-all me-1"></i>' };
            case 'rejected':          return { class: 'bg-danger text-white',   icon: '<i class="bi bi-x-octagon me-1"></i>' };
            case 'shipping':          return { class: 'bg-primary text-white',  icon: '<i class="bi bi-truck me-1"></i>' };
            case 'completed':         return { class: 'bg-success text-white',  icon: '<i class="bi bi-check-circle-fill me-1"></i>' };
            case 'cancelled':         return { class: 'bg-secondary text-white',icon: '<i class="bi bi-slash-circle me-1"></i>' };
            case 'awaiting payment':  return { class: 'bg-warning text-dark',   icon: '<i class="bi bi-credit-card me-1"></i>' };
            default:                  return { class: 'bg-dark text-white',     icon: '' };
        }
    }

    function getPaymentBadge(paymentStatus) {
        switch(paymentStatus) {
            case 'paid':     return { class: 'bg-success text-white',   icon: '<i class="bi bi-check-circle-fill"></i>', label: 'Lunas' };
            case 'refunded': return { class: 'bg-warning text-dark',    icon: '<i class="bi bi-arrow-counterclockwise"></i>', label: 'Refund' };
            case 'cash':     return { class: 'bg-info text-white',      icon: '<i class="bi bi-cash-coin"></i>', label: 'Tunai' };
            default:         return { class: 'bg-danger text-white',    icon: '<i class="bi bi-clock"></i>', label: 'Belum Bayar' };
        }
    }

    // ============================================================
    // VIEW DETAIL
    // ============================================================
    function viewDetail(id) {
        const modalBody = document.getElementById('detailContent');
        modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border spinner-border-sm" style="color: var(--primary);"></div></div>';
        new bootstrap.Modal(document.getElementById('modalDetail')).show();

        axios.get('/api/orders').then(res => {
            const order = res.data.find(r => r.id === id);
            if (!order) return;

            const payBadge = getPaymentBadge(order.payment_status);

            let itemsHtml = '<div class="list-group list-group-flush small">';
            order.items.forEach(item => {
                const name = item.product ? item.product.name : 'Produk tidak ditemukan';
                itemsHtml += `
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-0 border-light">
                    <div>
                        <div class="fw-bold text-dark">${name}</div>
                        <small class="text-muted">Harga: Rp${Number(item.price_at_order).toLocaleString()}</small>
                    </div>
                    <span class="badge bg-light text-accent rounded-pill border px-2">Qty: ${item.quantity}</span>
                </div>`;
            });

            if (order.product_order_delivery_cost > 0) {
                itemsHtml += `
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-0 border-light">
                    <div class="text-muted">Biaya Pengiriman</div>
                    <span class="text-dark fw-bold small">Rp${Number(order.product_order_delivery_cost).toLocaleString()}</span>
                </div>`;
            }

            if (order.product_order_discount > 0) {
                itemsHtml += `
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-0 border-light">
                    <div class="text-success">Diskon</div>
                    <span class="text-success fw-bold small">- Rp${Number(order.product_order_discount).toLocaleString()}</span>
                </div>`;
            }

            itemsHtml += `
                <div class="mt-3 pt-2 border-top">
                    <div class="d-flex justify-content-between fw-bold text-accent mb-2">
                        <span>Total Pembayaran</span>
                        <span>Rp${Number(order.total).toLocaleString()}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Status Pembayaran</span>
                        <span class="payment-badge ${payBadge.class}">
                            ${payBadge.icon} ${payBadge.label}
                        </span>
                    </div>
                    ${order.paid_at ? `
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="text-muted small">Dibayar pada</span>
                        <span class="text-dark small fw-bold">
                            ${new Date(order.paid_at).toLocaleString('id-ID')}
                        </span>
                    </div>` : ''}
                    ${order.estimated_delivery_start && order.estimated_delivery_end ? `
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                        <span class="text-muted small"><i class="bi bi-calendar-check me-1 text-accent"></i>Estimasi Tiba</span>
                        <span class="text-dark small fw-bold">
                            ${new Date(order.estimated_delivery_start).toLocaleDateString('id-ID', {day:'numeric',month:'short',year:'numeric'})}
                            &ndash;
                            ${new Date(order.estimated_delivery_end).toLocaleDateString('id-ID', {day:'numeric',month:'short',year:'numeric'})}
                        </span>
                    </div>` : ''}
                </div>`;
            itemsHtml += '</div>';
            modalBody.innerHTML = itemsHtml;
        });
    }

    // ============================================================
    // CANCEL ORDER
    // ============================================================
    function cancelRequest(id, statusName) {
        const isAwaiting = statusName === 'Awaiting Payment';
        const warningText = isAwaiting
            ? 'Pesanan yang belum dibayar ini akan dibatalkan.'
            : 'Pesanan yang sudah dibayar akan diproses refund ke metode pembayaran Anda.';

        Swal.fire({
            title: 'Batalkan Pesanan?',
            text: warningText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: 'var(--primary)',
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Kembali'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post(`/api/orders/${id}/cancel`)
                    .then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: isAwaiting ? 'Pesanan Dibatalkan' : 'Dibatalkan & Refund Diproses',
                            text: isAwaiting ? '' : 'Dana akan dikembalikan dalam 1-3 hari kerja.',
                            confirmButtonColor: 'var(--primary)'
                        });
                        fetchHistory();
                    })
                    .catch(err => {
                        Swal.fire('Gagal', err.response?.data?.message ?? 'Terjadi kesalahan.', 'error');
                    });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchHistory);
</script>
@endsection
