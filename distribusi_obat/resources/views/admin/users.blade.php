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
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h5 class="modal-title fw-bold">Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">

                <div class="mb-3">
                    <label class="small fw-bold text-muted">Nama Lengkap</label>
                    <input type="text" id="input_name" class="form-control" placeholder="Nama asli atau Nama Unit">
                </div>
                <div class="mb-3">
                    <label class="small fw-bold text-muted">Alamat Email</label>
                    <input type="email" id="input_email" class="form-control" placeholder="email@domain.com">
                </div>
                <div class="mb-3">
                    <label class="small fw-bold text-muted">No. Telepon</label>
                    <input type="text" id="input_phone" class="form-control" placeholder="08xxxxxxxxxx">
                </div>
                <div class="mb-3">
                    <label class="small fw-bold text-muted">Pilih Role</label>
                    <select id="input_role" class="form-select" onchange="handleRoleChange()">
                        <option value="" disabled selected>-- Pilih Role --</option>
                    </select>
                </div>

                {{-- PASSWORD: hanya muncul untuk non-customer --}}
                <div class="mb-3" id="passwordField">
                    <label class="small fw-bold text-muted">Kata Sandi</label>
                    <input type="password" id="input_password" class="form-control" placeholder="Min. 6 karakter">
                    <small class="text-muted" style="font-size:10px">Tidak diperlukan untuk akun Mitra — password akan digenerate otomatis.</small>
                </div>

                {{-- ALAMAT: selalu tampil --}}
                <div class="mb-3">
                    <label class="small fw-bold text-muted">Detail Alamat</label>
                    <textarea id="input_address" class="form-control" rows="2" placeholder="Jl. Kesehatan No. 123..."></textarea>
                </div>

                {{-- HUB REGIONAL: hanya muncul untuk customer --}}
                <div id="regionalFields" class="d-none">
                    <div class="p-3 bg-light rounded-3 border-start border-4 border-indigo mb-3">
                        <h6 class="fw-bold text-indigo mb-3"><i class="ph-map-pin me-2"></i>Hub Regional</h6>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted">Provinsi</label>
                            <input type="text" class="form-control bg-light" value="Sumatera Utara" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted">Kabupaten / Kota</label>
                            <select id="input_regency" class="form-select" onchange="loadDistricts(this.value)">
                                <option value="" disabled selected>-- Pilih Kabupaten --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted">Kecamatan</label>
                            <select id="input_district" class="form-select" onchange="loadVillages(this.value)" disabled>
                                <option value="" disabled selected>-- Pilih Kecamatan --</option>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="small fw-bold text-muted">Kelurahan / Desa</label>
                            <select id="input_village" class="form-select" disabled>
                                <option value="" disabled selected>-- Pilih Kelurahan --</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer border-0 bg-light py-2">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                <button id="btnSubmitUser" onclick="submitUser()" class="btn btn-indigo px-4 fw-bold shadow-sm rounded-pill">SIMPAN AKUN</button>
            </div>
        </div>
    </div>
</div>

