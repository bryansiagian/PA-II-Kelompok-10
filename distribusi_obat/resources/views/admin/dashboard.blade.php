@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0 text-dark">Dashboard Rekapitulasi Umum</h4>
            <div class="text-muted small">Pantau tren distribusi, stok inventaris, dan status logistik real-time.</div>
        </div>
        <div class="ms-3 d-flex gap-2">
            <button class="btn btn-indigo rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalExportReport">
                <i class="ph-file-arrow-down me-2"></i> Export Data
            </button>
            <button onclick="initDashboard()" class="btn btn-light btn-icon rounded-circle shadow-sm">
                <i class="ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Quick stats boxes -->
    <div class="row">
        <div class="col-lg-3 col-sm-6">
            <div class="card card-body bg-indigo text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalUsers">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Customer Terverifikasi</div>
                    </div>
                    <i class="ph-users-three ph-2x opacity-50 ms-3"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card card-body bg-teal text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalProducts">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Katalog Produk</div>
                    </div>
                    <i class="ph-package ph-2x opacity-50 ms-3"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card card-body bg-pink text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalOrders">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Jumlah Pembelian</div>
                    </div>
                    <i class="ph-shopping-cart-simple ph-2x opacity-50 ms-3"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card card-body bg-warning text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalShipping">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Belum Terkirim</div>
                    </div>
                    <i class="ph-truck ph-2x opacity-50 ms-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CHARTS ROW -->
    <div class="row">
        <div class="col-xl-7">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="ph-chart-line me-2 text-primary"></i>Tren Distribusi</h5>
                    <div class="ms-auto d-flex gap-1">
                        <button onclick="changePeriod('daily')" class="btn btn-xs btn-light rounded-pill px-3 shadow-none">Harian</button>
                        <button onclick="changePeriod('monthly')" class="btn btn-xs btn-light rounded-pill px-3 shadow-none">Bulanan</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:320px;">
                        <canvas id="orderTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h5 class="mb-0 fw-bold"><i class="ph-chart-pie me-2 text-success"></i>Rasio Pengiriman</h5>
                        </div>
                        <div class="card-body d-flex align-items-center">
                            <div class="chart-container" style="position: relative; height:180px; width: 180px;">
                                <canvas id="deliveryRatioChart"></canvas>
                            </div>
                            <div class="ms-4 flex-fill">
                                <div class="mb-2">
                                    <span class="badge badge-dot bg-success me-1"></span> <small>Selesai Terkirim</small>
                                    <h6 class="fw-bold mb-0" id="ratioShippedText">0</h6>
                                </div>
                                <div>
                                    <span class="badge badge-dot bg-warning me-1"></span> <small>Belum Terkirim</small>
                                    <h6 class="fw-bold mb-0" id="ratioPendingText">0</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-transparent border-bottom py-2">
                            <h6 class="mb-0 fw-bold">Top 5 Produk Terdistribusi</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" id="topProductsList">
                                <div class="p-3 text-center text-muted small">Memuat data...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-xl-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header d-flex align-items-center py-3 bg-transparent border-bottom">
                    <h5 class="mb-0 fw-bold"><i class="ph-clock-counter-clockwise me-2"></i>Log Aktivitas Terakhir</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr class="fs-xs text-uppercase fw-bold text-muted">
                                <th>Aktor</th>
                                <th>Aksi / Aktivitas</th>
                                <th class="text-center">Waktu</th>
                            </tr>
                        </thead>
                        <tbody id="auditLogsBody">
                            <tr><td colspan="3" class="text-center py-4">Memuat log...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="ph-info me-2"></i>Status Inventaris</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex px-0 border-0">
                            Stok Kritis/Rendah <span class="ms-auto fw-bold text-danger" id="summaryLowStock">0</span>
                        </div>
                        <div class="list-group-item d-flex px-0 border-0">
                            Produk Stok Habis <span class="ms-auto fw-bold text-dark" id="summaryOutOfStock">0</span>
                        </div>
                        <div class="list-group-item d-flex px-0 border-0">
                            Total Item Terdistribusi <span class="ms-auto fw-bold text-success" id="summaryDistributed">0</span>
                        </div>
                    </div>
                    <hr class="opacity-10 my-3">
                    <div class="chart-container mb-3" style="position: relative; height:150px;">
                        <canvas id="userRoleChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EXPORT REPORT -->
