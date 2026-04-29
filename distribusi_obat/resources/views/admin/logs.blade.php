@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Audit Logs Sistem</h4>
            <p class="text-muted small mb-0">Rekam jejak aktivitas seluruh pengguna dalam ekosistem E-Pharma.</p>
        </div>
        <button onclick="fetchLogs()" class="btn btn-white border shadow-sm rounded-pill px-4 fw-bold small">
            <i class="bi bi-arrow-clockwise me-1 text-primary"></i> Perbarui Log
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-muted small fw-bold" style="width: 200px;">WAKTU</th>
                            <th class="text-muted small fw-bold" style="width: 250px;">AKTOR</th>
                            <th class="text-muted small fw-bold">AKTIVITAS / DESKRIPSI AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="logTableBody">
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat catatan...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Header token diambil dari session via layout backoffice
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    function fetchLogs() {
        const tableBody = document.getElementById('logTableBody');

        axios.get('/api/admin/logs')
            .then(res => {
                let html = '';
                const logs = res.data;

                if (!logs || logs.length === 0) {
                    html = '<tr><td colspan="3" class="text-center py-5 text-muted small italic">Belum ada catatan aktivitas sistem.</td></tr>';
                } else {
                    logs.forEach(log => {
                        // 1. Penanganan Waktu
                        const date = new Date(log.created_at);
                        const timeStr = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                        const dateStr = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

                        // 2. Penanganan Aktor & Role (Spatie Safe)
                        const user = log.user || { name: 'Sistem/Deleted', roles: [] };
                        const roleName = user.roles.length > 0 ? user.roles[0].name : 'no-role';

                        // 3. Penanganan Warna & Icon Aksi
                        let actionTheme = getActionTheme(log.action);

                        html += `
                        <tr class="border-bottom">
                            <td class="ps-4">
                                <div class="fw-bold text-dark small">${timeStr}</div>
                                <div class="text-muted" style="font-size: 11px;">${dateStr}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random" class="rounded-circle me-3" width="32">
                                    <div>
                                        <div class="fw-bold text-dark small">${user.name}</div>
                                        <span class="badge bg-light text-muted border rounded-pill" style="font-size: 9px; letter-spacing: 0.5px;">
                                            ${roleName.toUpperCase()}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-start">
                                    <i class="bi ${actionTheme.icon} ${actionTheme.color} me-2 mt-1"></i>
                                    <div class="small ${actionTheme.bold ? 'fw-bold' : ''} text-dark" style="line-height: 1.5;">
                                        ${log.action}
                                    </div>
                                </div>
                            </td>
                        </tr>`;
                    });
                }
                tableBody.innerHTML = html;
            })
            .catch(err => {
                console.error("Audit Log Error:", err);
                let msg = err.response ? err.response.data.message : 'Koneksi Gagal';
                tableBody.innerHTML = `<tr><td colspan="3" class="text-center py-5 text-danger">Gagal Memuat: ${msg}</td></tr>`;
            });
    }

    // Helper untuk mempercantik tampilan aksi
    function getActionTheme(action) {
        const act = action.toUpperCase();
        if(act.includes('LOGIN')) return { icon: 'bi-box-arrow-in-right', color: 'text-info', bold: false };
        if(act.includes('LOGOUT')) return { icon: 'bi-box-arrow-right', color: 'text-muted', bold: false };
        if(act.includes('APPROVE')) return { icon: 'bi-check-circle-fill', color: 'text-success', bold: true };
        if(act.includes('REJECT') || act.includes('DELETE') || act.includes('CANCEL')) return { icon: 'bi-x-circle-fill', color: 'text-danger', bold: true };
        if(act.includes('CREATE') || act.includes('TAMBAH')) return { icon: 'bi-plus-circle-fill', color: 'text-primary', bold: true };
        if(act.includes('STOCK')) return { icon: 'bi-capsule', color: 'text-warning', bold: true };

        return { icon: 'bi-info-circle', color: 'text-dark', bold: false };
    }

    document.addEventListener('DOMContentLoaded', fetchLogs);
</script>

<style>
    .table thead th {
        font-size: 10px;
        letter-spacing: 1px;
        background-color: #f1f5f9;
        border-top: none;
    }
    .table tbody tr { transition: all 0.2s; }
    .table tbody tr:hover { background-color: #f8fafc; }
    .badge { font-weight: 700; }
</style>
@endsection