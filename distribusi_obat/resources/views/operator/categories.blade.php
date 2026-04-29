@extends('layouts.backoffice')

@section('page_title', 'Manajemen Kategori Produk')

@section('content')
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-flex align-items-center mb-3">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0 text-dark">Kategori Produk</h4>
            <div class="text-muted small">Kelola klasifikasi produk untuk mempermudah manajemen inventaris.</div>
        </div>
        <div class="ms-3">
            <button class="btn btn-indigo shadow-sm rounded-pill px-4" onclick="openAddModal()">
                <i class="ph-plus-circle me-2"></i> Tambah Kategori
            </button>
        </div>
    </div>

    <!-- Statistik Row -->
    <div class="row mb-3">
        <div class="col-lg-4">
            <div class="card card-body bg-indigo text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="statTotalCategories">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Total Kategori</div>
                    </div>
                    <i class="ph-list-dashes ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-body bg-teal text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="statActiveCategories">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Kategori Aktif</div>
                    </div>
                    <i class="ph-check-circle ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-body bg-secondary text-white shadow-sm border-0 mb-3">
                <div class="d-flex align-items-center">
                    <div class="flex-fill">
                        <h4 class="mb-0 fw-bold" id="statEmptyCategories">0</h4>
                        <div class="text-uppercase fs-xs opacity-75">Kategori Kosong</div>
                    </div>
                    <i class="ph-folder-simple ph-2x opacity-75 ms-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header d-flex align-items-center bg-transparent border-bottom py-3">
            <h5 class="mb-0 fw-bold"><i class="ph-tag me-2 text-primary"></i>Daftar Kategori Produk</h5>
            <div class="ms-auto">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <span class="input-group-text bg-light border-0"><i class="ph-magnifying-glass"></i></span>
                    <input type="text" id="tableSearch" class="form-control bg-light border-0" placeholder="Cari kategori...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3">Kode & Nama Kategori</th>
                        <th class="text-center">Jumlah Produk</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="categoryTableBody">
                    <tr><td colspan="4" class="text-center py-5 text-muted">Memuat data kategori...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: TAMBAH/EDIT KATEGORI -->
