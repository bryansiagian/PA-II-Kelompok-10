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
            <button onclick="initDashboard()" class="btn btn-light btn-icon rounded-circle shadow-sm" title="Refresh">
                <i class="ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- ===== FILTER PERIODE ===== -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                <!-- Tombol preset -->
                <div class="col-auto">
                    <div class="btn-group" id="presetBtnGroup" role="group">
                        <button type="button" onclick="setPreset('today')"
                            class="btn btn-sm btn-outline-indigo rounded-start-pill preset-btn active"
                            data-preset="today">
                            <i class="ph-sun me-1"></i>Hari Ini
                        </button>
                        <button type="button" onclick="setPreset('this_month')"
                            class="btn btn-sm btn-outline-indigo preset-btn"
                            data-preset="this_month">
                            <i class="ph-calendar me-1"></i>Bulan Ini
                        </button>
                        <button type="button" onclick="setPreset('this_year')"
                            class="btn btn-sm btn-outline-indigo preset-btn"
                            data-preset="this_year">
                            <i class="ph-calendar-blank me-1"></i>Tahun Ini
                        </button>
                        <button type="button" onclick="setPreset('custom')"
                            class="btn btn-sm btn-outline-indigo rounded-end-pill preset-btn"
                            data-preset="custom">
                            <i class="ph-sliders me-1"></i>Custom
                        </button>
                    </div>
                </div>

                <!-- Input custom (tersembunyi secara default) -->
                <div class="col-auto d-flex align-items-center gap-2" id="customRangeInputs" style="display:none !important;">
                    <input type="date" id="filter_start_date" class="form-control form-control-sm bg-light border-0 rounded-pill" style="width:150px;">
                    <span class="text-muted small">–</span>
                    <input type="date" id="filter_end_date" class="form-control form-control-sm bg-light border-0 rounded-pill" style="width:150px;">
                    <button onclick="applyCustomRange()" class="btn btn-indigo btn-sm rounded-pill px-3 shadow-sm">
                        <i class="ph-magnifying-glass me-1"></i>Terapkan
                    </button>
                </div>

                <!-- Label periode aktif -->
                <div class="col-auto ms-auto">
                    <span class="badge bg-indigo bg-opacity-10 rounded-pill px-3 py-2 small fw-bold" id="activePeriodLabel">
                        <i class="ph-calendar-check me-1"></i><span id="activePeriodText">Hari Ini</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Boxes -->
    <div class="row">
        <div class="col-lg-3 col-sm-6">
            <div class="card card-body bg-indigo text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalUsers">–</h4>
                        <div class="text-uppercase fs-xs opacity-75">Customer Terverifikasi</div>
                        <div class="fs-xs opacity-60 mt-1" id="totalUsersSub"></div>
                    </div>
                    <i class="ph-users-three ph-2x opacity-50 ms-3"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card card-body bg-teal text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalProducts">–</h4>
                        <div class="text-uppercase fs-xs opacity-75">Katalog Produk</div>
                        <div class="fs-xs opacity-60 mt-1" id="totalProductsSub"></div>
                    </div>
                    <i class="ph-package ph-2x opacity-50 ms-3"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card card-body bg-pink text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalOrders">–</h4>
                        <div class="text-uppercase fs-xs opacity-75">Jumlah Pembelian</div>
                        <div class="fs-xs opacity-60 mt-1" id="totalOrdersSub"></div>
                    </div>
                    <i class="ph-shopping-cart-simple ph-2x opacity-50 ms-3"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card card-body bg-warning text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="totalShipping">–</h4>
                        <div class="text-uppercase fs-xs opacity-75">Belum Terkirim</div>
                        <div class="fs-xs opacity-60 mt-1" id="totalShippingSub"></div>
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
                    <small class="ms-2 text-muted" id="trendChartSub"></small>
                    <div class="ms-auto d-flex gap-1">
                        <button onclick="changeTrendGranularity('daily')"
                            class="btn btn-xs btn-light rounded-pill px-3 shadow-none granularity-btn active-gran"
                            id="gran-daily">Harian</button>
                        <button onclick="changeTrendGranularity('monthly')"
                            class="btn btn-xs btn-light rounded-pill px-3 shadow-none granularity-btn"
                            id="gran-monthly">Bulanan</button>
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
                                    <h6 class="fw-bold mb-0" id="ratioShippedText">–</h6>
                                </div>
                                <div>
                                    <span class="badge badge-dot bg-warning me-1"></span> <small>Belum Terkirim</small>
                                    <h6 class="fw-bold mb-0" id="ratioPendingText">–</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-transparent border-bottom py-2 d-flex align-items-center">
                            <h6 class="mb-0 fw-bold">Top 5 Produk Terdistribusi</h6>
                            <small class="ms-auto text-muted" id="topProductsPeriodLabel"></small>
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
                    <small class="ms-2 text-muted" id="logPeriodLabel"></small>
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
                            <tr><td colspan="3" class="text-center py-4 text-muted">Memuat log...</td></tr>
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
                            Stok Kritis/Rendah
                            <span class="ms-auto fw-bold text-danger" id="summaryLowStock">–</span>
                        </div>
                        <div class="list-group-item d-flex px-0 border-0">
                            Produk Stok Habis
                            <span class="ms-auto fw-bold text-dark" id="summaryOutOfStock">–</span>
                        </div>
                        <div class="list-group-item d-flex px-0 border-0">
                            Total Item Terdistribusi
                            <span class="ms-auto fw-bold text-success" id="summaryDistributed">–</span>
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

    // ─── State ────────────────────────────────────────────────────────────────
    let trendChartObj  = null;
    let roleChartObj   = null;
    let ratioChartObj  = null;

    let currentPreset      = 'today';
    let currentStartDate   = '';
    let currentEndDate     = '';
    let currentGranularity = 'daily';

    // AbortController aktif — dibatalkan setiap kali loadDashboard() dipanggil lagi
    let activeAbortController = null;

    // ─── Helpers: Date ───────────────────────────────────────────────────────
    function toDateStr(d) {
        return d.toISOString().split('T')[0];
    }

    function computeRange(preset) {
        const now = new Date();
        if (preset === 'today') {
            currentStartDate = toDateStr(now);
            currentEndDate   = toDateStr(now);
        } else if (preset === 'this_month') {
            currentStartDate = toDateStr(new Date(now.getFullYear(), now.getMonth(), 1));
            currentEndDate   = toDateStr(now);
        } else if (preset === 'this_year') {
            currentStartDate = toDateStr(new Date(now.getFullYear(), 0, 1));
            currentEndDate   = toDateStr(now);
        }
        // 'custom' → caller yang set
    }

    function buildPeriodLabel(preset, start, end) {
        const fmt = d => new Date(d).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' });
        if (preset === 'today')      return 'Hari Ini – ' + fmt(start);
        if (preset === 'this_month') return 'Bulan Ini – ' + fmt(start) + ' s/d ' + fmt(end);
        if (preset === 'this_year')  return 'Tahun ' + new Date(start).getFullYear();
        return fmt(start) + ' – ' + fmt(end);
    }

    // ─── Skeleton Helpers ────────────────────────────────────────────────────
    const SKELETON_STAT = `<span class="skeleton-line" style="width:60px;height:28px;"></span>`;
    const SKELETON_SUB  = `<span class="skeleton-line" style="width:80px;height:10px;margin-top:4px;"></span>`;

    function showSkeletons() {
        // Stat cards
        ['totalUsers','totalProducts','totalOrders','totalShipping'].forEach(id => {
            document.getElementById(id).innerHTML = SKELETON_STAT;
            const sub = document.getElementById(id + 'Sub');
            if (sub) sub.innerHTML = SKELETON_SUB;
        });

        // Inventaris
        ['summaryLowStock','summaryOutOfStock','summaryDistributed'].forEach(id => {
            document.getElementById(id).innerHTML =
                `<span class="skeleton-line" style="width:40px;height:14px;"></span>`;
        });

        // Top products list
        let topHtml = '';
        for (let i = 0; i < 5; i++) {
            topHtml += `
            <div class="list-group-item d-flex align-items-center py-2 px-3 border-0">
                <span class="skeleton-circle me-3" style="width:24px;height:24px;"></span>
                <div class="flex-fill">
                    <span class="skeleton-line" style="width:${60 + i * 8}%;height:12px;"></span>
                </div>
                <span class="skeleton-line ms-2" style="width:48px;height:20px;border-radius:999px;"></span>
            </div>`;
        }
        document.getElementById('topProductsList').innerHTML = topHtml;

        // Audit logs table
        let logHtml = '';
        for (let i = 0; i < 5; i++) {
            logHtml += `
            <tr>
                <td><span class="skeleton-line" style="width:90px;height:13px;"></span></td>
                <td><span class="skeleton-line" style="width:${120 + i * 15}px;height:13px;"></span></td>
                <td class="text-center"><span class="skeleton-line" style="width:60px;height:13px;display:inline-block;"></span></td>
            </tr>`;
        }
        document.getElementById('auditLogsBody').innerHTML = logHtml;

        // Rasio teks
        document.getElementById('ratioShippedText').innerHTML =
            `<span class="skeleton-line" style="width:60px;height:16px;"></span>`;
        document.getElementById('ratioPendingText').innerHTML =
            `<span class="skeleton-line" style="width:60px;height:16px;"></span>`;
    }

    // ─── Preset & Custom Handlers ────────────────────────────────────────────
    function setPreset(preset) {
        currentPreset = preset;

        document.querySelectorAll('.preset-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.preset === preset);
        });

        const customInputs = document.getElementById('customRangeInputs');
        if (preset === 'custom') {
            customInputs.style.setProperty('display', 'flex', 'important');
            document.getElementById('filter_start_date').value = currentStartDate;
            document.getElementById('filter_end_date').value   = currentEndDate;
            return;
        } else {
            customInputs.style.setProperty('display', 'none', 'important');
            computeRange(preset);
            loadDashboard();
        }
    }

    function applyCustomRange() {
        const s = document.getElementById('filter_start_date').value;
        const e = document.getElementById('filter_end_date').value;
        if (!s || !e) {
            Swal.fire({ icon: 'warning', title: 'Tanggal Kosong', text: 'Pilih tanggal mulai dan akhir terlebih dahulu.', confirmButtonColor: '#5c6bc0' });
            return;
        }
        if (s > e) {
            Swal.fire({ icon: 'warning', title: 'Rentang Tidak Valid', text: '"Dari Tanggal" tidak boleh lebih dari "Sampai Tanggal".', confirmButtonColor: '#5c6bc0' });
            return;
        }
        currentStartDate = s;
        currentEndDate   = e;
        loadDashboard();
    }

    function changeTrendGranularity(gran) {
        currentGranularity = gran;
        document.querySelectorAll('.granularity-btn').forEach(btn => btn.classList.remove('active-gran'));
        document.getElementById('gran-' + gran).classList.add('active-gran');
        loadDashboard();
    }

    // ─── Main loader ─────────────────────────────────────────────────────────
    function initDashboard() {
        computeRange('today');
        loadDashboard();
        loadOrderStatuses();
    }

    function loadDashboard() {
        // 1. Batalkan semua request sebelumnya yang masih in-flight
        if (activeAbortController) {
            activeAbortController.abort();
        }
        activeAbortController = new AbortController();
        const signal = activeAbortController.signal;

        // 2. Tampilkan skeleton sebelum fetch dimulai
        showSkeletons();

        // 3. Update label periode
        const label = buildPeriodLabel(currentPreset, currentStartDate, currentEndDate);
        document.getElementById('activePeriodText').innerText       = label;
        document.getElementById('trendChartSub').innerText          = '(' + label + ')';
        document.getElementById('topProductsPeriodLabel').innerText = label;
        document.getElementById('logPeriodLabel').innerText         = '(' + label + ')';

        const params = {
            period:     currentGranularity,
            start_date: currentStartDate,
            end_date:   currentEndDate
        };

        // Helper: cek apakah error karena dibatalkan (bukan error sungguhan)
        const isCancelled = err => err.name === 'AbortError' || err.code === 'ERR_CANCELED';

        // ── Analytics ────────────────────────────────────────────────────────
        axios.get('/api/admin/analytics', { params, signal })
            .then(res => {
                const analytics = res.data;
                const s = analytics.summary || {};

                setStatCard('totalUsers',    s.total_users    || 0, '');
                setStatCard('totalProducts', s.total_products || 0, '');
                setStatCard('totalOrders',   s.total_orders   || 0, 'dalam periode ini');
                setStatCard('totalShipping', s.not_shipped    || 0, 'belum dikirim');

                document.getElementById('summaryLowStock').innerText    = s.low_stock_products || 0;
                document.getElementById('summaryDistributed').innerText =
                    (s.total_items_distributed || 0).toLocaleString('id-ID') + ' Unit';

                renderTopProducts(analytics.top_drugs || []);
                renderTrendChart(analytics.stats || []);
                renderDeliveryRatioChart(analytics.delivery_ratio || { shipped: 0, not_shipped: 0 });
            })
            .catch(err => { if (!isCancelled(err)) console.error('Analytics error:', err); });

        // ── Products ─────────────────────────────────────────────────────────
        axios.get('/api/products', { signal })
            .then(res => {
                document.getElementById('summaryOutOfStock').innerText =
                    (res.data || []).filter(p => p.stock <= 0).length;
            })
            .catch(err => { if (!isCancelled(err)) console.error('Products error:', err); });

        // ── Users (role chart) ────────────────────────────────────────────────
        axios.get('/api/users', { signal })
            .then(res => renderRoleChart(res.data || []))
            .catch(err => { if (!isCancelled(err)) console.error('Users error:', err); });

        // ── Audit Logs ────────────────────────────────────────────────────────
        axios.get('/api/admin/logs', {
            params: { start_date: currentStartDate, end_date: currentEndDate },
            signal
        }).then(res => {
            let html = '';
            (res.data || []).slice(0, 8).forEach(log => {
                const name = log.user ? log.user.name : 'System';
                const time = new Date(log.created_at).toLocaleString('id-ID', {
                    day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
                });
                html += `<tr>
                    <td><div class="fw-bold small text-indigo">${name}</div></td>
                    <td><div class="text-muted small">${log.action}</div></td>
                    <td class="text-center small opacity-50">${time}</td>
                </tr>`;
            });
            document.getElementById('auditLogsBody').innerHTML =
                html || '<tr><td colspan="3" class="text-center py-3 text-muted">Tidak ada log pada periode ini.</td></tr>';
        }).catch(err => { if (!isCancelled(err)) console.error('Logs error:', err); });

        // Sync ke modal export
        document.getElementById('export_start_date').value = currentStartDate;
        document.getElementById('export_end_date').value   = currentEndDate;
    }

    // ─── Helpers: UI ─────────────────────────────────────────────────────────
    function setStatCard(id, value, subText) {
        document.getElementById(id).innerText = typeof value === 'number'
            ? value.toLocaleString('id-ID') : value;
        const subEl = document.getElementById(id + 'Sub');
        if (subEl) subEl.innerText = subText;
    }

    // ─── Chart Renderers ─────────────────────────────────────────────────────
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
                    backgroundColor: 'rgba(92, 107, 192, 0.07)',
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
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    function renderDeliveryRatioChart(ratio) {
        document.getElementById('ratioShippedText').innerText = (ratio.shipped || 0) + ' Selesai';
        document.getElementById('ratioPendingText').innerText = (ratio.not_shipped || 0) + ' Proses';

        const ctx = document.getElementById('deliveryRatioChart').getContext('2d');
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
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '80%',
                plugins: { legend: { display: false } }
            }
        });
    }

    function renderRoleChart(users) {
        const ctx = document.getElementById('userRoleChart').getContext('2d');
        const roles = {};
        (users.filter(u => u.status == 1)).forEach(u => {
            const roleName = (u.roles && u.roles[0]) ? u.roles[0].name : 'unknown';
            roles[roleName] = (roles[roleName] || 0) + 1;
        });
        if (roleChartObj) roleChartObj.destroy();
        roleChartObj = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(roles).map(r => r.toUpperCase()),
                datasets: [{
                    label: 'Akun Aktif',
                    data: Object.values(roles),
                    backgroundColor: '#5c6bc0',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    function renderTopProducts(products) {
        let html = '';
        if (products.length > 0) {
            products.forEach((p, idx) => {
                html += `
                <div class="list-group-item d-flex align-items-center py-2 px-3 border-0">
                    <span class="badge bg-light text-indigo me-3">${idx + 1}</span>
                    <div class="flex-fill small fw-bold text-dark text-truncate">${p.name}</div>
                    <div class="ms-2 badge bg-primary bg-opacity-10 text-primary">${p.total_qty} unit</div>
                </div>`;
            });
        } else {
            html = '<div class="p-3 text-center text-muted small">Belum ada data distribusi pada periode ini.</div>';
        }
        document.getElementById('topProductsList').innerHTML = html;
    }

    // ─── Order Statuses ───────────────────────────────────────────────────────
    function loadOrderStatuses() {
        axios.get('/api/order-statuses')
            .then(res => {
                let html = '<option value="all">Semua Status</option>';
                res.data.forEach(s => {
                    html += `<option value="${s.id}">${s.name}</option>`;
                });
                document.getElementById('export_status_id').innerHTML = html;
            })
            .catch(err => console.error('Status error:', err));
    }

    // ─── Export ───────────────────────────────────────────────────────────────
    function toggleStatusFilter() {
        const type = document.getElementById('export_type').value;
        document.getElementById('status_filter_container').style.display =
            type === 'users' ? 'none' : 'block';
    }

    function handleComplexExport(format) {
        const type      = document.getElementById('export_type').value;
        const statusId  = document.getElementById('export_status_id').value;
        const startDate = document.getElementById('export_start_date').value;
        const endDate   = document.getElementById('export_end_date').value;

        if (!startDate || !endDate) {
            Swal.fire({ icon: 'warning', title: 'Rentang Tanggal Kosong', text: 'Silakan pilih rentang tanggal laporan.', confirmButtonColor: '#5c6bc0' });
            return;
        }

        const params = new URLSearchParams({ type, status_id: statusId, start_date: startDate, end_date: endDate });
        const baseUrl = format === 'excel'
            ? '/api/admin/reports/export/excel'
            : '/api/admin/reports/export/pdf';

        window.location.href = `${baseUrl}?${params.toString()}`;
    }

    // ─── Init ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', initDashboard);
</script>

<style>
    .card { border-radius: 0.6rem; }
    .badge-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; vertical-align: middle; }
    .list-group-item { border-bottom: 1px solid #f0f0f0 !important; }

    /* Indigo palette */
    .btn-indigo            { background-color: #5c6bc0; color: white; border: none; }
    .btn-indigo:hover      { background-color: #3f51b5; color: white; }
    .text-indigo           { color: #5c6bc0 !important; }
    .bg-indigo             { background-color: #5c6bc0 !important; }

    /* Outline indigo untuk preset buttons */
    .btn-outline-indigo {
        color: #5c6bc0;
        border-color: #5c6bc0;
        background: transparent;
    }
    .btn-outline-indigo:hover,
    .btn-outline-indigo.active {
        background-color: #5c6bc0;
        color: #fff;
        border-color: #5c6bc0;
    }

    /* Active granularity button */
    .granularity-btn.active-gran {
        background-color: #5c6bc0 !important;
        color: #fff !important;
    }

    /* ── Skeleton loading ──────────────────────────────────────────────────── */
    @keyframes shimmer {
        0%   { background-position: -400px 0; }
        100% { background-position:  400px 0; }
    }

    .skeleton-line,
    .skeleton-circle {
        display: inline-block;
        border-radius: 6px;
        background: linear-gradient(90deg, rgba(255,255,255,.15) 25%, rgba(255,255,255,.35) 50%, rgba(255,255,255,.15) 75%);
        background-size: 800px 100%;
        animation: shimmer 1.4s infinite linear;
    }

    /* Pada latar putih (kartu biasa) warna shimmer berbeda */
    .list-group-item .skeleton-line,
    td .skeleton-line,
    .card-body:not([class*="bg-"]) .skeleton-line {
        background: linear-gradient(90deg, #e8e8e8 25%, #f5f5f5 50%, #e8e8e8 75%);
        background-size: 800px 100%;
        animation: shimmer 1.4s infinite linear;
    }

    .skeleton-circle {
        border-radius: 50% !important;
        background: linear-gradient(90deg, #e8e8e8 25%, #f5f5f5 50%, #e8e8e8 75%);
        background-size: 800px 100%;
        animation: shimmer 1.4s infinite linear;
    }
</style>
@endsection
