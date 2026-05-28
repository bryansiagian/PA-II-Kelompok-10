@extends('layouts.backoffice')

@section('page_title', 'Kelola Armada Kendaraan')

@section('content')
<div class="container-fluid">

    {{-- ===================== HEADER ===================== --}}
    <div class="d-flex align-items-center mb-4">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0 text-dark">Armada Kendaraan</h4>
            <div class="text-muted small">Kelola kendaraan operasional pengiriman — motor dan mobil.</div>
        </div>
        <div class="ms-3 d-flex gap-2">
            <button class="btn btn-indigo shadow-sm rounded-pill px-4" onclick="openAddModal()">
                <i class="ph-plus-circle me-2"></i> Tambah Kendaraan
            </button>
            <button class="btn btn-light shadow-sm rounded-pill px-4" onclick="fetchVehicles()">
                <i class="ph-arrow-clockwise me-2"></i> Refresh
            </button>
        </div>
    </div>

    {{-- ===================== STATS ===================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #5c6bc0 !important;">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:48px;height:48px;background:#ede7f6;">
                        <i class="ph-truck" style="font-size:20px;color:#5c6bc0;"></i>
                    </div>
                    <div>
                        <div class="text-muted fw-bold text-uppercase" style="font-size:10px;letter-spacing:.05em">Total Kendaraan</div>
                        <div class="fw-bold lh-1" style="font-size:1.6rem;" id="statTotal">-</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #2e7d32 !important;">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:48px;height:48px;background:#e8f5e9;">
                        <i class="ph-check-circle" style="font-size:20px;color:#2e7d32;"></i>
                    </div>
                    <div>
                        <div class="text-muted fw-bold text-uppercase" style="font-size:10px;letter-spacing:.05em">Tersedia</div>
                        <div class="fw-bold lh-1" style="font-size:1.6rem;" id="statAvailable">-</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #f57c00 !important;">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:48px;height:48px;background:#fff3e0;">
                        <i class="ph-clock" style="font-size:20px;color:#f57c00;"></i>
                    </div>
                    <div>
                        <div class="text-muted fw-bold text-uppercase" style="font-size:10px;letter-spacing:.05em">Sedang Dipakai</div>
                        <div class="fw-bold lh-1" style="font-size:1.6rem;" id="statBusy">-</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #9e9e9e !important;">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:48px;height:48px;background:#f5f5f5;">
                        <i class="ph-prohibit" style="font-size:20px;color:#9e9e9e;"></i>
                    </div>
                    <div>
                        <div class="text-muted fw-bold text-uppercase" style="font-size:10px;letter-spacing:.05em">Nonaktif</div>
                        <div class="fw-bold lh-1" style="font-size:1.6rem;" id="statInactive">-</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== TABLE ===================== --}}
    <div class="card shadow-sm border-0 rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-uppercase fw-bold text-muted" style="font-size:.72rem;letter-spacing:.04em;">
                        <th class="ps-4">Kendaraan</th>
                        <th>Tipe</th>
                        <th>Plat Nomor</th>
                        <th>Warna</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody id="vehicleTableBody">
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm text-indigo me-2"></div>
                            Memuat data kendaraan...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>


{{-- =====================================================
     MODAL TAMBAH / EDIT
===================================================== --}}
<div class="modal fade" id="modalVehicle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold" id="modalVehicleTitle">
                    <i class="ph-plus-circle me-2"></i> Tambah Kendaraan
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                <input type="hidden" id="vehicle_id">

                <div class="mb-3">
                    <label class="field-label">Tipe Kendaraan</label>
                    <select id="input_type" class="form-select form-field" onchange="onTypeChange(this.value)">
                        <option value="" disabled selected>-- Pilih Tipe --</option>
                        <option value="motorcycle">Motor</option>
                        <option value="car">Mobil</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="field-label">Sub-tipe</label>
                    <select id="input_subtype" class="form-select form-field" disabled>
                        <option value="" disabled selected>-- Pilih Tipe dulu --</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="field-label">Merek / Brand</label>
                    <input type="text" id="input_brand" class="form-control form-field" placeholder="Honda, Yamaha, Toyota, dll.">
                </div>

                <div class="mb-3">
                    <label class="field-label">Nomor Plat</label>
                    <input type="text" id="input_plate" class="form-control form-field" placeholder="B 1234 ABC" style="text-transform:uppercase">
                </div>

                <div class="mb-3">
                    <label class="field-label">Warna</label>
                    <input type="text" id="input_color" class="form-control form-field" placeholder="Hitam, Putih, Merah, dll.">
                </div>

                <div class="mb-1 d-none" id="activeToggleWrapper">
                    <label class="field-label">Status</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="input_active" checked>
                        <label class="form-check-label" for="input_active">Aktif</label>
                    </div>
                </div>

            </div>

            <div class="modal-footer border-0 bg-light py-2">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                <button id="btnSimpanKendaraan" onclick="submitVehicle()" class="btn btn-indigo px-4 fw-bold shadow-sm rounded-pill">
                    SIMPAN
                </button>
            </div>

        </div>
    </div>
</div>


