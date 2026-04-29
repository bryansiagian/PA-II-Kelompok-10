@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-sm-flex align-items-sm-center justify-content-sm-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Laporan Rekapitulasi Distribusi</h4>
            <div class="text-muted small">Analisis data transaksi dan pengiriman sediaan farmasi.</div>
        </div>
        <div class="mt-3 mt-sm-0">
            <button class="btn btn-indigo rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalExportReport">
                <i class="ph-file-arrow-down me-2"></i> Export Data
            </button>
        </div>
    </div>

    <!-- Filter & Statistik -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold text-indigo"><i class="ph-funnel me-2"></i>Filter Laporan & Grafik</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="fs-xs fw-bold text-muted text-uppercase mb-1">Dari Tanggal</label>
                            <input type="date" id="start_date" class="form-control form-control-sm bg-light border-0 rounded-pill">
                        </div>
                        <div class="col-md-3">
                            <label class="fs-xs fw-bold text-muted text-uppercase mb-1">Sampai Tanggal</label>
                            <input type="date" id="end_date" class="form-control form-control-sm bg-light border-0 rounded-pill">
                        </div>
                        <div class="col-md-4">
                            <label class="fs-xs fw-bold text-muted text-uppercase mb-1">Status Pesanan</label>
                            <select id="status_filter" class="form-select form-select-sm bg-light border-0 rounded-pill">
                                <option value="all">Semua Status</option>
                                <!-- Diisi via JS -->
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button onclick="fetchReportData()" class="btn btn-indigo btn-sm w-100 rounded-pill fw-bold shadow-sm">
                                <i class="ph-magnifying-glass me-1"></i> FILTER
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-4">
            <div class="card card-body bg-indigo text-white shadow-sm border-0 rounded-3 p-3 mb-3">
                <small class="text-uppercase fs-xs opacity-75">Total Pesanan (Filter)</small>
                <h4 class="mb-0 fw-bold" id="sumCompleted">0</h4>
            </div>
            <div class="card card-body bg-teal text-white shadow-sm border-0 rounded-3 p-3 mb-3">
                <small class="text-uppercase fs-xs opacity-75">Item Terdistribusi (Filter)</small>
                <h4 class="mb-0 fw-bold" id="sumItems">0</h4>
            </div>
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="ph-ranking me-2 text-warning"></i>Produk Terlaris</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush border-top-0" id="topProductsList">
                        <div class="text-center py-5"><div class="ph-spinner spinner text-muted"></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="ph-chart-line-up me-2 text-primary"></i>Tren Volume Pesanan</h6>
                    <small class="ms-2 text-muted" id="chartStatusLabel">(Semua Status)</small>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 350px;">
                        <canvas id="reportChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm border-0 rounded-3 mb-5">
        <div class="card-header bg-transparent border-bottom py-3">
            <h6 class="mb-0 fw-bold"><i class="ph-table me-2 text-indigo"></i>Data Detail Transaksi</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3">ID Order</th>
                        <th>Fasilitas Kesehatan</th>
                        <th class="text-center">Item</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Total</th>
                    </tr>
                </thead>
                <tbody id="reportTableBody">
                    <tr><td colspan="5" class="text-center py-5 text-muted">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL EXPORT REPORT -->
