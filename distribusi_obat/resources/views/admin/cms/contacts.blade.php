@extends('layouts.backoffice')

@section('page_title', 'Manajemen Kontak & Sosmed')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Kontak & Sosial Media</h4>
            <div class="text-muted small">Kelola saluran komunikasi yang tampil di footer Landing Page.</div>
        </div>

        <div class="mt-3 mt-sm-0">
            <button class="btn btn-indigo rounded-pill px-4 shadow-sm fw-bold" onclick="openAddModal()">
                <i class="ph-plus-circle me-2"></i> Tambah Kontak
            </button>
        </div>
    </div>

    <!-- TABLE CARD -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
            <h6 class="mb-0 fw-bold"><i class="ph-phone-call me-2 text-indigo"></i>Daftar Kontak Aktif</h6>
            <div class="ms-auto text-muted small">
                Total: <span id="contactCount" class="fw-bold text-indigo">0</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="fs-xs text-uppercase fw-bold text-muted">
                        <th class="ps-3 py-3">Nama Tampilan (Title)</th>
                        <th>Key Identifier</th>
                        <th>Value / Isi Kontak</th>
                        <th class="text-center">Ikon</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="contactTableBody">
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="ph-spinner spinner text-indigo me-2"></div>
                            <span class="text-muted small">Menghubungkan ke database kontak...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: TAMBAH/EDIT KONTAK (Limitless Style)
     ========================================== -->
<div class="modal fade" id="modalContact" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalTitle">Kontak Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="contactForm" onsubmit="saveContact(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="contact_id">

                    <div class="mb-3">
                        <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Nama Tampilan (Title)</label>
                        <input type="text" name="title" id="title" class="form-control border-0 bg-light py-2 shadow-none" placeholder="Contoh: WhatsApp Layanan" required>
                    </div>

                    <div class="mb-3">
                        <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Key Identitas (Unik)</label>
                        <input type="text" name="key" id="key" class="form-control border-0 bg-light py-2 shadow-none" placeholder="Contoh: whatsapp, email, instagram" required>
                        <div class="form-text fs-xs text-muted">*Gunakan huruf kecil dan tanpa spasi.</div>
                    </div>

                    <div class="mb-3">
                        <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Isi Kontak (Value)</label>
                        <input type="text" name="value" id="value" class="form-control border-0 bg-light py-2 shadow-none" placeholder="Contoh: 0812-xxxx-xxxx" required>
                    </div>

                    <div class="mb-0">
                        <label class="fs-xs fw-bold text-uppercase text-muted mb-1">Ikon / Gambar (Opsional)</label>
                        <input type="file" name="image" id="image" class="form-control border-0 bg-light py-2" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 d-flex justify-content-between p-3">
                    <button type="button" class="btn btn-link text-muted text-decoration-none fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSave" class="btn btn-indigo rounded-pill px-4 fw-bold shadow-sm">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Axios global config sudah ada di master layout

    function fetchContacts() {
        const tableBody = document.getElementById('contactTableBody');

        axios.get('/api/cms/contacts')
            .then(res => {
                const contacts = res.data;
                document.getElementById('contactCount').innerText = contacts.length;
                let html = '';

                if (!contacts || contacts.length === 0) {
                    html = '<tr><td colspan="5" class="text-center py-5 text-muted italic small">Belum ada data kontak yang dibuat.</td></tr>';
                } else {
                    contacts.forEach(c => {
                        const img = c.image ? `/${c.image}` : 'https://placehold.co/40x40?text=?';

                        html += `
                        <tr class="border-bottom">
                            <td class="ps-3 py-3">
                                <div class="fw-bold text-dark">${c.title}</div>
                            </td>
                            <td><code class="text-indigo bg-light px-2 py-1 rounded small">${c.key}</code></td>
                            <td><div class="small text-dark fw-semibold">${c.value}</div></td>
                            <td class="text-center">
                                <img src="${img}" class="rounded-circle shadow-sm border" width="32" height="32" style="object-fit: cover;">
                            </td>
                            <td class="text-center pe-3">
                                <div class="d-flex justify-content-center gap-1">
                                    <button onclick="editContact(${c.id})" class="btn btn-light btn-icon btn-sm rounded-pill shadow-sm border text-indigo" title="Edit">
                                        <i class="ph-pencil-line"></i>
                                    </button>
                                    <button onclick="deleteContact(${c.id}, '${c.title}')" class="btn btn-light btn-icon btn-sm rounded-pill shadow-sm border text-danger" title="Hapus">
                                        <i class="ph-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    });
                }
                tableBody.innerHTML = html;
            })
            .catch(err => {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-danger small">Gagal mengambil data dari server.</td></tr>';
            });
    }

    function openAddModal() {
        document.getElementById('contactForm').reset();
        document.getElementById('contact_id').value = '';
        document.getElementById('modalTitle').innerText = 'Tambah Kontak Baru';
        new bootstrap.Modal(document.getElementById('modalContact')).show();
    }

    function editContact(id) {
        axios.get(`/api/cms/contacts`).then(res => {
            const c = res.data.find(item => item.id === id);
            if(!c) return;

            document.getElementById('contact_id').value = c.id;
            document.getElementById('title').value = c.title;
            document.getElementById('key').value = c.key;
            document.getElementById('value').value = c.value;
            document.getElementById('modalTitle').innerText = 'Edit Informasi Kontak';
            new bootstrap.Modal(document.getElementById('modalContact')).show();
        });
    }

    function saveContact(e) {
        e.preventDefault();
        const id = document.getElementById('contact_id').value;
        const btn = document.getElementById('btnSave');
        const formData = new FormData(e.target);

        // Logic Spoofing untuk PUT (Karena menggunakan FormData)
        if(id) formData.append('_method', 'PUT');
        const url = id ? `/api/cms/contacts/${id}` : '/api/cms/contacts';

        btn.disabled = true;
        btn.innerHTML = '<span class="ph-spinner spinner me-2"></span> Menyimpan...';

        axios.post(url, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalContact')).hide();
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Informasi kontak telah diperbarui.', timer: 1500, showConfirmButton: false });
            fetchContacts();
        })
        .catch(err => {
            const msg = err.response ? err.response.data.message : 'Terjadi kesalahan sistem.';
            Swal.fire('Gagal', msg, 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Simpan Data';
        });
    }

    function deleteContact(id, title) {
        Swal.fire({
            title: 'Hapus Kontak?',
            text: `Kontak "${title}" akan segera dihapus dari Landing Page.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(res => {
            if(res.isConfirmed) {
                axios.delete(`/api/cms/contacts/${id}`).then(() => {
                    Swal.fire({ icon: 'success', title: 'Terhapus!', text: 'Kontak telah dihapus.', timer: 1500, showConfirmButton: false });
                    fetchContacts();
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchContacts);
</script>

<style>
    .bg-indigo { background-color: #5c6bc0 !important; }
    .text-indigo { color: #5c6bc0 !important; }
    .btn-indigo { background-color: #5c6bc0; color: #fff; border: none; }
    .btn-indigo:hover { background-color: #3f51b5; color: #fff; }
    .btn-outline-indigo { color: #5c6bc0; border-color: #5c6bc0; }
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
    .fs-xs { font-size: 0.7rem; }
    /* Limitless spacing */
    .table td { padding: 0.85rem 1.25rem; }
    .table th { padding: 0.75rem 1.25rem; border-top: none; }
</style>
@endsection