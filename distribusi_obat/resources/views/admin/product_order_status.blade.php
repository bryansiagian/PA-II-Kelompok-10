@extends('layouts.backoffice')
@section('page_title', 'Status Pesanan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Kelola Status Pesanan</h4>
        <button class="btn btn-indigo rounded-pill" onclick="openModal()">
            <i class="ph-plus-circle me-2"></i>Tambah Status
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3 py-3">#</th>
                        <th>Nama Status</th>
                        <th class="text-center">Aktif</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="statusTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Status Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form onsubmit="saveStatus(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="status_id">
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Nama Status</label>
                        <input type="text" id="status_name" class="form-control border-0 bg-light"
                               placeholder="Contoh: Pending" required>
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold text-muted d-block mb-2">Status Aktif</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="status_active" checked>
                            <label class="form-check-label small text-muted" for="status_active">
                                Tampilkan sebagai pilihan aktif
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="submit" class="btn btn-indigo rounded-pill px-4 fw-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showSkeletons() {
        let html = '';
        for (let i = 0; i < 5; i++) {
            html += `
            <tr class="border-bottom">
                <td class="ps-3 py-3">
                    <span class="skeleton-line" style="width:24px;height:14px;"></span>
                </td>
                <td>
                    <span class="skeleton-line" style="width:${100 + i * 20}px;height:14px;"></span>
                </td>
                <td class="text-center">
                    <span class="skeleton-line" style="width:52px;height:22px;border-radius:999px;"></span>
                </td>
                <td class="text-center pe-3">
                    <span class="skeleton-line" style="width:64px;height:30px;border-radius:999px;"></span>
                </td>
            </tr>`;
        }
        document.getElementById('statusTableBody').innerHTML = html;
    }

    function fetchStatuses() {
        showSkeletons();
        axios.get('/api/admin/product-order-statuses')
            .then(res => {
                let html = '';
                res.data.forEach((s, i) => {
                    const activeBadge = s.active
                        ? '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Aktif</span>'
                        : '<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Nonaktif</span>';
                    html += `
                    <tr class="border-bottom">
                        <td class="ps-3 py-3 text-muted">${i + 1}</td>
                        <td class="fw-bold text-indigo">${s.name}</td>
                        <td class="text-center">${activeBadge}</td>
                        <td class="text-center">
                            <button onclick="editStatus(${s.id}, '${s.name}', ${s.active})"
                                    class="btn btn-light btn-icon btn-sm rounded-pill text-primary shadow-none border">
                                <i class="ph-pencil"></i>
                            </button>
                            <button onclick="deleteStatus(${s.id})"
                                    class="btn btn-light btn-icon btn-sm rounded-pill text-danger shadow-none border">
                                <i class="ph-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                });
                document.getElementById('statusTableBody').innerHTML =
                    html || '<tr><td colspan="4" class="text-center py-4 text-muted">Belum ada status.</td></tr>';
            })
            .catch(() => {
                document.getElementById('statusTableBody').innerHTML =
                    '<tr><td colspan="4" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>';
            });
    }

    function openModal() {
        document.getElementById('status_id').value    = '';
        document.getElementById('status_name').value  = '';
        document.getElementById('status_active').checked = true;
        document.getElementById('modalTitle').innerText  = 'Tambah Status Baru';
        new bootstrap.Modal(document.getElementById('modalStatus')).show();
    }

    function editStatus(id, name, active) {
        document.getElementById('status_id').value       = id;
        document.getElementById('status_name').value     = name;
        document.getElementById('status_active').checked = !!active;
        document.getElementById('modalTitle').innerText  = 'Edit Status';
        new bootstrap.Modal(document.getElementById('modalStatus')).show();
    }

    function saveStatus(e) {
        e.preventDefault();
        const id   = document.getElementById('status_id').value;
        const data = {
            name:   document.getElementById('status_name').value,
            active: document.getElementById('status_active').checked,
        };
        const req = id
            ? axios.put(`/api/admin/product-order-statuses/${id}`, data)
            : axios.post('/api/admin/product-order-statuses', data);

        req.then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalStatus')).hide();
            Swal.fire('Berhasil', 'Status disimpan', 'success');
            fetchStatuses();
        }).catch(err => {
            Swal.fire('Gagal', err.response?.data?.message || 'Terjadi kesalahan', 'error');
        });
    }

    function deleteStatus(id) {
        Swal.fire({ title: 'Hapus Status?', icon: 'warning', showCancelButton: true })
            .then(res => {
                if (res.isConfirmed) {
                    axios.delete(`/api/admin/product-order-statuses/${id}`)
                        .then(() => fetchStatuses())
                        .catch(e => Swal.fire('Gagal', e.response?.data?.message || 'Terjadi kesalahan', 'error'));
                }
            });
        }

    document.addEventListener('DOMContentLoaded', fetchStatuses);
</script>

<style>
    .btn-indigo       { background-color: #5c6bc0; color: #fff; }
    .btn-indigo:hover { background-color: #3f51b5; color: #fff; }
    .text-indigo      { color: #5c6bc0; }
    .bg-opacity-10    { --bs-bg-opacity: 0.1; }

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