<div class="modal fade" id="modalExportReport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="ph-file-arrow-down me-2"></i>Konfigurasi Ekspor Laporan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="small fw-bold text-muted mb-1 text-uppercase">1. Jenis Laporan</label>
                    <select id="export_type" class="form-select border-light shadow-sm" onchange="toggleStatusFilter()">
                        <option value="orders">Laporan Distribusi (Data Pesanan)</option>
                        <option value="users">Laporan Data Pengguna (Mitra/Customer)</option>
                    </select>
                </div>

                <div id="status_filter_container" class="mb-3">
                    <label class="small fw-bold text-muted mb-1 text-uppercase">2. Filter Status Pesanan</label>
                    <select id="export_status_id" class="form-select border-light shadow-sm">
                        <option value="all">Semua Status</option>
                    </select>
                </div>

                <div class="mb-0">
                    <label class="small fw-bold text-muted mb-1 text-uppercase">3. Rentang Tanggal Laporan</label>
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
            <div class="modal-footer bg-light border-0 py-3">
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

    let myChartObj = null;

    document.addEventListener('DOMContentLoaded', () => {
        const today = new Date().toISOString().split('T')[0];
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);

        document.getElementById('start_date').value = thirtyDaysAgo.toISOString().split('T')[0];
        document.getElementById('end_date').value = today;
        document.getElementById('export_start_date').value = thirtyDaysAgo.toISOString().split('T')[0];
        document.getElementById('export_end_date').value = today;

        loadOrderStatuses();
        fetchReportData();
    });

    function fetchReportData() {
        const start = document.getElementById('start_date').value;
        const end = document.getElementById('end_date').value;
        const status = document.getElementById('status_filter').value;

        // Update Label Grafik
        const statusText = document.getElementById('status_filter').options[document.getElementById('status_filter').selectedIndex].text;
        document.getElementById('chartStatusLabel').innerText = `(${statusText})`;

        // 1. Ambil Data Analitik (Kirim params agar grafik juga terfilter)
        axios.get(`http://localhost:8002/api/analytics`, {
            params: {
                period: 'daily',
                status_id: status,
                start_date: start,
                end_date: end
            }
        }).then(res => {
            const data = res.data;
            document.getElementById('sumCompleted').innerText = data.summary?.total_orders || 0;
            document.getElementById('sumItems').innerText = (data.summary?.total_items_distributed || 0).toLocaleString();

            renderChart(data.stats || []);
            renderTopProducts(data.top_drugs || []);
        });

        // 2. Ambil Data Tabel Detail
        axios.get(`http://localhost:8002/api/reports`, {
            params: { start_date: start, end_date: end, status_id: status }
        }).then(res => {
            let html = '';
            res.data.forEach(o => {
                const statusName = o.status?.name || 'PENDING';
                html += `
                <tr>
                    <td class="ps-3 fw-bold text-indigo">#${o.id.substring(0,8)}</td>
                    <td>
                        <div class="fw-bold text-dark">${o.user?.name || 'N/A'}</div>
                        <div class="fs-xs text-muted">${new Date(o.created_at).toLocaleDateString('id-ID')}</div>
                    </td>
                    <td class="text-center">${o.items ? o.items.length : 0} Jenis</td>
                    <td class="text-center">
                        <span class="badge bg-light text-primary border rounded-pill px-2">
                            ${statusName.toUpperCase()}
                        </span>
                    </td>
                    <td class="text-end pe-3 fw-bold">Rp${Number(o.total).toLocaleString()}</td>
                </tr>`;
            });
            document.getElementById('reportTableBody').innerHTML = html || '<tr><td colspan="5" class="text-center py-4">Tidak ada data.</td></tr>';
        });
    }

    function renderChart(stats) {
        const ctx = document.getElementById('reportChart').getContext('2d');
        if (myChartObj) myChartObj.destroy();

        myChartObj = new Chart(ctx, {
            type: 'line',
            data: {
                labels: stats.map(s => s.label),
                datasets: [{
                    label: 'Volume Pesanan',
                    data: stats.map(s => s.total_requests),
                    borderColor: '#5c6bc0',
                    backgroundColor: 'rgba(92, 107, 192, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#5c6bc0'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    function renderTopProducts(products) {
        let html = '';
        products.forEach((p, idx) => {
            html += `
            <div class="list-group-item d-flex align-items-center py-3 border-0">
                <div class="me-3 badge bg-light text-indigo rounded-circle" style="width:30px; height:30px; display:flex; align-items:center; justify-content:center;">${idx+1}</div>
                <div class="flex-fill fw-bold text-dark small text-uppercase text-truncate">${p.name}</div>
                <div class="ms-2"><span class="badge bg-indigo bg-opacity-10 text-indigo">${p.total_qty} unit</span></div>
            </div>`;
        });
        document.getElementById('topProductsList').innerHTML = html || '<div class="p-3 text-center small text-muted">Kosong</div>';
    }

    function loadOrderStatuses() {
        axios.get('/api/order-statuses')
            .then(res => {
                let html = '<option value="all">Semua Status</option>';
                res.data.forEach(status => {
                    html += `<option value="${status.id}">${status.name}</option>`;
                });
                // Isi kedua dropdown (filter utama & modal export)
                document.getElementById('status_filter').innerHTML = html;
                document.getElementById('export_status_id').innerHTML = html;
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
    .btn-indigo { background-color: #5c6bc0; color: white; border: none; }
    .btn-indigo:hover { background-color: #3f51b5; color: white; }
    .text-indigo { color: #5c6bc0 !important; }
    .bg-indigo { background-color: #5c6bc0 !important; }
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
</style>
@endsection
