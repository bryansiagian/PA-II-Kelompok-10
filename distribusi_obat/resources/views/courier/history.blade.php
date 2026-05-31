@extends('layouts.backoffice')

@section('page_title', 'Riwayat Pengiriman')

@section('content')
<div class="container-fluid">

    <!-- HEADER -->
    <div class="d-sm-flex align-items-sm-center justify-content-sm-between mb-3">

        <div class="flex-fill">
            <h4 class="fw-bold mb-0">Arsip Pengiriman Selesai</h4>
            <div class="text-muted small">Rekam jejak seluruh paket yang telah berhasil Anda antarkan</div>
        </div>

        <div class="mt-3 mt-sm-0 ms-sm-3">
            <button onclick="fetchHistory()" class="btn btn-indigo rounded-pill px-3 shadow-sm fw-bold">
                <i class="ph-arrows-clockwise me-2"></i>Perbarui Riwayat
            </button>
        </div>

    </div>

    <!-- CARD -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">

        <!-- CARD HEADER -->
        <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
            <h6 class="mb-0 fw-bold">
                <i class="ph-clock-counter-clockwise me-2 text-success"></i>Log Aktivitas Selesai
            </h6>
            <div class="ms-auto">
                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 rounded-pill">Total Completed</span>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3" style="width:220px;">No. Resi & Waktu</th>
                        <th>Penerima & Lokasi</th>
                        <th>Rincian Barang</th>
                        <th class="text-center">Bukti Foto</th>
                        <th class="text-center pe-3">Status</th>
                    </tr>
                </thead>

                <tbody id="historyTable"></tbody>

            </table>
        </div>

    </div>

</div>

<!-- MODAL DETAIL BARANG -->
<div class="modal fade" id="modalItems" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">

            <div class="modal-header bg-indigo text-white border-0">
                <h6 class="modal-title fw-bold">
                    <i class="ph-package me-2"></i>Daftar Obat Terkirim
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0" id="modalItemsBody"></div>

            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">TUTUP</button>
            </div>

        </div>
    </div>
</div>

<script>

/* =========================
   SKELETON
========================= */
function showSkeletons() {
    let html = '';
    for (let i = 0; i < 6; i++) {
        html += `
        <tr class="border-bottom">

            <!-- RESI -->
            <td class="ps-3 py-3">
                <span class="skeleton-line d-block mb-2" style="width:${130 + i * 8}px;height:14px;"></span>
                <span class="skeleton-line d-block" style="width:100px;height:11px;"></span>
            </td>

            <!-- PENERIMA -->
            <td>
                <span class="skeleton-line d-block mb-2" style="width:${110 + i * 10}px;height:14px;"></span>
                <span class="skeleton-line d-block" style="width:${160 + i * 6}px;height:11px;"></span>
            </td>

            <!-- ITEMS -->
            <td>
                <span class="skeleton-line" style="width:110px;height:30px;border-radius:999px;"></span>
            </td>

            <!-- FOTO -->
            <td class="text-center">
                <span class="skeleton-line" style="width:70px;height:70px;border-radius:12px;"></span>
            </td>

            <!-- STATUS -->
            <td class="text-center pe-3">
                <span class="skeleton-line" style="width:90px;height:28px;border-radius:999px;"></span>
            </td>

        </tr>`;
    }
    document.getElementById('historyTable').innerHTML = html;
}

