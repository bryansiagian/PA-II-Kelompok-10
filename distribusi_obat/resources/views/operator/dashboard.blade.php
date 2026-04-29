@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold text-dark">Operator Command Center</h4>
        <p class="text-muted small">Ringkasan operasional gudang dan distribusi hari ini.</p>
    </div>

    <!-- Row Statistik Utama -->
    <div class="row g-4 mb-4">
        <!-- Card 1: Total Permintaan -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-primary border-4 stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-cart-check-fill text-primary fs-3"></i>
                    </div>
                    <div class="ms-3">
                        <small class="text-muted fw-bold d-block">TOTAL PERMINTAAN</small>
                        <h2 class="fw-bold mb-0" id="countRequests">0</h2>
                    </div>
                </div>
                <a href="/operator/orders" class="stretched-link"></a>
            </div>
        </div>

        <!-- Card 2: Total Obat -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-success border-4 stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-capsule text-success fs-3"></i>
                    </div>
                    <div class="ms-3">
                        <small class="text-muted fw-bold d-block">KATALOG OBAT</small>
                        <h2 class="fw-bold mb-0" id="countDrugs">0</h2>
                    </div>
                </div>
                <a href="/operator/products" class="stretched-link"></a>
            </div>
        </div>

        <!-- Card 3: Total Kategori -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-info border-4 stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-tags-fill text-info fs-3"></i>
                    </div>
                    <div class="ms-3">
                        <small class="text-muted fw-bold d-block">TOTAL KATEGORI</small>
                        <h2 class="fw-bold mb-0" id="countCategories">0</h2>
                    </div>
                </div>
                <a href="/operator/categories" class="stretched-link"></a>
            </div>
        </div>
    </div>

    <!-- Row Tambahan: Informasi Stok & Aktivitas -->
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-exclamation-circle me-2 text-warning"></i> Obat Stok Rendah</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small">
                                <tr>
                                    <th class="ps-4">Obat</th>
                                    <th>Sisa Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="lowStockPreview">
                                <tr><td colspan="3" class="text-center py-4 text-muted">Memeriksa stok rendah...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 text-center">
                    <h6 class="fw-bold mb-0">Akses Cepat</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="/operator/requests" class="btn btn-outline-primary py-3 rounded-4">
                        <i class="bi bi-clipboard-check me-2"></i> Lihat Antrian Request
                    </a>
                    <a href="/operator/drugs" class="btn btn-outline-success py-3 rounded-4">
                        <i class="bi bi-plus-circle me-2"></i> Lakukan Stock In
                    </a>
                    <a href="/operator/categories" class="btn btn-outline-info py-3 rounded-4">
                        <i class="bi bi-folder-plus me-2"></i> Tambah Kategori Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Konfigurasi Token
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function loadSummary() {
        const apiRequests = axios.get('/api/orders');
        const apiDrugs = axios.get('/api/products');
        const apiCategories = axios.get('/api/product-categories');

        Promise.all([apiRequests, apiDrugs, apiCategories])
            .then(results => {
                const requests = results[0].data;
                const drugs = results[1].data;
                const categories = results[2].data;

                document.getElementById('countRequests').innerText = requests.length;
                document.getElementById('countDrugs').innerText = drugs.length;
                document.getElementById('countCategories').innerText = categories.length;

                const lowStock = drugs.filter(d => d.stock <= d.min_stock);
                let html = '';

                if(lowStock.length === 0) {
                    html = '<tr><td colspan="3" class="text-center py-4 text-success">Stok semua obat aman.</td></tr>';
                } else {
                    lowStock.slice(0, 5).forEach(d => {
                        html += `
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold small d-block">${d.name}</span>
                                <code class="text-muted" style="font-size:10px">${d.sku}</code>
                            </td>
                            <td><span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">${d.stock} ${d.unit}</span></td>
                            <td><a href="/operator/products" class="btn btn-sm btn-light rounded-circle"><i class="bi bi-arrow-right"></i></a></td>
                        </tr>`;
                    });
                }
                document.getElementById('lowStockPreview').innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Gagal memuat ringkasan data.', 'error');
            });
    }

    document.addEventListener('DOMContentLoaded', loadSummary);
</script>

<style>
    .stat-card { transition: all 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }
    .table thead th { font-size: 11px; letter-spacing: 0.5px; }
</style>
@endsection
