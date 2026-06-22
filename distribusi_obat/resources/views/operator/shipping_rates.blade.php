@extends('layouts.backoffice')

@section('page_title', 'Tarif Ongkos Kirim')

@section('content')
<div class="container-fluid">

    {{-- ===================== HEADER ===================== --}}
    <div class="d-flex align-items-center mb-4">
        <div class="flex-fill">
            <h4 class="fw-bold mb-0 text-dark">Tarif Ongkos Kirim</h4>
            <div class="text-muted small">Kelola tarif pengiriman berdasarkan wilayah tujuan dari gudang Sitoluama, Laguboti, Toba Samosir.</div>
        </div>
        <div class="ms-3 d-flex gap-2">
            <button class="btn btn-indigo shadow-sm rounded-pill px-4" onclick="openAddModal()">
                <i class="ph-plus-circle me-2"></i> Tambah Tarif
            </button>
            <button class="btn btn-light shadow-sm rounded-pill px-4" onclick="fetchRates()">
                <i class="ph-arrow-clockwise me-2"></i> Refresh
            </button>
        </div>
    </div>

    {{-- ===================== TIER INFO CARDS ===================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #2e7d32 !important;">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:48px;height:48px;background:#e8f5e9;">
                        <i class="ph-map-pin" style="font-size:20px;color:#2e7d32;"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:.78rem;color:#2e7d32;">same_village</div>
                        <div class="text-muted small">Kelurahan sama dengan gudang</div>
                        <div class="fw-semibold small text-dark">Sitoluama, Laguboti</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #1565c0 !important;">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:48px;height:48px;background:#e3f2fd;">
                        <i class="ph-map-trifold" style="font-size:20px;color:#1565c0;"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:.78rem;color:#1565c0;">same_district</div>
                        <div class="text-muted small">Kecamatan sama, kelurahan beda</div>
                        <div class="fw-semibold small text-dark">Kec. Laguboti</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #f57c00 !important;">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:48px;height:48px;background:#fff3e0;">
                        <i class="ph-buildings" style="font-size:20px;color:#f57c00;"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:.78rem;color:#f57c00;">same_regency</div>
                        <div class="text-muted small">Kabupaten sama, kecamatan beda</div>
                        <div class="fw-semibold small text-dark">Kab. Toba Samosir</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #c62828 !important;">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:48px;height:48px;background:#ffebee;">
                        <i class="ph-globe" style="font-size:20px;color:#c62828;"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:.78rem;color:#c62828;">other_regency</div>
                        <div class="text-muted small">Kabupaten/kecamatan lain di Sumut</div>
                        <div class="fw-semibold small text-dark">Per kabupaten / kecamatan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== FILTER ===================== --}}
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-3">
            <div class="flex-fill">
                <div class="input-group input-group-sm" style="max-width:280px;">
                    <span class="input-group-text bg-white border-end-0"><i class="ph-magnifying-glass text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0"
                        placeholder="Cari wilayah atau tier..." oninput="renderTable()">
                </div>
            </div>
            <select id="filterTier" class="form-select form-select-sm" style="width:auto;" onchange="renderTable()">
                <option value="">Semua Tier</option>
                <option value="same_village">same_village</option>
                <option value="same_district">same_district</option>
                <option value="same_regency">same_regency</option>
                <option value="other_regency">other_regency</option>
            </select>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-uppercase fw-bold text-muted" style="font-size:.72rem;letter-spacing:.04em;">
                        <th class="ps-4">Tier</th>
                        <th>Kabupaten / Kota</th>
                        <th>Kecamatan</th>
                        <th>Tarif (Rp)</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody id="ratesBody">
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm text-indigo me-2"></div>
                            Memuat data tarif...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ===================== MODAL TAMBAH / EDIT ===================== --}}
