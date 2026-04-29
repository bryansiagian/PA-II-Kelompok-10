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
                        <select id="storage_id" class="form-select border-0 bg-light" required></select>
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
    function fetchRacks() {
        axios.get('/api/inventory/racks').then(res => {
            let html = '';
            res.data.forEach(r => {
                html += `
                <tr class="border-bottom">
                    <td class="ps-3 py-3 fw-bold text-indigo">${r.name}</td>
                    <td><span class="badge bg-light text-dark border"><i class="ph-buildings me-1"></i>${r.storage.name}</span></td>
                    <td class="text-center">
                        <button onclick="editRack(${r.id}, ${r.storage_id}, '${r.name}')" class="btn btn-light btn-icon btn-sm rounded-pill text-primary shadow-none border"><i class="ph-pencil"></i></button>
                        <button onclick="deleteRack(${r.id})" class="btn btn-light btn-icon btn-sm rounded-pill text-danger shadow-none border"><i class="ph-trash"></i></button>
                    </td>
                </tr>`;
            });
            document.getElementById('rackTableBody').innerHTML = html || '<tr><td colspan="3" class="text-center py-4">Belum ada rak.</td></tr>';
        });
    }

    function openModal() {
        document.getElementById('rackForm').reset();
        document.getElementById('rack_id').value = '';
        loadStorageOptions();
        document.getElementById('modalTitle').innerText = 'Tambah Rak Baru';
        new bootstrap.Modal(document.getElementById('modalRack')).show();
    }

    function loadStorageOptions(selectedId = null) {
        axios.get('/api/inventory/warehouses').then(res => {
            let opt = '<option value="" disabled selected>-- Pilih Gudang --</option>';
            res.data.forEach(s => {
                opt += `<option value="${s.id}" ${s.id == selectedId ? 'selected' : ''}>${s.name}</option>`;
            });
            document.getElementById('storage_id').innerHTML = opt;
        });
    }

    function editRack(id, storageId, name) {
        document.getElementById('rack_id').value = id;
        document.getElementById('rack_name').value = name;
        loadStorageOptions(storageId);
        document.getElementById('modalTitle').innerText = 'Edit Data Rak';
        new bootstrap.Modal(document.getElementById('modalRack')).show();
    }

    function saveRack(e) {
        e.preventDefault();
        const id = document.getElementById('rack_id').value;
        const data = { storage_id: document.getElementById('storage_id').value, name: document.getElementById('rack_name').value };
        const req = id ? axios.put(`/api/inventory/racks/${id}`, data) : axios.post('/api/inventory/racks', data);
        req.then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalRack')).hide();
            Swal.fire('Berhasil', 'Data rak disimpan', 'success');
            fetchRacks();
        });
    }

    function deleteRack(id) {
        Swal.fire({ title: 'Hapus Rak?', icon: 'warning', showCancelButton: true }).then(res => {
            if(res.isConfirmed) axios.delete(`/api/inventory/racks/${id}`).then(() => fetchRacks()).catch(e => Swal.fire('Gagal', e.response.data.message, 'error'));
        });
    }
    document.addEventListener('DOMContentLoaded', fetchRacks);
</script>
@endsection