<div class="modal fade" id="modalExportReport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-file-arrow-down me-2"></i>Konfigurasi Laporan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="small fw-bold text-muted mb-1 text-uppercase">1. Jenis Laporan</label>
                    <select id="export_type" class="form-select border-light shadow-sm" onchange="toggleStatusFilter()">
                        <option value="orders">Laporan Distribusi (Data Pesanan)</option>
                        <option value="users">Laporan Data Pengguna (Hanya Mitra Disetujui)</option>
                    </select>
                </div>

                <div id="status_filter_container" class="mb-3">
                    <label class="small fw-bold text-muted mb-1 text-uppercase">2. Filter Status Pesanan</label>
                    <select id="export_status_id" class="form-select border-light shadow-sm">
                        <option value="all">Semua Status</option>
                        <!-- Opsi status akan muncul di sini via loadOrderStatuses() -->
                    </select>
                </div>

                <div class="mb-0">
                    <label class="small fw-bold text-muted mb-1 text-uppercase">3. Rentang Tanggal</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="date" id="export_start_date" class="form-control shadow-sm">
                            <small class="text-muted">Mulai dari</small>
                        </div>
                        <div class="col-6">
                            <input type="date" id="export_end_date" class="form-control shadow-sm">
                            <small class="text-muted">Sampai dengan</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3 text-center">
                <div class="w-100 d-flex gap-2">
                    <button type="button" onclick="handleComplexExport('excel')" class="btn btn-success flex-fill fw-bold rounded-pill shadow-sm">
                        <i class="ph-file-xls me-2"></i> EXCEL
                    </button>
                    <button type="button" onclick="handleComplexExport('pdf')" class="btn btn-danger flex-fill fw-bold rounded-pill shadow-sm">
                        <i class="ph-file-pdf me-2"></i> PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    let trendChartObj = null;
    let roleChartObj = null;
    let ratioChartObj = null;

    document.addEventListener('DOMContentLoaded', () => {
        initDashboard('daily');
        loadOrderStatuses(); // Memuat status saat halaman dibuka
    });

    function initDashboard(period = 'daily') {
        const apiUsers = axios.get('/api/users');
        const apiProducts = axios.get('/api/products');
        const apiAnalytics = axios.get(`http://localhost:8002/api/analytics?period=${period}`);

        Promise.all([apiUsers, apiProducts, apiAnalytics])
            .then(results => {
                const users = results[0].data;
                const products = results[1].data;
                const analytics = results[2].data;

                if (analytics.summary) {
                    document.getElementById('totalUsers').innerText = analytics.summary.total_users || 0;
                    document.getElementById('totalProducts').innerText = analytics.summary.total_products || 0;
                    document.getElementById('totalOrders').innerText = analytics.summary.total_orders || 0;
                    document.getElementById('totalShipping').innerText = analytics.summary.not_shipped || 0;
                    document.getElementById('summaryLowStock').innerText = analytics.summary.low_stock_products || 0;
                    document.getElementById('summaryDistributed').innerText = (analytics.summary.total_items_distributed || 0).toLocaleString() + ' Unit';
                    const outOfStock = products.filter(p => p.stock <= 0).length;
                    document.getElementById('summaryOutOfStock').innerText = outOfStock;
                }

                let topHtml = '';
                if (analytics.top_drugs && analytics.top_drugs.length > 0) {
                    analytics.top_drugs.forEach((p, index) => {
                        topHtml += `<div class="list-group-item d-flex align-items-center py-2 px-3 border-0">
                                <span class="badge bg-light text-indigo me-3">${index+1}</span>
                                <div class="flex-fill small fw-bold text-dark text-truncate">${p.name}</div>
                                <div class="ms-2 badge bg-primary bg-opacity-10 text-primary">${p.total_qty}</div>
                            </div>`;
                    });
                } else {
                    topHtml = '<div class="p-3 text-center text-muted small">Belum ada data distribusi.</div>';
                }
                document.getElementById('topProductsList').innerHTML = topHtml;

                renderTrendChart(analytics.stats || []);
                renderRoleChart(users || []);
                renderDeliveryRatioChart(analytics.delivery_ratio || {shipped: 0, not_shipped: 0});
            })
            .catch(err => console.error("Gagal sinkronisasi dashboard:", err));

        axios.get('/api/admin/logs').then(res => {
            let html = '';
            const logs = res.data || [];
            logs.slice(0, 8).forEach(log => {
                const name = log.user ? log.user.name : 'System';
                html += `<tr>
                    <td><div class="fw-bold small text-indigo">${name}</div></td>
                    <td><div class="text-muted small">${log.action}</div></td>
                    <td class="text-center small opacity-50">${new Date(log.created_at).toLocaleTimeString('id-ID')}</td>
                </tr>`;
            });
            document.getElementById('auditLogsBody').innerHTML = html || '<tr><td colspan="3" class="text-center py-3">Tidak ada log.</td></tr>';
        });
    }

    function changePeriod(p) { initDashboard(p); }

    function renderTrendChart(stats) {
        const ctx = document.getElementById('orderTrendChart').getContext('2d');
        if (trendChartObj) trendChartObj.destroy();
        trendChartObj = new Chart(ctx, {
            type: 'line',
            data: {
                labels: stats.map(s => s.label),
                datasets: [{
                    label: 'Pesanan Masuk',
                    data: stats.map(s => s.total_requests),
                    borderColor: '#5c6bc0',
                    backgroundColor: 'rgba(92, 107, 192, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: '#5c6bc0'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    }

    function renderDeliveryRatioChart(ratio) {
        const ctx = document.getElementById('deliveryRatioChart').getContext('2d');
        document.getElementById('ratioShippedText').innerText = (ratio.shipped || 0) + " Selesai";
        document.getElementById('ratioPendingText').innerText = (ratio.not_shipped || 0) + " Proses";

        if (ratioChartObj) ratioChartObj.destroy();
        ratioChartObj = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Terkirim', 'Proses'],
                datasets: [{
                    data: [ratio.shipped || 0, ratio.not_shipped || 0],
                    backgroundColor: ['#10b981', '#ffa726'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '80%', plugins: { legend: { display: false } } }
        });
    }

    function renderRoleChart(users) {
        const ctx = document.getElementById('userRoleChart').getContext('2d');
        const roles = {};
        const activeUsers = users.filter(u => u.status == 1);
        activeUsers.forEach(u => {
            const roleName = (u.roles && u.roles[0]) ? u.roles[0].name : 'unknown';
            roles[roleName] = (roles[roleName] || 0) + 1;
        });

        if (roleChartObj) roleChartObj.destroy();
        roleChartObj = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(roles).map(r => r.toUpperCase()),
                datasets: [{ label: 'Jumlah Akun Aktif', data: Object.values(roles), backgroundColor: '#5c6bc0', borderRadius: 4 }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    }

    // --- FUNGSI LOAD STATUS ---
    function loadOrderStatuses() {
        axios.get('/api/order-statuses')
            .then(res => {
                let html = '<option value="all">Semua Status</option>';
                res.data.forEach(status => {
                    html += `<option value="${status.id}">${status.name}</option>`;
                });
                document.getElementById('export_status_id').innerHTML = html;
            })
            .catch(err => {
                console.error("Gagal memuat status order:", err);
            });
    }

    function toggleStatusFilter() {
        const type = document.getElementById('export_type').value;
        const container = document.getElementById('status_filter_container');
        container.style.display = (type === 'users') ? 'none' : 'block';
    }

    function handleComplexExport(format) {
        const type = document.getElementById('export_type').value;
        const statusId = document.getElementById('export_status_id').value;
        const startDate = document.getElementById('export_start_date').value;
        const endDate = document.getElementById('export_end_date').value;

        if (!startDate || !endDate) {
            Swal.fire({ icon: 'warning', title: 'Rentang Tanggal Kosong', text: 'Silakan pilih rentang tanggal laporan.', confirmButtonColor: '#5c6bc0' });
            return;
        }

        const params = new URLSearchParams({
            type: type,
            status_id: statusId,
            start_date: startDate,
            end_date: endDate
        });

        // Langsung ke report_service, bypass app utama
        const baseUrl = format === 'excel'
            ? 'http://127.0.0.1:8002/api/export/excel'
            : 'http://127.0.0.1:8002/api/export/pdf';

        window.location.href = `${baseUrl}?${params.toString()}`;
    }
</script>

<style>
    .card { border-radius: 0.6rem; }
    .badge-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; vertical-align: middle; }
    .list-group-item { border-bottom: 1px solid #f0f0f0 !important; }
    .btn-indigo { background-color: #5c6bc0; color: white; border: none; }
    .btn-indigo:hover { background-color: #3f51b5; color: white; }
</style>
@endsection
