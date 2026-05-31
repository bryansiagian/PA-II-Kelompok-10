@extends('layouts.backoffice')
@section('page_title', 'Kelola Rak')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Manajemen Rak Gudang</h4>
        <button class="btn btn-indigo rounded-pill" onclick="openModal()"><i class="ph-plus-circle me-2"></i> Tambah Rak</button>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3 py-3">Nama Rak</th>
                        <th>Berada di Gudang</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="rackTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Rack -->
<div class="modal fade" id="modalRack" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Rak Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rackForm" onsubmit="saveRack(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="rack_id">
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Pilih Gudang</label>
                        <select id="warehouse_id" class="form-select border-0 bg-light" required></select>
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold text-muted">Nama/Nomor Rak</label>
                        <input type="text" id="rack_name" class="form-control border-0 bg-light" placeholder="Contoh: RAK-A1" required>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light text-end">
                    <button type="submit" class="btn btn-indigo rounded-pill px-4 fw-bold">Simpan Rak</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // ─── Skeleton Helpers ─────────────────────────────────────────────────────
    function showSkeletons() {
        let html = '';
        for (let i = 0; i < 7; i++) {
            html += `
            <tr class="border-bottom">
                <td class="ps-3 py-3">
                    <span class="skeleton-line" style="width:${80 + i * 12}px;height:14px;"></span>
                </td>
                <td>
                    <span class="skeleton-line" style="width:${110 + i * 10}px;height:24px;border-radius:6px;"></span>
                </td>
                <td class="text-center pe-3">
                    <span class="skeleton-line" style="width:64px;height:30px;border-radius:999px;"></span>
                </td>
            </tr>`;
        }
        document.getElementById('rackTableBody').innerHTML = html;
    }

    // ─── Fetch Racks ──────────────────────────────────────────────────────────
    function fetchRacks() {
        showSkeletons();

        axios.get('/api/inventory/racks')
            .then(res => {
                let html = '';
                res.data.forEach(r => {
                    const warehouseName = r.warehouse ? r.warehouse.name : '-';
                    html += `
                    <tr class="border-bottom">
                        <td class="ps-3 py-3 fw-bold text-indigo">${r.name}</td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="ph-buildings me-1"></i>${warehouseName}
                            </span>
                        </td>
                        <td class="text-center">
                            <button onclick="editRack(${r.id}, ${r.warehouse_id}, '${r.name}')"
                                    class="btn btn-light btn-icon btn-sm rounded-pill text-primary shadow-none border">
                                <i class="ph-pencil"></i>
                            </button>
                            <button onclick="deleteRack(${r.id})"
                                    class="btn btn-light btn-icon btn-sm rounded-pill text-danger shadow-none border">
                                <i class="ph-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                });
                document.getElementById('rackTableBody').innerHTML =
                    html || '<tr><td colspan="3" class="text-center py-4">Belum ada rak.</td></tr>';
            })
            .catch(() => {
                document.getElementById('rackTableBody').innerHTML =
                    '<tr><td colspan="3" class="text-center py-4 text-danger">Gagal memuat data rak.</td></tr>';
            });
    }

    // ─── Open Modal ───────────────────────────────────────────────────────────
    function openModal() {
        document.getElementById('rackForm').reset();
        document.getElementById('rack_id').value = '';
        loadWarehouseOptions();
        document.getElementById('modalTitle').innerText = 'Tambah Rak Baru';
        new bootstrap.Modal(document.getElementById('modalRack')).show();
    }

    // ─── Load Warehouse Options ───────────────────────────────────────────────
    function loadWarehouseOptions(selectedId = null) {
        axios.get('/api/inventory/warehouses').then(res => {
            let opt = '<option value="" disabled selected>-- Pilih Gudang --</option>';
            res.data.forEach(s => {
                opt += `<option value="${s.id}" ${s.id == selectedId ? 'selected' : ''}>${s.name}</option>`;
            });
            document.getElementById('warehouse_id').innerHTML = opt;
        });
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────
    function editRack(id, warehouseId, name) {
        document.getElementById('rack_id').value   = id;
        document.getElementById('rack_name').value = name;
        loadWarehouseOptions(warehouseId);
        document.getElementById('modalTitle').innerText = 'Edit Data Rak';
        new bootstrap.Modal(document.getElementById('modalRack')).show();
    }

    // ─── Save ─────────────────────────────────────────────────────────────────
    function saveRack(e) {
        e.preventDefault();
        const id   = document.getElementById('rack_id').value;
        const data = {
            warehouse_id: document.getElementById('warehouse_id').value,
            name:         document.getElementById('rack_name').value
        };
        const req = id
            ? axios.put(`/api/inventory/racks/${id}`, data)
            : axios.post('/api/inventory/racks', data);

        req.then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalRack')).hide();
            Swal.fire('Berhasil', 'Data rak disimpan', 'success');
            fetchRacks();
        }).catch(err => {
            Swal.fire('Gagal', err.response?.data?.message || 'Terjadi kesalahan', 'error');
        });
    }

    // ─── Delete ───────────────────────────────────────────────────────────────
    function deleteRack(id) {
        Swal.fire({ title: 'Hapus Rak?', icon: 'warning', showCancelButton: true })
            .then(res => {
                if (res.isConfirmed) {
                    axios.delete(`/api/inventory/racks/${id}`)
                        .then(() => fetchRacks())
                        .catch(e => Swal.fire('Gagal', e.response?.data?.message || 'Terjadi kesalahan', 'error'));
                }
            });
    }

    document.addEventListener('DOMContentLoaded', fetchRacks);
</script>

<style>
    .btn-indigo       { background-color: #5c6bc0; color: #fff; }
    .btn-indigo:hover { background-color: #3f51b5; color: #fff; }
    .text-indigo      { color: #5c6bc0; }

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