<script>
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';

    const PROVINCE_ID = '12'; // Sumatera Utara
    let modalUser = null;

    document.addEventListener('DOMContentLoaded', () => {
        modalUser = new bootstrap.Modal(document.getElementById('modalUser'));
        fetchRoles();
        fetchUsers();
    });

    // ── ROLES ────────────────────────────────────────────────
    function fetchRoles() {
        axios.get('/api/roles').then(res => {
            let opt = '<option value="" disabled selected>-- Pilih Role --</option>';
            res.data.forEach(r => {
                opt += `<option value="${r.id}" data-name="${r.name}">${r.name.toUpperCase()}</option>`;
            });
            document.getElementById('input_role').innerHTML = opt;
        });
    }

    // ── ROLE CHANGE ──────────────────────────────────────────
    function handleRoleChange() {
        const select   = document.getElementById('input_role');
        const roleName = select.options[select.selectedIndex]?.dataset?.name ?? '';

        // Password: sembunyikan untuk customer
        document.getElementById('passwordField').classList.toggle('d-none', roleName === 'customer');

        // Regional: tampilkan hanya untuk customer
        document.getElementById('regionalFields').classList.toggle('d-none', roleName !== 'customer');

        if (roleName === 'customer') {
            document.getElementById('input_password').value = '';
            loadRegencies();
        }
    }

    // ── WILAYAH ──────────────────────────────────────────────
    function loadRegencies() {
        const sel = document.getElementById('input_regency');
        sel.innerHTML = '<option disabled selected>Memuat...</option>';
        fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${PROVINCE_ID}.json`)
            .then(r => r.json())
            .then(data => {
                sel.innerHTML = '<option value="" disabled selected>-- Pilih Kabupaten --</option>';
                data.forEach(r => {
                    sel.innerHTML += `<option value="${r.id}" data-name="${r.name}">${r.name}</option>`;
                });
            });
    }

    function loadDistricts(regencyId) {
        const sel = document.getElementById('input_district');
        sel.innerHTML = '<option disabled selected>Memuat...</option>';
        sel.disabled  = true;

        const vilSel  = document.getElementById('input_village');
        vilSel.innerHTML = '<option value="" disabled selected>-- Pilih Kelurahan --</option>';
        vilSel.disabled  = true;

        fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${regencyId}.json`)
            .then(r => r.json())
            .then(data => {
                sel.innerHTML = '<option value="" disabled selected>-- Pilih Kecamatan --</option>';
                data.forEach(d => {
                    sel.innerHTML += `<option value="${d.id}" data-name="${d.name}">${d.name}</option>`;
                });
                sel.disabled = false;
            });
    }

    function loadVillages(districtId) {
        const sel = document.getElementById('input_village');
        sel.innerHTML = '<option disabled selected>Memuat...</option>';
        sel.disabled  = true;
        fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${districtId}.json`)
            .then(r => r.json())
            .then(data => {
                sel.innerHTML = '<option value="" disabled selected>-- Pilih Kelurahan --</option>';
                data.forEach(v => {
                    sel.innerHTML += `<option value="${v.id}" data-name="${v.name}">${v.name}</option>`;
                });
                sel.disabled = false;
            });
    }

    // ── TABEL USERS ──────────────────────────────────────────
    function fetchUsers() {
        axios.get('/api/users').then(res => {
            let html = '';
            res.data.forEach(u => {
                const roleName = u.roles[0] ? u.roles[0].name.toUpperCase() : 'NO ROLE';
                const date     = new Date(u.created_at).toLocaleDateString('id-ID', {
                    day: 'numeric', month: 'long', year: 'numeric'
                });
                const badgeClass = roleName === 'CUSTOMER' ? 'bg-teal' : 'bg-indigo';

                html += `
                <tr>
                    <td class="ps-3">
                        <div class="fw-bold text-dark">${u.name}</div>
                        <div class="text-muted small">${u.email}</div>
                    </td>
                    <td>
                        <span class="badge ${badgeClass} bg-opacity-10 text-indigo border border-indigo border-opacity-25 px-2 py-1">
                            ${roleName}
                        </span>
                    </td>
                    <td><div class="small text-muted">${date}</div></td>
                    <td class="text-center pe-3">
                        <button onclick="deleteUser(${u.id})"
                                class="btn btn-light btn-icon btn-sm rounded-pill text-danger shadow-none border">
                            <i class="ph-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            document.getElementById('userTableBody').innerHTML =
                html || '<tr><td colspan="4" class="text-center py-4 text-muted">Tidak ada data.</td></tr>';
        });
    }

    // ── OPEN MODAL ───────────────────────────────────────────
    function openAddModal() {
        // Reset semua field
        document.getElementById('input_name').value     = '';
        document.getElementById('input_email').value    = '';
        document.getElementById('input_phone').value    = '';
        document.getElementById('input_password').value = '';
        document.getElementById('input_address').value  = '';
        document.getElementById('input_role').selectedIndex = 0;

        // Reset wilayah
        document.getElementById('input_regency').innerHTML  = '<option value="" disabled selected>-- Pilih Kabupaten --</option>';
        document.getElementById('input_district').innerHTML = '<option value="" disabled selected>-- Pilih Kecamatan --</option>';
        document.getElementById('input_village').innerHTML  = '<option value="" disabled selected>-- Pilih Kelurahan --</option>';
        document.getElementById('input_district').disabled  = true;
        document.getElementById('input_village').disabled   = true;

        // Reset toggle section
        document.getElementById('passwordField').classList.remove('d-none');
        document.getElementById('regionalFields').classList.add('d-none');

        modalUser.show();
    }

    // ── SUBMIT ───────────────────────────────────────────────
    function submitUser() {
        const btn        = document.getElementById('btnSubmitUser');
        const roleSelect = document.getElementById('input_role');
        const roleName   = roleSelect.options[roleSelect.selectedIndex]?.dataset?.name ?? '';

        if (!document.getElementById('input_name').value.trim())  return Swal.fire('Peringatan', 'Nama wajib diisi', 'warning');
        if (!document.getElementById('input_email').value.trim()) return Swal.fire('Peringatan', 'Email wajib diisi', 'warning');
        if (!roleSelect.value)                                     return Swal.fire('Peringatan', 'Pilih role terlebih dahulu', 'warning');
        if (roleName !== 'customer' && !document.getElementById('input_password').value)
            return Swal.fire('Peringatan', 'Password wajib diisi', 'warning');

        const regencySel  = document.getElementById('input_regency');
        const districtSel = document.getElementById('input_district');
        const villageSel  = document.getElementById('input_village');

        const payload = {
            name:      document.getElementById('input_name').value.trim(),
            email:     document.getElementById('input_email').value.trim(),
            phone:     document.getElementById('input_phone').value.trim(),
            address:   document.getElementById('input_address').value.trim(),
            role_id:   roleSelect.value,
            role_name: roleName,
        };

        if (roleName !== 'customer') {
            payload.password = document.getElementById('input_password').value;
        }

        if (roleName === 'customer') {
            payload.regency  = regencySel.options[regencySel.selectedIndex]?.dataset?.name  ?? '';
            payload.district = districtSel.options[districtSel.selectedIndex]?.dataset?.name ?? '';
            payload.village  = villageSel.options[villageSel.selectedIndex]?.dataset?.name   ?? '';
        }

        btn.disabled    = true;
        btn.innerHTML   = '<i class="ph-spinner spinner me-2"></i> Memproses...';

        axios.post('/api/users', payload)
            .then(res => {
                modalUser.hide();

                if (res.data.plain_password) {
                    Swal.fire({
                        title: 'Akun Mitra Dibuat!',
                        html: `Password sementara untuk <b>${payload.email}</b>:<br>
                               <div class="mt-2 p-2 bg-light rounded fw-bold fs-5 font-monospace">${res.data.plain_password}</div>
                               <small class="text-muted">Catat password ini — tidak akan ditampilkan lagi.</small>`,
                        icon: 'success',
                        confirmButtonText: 'Sudah Dicatat'
                    });
                } else {
                    Swal.fire('Berhasil', res.data.message, 'success');
                }

                fetchUsers();
            })
            .catch(err => {
                Swal.fire('Gagal', err.response?.data?.message ?? 'Terjadi kesalahan', 'error');
            })
            .finally(() => {
                btn.disabled  = false;
                btn.innerHTML = 'SIMPAN AKUN';
            });
    }

    // ── DELETE ───────────────────────────────────────────────
    function deleteUser(id) {
        Swal.fire({
            title: 'Hapus Akun?',
            text: 'Akun akan dihapus permanen dari sistem.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef5350',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (res.isConfirmed) {
                axios.delete(`/api/users/${id}`)
                    .then(() => {
                        Swal.fire('Terhapus', 'Akun berhasil dihapus', 'success');
                        fetchUsers();
                    })
                    .catch(err => {
                        Swal.fire('Gagal', err.response?.data?.message ?? 'Terjadi kesalahan', 'error');
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
