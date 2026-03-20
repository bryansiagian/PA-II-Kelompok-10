@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <h4 class="fw-bold mb-0 text-dark">Dashboard Admin</h4>
        <div class="text-muted small">Ringkasan operasional logistik dan distribusi E-Pharma</div>
    </div>

    <!-- Quick stats boxes (Limitless Pattern) -->
    <div class="row">
        <div class="col-lg-3">
            <div class="card card-body bg-indigo text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalUsers">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Total Pengguna</div>
                    </div>
                    <i class="ph-users ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card card-body bg-teal text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalProducts">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Katalog Produk</div>
                    </div>
                    <i class="ph-pill ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card card-body bg-pink text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalOrders">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Total Pesanan</div>
                    </div>
                    <i class="ph-clipboard-text ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card card-body bg-warning text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <h4 class="mb-0 fw-bold" id="totalShipping">0</h4>
                    <div class="ms-auto"><i class="ph-truck ph-2x opacity-75"></i></div>
                </div>
                <div>
                    Pengiriman Aktif
                    <div class="fs-sm opacity-75">Kurir di perjalanan</div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CHARTS ROW -->
    <div class="row">
        <!-- Grafik Tren Permintaan (Line) -->
        <div class="col-xl-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="ph-chart-line me-2 text-primary"></i>Tren Distribusi (7 Hari Terakhir)</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px; width:100%">
                        <canvas id="orderTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Komposisi Role (Doughnut) -->
        <div class="col-xl-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="ph-chart-pie me-2 text-success"></i>Komposisi Role</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px; width:100%">
                        <canvas id="userRoleChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <!-- Aktivitas Terbaru -->
        <div class="col-xl-8">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex align-items-center py-3 bg-transparent border-bottom">
                    <h5 class="mb-0 fw-bold">Log Aktivitas Terakhir</h5>
                    <div class="ms-auto">
                        <span class="badge bg-primary bg-opacity-10 text-primary">Live Updates</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap">
                        <thead class="table-light">
                            <tr class="fs-xs text-uppercase fw-bold text-muted">
                                <th>Aktor</th>
                                <th>Aksi</th>
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

        <!-- Info Sistem -->
        <div class="col-xl-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Informasi Sistem</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex px-0">
                            Environment <span class="ms-auto fw-bold text-primary">Development</span>
                        </div>
                        <div class="list-group-item d-flex px-0">
                            Status Database <span class="ms-auto text-success fw-bold">Connected</span>
                        </div>
                        <div class="list-group-item d-flex px-0">
                            Versi Laravel <span class="ms-auto fw-bold">v{{ app()->version() }}</span>
                        </div>
                        <div class="list-group-item d-flex px-0">
                            PHP Version <span class="ms-auto">{{ PHP_VERSION }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    let trendChartObj = null;
    let roleChartObj = null;

    function initDashboard() {
        // Update rute ke products dan orders
        const apiUsers = axios.get('/api/users');
        const apiProducts = axios.get('/api/products');
        const apiOrders = axios.get('/api/orders');
        const apiAnalytics = axios.get('/api/admin/analytics?period=daily');

        Promise.all([apiUsers, apiProducts, apiOrders, apiAnalytics])
            .then(results => {
                const users = results[0].data;
                const products = results[1].data;
                const orders = results[2].data;
                const analytics = results[3].data;

                // 1. Update Counter Widgets
                document.getElementById('totalUsers').innerText = users.length;
                document.getElementById('totalProducts').innerText = products.length;
                document.getElementById('totalOrders').innerText = orders.length;

                // Cek status pengiriman dari tabel lookup status
                const activeShipping = orders.filter(o => o.status && o.status.name === 'Shipping').length;
                document.getElementById('totalShipping').innerText = activeShipping;

                // 2. Render Grafik Tren Permintaan (Line Chart)
                renderTrendChart(analytics.stats);

                // 3. Render Grafik Role (Doughnut Chart)
                renderRoleChart(users);
            })
            .catch(err => console.error("Gagal sinkronisasi data:", err));

        // Load Audit Logs
        axios.get('/api/admin/logs').then(res => {
            let html = '';
            res.data.slice(0, 6).forEach(log => {
                const name = log.user ? log.user.name : 'System';
                html += `<tr>
                    <td><div class="fw-bold small">${name}</div></td>
                    <td><div class="text-muted small">${log.action}</div></td>
                    <td class="text-center small">${new Date(log.created_at).toLocaleTimeString('id-ID')}</td>
                </tr>`;
            });
            document.getElementById('auditLogsBody').innerHTML = html;
        });
    }

    function renderTrendChart(stats) {
        const ctx = document.getElementById('orderTrendChart').getContext('2d');
        const labels = stats.map(s => s.label);
        const data = stats.map(s => s.total_requests);

        if (trendChartObj) trendChartObj.destroy();

        trendChartObj = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pesanan Masuk',
                    data: data,
                    borderColor: '#5c6bc0',
                    backgroundColor: 'rgba(92, 107, 192, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { drawBorder: false, color: '#f0f0f0' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    function renderRoleChart(users) {
        const ctx = document.getElementById('userRoleChart').getContext('2d');

        // Menghitung jumlah per role secara dinamis
        const roles = {};
        users.forEach(u => {
            const roleName = (u.roles && u.roles[0]) ? u.roles[0].name : 'unknown';
            roles[roleName] = (roles[roleName] || 0) + 1;
        });

        if (roleChartObj) roleChartObj.destroy();

        roleChartObj = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(roles).map(r => r.toUpperCase()),
                datasets: [{
                    data: Object.values(roles),
                    backgroundColor: ['#5c6bc0', '#26a69a', '#ffa726', '#ef5350', '#06b6d4'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 6 } }
                },
                cutout: '75%'
            }
        });
    }

    document.addEventListener('DOMContentLoaded', initDashboard);
</script>

<style>
    .card { border-radius: 0.75rem; }
    .ph-2x { font-size: 2.2rem; }
    .table td { padding: 0.85rem 1rem; }
</style>
@endsection
