@extends('layouts.portal')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-lg p-4 rounded-4">
                <div class="text-center mb-4">
                    <div class="badge bg-soft-primary text-primary mb-2" style="background: #e7f1ff; padding: 10px 20px;">
                        <i class="bi bi-pencil-square"></i> Form Permintaan Khusus
                    </div>
                    <h3 class="fw-bold">Request Obat Baru</h3>
                    <p class="text-muted">Gunakan form ini untuk memesan obat yang tidak tersedia di katalog kami.</p>
                </div>

                <form id="manualReqForm">
                    <div id="manualItemsContainer">
                        <!-- Row Pertama -->
                        <div class="item-row bg-light p-3 rounded-4 mb-3 position-relative border">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Nama Obat <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control custom-name" placeholder="Misal: Vitamin D3 1000 IU" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">Jumlah <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control custom-qty" min="1" placeholder="0" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">Unit/Satuan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control custom-unit" placeholder="Botol/Box" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="button" onclick="addMoreRow()" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="bi bi-plus-circle"></i> Tambah Obat Lain
                        </button>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Catatan Tambahan (Opsional)</label>
                        <textarea id="manualNotes" class="form-control" rows="3" placeholder="Berikan info tambahan seperti merk spesifik atau urgensi..."></textarea>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <a href="/dashboard" class="btn btn-light w-100 py-3 rounded-pill fw-bold">Batal</a>
                        </div>
                        <div class="col-6">
                            <button type="button" id="btnSubmitManual" onclick="submitManualRequest()" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow">
                                <i class="bi bi-send-fill"></i> Kirim Permintaan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi Menambah Baris Baru
    function addMoreRow() {
        const container = document.getElementById('manualItemsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'item-row bg-light p-3 rounded-4 mb-3 position-relative border';
        newRow.innerHTML = `
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()"></button>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Nama Obat</label>
                    <input type="text" class="form-control custom-name" placeholder="Nama Obat" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Jumlah</label>
                    <input type="number" class="form-control custom-qty" min="1" placeholder="0" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Unit/Satuan</label>
                    <input type="text" class="form-control custom-unit" placeholder="Unit" required>
                </div>
            </div>`;
        container.appendChild(newRow);
    }

    // Fungsi Submit ke API
    function submitManualRequest() {
        const rows = document.querySelectorAll('.item-row');
        const btn = document.getElementById('btnSubmitManual');
        let items = [];
        let isValid = true;

        // 1. Ambil data dan Validasi sederhana
        rows.forEach(row => {
            const name = row.querySelector('.custom-name').value;
            const qty = row.querySelector('.custom-qty').value;
            const unit = row.querySelector('.custom-unit').value;

            if(!name || !qty || !unit) {
                isValid = false;
                return;
            }

            items.push({
                custom_drug_name: name,
                qty: qty, // Kita gunakan key 'qty' agar konsisten dengan RequestController
                custom_unit: unit
            });
        });

        if(!isValid) {
            Swal.fire('Peringatan', 'Harap lengkapi semua field nama, jumlah, dan unit obat!', 'warning');
            return;
        }

        // 2. Konfirmasi & Kirim
        Swal.fire({
            title: 'Kirim Permintaan?',
            text: "Permintaan Anda akan diproses manual oleh Admin Gudang.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim!',
            confirmButtonColor: '#0d6efd'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan Loading
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengirim...';

                axios.post('/api/requests', {
                    items: items,
                    notes: document.getElementById('manualNotes').value
                })
                .then(res => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terkirim!',
                        text: 'Permintaan manual berhasil diajukan.',
                        confirmButtonColor: '#0d6efd'
                    }).then(() => {
                        window.location.href = '/customer/history';
                    });
                })
                .catch(err => {
                    console.error(err);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-send-fill"></i> Kirim Permintaan';
                    Swal.fire('Gagal', 'Terjadi kesalahan saat mengirim data. Cek koneksi Anda.', 'error');
                });
            }
        });
    }
</script>
@endsection