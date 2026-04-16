@extends('layouts.portal')

@section('content')
<style>
    /* MODIFIKASI: Mengubah header agar menyatu secara natural */
    .page-header {
        background: transparent;
        padding: 30px 0;
        border-bottom: none;
        margin-bottom: 20px;
    }
    .section-heading {
        color: #2c4964;
        font-weight: 700;
        position: relative;
        padding-bottom: 15px;
        font-family: 'Poppins', sans-serif;
        font-size: 2rem;
    }
    .section-heading::after {
        content: "";
        position: absolute;
        width: 50px;
        height: 4px;
        background: #3fbbc0;
        bottom: 0;
        left: 0;
        border-radius: 2px;
    }

    /* Card Styling */
    .card-cart {
        border: none;
        border-radius: 15px;
        background: #fff;
        border-left: 5px solid #3fbbc0;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: 0.3s;
        margin-bottom: 15px;
    }
    .card-cart:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }

    /* Quantity Controls */
    .qty-control {
        background: #f8f9fa;
        border-radius: 30px;
        padding: 5px;
        border: 1px solid #eee;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-qty {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #dee2e6;
        color: #3fbbc0;
        font-weight: bold;
        cursor: pointer;
        transition: 0.2s;
        padding: 0;
    }
    .btn-qty:hover { background: #3fbbc0; color: #fff; border-color: #3fbbc0; }

    .card-summary {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        overflow: hidden;
        z-index: 10;
    }

    .btn-medinest {
        background: #3fbbc0;
        color: white;
        border-radius: 30px;
        padding: 12px 25px;
        font-weight: 600;
        border: none;
        width: 100%;
        transition: 0.3s;
    }
    .btn-medinest:hover:not(:disabled) { background: #329ea2; transform: translateY(-2px); }
    .btn-medinest:disabled { background: #a5d8da; cursor: not-allowed; }

    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
</style>

<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h2 class="section-heading">Keranjang Permintaan</h2>
                <p class="text-muted">Tinjau daftar sediaan farmasi sebelum dikirim ke pusat logistik.</p>
            </div>
            <div class="col-md-5 text-md-end">
                <button id="btnClearCart" onclick="clearCart()" class="btn btn-outline-danger rounded-pill px-4 bg-white shadow-sm" style="display: none;">
                    <i class="bi bi-trash3 me-1"></i> Kosongkan
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row g-4">
        <!-- LIST ITEM -->
        <div class="col-lg-8">
            <div id="cartItemsContainer">
                <div class="text-center py-5">
                    <div class="spinner-border text-info"></div>
                </div>
            </div>
        </div>

        <!-- SUMMARY -->
        <div class="col-lg-4">
            <div class="card card-summary sticky-top" style="top: 100px;">
                <div class="p-4 bg-light border-bottom text-center">
                    <h5 class="fw-bold m-0">Ringkasan Permintaan</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Metode Pengambilan</label>
                        <select id="request_type" class="form-select border-light shadow-sm bg-light">
                            <option value="delivery">🚚 Kurir Logistik Internal</option>
                            <option value="self_pickup">🏢 Ambil Sendiri di Gudang</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Total Jenis</span>
                        <span class="fw-bold" id="totalKinds">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 align-items-center">
                        <span class="text-muted small">Total Kuantitas</span>
                        <span class="fw-bold text-info fs-4" id="totalQty">0</span>
                    </div>

                    <hr class="my-4 opacity-25">

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Catatan Tambahan</label>
                        <textarea id="checkoutNotes" class="form-control bg-light border-0 small" rows="3" placeholder="Contoh: Titip di resepsionis..."></textarea>
                    </div>

                    <button id="btnCheckout" onclick="processCheckout()" class="btn btn-medinest shadow-sm mb-3" disabled>
                        Proses Pengajuan <i class="bi bi-send-fill ms-2"></i>
                    </button>

                    <a href="/#katalog" class="btn btn-link w-100 text-muted text-decoration-none small">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Item Lain
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function fetchCart() {
        const container = document.getElementById('cartItemsContainer');
        const btnClear = document.getElementById('btnClearCart');
        const btnCheckout = document.getElementById('btnCheckout');

        axios.get('/api/cart')
            .then(res => {
                const carts = res.data;
                let html = '';
                let totalQuantity = 0;

                if (!carts || carts.length === 0) {
                    btnClear.style.display = 'none';
                    btnCheckout.disabled = true;
                    html = `<div class="text-center py-5 bg-white rounded-4 border border-dashed shadow-sm">
                                <i class="bi bi-cart-x text-muted opacity-25" style="font-size: 5rem;"></i>
                                <h4 class="fw-bold mt-3">Keranjang Kosong</h4>
                                <a href="/#katalog" class="btn btn-medinest px-5 mt-2 w-auto">Lihat Katalog</a>
                            </div>`;
                } else {
                    btnClear.style.display = 'inline-block';
                    btnCheckout.disabled = false;

                    carts.forEach(item => {
                        const product = item.product || {}; // Perubahan dari item.drug ke item.product
                        const img = product.image ? `/${product.image}` : 'https://placehold.co/200x200?text=Produk';
                        const currentQty = parseInt(item.quantity);
                        totalQuantity += currentQty;

                        html += `
                        <div class="card card-cart">
                            <div class="card-body p-3">
                                <div class="row align-items-center text-center text-md-start">
                                    <div class="col-md-2">
                                        <img src="${img}" class="img-fluid rounded-3" style="max-height: 80px;">
                                    </div>
                                    <div class="col-md-5">
                                        <h6 class="fw-bold text-dark mb-1">${product.name || 'Produk'}</h6>
                                        <div class="text-muted small">Kemasan: ${product.unit || '-'}</div>
                                        <span class="badge bg-light text-primary border mt-1">Stok Gudang: ${product.stock}</span>
                                    </div>
                                    <div class="col-md-3 mt-3 mt-md-0">
                                        <div class="qty-control">
                                            <button type="button" class="btn-qty" onclick="changeQty(${item.id}, ${currentQty - 1}, ${product.stock})">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <input type="number" class="form-control text-center border-0 bg-transparent fw-bold"
                                                   style="width: 60px;" value="${currentQty}"
                                                   onchange="changeQty(${item.id}, this.value, ${product.stock})">
                                            <button type="button" class="btn-qty" onclick="changeQty(${item.id}, ${currentQty + 1}, ${product.stock})">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-md-end mt-3 mt-md-0">
                                        <button class="btn btn-link text-danger p-0" onclick="deleteItem(${item.id})">
                                            <i class="bi bi-trash fs-5"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    });
                }

                container.innerHTML = html;
                document.getElementById('totalKinds').innerText = carts.length;
                document.getElementById('totalQty').innerText = totalQuantity;
                if(typeof updateCartBadge === 'function') updateCartBadge();
            });
    }

    function changeQty(cartId, newQty, maxStock) {
        const qty = parseInt(newQty);
        if (qty < 1) { deleteItem(cartId); return; }
        if (qty > maxStock) {
            Swal.fire({ icon: 'warning', title: 'Stok Terbatas', text: `Sisa stok hanya ${maxStock} unit.`, confirmButtonColor: '#3fbbc0' });
            fetchCart(); return;
        }
        axios.put(`/api/cart/${cartId}`, { quantity: qty }).then(() => fetchCart());
    }

    function deleteItem(cartId) {
        axios.delete(`/api/cart/${cartId}`).then(() => fetchCart());
    }

    function clearCart() {
        Swal.fire({
            title: 'Kosongkan Keranjang?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3fbbc0',
            confirmButtonText: 'Ya, Kosongkan!'
        }).then((result) => {
            if (result.isConfirmed) axios.delete('/api/cart-clear').then(() => fetchCart());
        });
    }

    function processCheckout() {
        const notes = document.getElementById('checkoutNotes').value;
        const reqType = document.getElementById('request_type').value;

        Swal.fire({
            title: 'Kirim Pengajuan?',
            text: "Daftar permintaan akan dikirim ke petugas gudang.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3fbbc0',
            confirmButtonText: 'Ya, Kirim'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Mengirim...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                // PERBAIKAN: Ganti URL dari /api/requests menjadi /api/orders
                axios.post('/api/orders', { notes: notes, request_type: reqType })
                    .then(() => {
                        Swal.fire({ icon: 'success', title: 'Berhasil', confirmButtonColor: '#3fbbc0' })
                            .then(() => window.location.href = '/customer/history');
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Gagal', err.response?.data?.message || 'Terjadi kesalahan sistem.', 'error');
                    });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', fetchCart);
</script>
@endsection
