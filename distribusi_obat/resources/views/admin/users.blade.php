@extends('layouts.backoffice')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Kelola Pengguna</h4>
            <p class="text-muted small mb-0">Daftar akun Operator, Kurir, dan Mitra Faskes yang aktif.</p>
        </div>
        <button class="btn btn-indigo rounded-pill px-4 shadow-sm" onclick="openAddModal()">
            <i class="ph-plus-circle me-2"></i> Tambah Pengguna
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3">Nama & Email</th>
                        <th>Role / Hak Akses</th>
                        <th>Terdaftar Pada</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <tr><td colspan="4" class="text-center py-5 text-muted">Memuat data pengguna...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: TAMBAH USER -->
<div class="modal fade" id="modalUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalTitle">Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm" onsubmit="submitUser(event)">
                <!-- Cari bagian modal-body di dalam modalUser dan sesuaikan isinya -->
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" placeholder="Nama asli atau Nama Unit" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Alamat Email</label>
                        <input type="email" name="email" class="form-control" placeholder="email@domain.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Kata Sandi</label>
                        <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required>
                    </div>

                    <!-- INPUT ALAMAT (Baru Ditambahkan) -->
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Alamat Lengkap / Lokasi Unit</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Jl. Kesehatan No. 123..."></textarea>
                        <small class="text-muted" style="font-size: 10px;">Wajib diisi untuk akun Mitra Faskes (Customer).</small>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Pilih Role</label>
                        <select name="role_id" id="roleSelect" class="form-select" onchange="handleRoleChange()" required>
                            <option value="" selected disabled>-- Pilih Role --</option>
                            <!-- Diisi via JS fetchRoles -->
                        </select>
                        <!-- Penting: Input ini untuk membantu validasi required_if di Laravel -->
                        <input type="hidden" name="role_name" id="role_name">
                    </div>

                    <!-- SEKSI KHUSUS KURIR -->
                    <div id="courierFields" class="p-3 bg-light rounded-3 border-start border-start-width-5 border-start-indigo d-none mb-3">
                        <h6 class="fw-bold text-indigo mb-3"><i class="ph-truck me-2"></i>Informasi Kendaraan</h6>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted">Jenis Kendaraan</label>
                            <select name="vehicle_type" class="form-select border-0">
                                <option value="motorcycle">Sepeda Motor</option>
                                <option value="car">Mobil / Van</option>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="small fw-bold text-muted">Nomor Plat</label>
                            <input type="text" name="vehicle_plate" class="form-control border-0" placeholder="Contoh: B 1234 ABC">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light py-2">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                    <button type="submit" id="btnSubmit" class="btn btn-indigo px-4 fw-bold shadow-sm rounded-pill">SIMPAN AKUN</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    const modalUser = new bootstrap.Modal(document.getElementById('modalUser'));
    let roleList = [];

    document.addEventListener('DOMContentLoaded', () => {
        fetchRoles();
        fetchUsers();
    });

    function fetchRoles() {
        axios.get('/api/roles').then(res => {
            roleList = res.data;
            let opt = '<option value="" selected disabled>-- Pilih Role --</option>';
            roleList.forEach(r => {
                opt += `<option value="${r.id}" data-name="${r.name}">${r.name.toUpperCase()}</option>`;
            });
            document.getElementById('roleSelect').innerHTML = opt;
        });
    }

    function handleRoleChange() {
        const select = document.getElementById('roleSelect');
        const selectedOption = select.options[select.selectedIndex];
        const roleName = selectedOption.getAttribute('data-name');

        // Simpan role name ke hidden input untuk validasi required_if di backend
        document.getElementById('role_name').value = roleName;

        const courierSection = document.getElementById('courierFields');
        if (roleName === 'courier') {
            courierSection.classList.remove('d-none');
        } else {
            courierSection.classList.add('d-none');
        }
    }

    function fetchUsers() {
        axios.get('/api/users').then(res => {
            let html = '';
            res.data.forEach(u => {
                const roleName = u.roles[0] ? u.roles[0].name.toUpperCase() : 'NO ROLE';
                const date = new Date(u.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});

                html += `
                <tr>
                    <td class="ps-3">
                        <div class="fw-bold text-dark">${u.name}</div>
                        <div class="text-muted small">${u.email}</div>
                    </td>
                    <td>
                        <span class="badge ${roleName === 'CUSTOMER' ? 'bg-teal' : 'bg-indigo'} bg-opacity-10 text-indigo border-indigo border-opacity-25 px-2 py-1">
                            ${roleName}
                        </span>
                    </td>
                    <td><div class="small text-muted">${date}</div></td>
                    <td class="text-center pe-3">
                        <button onclick="deleteUser(${u.id})" class="btn btn-light btn-icon btn-sm rounded-pill text-danger shadow-none border">
                            <i class="ph-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            document.getElementById('userTableBody').innerHTML = html || '<tr><td colspan="4" class="text-center py-4">Tidak ada data.</td></tr>';
        });
    }

    function openAddModal() {
        document.getElementById('userForm').reset();
        document.getElementById('courierFields').classList.add('d-none');
        modalUser.show();
    }

    function submitUser(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmit');
        const formData = new FormData(e.target);

        btn.disabled = true;
        btn.innerHTML = '<i class="ph-spinner spinner me-2"></i> Memproses...';

        axios.post('/api/users', formData)
            .then(res => {
                modalUser.hide();
                Swal.fire('Berhasil', res.data.message, 'success');
                fetchUsers();
            })
            .catch(err => {
                Swal.fire('Gagal', err.response.data.message || 'Terjadi kesalahan', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'SIMPAN AKUN';
            });
    }

    function deleteUser(id) {
        Swal.fire({
            title: 'Hapus Akun?',
            text: "Akun akan dihapus permanen dari sistem.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef5350',
            confirmButtonText: 'Ya, Hapus'
        }).then(res => {
            if (res.isConfirmed) {
                axios.delete(`/api/users/${id}`).then(() => {
                    Swal.fire('Terhapus', 'Akun berhasil dihapus', 'success');
                    fetchUsers();
                });
            }
        });
    }
</script>

<style>
    .btn-indigo { background-color: #5c6bc0; color: #fff; }
    .spinner { animation: rotation 2s infinite linear; display: inline-block; }
    @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
</style>
@endsection