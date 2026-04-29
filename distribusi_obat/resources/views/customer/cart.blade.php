@extends('layouts.portal')

@section('content')
<style>
    :root {
        --primary: #00838f;
        --secondary: #2c4964;
        --hover-color: #006064;
    }

    .page-header { background: transparent; padding: 30px 0; border-bottom: none; margin-bottom: 20px; }
    .section-heading { color: var(--secondary); font-weight: 700; position: relative; padding-bottom: 15px; font-family: 'Poppins', sans-serif; font-size: 2rem; }
    .section-heading::after { content: ""; position: absolute; width: 50px; height: 4px; background: var(--primary); bottom: 0; left: 0; border-radius: 2px; }

    .card-cart { border: none; border-radius: 15px; background: #fff; border-left: 5px solid var(--primary); box-shadow: 0 2px 12px rgba(0,0,0,0.04); transition: 0.3s; margin-bottom: 15px; }
    .card-cart:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }

    .qty-control { background: #f8f9fa; border-radius: 30px; padding: 5px; border: 1px solid #eee; display: inline-flex; align-items: center; justify-content: center; }
    .btn-qty { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #dee2e6; color: var(--primary); font-weight: bold; cursor: pointer; transition: 0.2s; padding: 0; }
    .btn-qty:hover { background: var(--primary); color: #fff; border-color: var(--primary); }

    .card-summary { border: none; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); overflow: hidden; z-index: 10; }
    .btn-medinest { background: var(--primary); color: white !important; border-radius: 30px; padding: 12px 25px; font-weight: 600; border: none; width: 100%; transition: 0.3s; }
    .btn-medinest:hover:not(:disabled) { background: var(--hover-color); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 131, 143, 0.3); }
    .btn-medinest:disabled { background: #a5d8da; cursor: not-allowed; }

    .text-teal { color: var(--primary) !important; }
    .detail-label { font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; margin-bottom: 5px; display: block;}
    .address-box { background-color: #f8fcfc; border: 1px solid #e0eeee; border-radius: 15px; }

    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
</style>

<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h2 class="section-heading">Keranjang Permintaan</h2>
                <p class="text-muted">Tinjau daftar sediaan dan tentukan lokasi tujuan distribusi.</p>
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
        <div class="col-lg-7">
            <div id="cartItemsContainer">
                <div class="text-center py-5">
                    <div class="spinner-border text-teal" role="status"></div>
                </div>
            </div>
        </div>

        <!-- SUMMARY & LOGISTICS -->
        <div class="col-lg-5">
            <div class="card card-summary sticky-top" style="top: 100px;">
                <div class="p-4 bg-light border-bottom text-center">
                    <h5 class="fw-bold m-0" style="color: var(--secondary);">Konfirmasi Logistik</h5>
                </div>
                <div class="card-body p-4">

                    <!-- WILAYAH SUMATERA UTARA (API DRIVEN) -->
                    <div class="mb-4">
                        <label class="detail-label text-teal"><i class="bi bi-geo-alt-fill me-1"></i> Hub Regional Lokal (Sumut)</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <select id="regency" class="form-select form-select-sm shadow-sm" onchange="fetchDistricts(this.value)">
                                    <option value="" selected disabled>Pilih Kab/Kota</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select id="district" class="form-select form-select-sm shadow-sm" onchange="fetchVillages(this.value)" disabled>
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                            <div class="col-md-12 mt-2">
                                <select id="village" class="form-select form-select-sm shadow-sm" disabled>
                                    <option value="">Pilih Kelurahan/Desa</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- ALAMAT TUJUAN -->
                    <div class="mb-4">
                        <label class="detail-label">Alamat Pengiriman</label>
                        <div class="address-box p-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="addr_type" id="addr_profile" value="profile" checked onchange="toggleAddrInput()">
                                <label class="form-check-label small fw-bold" for="addr_profile">Gunakan Alamat Akun</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="addr_type" id="addr_custom" value="custom" onchange="toggleAddrInput()">
                                <label class="form-check-label small fw-bold" for="addr_custom">Input Alamat Pengiriman Baru</label>
                            </div>
                            <textarea id="shipping_address" class="form-control form-control-sm mt-3 d-none border-0 shadow-sm" rows="3" placeholder="Masukkan nama jalan, nomor bangunan, blok..."></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="detail-label">Metode</label>
                            <select id="request_type" class="form-select form-select-sm">
                                <option value="delivery">Kirim Kurir</option>
                                <option value="self_pickup">Ambil Sendiri</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="detail-label">Total Item</label>
                            <div class="fw-bold fs-5 text-teal" id="totalQty">0</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="detail-label">Catatan Tambahan</label>
                        <textarea id="checkoutNotes" class="form-control form-control-sm" rows="2" placeholder="Contoh: Unit Gawat Darurat..."></textarea>
                    </div>

                    <button id="btnCheckout" onclick="processCheckout()" class="btn btn-medinest shadow-sm mb-3" disabled>
                        Kirim Pengajuan <i class="bi bi-send-fill ms-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Token ini hanya untuk API Internal kita
    const apiToken = '{{ session('api_token') }}';
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + apiToken;

    // Sumatera Utara ID = 12
    const PROVINCE_ID = '12';

    document.addEventListener('DOMContentLoaded', () => {
        fetchCart();
        fetchRegencies(); // Load Kabupaten saat pertama kali buka
    });

    // --- LOGIKA API WILAYAH (MENGGUNAKAN FETCH AGAR BEBAS DARI HEADER AXIOS) ---

    async function fetchRegencies() {
        try {
            const response = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${PROVINCE_ID}.json`);
            const data = await response.json();

            let html = '<option value="" selected disabled>Pilih Kab/Kota</option>';
            data.forEach(item => {
                html += `<option value="${item.id}" data-name="${item.name}">${item.name}</option>`;
            });
            document.getElementById('regency').innerHTML = html;
        } catch (error) {
            console.error("Gagal muat kabupaten:", error);
        }
    }

    async function fetchDistricts(regencyId) {
        const districtSelect = document.getElementById('district');
        districtSelect.disabled = true;
        districtSelect.innerHTML = '<option>Memuat...</option>';

        try {
            const response = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${regencyId}.json`);
            const data = await response.json();

            let html = '<option value="" selected disabled>Pilih Kecamatan</option>';
            data.forEach(item => {
                html += `<option value="${item.id}" data-name="${item.name}">${item.name}</option>`;
            });
            districtSelect.innerHTML = html;
            districtSelect.disabled = false;
        } catch (error) {
            console.error("Gagal muat kecamatan:", error);
        }
    }

    async function fetchVillages(districtId) {
        const villageSelect = document.getElementById('village');
        villageSelect.disabled = true;
        villageSelect.innerHTML = '<option>Memuat...</option>';

        try {
            const response = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${districtId}.json`);
            const data = await response.json();

            let html = '<option value="" selected disabled>Pilih Kelurahan/Desa</option>';
            data.forEach(item => {
                html += `<option value="${item.id}" data-name="${item.name}">${item.name}</option>`;
            });
            villageSelect.innerHTML = html;
            villageSelect.disabled = false;
        } catch (error) {
            console.error("Gagal muat kelurahan:", error);
        }
    }

    // --- LOGIKA UI & CHECKOUT (TETAP PAKAI AXIOS UNTUK API INTERNAL) ---

    function toggleAddrInput() {
        const isCustom = document.getElementById('addr_custom').checked;
        document.getElementById('shipping_address').classList.toggle('d-none', !isCustom);
    }

    function fetchCart() {
        const container = document.getElementById('cartItemsContainer');
        const btnCheckout = document.getElementById('btnCheckout');

        axios.get('/api/cart').then(res => {
            const carts = res.data;
            let html = '';
            let totalQuantity = 0;

            if (!carts || carts.length === 0) {
                btnCheckout.disabled = true;
                html = `<div class="text-center py-5 bg-white rounded-4 border border-dashed p-5">
                            <i class="bi bi-cart-x text-muted opacity-25" style="font-size: 5rem; color: #00838f;"></i>
                            <h4 class="fw-bold mt-3">Keranjang Kosong</h4>
                            <a href="/customer/products" class="btn btn-medinest px-5 mt-2 w-auto">Buka Katalog</a>
                        </div>`;
            } else {
                btnCheckout.disabled = false;
                carts.forEach(item => {
                    const product = item.product || {};
                    totalQuantity += parseInt(item.quantity);
                    html += `
                    <div class="card card-cart">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-3 col-md-2">
                                    <img src="${product.image ? '/'+product.image : 'https://placehold.co/100'}" class="img-fluid rounded-3">
                                </div>
                                <div class="col-9 col-md-5">
                                    <h6 class="fw-bold text-dark mb-1">${product.name}</h6>
                                    <span class="badge badge-teal">Stok: ${product.stock}</span>
                                </div>
                                <div class="col-8 col-md-3 mt-3 mt-md-0">
                                    <div class="qty-control">
                                        <button class="btn-qty" onclick="changeQty('${item.id}', ${parseInt(item.quantity)-1}, ${product.stock})"><i class="bi bi-dash"></i></button>
                                        <input type="number" class="form-control text-center border-0 bg-transparent fw-bold" style="width: 50px;" value="${item.quantity}" readonly>
                                        <button class="btn-qty" onclick="changeQty('${item.id}', ${parseInt(item.quantity)+1}, ${product.stock})"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 text-end">
                                    <button class="btn btn-link text-danger p-0" onclick="deleteItem('${item.id}')"><i class="bi bi-trash fs-5"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
            }
            container.innerHTML = html;
            document.getElementById('totalQty').innerText = totalQuantity;
        });
    }

    function changeQty(id, qty, max) {
        if(qty < 1) return deleteItem(id);
        if(qty > max) return Swal.fire('Stok Terbatas', `Sisa stok ${max} unit.`, 'warning');
        axios.put(`/api/cart/${id}`, { quantity: qty }).then(() => fetchCart());
    }

    function deleteItem(id) {
        axios.delete(`/api/cart/${id}`).then(() => fetchCart());
    }

    function processCheckout() {
        const regSelect = document.getElementById('regency');
        const distSelect = document.getElementById('district');
        const villSelect = document.getElementById('village');

        const data = {
            regency: regSelect.options[regSelect.selectedIndex]?.getAttribute('data-name'),
            district: distSelect.options[distSelect.selectedIndex]?.getAttribute('data-name'),
            village: villSelect.options[villSelect.selectedIndex]?.getAttribute('data-name'),
            use_profile_address: document.getElementById('addr_profile').checked ? 1 : 0,
            shipping_address: document.getElementById('shipping_address').value,
            request_type: document.getElementById('request_type').value,
            notes: document.getElementById('checkoutNotes').value
        };

        if(!data.regency || !data.district || !data.village) {
            return Swal.fire('Peringatan', 'Mohon pilih lokasi hingga tingkat Kelurahan.', 'warning');
        }

        Swal.fire({
            title: 'Kirim Pengajuan?',
            text: "Pastikan data wilayah dan alamat sudah benar.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00838f',
            confirmButtonText: 'Ya, Kirim'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post('/api/orders', data).then(() => {
                    Swal.fire('Berhasil!', 'Permintaan stok sedang diproses.', 'success')
                        .then(() => window.location.href = '/customer/history');
                }).catch(err => {
                    Swal.fire('Gagal', err.response?.data?.message || 'Error sistem', 'error');
                });
            }
        });
    }
</script>
@endsection