{{-- =====================================================
     JAVASCRIPT
===================================================== --}}
<script>

const SUBTYPES = {
    motorcycle: ['Bebek', 'Sport', 'Matic', 'Trail'],
    car:        ['Sedan', 'SUV', 'Van', 'Pickup', 'MPV'],
};

let modalInstance = null;
let editingId     = null;

document.addEventListener('DOMContentLoaded', () => {
    modalInstance = new bootstrap.Modal(document.getElementById('modalVehicle'));
    fetchVehicles();
});

// ─── FETCH & RENDER ──────────────────────────────────

function fetchVehicles() {
    document.getElementById('vehicleTableBody').innerHTML = `
        <tr><td colspan="6" class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm text-indigo me-2"></div> Memuat...
        </td></tr>`;

    axios.get('/api/vehicles/with-status')
        .then(res => {
            const vehicles = res.data;

            const total     = vehicles.length;
            const busy      = vehicles.filter(v => v.is_busy).length;
            const inactive  = vehicles.filter(v => !v.active).length;
            const available = vehicles.filter(v => v.active && !v.is_busy).length;

            document.getElementById('statTotal').innerText     = total;
            document.getElementById('statAvailable').innerText = available;
            document.getElementById('statBusy').innerText      = busy;
            document.getElementById('statInactive').innerText  = inactive;

            if (!vehicles.length) {
                document.getElementById('vehicleTableBody').innerHTML = `
                    <tr><td colspan="6" class="text-center py-5 text-muted">
                        Belum ada kendaraan terdaftar.
                    </td></tr>`;
                return;
            }

            let html = '';
            vehicles.forEach(v => {

                const isCar = v.type === 'car';

                const avatarIcon  = isCar ? 'ph-truck'      : 'ph-bicycle';
                const avatarBg    = isCar ? '#e3f2fd'        : '#ede7f6';
                const avatarColor = isCar ? '#1565c0'        : '#5c6bc0';

                // Tipe badge
                const typeLabel   = isCar ? 'Mobil' : 'Motor';
                const typeBg      = isCar ? '#e3f2fd' : '#ede7f6';
                const typeColor   = isCar ? '#1565c0' : '#5c6bc0';
                const typeBorder  = isCar ? '#bbdefb' : '#d1c4e9';

                // Status badge
                let statusHtml = '';
                if (!v.active) {
                    statusHtml = `<span class="badge rounded-pill px-3 py-2" style="background:#eeeeee;color:#757575;font-size:.78rem;">
                        <i class="ph-prohibit me-1"></i>Nonaktif
                    </span>`;
                } else if (v.is_busy) {
                    statusHtml = `<span class="badge rounded-pill px-3 py-2" style="background:#fff3e0;color:#e65100;border:1px solid #ffe0b2;font-size:.78rem;">
                        <i class="ph-clock me-1"></i>Dipakai
                    </span>`;
                } else {
                    statusHtml = `<span class="badge rounded-pill px-3 py-2" style="background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;font-size:.78rem;">
                        <i class="ph-check me-1"></i>Tersedia
                    </span>`;
                }

                const deleteBtnStyle = v.is_busy
                    ? 'opacity:.4;pointer-events:none;cursor:not-allowed;'
                    : '';

                html += `
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:40px;height:40px;background:${avatarBg};">
                                <i class="${avatarIcon}" style="font-size:18px;color:${avatarColor};"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark">${v.brand} <span class="text-capitalize">${v.subtype}</span></div>
                                <div class="text-muted small font-monospace">${v.plate_number}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge rounded-pill px-3 py-2 fw-semibold"
                              style="background:${typeBg};color:${typeColor};border:1px solid ${typeBorder};font-size:.78rem;">
                            ${typeLabel}
                        </span>
                    </td>
                    <td>
                        <span class="fw-semibold font-monospace text-dark">${v.plate_number}</span>
                    </td>
                    <td>
                        <span class="text-dark">${v.color}</span>
                    </td>
                    <td class="text-center">${statusHtml}</td>
                    <td class="text-center pe-4">
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-light btn-sm rounded-circle" onclick="openEditModal(${v.id})" title="Edit"
                                    style="width:34px;height:34px;padding:0;">
                                <i class="ph-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm rounded-circle" onclick="deleteVehicle(${v.id}, '${v.brand} ${v.subtype} - ${v.plate_number}')"
                                    title="${v.is_busy ? 'Sedang dipakai kurir' : 'Hapus'}"
                                    style="width:34px;height:34px;padding:0;${deleteBtnStyle}">
                                <i class="ph-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            });

            document.getElementById('vehicleTableBody').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('vehicleTableBody').innerHTML = `
                <tr><td colspan="6" class="text-center py-5 text-danger">
                    Gagal memuat data kendaraan.
                </td></tr>`;
        });
}

// ─── SUBTYPE DROPDOWN ────────────────────────────────

function onTypeChange(type) {
    const sel = document.getElementById('input_subtype');
    if (!type || !SUBTYPES[type]) {
        sel.innerHTML = '<option value="" disabled selected>-- Pilih Tipe dulu --</option>';
        sel.disabled  = true;
        return;
    }
    let html = '<option value="" disabled selected>-- Pilih Sub-tipe --</option>';
    SUBTYPES[type].forEach(s => {
        html += `<option value="${s.toLowerCase()}">${s}</option>`;
    });
    sel.innerHTML = html;
    sel.disabled  = false;
}

// ─── OPEN MODAL ADD ──────────────────────────────────

function openAddModal() {
    editingId = null;
    document.getElementById('modalVehicleTitle').innerHTML = '<i class="ph-plus-circle me-2"></i> Tambah Kendaraan';
    document.getElementById('vehicle_id').value   = '';
    document.getElementById('input_type').value   = '';
    document.getElementById('input_subtype').innerHTML = '<option value="" disabled selected>-- Pilih Tipe dulu --</option>';
    document.getElementById('input_subtype').disabled  = true;
    document.getElementById('input_brand').value  = '';
    document.getElementById('input_plate').value  = '';
    document.getElementById('input_color').value  = '';
    document.getElementById('input_active').checked = true;
    document.getElementById('activeToggleWrapper').classList.add('d-none');
    modalInstance.show();
}

// ─── OPEN MODAL EDIT ─────────────────────────────────

function openEditModal(id) {
    axios.get('/api/vehicles').then(res => {
        const v = res.data.find(x => x.id === id);
        if (!v) return;

        editingId = id;
        document.getElementById('modalVehicleTitle').innerHTML = '<i class="ph-pencil me-2"></i> Edit Kendaraan';
        document.getElementById('vehicle_id').value  = v.id;
        document.getElementById('input_type').value  = v.type;
        onTypeChange(v.type);

        setTimeout(() => {
            document.getElementById('input_subtype').value = v.subtype;
        }, 50);

        document.getElementById('input_brand').value  = v.brand;
        document.getElementById('input_plate').value  = v.plate_number;
        document.getElementById('input_color').value  = v.color;
        document.getElementById('input_active').checked = v.active == 1;
        document.getElementById('activeToggleWrapper').classList.remove('d-none');

        modalInstance.show();
    });
}

// ─── SUBMIT ──────────────────────────────────────────

function submitVehicle() {
    const btn          = document.getElementById('btnSimpanKendaraan');
    const originalHtml = btn.innerHTML;

    const type    = document.getElementById('input_type').value;
    const subtype = document.getElementById('input_subtype').value;
    const brand   = document.getElementById('input_brand').value.trim();
    const plate   = document.getElementById('input_plate').value.trim();
    const color   = document.getElementById('input_color').value.trim();
    const active  = document.getElementById('input_active').checked;

    if (!type || !subtype || !brand || !plate || !color) {
        Swal.fire({ icon: 'warning', title: 'Data tidak lengkap', text: 'Semua field wajib diisi.', confirmButtonColor: '#5c6bc0' });
        return;
    }

    const payload = { type, subtype, brand, plate_number: plate.toUpperCase(), color, active };

    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

    const request = editingId
        ? axios.put(`/api/vehicles/${editingId}`, payload)
        : axios.post('/api/vehicles', payload);

    request
        .then(() => {
            modalInstance.hide();
            Swal.fire({ icon: 'success', title: editingId ? 'Kendaraan diperbarui' : 'Kendaraan ditambahkan', timer: 1500, showConfirmButton: false });
            fetchVehicles();
        })
        .catch(err => {
            const msg = err.response?.data?.message
                ?? (err.response?.data?.errors ? Object.values(err.response.data.errors)[0][0] : 'Terjadi kesalahan.');
            Swal.fire({ icon: 'error', title: 'Gagal', text: msg, confirmButtonColor: '#d33' });
        })
        .finally(() => {
            btn.disabled  = false;
            btn.innerHTML = originalHtml;
        });
}

// ─── DELETE ──────────────────────────────────────────

function deleteVehicle(id, label) {
    Swal.fire({
        title: 'Hapus Kendaraan?',
        html: `<b>${label}</b> akan dihapus secara permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return axios.delete(`/api/vehicles/${id}`)
                .catch(err => {
                    Swal.showValidationMessage(err.response?.data?.message ?? 'Gagal menghapus kendaraan.');
                });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then(result => {
        if (!result.isConfirmed) return;
        Swal.fire({ icon: 'success', title: 'Dihapus', timer: 1200, showConfirmButton: false });
        fetchVehicles();
    });
}

</script>


{{-- =====================================================
     CSS
===================================================== --}}
<style>
.bg-indigo  { background: #5c6bc0 !important; }
.btn-indigo { background: #5c6bc0; color: #fff; border: none; }
.btn-indigo:hover { background: #4a5ab0; color: #fff; }
.text-indigo { color: #5c6bc0 !important; }

.field-label {
    display: block;
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: .04em;
    margin-bottom: 6px;
}
.form-field {
    border-radius: 10px;
    min-height: 44px;
    border: 1px solid #dbe4ee;
    padding: 10px 14px;
    font-size: .9rem;
}
.form-field:focus {
    border-color: #5c6bc0;
    box-shadow: 0 0 0 3px rgba(92, 107, 192, .15);
}
</style>

@endsection
