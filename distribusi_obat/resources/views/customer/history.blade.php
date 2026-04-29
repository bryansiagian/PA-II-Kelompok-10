@extends('layouts.portal')

@section('content')
<style>
    :root {
      --primary: #00838f;
      --secondary: #2c4964;
      --hover-color: #006064;
    }

    /* Custom Styling untuk menyelaraskan dengan MediNest */
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

    .text-accent { color: var(--primary) !important; }
    .btn-outline-info { border-color: var(--primary); color: var(--primary); }
    .btn-outline-info:hover { background-color: var(--primary); border-color: var(--primary); color: #fff; }

    @media (max-width: 768px) {
        .page-header { padding: 20px 0; }
        .section-heading { font-size: 1.25rem; }
        .btn-group-mobile {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            width: 100%;
        }
        .btn-group-mobile .btn {
            border-radius: 10px !important;
            margin: 0 !important;
            width: 100%;
        }
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
                <h6 class="fw-bold mb-0" style="color: var(--secondary);"><i class="bi bi-file-earmark-medical me-2 text-accent"></i>Rincian Item</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="detailContent"></div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill w-100" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchHistory() {
        const listContainer = document.getElementById('historyList');
        const loading = document.getElementById('loadingHistory');
        const empty = document.getElementById('emptyHistory');

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
                    const statusName = req.status ? req.status.name : 'Unknown';
                    const statusConfig = getStatusBadge(statusName.toLowerCase());

                    const date = new Date(req.created_at).toLocaleDateString('id-ID', {
                        day: 'numeric', month: 'short', year: 'numeric'
                    });

                    const isPickup = req.product_order_delivery_id == 2;
                    let statusText = statusName.toUpperCase();

                    if (statusName === 'Processed' && isPickup) {
                        statusText = "SIAP DIAMBIL";
                    }

                    const canCancel = statusName === 'Pending';
                    const deliveryId = req.delivery ? req.delivery.id : null;

                    html += `
                    <div class="col-12 mb-3">
                        <div class="card card-history">
                            <div class="card-body p-3 p-md-4">
                                <div class="row align-items-center">
                                    <div class="col-6 col-md-3">
                                        <div class="small text-muted text-uppercase mb-1 fw-bold" style="font-size: 9px;">ID Pesanan</div>
                                        <h6 class="req-number fw-bold mb-0 text-truncate">#${req.id.substring(0,8)}...</h6>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="small text-muted text-uppercase mb-1 fw-bold" style="font-size: 9px;">Tanggal</div>
                                        <div class="text-dark fw-bold small"><i class="bi bi-calendar3 me-1 text-accent"></i>${date}</div>
                                    </div>
                                    <div class="col-12 col-md-3 my-3 my-md-0 text-center text-md-start">
                                        <div class="small text-muted text-uppercase mb-1 fw-bold" style="font-size: 9px;">Status Logistik</div>
                                        <span class="badge rounded-pill badge-status ${statusConfig.class}">
                                            ${statusConfig.icon} ${statusText}
                                        </span>
                                    </div>
                                    <div class="col-12 col-md-3 text-center text-md-end">
                                        <div class="btn-group-mobile">
                                            <button onclick="viewDetail('${req.id}')" class="btn btn-outline-info rounded-pill px-3">
                                                <i class="bi bi-eye"></i> Detail
                                            </button>

                                            ${(statusName === 'Shipping' || statusName === 'Completed') && deliveryId ? `
                                                <a href="/customer/tracking/${deliveryId}" class="btn btn-medinest shadow-sm">
                                                    <i class="bi bi-geo-alt"></i> Lacak
                                                </a>
                                            ` : ''}

                                            ${canCancel ? `
                                                <button onclick="cancelRequest('${req.id}')" class="btn btn-outline-danger rounded-pill px-3">
                                                    <i class="bi bi-trash"></i> Batal
                                                </button>
                                            ` : ''}
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

    function getStatusBadge(status) {
        switch(status) {
            case 'pending':   return { class: 'bg-warning text-dark', icon: '<i class="bi bi-hourglass-split me-1"></i>' };
            case 'processed': return { class: 'bg-info text-white', icon: '<i class="bi bi-check2-all me-1"></i>' };
            case 'rejected':  return { class: 'bg-danger text-white', icon: '<i class="bi bi-x-octagon me-1"></i>' };
            case 'shipping':  return { class: 'bg-primary text-white', icon: '<i class="bi bi-truck me-1"></i>' };
            case 'completed': return { class: 'bg-success text-white', icon: '<i class="bi bi-check-circle-fill me-1"></i>' };
            case 'cancelled': return { class: 'bg-secondary text-white', icon: '<i class="bi bi-slash-circle me-1"></i>' };
            default:          return { class: 'bg-dark text-white', icon: '' };
        }
    }

    function viewDetail(id) {
        const modalBody = document.getElementById('detailContent');
        modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border spinner-border-sm" style="color: var(--primary);"></div></div>';
        new bootstrap.Modal(document.getElementById('modalDetail')).show();

        axios.get(`/api/orders`).then(res => {
            const order = res.data.find(r => r.id === id);
            if(!order) return;

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
            itemsHtml += `
                <div class="mt-3 pt-2 border-top">
                    <div class="d-flex justify-content-between fw-bold text-accent">
                        <span>Total Pembayaran</span>
                        <span>Rp${Number(order.total).toLocaleString()}</span>
                    </div>
                </div>
            `;
            itemsHtml += '</div>';
            modalBody.innerHTML = itemsHtml;
        });
    }

    function cancelRequest(id) {
        Swal.fire({
            title: 'Batalkan Pesanan?',
            text: 'Pesanan yang dibatalkan tidak dapat diproses kembali.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: 'var(--primary)',
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Kembali'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post(`/api/orders/${id}/cancel`).then(() => {
                    Swal.fire({ icon: 'success', title: 'Dibatalkan', confirmButtonColor: 'var(--primary)' });
                    fetchHistory();
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchHistory);
</script>
@endsection