<div class="modal fade" id="modalRate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            <div class="modal-header bg-indigo text-white border-0 py-3">
                <h6 class="modal-title fw-bold" id="modalRateTitle">
                    <i class="ph-plus-circle me-2"></i> Tambah Tarif
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <input type="hidden" id="editId">

                {{-- TIER --}}
                <div class="mb-3">
                    <label class="field-label">Tier Wilayah</label>
                    <select id="inputTier" class="form-select form-field" onchange="handleTierChange()">
                        <option value="" disabled selected>-- Pilih Tier --</option>
                        <option value="same_village">same_village — Kelurahan yang sama (Sitoluama)</option>
                        <option value="same_district">same_district — Kecamatan yang sama (Laguboti)</option>
                        <option value="same_regency">same_regency — Kab. Toba Samosir (kecamatan lain)</option>
                        <option value="other_regency">other_regency — Kabupaten/Kecamatan lain di Sumut</option>
                    </select>
                    <div class="form-text text-indigo fw-semibold" id="tierHint" style="font-size:.8rem;"></div>
                </div>

                {{-- WILAYAH — hanya muncul kalau tier = other_regency --}}
                <div id="wilayahSection" class="d-none">
                    <div class="mb-3">
                        <label class="field-label">Kabupaten / Kota</label>
                        <select id="inputRegency" class="form-select form-field" onchange="onRegencyChange(this.value)">
                            <option value="" disabled selected>-- Pilih Kabupaten/Kota --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="field-label">
                            Kecamatan
                            <span class="text-muted fw-normal text-lowercase" style="letter-spacing:0;">(opsional — kosongkan untuk tarif seluruh kabupaten)</span>
                        </label>
                        <select id="inputDistrict" class="form-select form-field" disabled>
                            <option value="">— Pilih kabupaten dulu —</option>
                        </select>
                    </div>
                </div>

                {{-- TARIF --}}
                <div class="mb-3">
                    <label class="field-label">Tarif Ongkos Kirim</label>
                    <div class="input-group">
                        <span class="input-group-text" style="border-radius:10px 0 0 10px;border-color:#dbe4ee;">Rp</span>
                        <input type="number" id="inputRate" class="form-control form-field"
                            style="border-radius:0 10px 10px 0;" min="0" placeholder="Contoh: 15000">
                    </div>
                    <div class="form-text">Masukkan 0 untuk gratis.</div>
                </div>
            </div>

            <div class="modal-footer border-0 bg-light py-2">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">BATAL</button>
                <button id="btnSimpanTarif" onclick="submitRate()" class="btn btn-indigo px-4 fw-bold shadow-sm rounded-pill">
                    SIMPAN
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ===================== JAVASCRIPT ===================== --}}
<script>
const PROVINCE_ID           = '12';
const WAREHOUSE_REGENCY_ID  = '1206';

let allRates      = [];
let modalInstance = null;

const tierHints = {
    same_village:  'Berlaku untuk tujuan Kelurahan Sitoluama. Biasanya gratis atau tarif terendah.',
    same_district: 'Berlaku untuk seluruh Kecamatan Laguboti selain Kelurahan Sitoluama.',
    same_regency:  'Berlaku untuk seluruh Kab. Toba Samosir selain Kecamatan Laguboti.',
    other_regency: 'Berlaku untuk kabupaten lain. Bisa dispesifikkan per kecamatan.',
};

const tierColors = {
    same_village:  { bg: '#e8f5e9', color: '#2e7d32', border: '#c8e6c9' },
    same_district: { bg: '#e3f2fd', color: '#1565c0', border: '#bbdefb' },
    same_regency:  { bg: '#fff3e0', color: '#e65100', border: '#ffe0b2' },
    other_regency: { bg: '#ffebee', color: '#c62828', border: '#ffcdd2' },
};

document.addEventListener('DOMContentLoaded', () => {
    modalInstance = new bootstrap.Modal(document.getElementById('modalRate'));
    fetchRates();
    loadRegencies();
});

// ── Fetch & Render ────────────────────────────────────────────────────────────

function fetchRates() {
    showSkeletons();
    axios.get('/api/shipping-rates')
        .then(res => {
            allRates = res.data;
            renderTable();
        })
        .catch(() => {
            document.getElementById('ratesBody').innerHTML =
                `<tr><td colspan="5" class="text-center text-danger py-4">Gagal memuat data tarif.</td></tr>`;
        });
}

function renderTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const filter = document.getElementById('filterTier').value;

    const filtered = allRates.filter(r => {
        const matchTier   = !filter || r.tier === filter;
        const matchSearch = !search
            || r.tier.includes(search)
            || (r.regency_name  || '').toLowerCase().includes(search)
            || (r.district_name || '').toLowerCase().includes(search);
        return matchTier && matchSearch;
    });

    if (filtered.length === 0) {
        document.getElementById('ratesBody').innerHTML =
            `<tr><td colspan="5" class="text-center text-muted py-5">
                <i class="ph-tray fs-2 d-block mb-2 opacity-25"></i>
                Belum ada tarif yang sesuai.
            </td></tr>`;
        return;
    }

    const tierOrder = ['same_village', 'same_district', 'same_regency', 'other_regency'];
    filtered.sort((a, b) => {
        const ta = tierOrder.indexOf(a.tier);
        const tb = tierOrder.indexOf(b.tier);
        if (ta !== tb) return ta - tb;
        return (a.regency_name || '').localeCompare(b.regency_name || '');
    });

    let html = '';
    filtered.forEach(r => {
        const tc  = tierColors[r.tier] || { bg: '#f5f5f5', color: '#555', border: '#ddd' };
        const tierBadge = `<span class="badge rounded-pill px-3 py-2 fw-semibold"
            style="background:${tc.bg};color:${tc.color};border:1px solid ${tc.border};font-size:.75rem;">
            ${r.tier}
        </span>`;

        const kabupaten = r.regency_name
            ? `<span class="fw-semibold text-dark">${r.regency_name}</span>`
            : `<span class="text-muted fst-italic small">— semua kabupaten</span>`;

        const kecamatan = r.district_name
            ? `<span class="text-dark">${r.district_name}</span>`
            : `<span class="text-muted fst-italic small">— semua kecamatan</span>`;

        const tarif = r.rate === 0
            ? `<span class="badge rounded-pill px-3 py-2" style="background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;font-size:.78rem;">
                <i class="ph-check me-1"></i>Gratis
               </span>`
            : `<span class="fw-bold text-dark">Rp ${Number(r.rate).toLocaleString('id-ID')}</span>`;

        const label = `${r.tier}${r.regency_name ? ' – ' + r.regency_name : ''}${r.district_name ? ', ' + r.district_name : ''}`;

        html += `
        <tr>
            <td class="ps-4">${tierBadge}</td>
            <td>${kabupaten}</td>
            <td>${kecamatan}</td>
            <td>${tarif}</td>
            <td class="text-center pe-4">
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-light btn-sm rounded-circle" onclick='openEditModal(${JSON.stringify(r)})'
                            title="Edit" style="width:34px;height:34px;padding:0;">
                        <i class="ph-pencil"></i>
                    </button>
                    <button class="btn btn-danger btn-sm rounded-circle"
                            onclick="deleteRate(${r.id}, '${label.replace(/'/g, "\\'")}')"
                            title="Hapus" style="width:34px;height:34px;padding:0;">
                        <i class="ph-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    });

    document.getElementById('ratesBody').innerHTML = html;
}

function showSkeletons() {
    let html = '';
    for (let i = 0; i < 6; i++) {
        html += `
        <tr>
            <td class="ps-4"><span class="skeleton-line" style="width:110px;height:26px;border-radius:999px;"></span></td>
            <td><span class="skeleton-line" style="width:${100 + i * 15}px;height:14px;"></span></td>
            <td><span class="skeleton-line" style="width:${80 + i * 10}px;height:14px;"></span></td>
            <td><span class="skeleton-line" style="width:80px;height:14px;"></span></td>
            <td class="text-center pe-4">
                <div class="d-flex justify-content-center gap-2">
                    <span class="skeleton-line" style="width:34px;height:34px;border-radius:50%;"></span>
                    <span class="skeleton-line" style="width:34px;height:34px;border-radius:50%;"></span>
                </div>
            </td>
        </tr>`;
    }
    document.getElementById('ratesBody').innerHTML = html;
}

// ── Modal helpers ─────────────────────────────────────────────────────────────

function resetModal() {
    document.getElementById('editId').value       = '';
    document.getElementById('inputTier').value    = '';
    document.getElementById('inputRate').value    = '';
    document.getElementById('tierHint').innerText = '';
    document.getElementById('wilayahSection').classList.add('d-none');
    document.getElementById('inputRegency').value = '';
    document.getElementById('inputDistrict').innerHTML = '<option value="">— Pilih kabupaten dulu —</option>';
    document.getElementById('inputDistrict').disabled  = true;
}

function openAddModal() {
    resetModal();
    document.getElementById('modalRateTitle').innerHTML = '<i class="ph-plus-circle me-2"></i> Tambah Tarif';
    modalInstance.show();
}

async function openEditModal(rate) {
    resetModal();
    document.getElementById('modalRateTitle').innerHTML = '<i class="ph-pencil me-2"></i> Edit Tarif';
    document.getElementById('editId').value    = rate.id;
    document.getElementById('inputRate').value = rate.rate;
    document.getElementById('inputTier').value = rate.tier;
    document.getElementById('tierHint').innerText = tierHints[rate.tier] || '';

    if (rate.tier === 'other_regency') {
        document.getElementById('wilayahSection').classList.remove('d-none');
        if (rate.regency_id) {
            document.getElementById('inputRegency').value = rate.regency_id;
            await loadDistricts(rate.regency_id);
            if (rate.district_id) {
                document.getElementById('inputDistrict').value = rate.district_id;
            }
        }
    }

    modalInstance.show();
}

function handleTierChange() {
    const tier = document.getElementById('inputTier').value;
    document.getElementById('tierHint').innerText = tierHints[tier] || '';

    if (tier === 'other_regency') {
        document.getElementById('wilayahSection').classList.remove('d-none');
    } else {
        document.getElementById('wilayahSection').classList.add('d-none');
        document.getElementById('inputRegency').value = '';
        document.getElementById('inputDistrict').innerHTML = '<option value="">— Pilih kabupaten dulu —</option>';
        document.getElementById('inputDistrict').disabled  = true;
    }
}

function onRegencyChange(regencyId) {
    if (regencyId) loadDistricts(regencyId);
}

// ── Submit ────────────────────────────────────────────────────────────────────

function submitRate() {
    const btn          = document.getElementById('btnSimpanTarif');
    const originalHtml = btn.innerHTML;

    const id        = document.getElementById('editId').value;
    const tier      = document.getElementById('inputTier').value;
    const rate      = document.getElementById('inputRate').value;
    const regencyEl = document.getElementById('inputRegency');
    const districtEl= document.getElementById('inputDistrict');

    if (!tier) {
        Swal.fire({ icon: 'warning', title: 'Tier belum dipilih', text: 'Pilih tier wilayah terlebih dahulu.', confirmButtonColor: '#5c6bc0' });
        return;
    }
    if (rate === '' || Number(rate) < 0) {
        Swal.fire({ icon: 'warning', title: 'Tarif tidak valid', text: 'Masukkan tarif yang valid (minimal 0).', confirmButtonColor: '#5c6bc0' });
        return;
    }

    let regency_id   = null, regency_name = null;
    let district_id  = null, district_name = null;

    if (tier === 'other_regency') {
        regency_id = regencyEl.value;
        if (!regency_id) {
            Swal.fire({ icon: 'warning', title: 'Kabupaten belum dipilih', text: 'Pilih kabupaten/kota untuk tier other_regency.', confirmButtonColor: '#5c6bc0' });
            return;
        }
        regency_name  = regencyEl.options[regencyEl.selectedIndex]?.getAttribute('data-name');
        district_id   = districtEl.value || null;
        district_name = district_id
            ? districtEl.options[districtEl.selectedIndex]?.getAttribute('data-name')
            : null;
    }

    const payload = { tier, regency_id, regency_name, district_id, district_name, rate: parseInt(rate) };
    const isEdit  = !!id;
    const url     = isEdit ? `/api/shipping-rates/${id}` : '/api/shipping-rates';
    const method  = isEdit ? 'put' : 'post';

    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

    axios[method](url, payload)
        .then(() => {
            modalInstance.hide();
            Swal.fire({
                icon: 'success',
                title: isEdit ? 'Tarif diperbarui' : 'Tarif ditambahkan',
                timer: 1500,
                showConfirmButton: false,
            });
            fetchRates();
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

// ── Delete ────────────────────────────────────────────────────────────────────

function deleteRate(id, label) {
    Swal.fire({
        title: 'Hapus Tarif Ini?',
        html: `<b>${label}</b> akan dihapus secara permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return axios.delete(`/api/shipping-rates/${id}`)
                .catch(err => {
                    Swal.showValidationMessage(err.response?.data?.message ?? 'Gagal menghapus tarif.');
                });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then(result => {
        if (!result.isConfirmed) return;
        Swal.fire({ icon: 'success', title: 'Tarif dihapus', timer: 1200, showConfirmButton: false });
        fetchRates();
    });
}

// ── Wilayah loaders ───────────────────────────────────────────────────────────

async function loadRegencies() {
    try {
        const data = await (await fetch(
            `https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${PROVINCE_ID}.json`
        )).json();

        let html = '<option value="" disabled selected>-- Pilih Kabupaten/Kota --</option>';
        data.forEach(item => {
            if (item.id !== WAREHOUSE_REGENCY_ID) {
                html += `<option value="${item.id}" data-name="${item.name}">${item.name}</option>`;
            }
        });
        document.getElementById('inputRegency').innerHTML = html;
    } catch (e) {
        console.error('Gagal muat kabupaten:', e);
    }
}

async function loadDistricts(regencyId) {
    const sel = document.getElementById('inputDistrict');
    sel.disabled = true;
    sel.innerHTML = '<option value="">Memuat...</option>';
    try {
        const data = await (await fetch(
            `https://www.emsifa.com/api-wilayah-indonesia/api/districts/${regencyId}.json`
        )).json();

        let html = '<option value="">— Berlaku semua kecamatan di kabupaten ini —</option>';
        data.forEach(item => {
            html += `<option value="${item.id}" data-name="${item.name}">${item.name}</option>`;
        });
        sel.innerHTML = html;
        sel.disabled  = false;
    } catch (e) {
        sel.innerHTML = '<option value="">Gagal memuat kecamatan</option>';
        console.error('Gagal muat kecamatan:', e);
    }
}
</script>

{{-- ===================== CSS ===================== --}}
<style>
.bg-indigo        { background: #5c6bc0 !important; }
.btn-indigo       { background: #5c6bc0; color: #fff; border: none; }
.btn-indigo:hover { background: #4a5ab0; color: #fff; }
.text-indigo      { color: #5c6bc0 !important; }

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
