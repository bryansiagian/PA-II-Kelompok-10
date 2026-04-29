@extends('layouts.backoffice')

@section('page_title', 'Riwayat Pengiriman')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-sm-flex align-items-sm-center justify-content-sm-between mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0">Arsip Pengiriman Selesai</h4>
            <div class="text-muted small">Rekam jejak seluruh paket yang telah berhasil Anda antarkan</div>
        </div>
        <div class="mt-3 mt-sm-0 ms-sm-3">
            <button onclick="fetchHistory()" class="btn btn-indigo rounded-pill px-3 shadow-sm fw-bold">
                <i class="ph-arrows-clockwise me-2"></i> Perbarui Riwayat
            </button>
        </div>
    </div>

    <!-- TABEL RIWAYAT (Limitless Style) -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
            <h6 class="mb-0 fw-bold"><i class="ph-clock-counter-clockwise me-2 text-success"></i>Log Aktivitas Selesai</h6>
            <div class="ms-auto">
                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 rounded-pill">Total Completed</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3" style="width: 200px;">No. Resi & Waktu</th>
                        <th>Penerima & Lokasi</th>
                        <th>Rincian Barang</th>
                        <th class="text-center">Bukti Foto</th>
                        <th class="text-center pe-3">Status</th>
                    </tr>
                </thead>
                <tbody id="historyTable">
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="ph-spinner spinner text-indigo me-2"></div>
                            <span class="text-muted small">Menyelaraskan arsip pengiriman...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: RINCIAN BARANG
     ========================================== -->
<div class="modal fade" id="modalItems" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-package me-2"></i>Daftar Obat Terkirim</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="modalItemsBody">
                <!-- List diisi via JS -->
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">TUTUP</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Header Token sudah otomatis dari layout.backoffice

    function fetchHistory() {
        const tableBody = document.getElementById('historyTable');

        axios.get('/api/deliveries/history')
            .then(res => {
                let html = '';
                const historyData = res.data;

                if (!historyData || historyData.length === 0) {
                    html = '<tr><td colspan="5" class="text-center py-5 text-muted italic small">Anda belum memiliki riwayat pengiriman yang selesai.</td></tr>';
                } else {
                    historyData.forEach(d => {
                        const deliveredAt = new Date(d.delivered_at);
                        const dateStr = deliveredAt.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});
                        const timeStr = deliveredAt.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});

                        // Proteksi jika data request null
                        const items = (d.request && d.request.items) ? d.request.items : [];
                        const itemsJson = JSON.stringify(items).replace(/"/g, '&quot;');
                        const customerName = (d.request && d.request.user) ? d.request.user.name : 'Unknown';
                        const customerAddr = (d.request && d.request.user) ? d.request.user.address : '-';

                        html += `
                        <tr class="border-bottom">
                            <td class="ps-3">
                                <div class="fw-bold text-indigo" style="font-family: monospace;">#${d.tracking_number}</div>
                                <div class="fs-xs text-muted mt-1"><i class="ph-calendar-check me-1 text-success"></i>${dateStr} - ${timeStr}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">${customerName}</div>
                                <div class="fs-xs text-muted text-truncate" style="max-width: 250px;">
                                    <i class="ph-map-pin me-1 text-danger"></i>${customerAddr}
                                </div>
                            </td>
                            <td>
                                <button onclick="showItems('${itemsJson}')" class="btn btn-sm btn-light border shadow-none rounded-pill px-3 fw-bold text-indigo">
                                    <i class="ph-list me-1"></i> ${items.length} Macam Obat
                                </button>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-block p-1 bg-light rounded-3">
                                    <img src="${d.proof_image_url}" class="rounded-2 shadow-sm border" width="45" height="45"
                                         style="object-fit: cover; cursor: pointer; transition: 0.2s;"
                                         onclick="window.open(this.src)"
                                         onmouseover="this.style.opacity='0.8'"
                                         onmouseout="this.style.opacity='1'"
                                         title="Klik untuk Zoom">
                                </div>
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
                console.error("History Error:", err);
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-danger small">Gagal menyinkronkan data riwayat dari server.</td></tr>';
            });
    }

    function showItems(encoded) {
        try {
            const items = JSON.parse(encoded);
            let html = '<div class="list-group list-group-flush">';

            if (items.length === 0) {
                html += '<div class="p-4 text-center text-muted">Data item tidak tersedia.</div>';
            } else {
                items.forEach(i => {
                    const drugName = i.drug ? i.drug.name : (i.custom_drug_name || 'Obat Tidak Diketahui');
                    const unit = i.drug ? i.drug.unit : (i.custom_unit || 'Unit');
                    html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-3 border-bottom-light">
                            <div class="d-flex align-items-center">
                                <div class="bg-indigo bg-opacity-10 p-2 rounded-3 me-3 text-indigo shadow-sm">
                                    <i class="ph-pill fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small text-uppercase">${drugName}</div>
                                    <div class="fs-xs text-muted">Kemasan: ${unit}</div>
                                </div>
                            </div>
                            <span class="badge bg-indigo rounded-pill px-3 shadow-sm">x${i.quantity}</span>
                        </div>`;
                });
            }
            document.getElementById('modalItemsBody').innerHTML = html + '</div>';
            new bootstrap.Modal(document.getElementById('modalItems')).show();
        } catch (e) {
            console.error("Error parsing items JSON", e);
        }
    }

    document.addEventListener('DOMContentLoaded', fetchHistory);
</script>

<style>
    .bg-indigo { background-color: #5c6bc0 !important; }
    .text-indigo { color: #5c6bc0 !important; }
    .btn-indigo { background-color: #5c6bc0; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #3f51b5; color: #fff; }

    .table td { padding: 0.85rem 1.25rem; }
    .table th { border-top: none !important; }
    .fs-xs { font-size: 0.75rem; }
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }

    /* Custom divider for list inside modal */
    .border-bottom-light { border-bottom: 1px solid #f1f5f9; }

    /* Scroll control for modal */
    #modalItemsBody { max-height: 450px; overflow-y: auto; }
</style>
@endsection