/* =========================
   FETCH HISTORY
========================= */
function fetchHistory() {

    showSkeletons();

    const tableBody = document.getElementById('historyTable');

    axios.get('/api/deliveries/history')
    .then(res => {

        let html = '';
        const historyData = res.data;

        if (!historyData || historyData.length === 0) {

            html = `
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted small">
                        Anda belum memiliki riwayat pengiriman selesai.
                    </td>
                </tr>`;

        } else {

            historyData.forEach(d => {

                const deliveredAt = d.delivered_at ? new Date(d.delivered_at) : null;

                const dateStr = deliveredAt
                    ? deliveredAt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
                    : '-';

                const timeStr = deliveredAt
                    ? deliveredAt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                    : '-';

                const order        = d.order || {};
                const user         = order.user || {};
                const items        = order.items || [];

                const customerName =
                    d.receiver_name ||
                    user.name ||
                    order.receiver_name ||
                    'Penerima Tidak Diketahui';

                const customerAddr =
                    d.delivery_address ||
                    order.shipping_address ||
                    user.address ||
                    order.address ||
                    'Alamat tidak tersedia';

                const itemsJson = JSON.stringify(items).replace(/"/g, '&quot;');

                let photoUrl = '';
                const rawPhoto = d.image || d.proof_photo || '';
                if (rawPhoto) {
                    const clean = rawPhoto.replace(/^\/+/, '');
                    photoUrl = clean.startsWith('storage/') ? '/' + clean : '/storage/' + clean;
                }

                html += `
                <tr class="border-bottom">

                    <td class="ps-3">
                        <div class="fw-bold text-indigo" style="font-family:monospace;">#${d.tracking_number || '-'}</div>
                        <div class="fs-xs text-muted mt-1">
                            <i class="ph-calendar-check me-1 text-success"></i>${dateStr} - ${timeStr}
                        </div>
                    </td>

                    <td>
                        <div class="fw-bold text-dark">${customerName}</div>
                        <div class="fs-xs text-muted text-truncate" style="max-width:250px;">
                            <i class="ph-map-pin me-1 text-danger"></i>${customerAddr}
                        </div>
                    </td>

                    <td>
                        <button onclick="showItems('${itemsJson}')"
                            class="btn btn-sm btn-light border rounded-pill px-3 fw-bold text-indigo">
                            <i class="ph-list me-1"></i>${items.length} Macam Obat
                        </button>
                    </td>

                    <td class="text-center">
                        ${photoUrl
                            ? `<a href="${photoUrl}" target="_blank">
                                   <img src="${photoUrl}" alt="Bukti Foto"
                                        onerror="this.parentElement.parentElement.innerHTML='<span class=\\'text-muted small\\'>Foto tidak ditemukan</span>'"
                                        style="width:70px;height:70px;object-fit:cover;border-radius:12px;cursor:pointer;border:1px solid #ddd;">
                               </a>`
                            : `<span class="text-muted small">Tidak ada foto</span>`
                        }
                    </td>

                    <td class="text-center pe-3">
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 rounded-pill">
                            <i class="ph-check-circle me-1"></i>DELIVERED
                        </span>
                    </td>

                </tr>`;
            });
        }

        tableBody.innerHTML = html;
    })
    .catch(err => {
        console.error(err);
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-5 text-danger small">
                    Gagal mengambil riwayat pengiriman.
                </td>
            </tr>`;
    });
}

/* =========================
   SHOW ITEMS
========================= */
function showItems(encoded) {
    try {
        const items = JSON.parse(encoded);
        let html = '<div class="list-group list-group-flush">';

        if (items.length === 0) {
            html += `<div class="p-4 text-center text-muted">Data item tidak tersedia.</div>`;
        } else {
            items.forEach(i => {
                const productName = i.product?.name || 'Produk';
                const qty         = i.quantity || 0;
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-indigo bg-opacity-10 p-2 rounded-3 me-3 text-indigo">
                                <i class="ph-pill fs-4"></i>
                            </div>
                            <div class="fw-bold text-dark small">${productName}</div>
                        </div>
                        <span class="badge bg-indigo rounded-pill px-3">x${qty}</span>
                    </div>`;
            });
        }

        html += '</div>';
        document.getElementById('modalItemsBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('modalItems')).show();

    } catch(e) {
        console.error(e);
    }
}

/* =========================
   INIT
========================= */
document.addEventListener('DOMContentLoaded', fetchHistory);

</script>

<style>
.bg-indigo   { background-color: #5c6bc0 !important; }
.text-indigo { color: #5c6bc0 !important; }
.btn-indigo  { background-color: #5c6bc0; color: #fff; border: none; }
.btn-indigo:hover { background-color: #3f51b5; color: #fff; }

.table td  { padding: 0.85rem 1.25rem; }
.table th  { border-top: none !important; }
.fs-xs     { font-size: 0.75rem; }
.bg-opacity-10 { --bs-bg-opacity: 0.1; }

#modalItemsBody { max-height: 450px; overflow-y: auto; }

/* ── Skeleton loading ──────────────────────────────────────────────────── */
@keyframes shimmer {
    0%   { background-position: -400px 0; }
    100% { background-position:  400px 0; }
}

.skeleton-line {
    display: inline-block;
    border-radius: 6px;
    background: linear-gradient(90deg, #e8e8e8 25%, #f5f5f5 50%, #e8e8e8 75%);
    background-size: 800px 100%;
    animation: shimmer 1.4s infinite linear;
}
</style>

@endsection
