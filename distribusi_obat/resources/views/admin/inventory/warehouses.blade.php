@extends('layouts.backoffice')

@section('page_title', 'Kelola Gudang')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Master Data Gudang</h4>
            <p class="text-muted small mb-0">Kelola lokasi penyimpanan sediaan farmasi dan logistik.</p>
        </div>
        <button class="btn btn-indigo rounded-pill px-4 shadow-sm" onclick="openModal()">
            <i class="ph-plus-circle me-2"></i> Tambah Gudang
        </button>
    </div>

    <!-- Statistik Ringkas -->
    <div class="row mb-3">
        <div class="col-sm-6 col-xl-4">
            <div class="card card-body shadow-sm border-0">
                <div class="d-flex align-items-center">
                    <i class="ph-warehouse ph-2x text-indigo opacity-75 me-3"></i>
                    <div>
                        <h4 class="mb-0 fw-bold" id="statTotalWarehouse">0</h4>
                        <span class="text-uppercase fs-xs text-muted">Total Lokasi Gudang</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Gudang -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3 py-3" style="width: 150px;">Kode</th>
                        <th>Nama Gudang</th>
                        <th>Lokasi / Keterangan</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="warehouseTableBody">
                    <tr><td colspan="4" class="text-center py-5 text-muted">Memuat data gudang...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: TAMBAH/EDIT GUDANG -->
<div class="modal fade" id="modalWarehouse" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalTitle">Gudang Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="warehouseForm" onsubmit="saveWarehouse(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="warehouse_id">

                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Kode Gudang</label>
                        <input type="text" id="form_code" class="form-control bg-light border-0" placeholder="Contoh: WH-01" required style="text-transform: uppercase;">
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Nama Gudang</label>
                        <input type="text" id="form_name" class="form-control bg-light border-0" placeholder="Contoh: Gudang Farmasi Pusat" required>
                    </div>

                    <div class="mb-0">
                        <label class="small fw-bold text-muted mb-1">Lokasi / Detail Alamat</label>
                        <textarea id="form_location" class="form-control bg-light border-0" rows="3" placeholder="Jl. Kesehatan No. 10..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light py-2">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" id="btnSave" class="btn btn-indigo rounded-pill px-4 fw-bold shadow-sm">SIMPAN DATA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    const modalWarehouse = new bootstrap.Modal(document.getElementById('modalWarehouse'));

    function fetchWarehouses() {
        // Endpoint diubah sesuai api.php terbaru
        axios.get('/api/inventory/warehouses').then(res => {
            let html = '';
            const data = res.data;

            data.forEach(w => {
                html += `
                <tr class="border-bottom">
                    <td class="ps-3 py-3">
                        <span class="badge bg-indigo bg-opacity-10 text-indigo fw-bold font-monospace">${w.code}</span>
                    </td>
                    <td class="fw-bold text-dark">${w.name}</td>
                    <td><small class="text-muted">${w.location || '<span class="fst-italic opacity-50">Tidak ada detail lokasi</span>'}</small></td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-2">
                            <button onclick="editWarehouse(${w.id}, '${w.code}', '${w.name}', '${w.location || ''}')" class="btn btn-light btn-icon btn-sm rounded-pill text-primary shadow-none border">
                                <i class="ph-pencil"></i>
                            </button>
                            <button onclick="confirmDelete(${w.id}, '${w.name}')" class="btn btn-light btn-icon btn-sm rounded-pill text-danger shadow-none border">
                                <i class="ph-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            });

            document.getElementById('warehouseTableBody').innerHTML = html || '<tr><td colspan="4" class="text-center py-5 text-muted">Belum ada gudang terdaftar</td></tr>';
            document.getElementById('statTotalWarehouse').innerText = data.length;
        });
    }

    function openModal() {
        document.getElementById('warehouseForm').reset();
        document.getElementById('warehouse_id').value = '';
        document.getElementById('modalTitle').innerText = 'Tambah Gudang Baru';
        document.getElementById('btnSave').innerText = 'Simpan Gudang';
        modalWarehouse.show();
    }

    function editWarehouse(id, code, name, loc) {
        document.getElementById('warehouse_id').value = id;
        document.getElementById('form_code').value = code;
        document.getElementById('form_name').value = name;
        document.getElementById('form_location').value = loc !== 'null' ? loc : '';

        document.getElementById('modalTitle').innerText = 'Edit Data Gudang';
        document.getElementById('btnSave').innerText = 'Update Gudang';
        modalWarehouse.show();
    }

    function saveWarehouse(e) {
        e.preventDefault();
        const id = document.getElementById('warehouse_id').value;
        const btn = document.getElementById('btnSave');

        const data = {
            code: document.getElementById('form_code').value,
            name: document.getElementById('form_name').value,
            location: document.getElementById('form_location').value
        };

        btn.disabled = true;
        btn.innerHTML = '<i class="ph-spinner spinner me-2"></i> Memproses...';

        // Endpoint diubah sesuai api.php terbaru
        const request = id
            ? axios.put(`/api/inventory/warehouses/${id}`, data)
            : axios.post('/api/inventory/warehouses', data);

        request.then(() => {
            modalWarehouse.hide();
            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data gudang telah diperbarui', timer: 1500, showConfirmButton: false });
            fetchWarehouses();
        }).catch(err => {
            Swal.fire('Gagal', err.response?.data?.message || 'Terjadi kesalahan sistem', 'error');
        }).finally(() => {
            btn.disabled = false;
            btn.innerHTML = id ? 'Update Gudang' : 'Simpan Gudang';
        });
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Gudang?',
            text: `Apakah Anda yakin ingin menghapus ${name}? Pastikan gudang sudah kosong dari stok produk.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus'
        }).then(res => {
            if(res.isConfirmed) {
                axios.delete(`/api/inventory/warehouses/${id}`)
                .then(() => {
                    Swal.fire({ icon: 'success', title: 'Terhapus', timer: 1000, showConfirmButton: false });
                    fetchWarehouses();
                })
                .catch(err => {
                    Swal.fire('Gagal', err.response?.data?.message || 'Gudang tidak bisa dihapus', 'error');
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchWarehouses);
</script>

<style>
    .btn-indigo { background-color: #5c6bc0; color: #fff; }
    .btn-indigo:hover { background-color: #3f51b5; color: #fff; }
    .text-indigo { color: #5c6bc0; }
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
</style>
@endsection