<div class="modal fade" id="modalCategory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold" id="modalTitle">Tambah Kategori</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCategory" onsubmit="submitCategory(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="category_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Kode Kategori (Singkatan)</label>
                        <input type="text" name="code" id="form_code" class="form-control" placeholder="Contoh: ALG, ABT, VKS" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nama Kategori</label>
                        <input type="text" name="name" id="form_name" class="form-control" placeholder="Contoh: Analgetik" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" id="btnSave" class="btn btn-indigo px-4 fw-bold shadow-sm">SIMPAN DATA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    const modalCategory = new bootstrap.Modal(document.getElementById('modalCategory'));

    function fetchCategories() {
        const tableBody = document.getElementById('categoryTableBody');

        axios.get('/api/product-categories').then(res => {
            const categories = res.data;
            let html = '';
            let activeCount = 0;
            let emptyCount = 0;

            if (categories.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-5">Belum ada kategori terdaftar.</td></tr>';
                return;
            }

            categories.forEach(c => {
                if (c.active) activeCount++;
                if (c.products_count === 0) emptyCount++;

                html += `
                <tr>
                    <td class="ps-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-indigo bg-opacity-10 text-indigo rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="ph-tag fw-bold"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">${c.name}</div>
                                <div class="fs-xs text-muted font-monospace">${c.code || 'NO-CODE'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-indigo border border-indigo border-opacity-25 px-2 py-1">
                            ${c.products_count} Produk
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge ${c.active ? 'bg-success' : 'bg-danger'} px-2 py-1 rounded-pill">
                            ${c.active ? 'AKTIF' : 'NON-AKTIF'}
                        </span>
                    </td>
                    <td class="text-center pe-3">
                        <div class="d-inline-flex">
                            <!-- ID dibungkus tanda petik untuk keamanan UUID -->
                            <button onclick="openEditModal('${c.id}')" class="btn btn-sm btn-light text-primary border-0 me-2 shadow-sm"><i class="ph-note-pencil"></i></button>
                            <button onclick="confirmDelete('${c.id}', '${c.name}')" class="btn btn-sm btn-light text-danger border-0 shadow-sm"><i class="ph-trash"></i></button>
                        </div>
                    </td>
                </tr>`;
            });

            tableBody.innerHTML = html;
            document.getElementById('statTotalCategories').innerText = categories.length;
            document.getElementById('statActiveCategories').innerText = activeCount;
            document.getElementById('statEmptyCategories').innerText = emptyCount;
        });
    }

    function openAddModal() {
        document.getElementById('formCategory').reset();
        document.getElementById('category_id').value = '';
        document.getElementById('modalTitle').innerText = 'Tambah Kategori Baru';
        document.getElementById('btnSave').innerText = 'SIMPAN DATA';
        modalCategory.show();
    }

    function openEditModal(id) {
        // Tampilkan loading swal sebentar
        axios.get(`/api/product-categories/${id}`).then(res => {
            const c = res.data;
            document.getElementById('category_id').value = c.id;
            document.getElementById('form_name').value = c.name;
            document.getElementById('form_code').value = c.code || '';

            document.getElementById('modalTitle').innerText = 'Edit Kategori Produk';
            document.getElementById('btnSave').innerText = 'UPDATE DATA';
            modalCategory.show();
        }).catch(err => {
            Swal.fire('Error', 'Gagal mengambil data kategori', 'error');
        });
    }

    function submitCategory(event) {
        event.preventDefault();
        const id = document.getElementById('category_id').value;
        const btn = document.getElementById('btnSave');

        const data = {
            name: document.getElementById('form_name').value,
            code: document.getElementById('form_code').value
        };

        btn.disabled = true;
        btn.innerHTML = '<i class="ph-spinner spinner me-2"></i> Memproses...';

        let url = '/api/product-categories';
        let method = 'post';

        if (id) {
            url = `/api/product-categories/${id}`;
            method = 'put'; // Menggunakan method PUT untuk update
        }

        axios({
            method: method,
            url: url,
            data: data
        }).then(res => {
            modalCategory.hide();
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.data.message, timer: 1500, showConfirmButton: false });
            fetchCategories();
        }).catch(err => {
            let msg = err.response?.data?.message || 'Terjadi kesalahan';
            Swal.fire('Gagal', msg, 'error');
        }).finally(() => {
            btn.disabled = false;
            btn.innerHTML = id ? 'UPDATE DATA' : 'SIMPAN DATA';
        });
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Arsipkan Kategori?',
            text: `Kategori ${name} akan dinonaktifkan dari sistem.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Arsipkan!',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                axios.delete(`/api/product-categories/${id}`).then(() => {
                    Swal.fire('Berhasil', 'Kategori telah diarsipkan', 'success');
                    fetchCategories();
                }).catch(err => {
                    Swal.fire('Gagal', err.response?.data?.message || 'Kategori sedang digunakan', 'error');
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchCategories);

    // Filter pencarian tabel sederhana
    document.getElementById('tableSearch').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#categoryTableBody tr');
        rows.forEach(row => {
            // Pastikan tidak menyembunyikan row "Memuat..." atau "Kosong"
            if(row.cells.length > 1) {
                row.style.display = (row.innerText.toLowerCase().indexOf(value) > -1) ? "" : "none";
            }
        });
    });
</script>

<style>
    .bg-indigo { background-color: #5c6bc0 !important; }
    .bg-teal { background-color: #26a69a !important; }
    .text-indigo { color: #5c6bc0 !important; }
    .btn-indigo { background-color: #5c6bc0; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #3f51b5; color: #fff; }
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
</style>
@endsection
