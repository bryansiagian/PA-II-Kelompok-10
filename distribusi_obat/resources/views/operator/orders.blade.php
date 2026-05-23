@extends('layouts.backoffice')

@section('page_title', 'Antrian Pesanan Produk')

@section('content')

<div class="container-fluid">

    <!-- HEADER -->
    <div class="d-flex align-items-center mb-3">

        <div class="flex-fill">

            <h4 class="fw-bold mb-0 text-dark">
                Antrian Pesanan Logistik
            </h4>

            <div class="text-muted small">
                Validasi pengajuan obat, tinjau alamat tujuan,
                dan kelola distribusi unit mitra.
            </div>

        </div>

        <div class="ms-3 d-flex gap-2">

            <button
                class="btn btn-indigo shadow-sm rounded-pill px-4"
                onclick="openCreateOrderModal()">

                <i class="ph-plus-circle me-2"></i>
                Buat Pesanan

            </button>

            <button
                onclick="fetchOrders()"
                class="btn btn-light shadow-sm rounded-pill px-4">

                <i class="ph-arrow-clockwise me-2"></i>
                Refresh

            </button>

        </div>

    </div>

    <!-- TABLE -->
    <div class="card shadow-sm border-0 rounded-3">

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-light">

                    <tr class="fs-xs text-uppercase fw-bold text-muted">

                        <th class="ps-3">
                            ID & Waktu
                        </th>

                        <th>
                            Mitra Pemesan
                        </th>

                        <th class="text-center">
                            Total Item
                        </th>

                        <th class="text-center">
                            Status
                        </th>

                        <th class="text-center pe-3">
                            Aksi
                        </th>

                    </tr>

                </thead>

                <tbody id="orderTableBody">

                    <tr>

                        <td
                            colspan="5"
                            class="text-center py-5 text-muted">

                            Menghubungkan ke server...

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- =========================================
MODAL DETAIL
========================================= -->
<div class="modal fade" id="modalDetail" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <div class="modal-header bg-indigo text-white border-0 py-3">

                <h6 class="modal-title fw-bold">

                    <i class="ph-info me-2"></i>
                    Rincian Lengkap Pesanan

                </h6>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div
                class="modal-body p-0"
                id="detailContent">
            </div>

        </div>

    </div>

</div>

<!-- =========================================
MODAL CREATE ORDER
========================================= -->
<div class="modal fade" id="modalCreateOrder" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <!-- HEADER -->
            <div class="modal-header bg-indigo text-white border-0 py-3">

                <h5 class="modal-title fw-bold">

                    <i class="ph-plus-circle me-2"></i>
                    Buat Pesanan Manual

                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <!-- FORM -->
            <form
                id="formCreateOrder"
                onsubmit="submitAdminOrder(event)">

                <div class="modal-body p-4">

                    <!-- CUSTOMER -->
                    <div class="mb-4">

                        <label class="small fw-bold text-muted mb-2">
                            Pilih Mitra (Customer)
                        </label>

                        <select
                            name="customer_id"
                            id="select_customer"
                            class="form-select form-select-lg"
                            required>

                            <option value="">
                                -- Pilih Mitra --
                            </option>

                        </select>

                    </div>

                    <!-- PRODUK -->
                    <div class="mb-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <label class="small fw-bold text-muted mb-0">
                                Daftar Produk Pesanan
                            </label>

                            <button
                                type="button"
                                class="btn btn-sm btn-indigo rounded-pill px-3"
                                onclick="addProductRow()">

                                <i class="ph-plus me-1"></i>
                                Tambah Produk

                            </button>

                        </div>

                        <!-- CONTAINER -->
                        <div id="productItemsContainer">

                            <!-- ROW PERTAMA -->
                            <div class="product-item card border-0 shadow-sm mb-3">

                                <div class="card-body p-3">

                                    <div class="row align-items-end g-3">

                                        <!-- PRODUK -->
                                        <div class="col-md-7">

                                            <label class="small text-muted mb-1">
                                                Produk
                                            </label>

                                            <select
    class="form-select product-select"
    required>

                                                <option value="">
                                                    -- Pilih Produk --
                                                </option>

                                            </select>

                                        </div>

                                        <!-- QTY -->
                                        <div class="col-md-3">

                                            <label class="small text-muted mb-1">
                                                Kuantitas
                                            </label>

                                            <input
    type="number"
    class="form-control"
                                                min="1"
                                                value="1"
                                                required>

                                        </div>

                                        <!-- DELETE -->
                                        <div class="col-md-2">

                                            <button
                                                type="button"
                                                class="btn btn-danger w-100"
                                                onclick="removeProductRow(this)">

                                                <i class="ph-trash"></i>

                                            </button>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer border-0 bg-light">

                    <button
                        type="submit"
                        class="btn btn-indigo text-white w-100 rounded-pill fw-bold py-3">

                        <i class="ph-check-circle me-2"></i>
                        SIMPAN PESANAN

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>

axios.defaults.headers.common['Authorization'] =
    'Bearer ' + '{{ session('api_token') }}';

let productOptionsHtml = '';

// ==========================================
// PRODUCT OPTION GENERATOR
// ==========================================

function generateProductOptions(products) {

    let html = `
        <option value="">
            -- Pilih Produk --
        </option>
    `;

    products.forEach(product => {

        html += `
            <option value="${product.id}">
                ${product.name} (Stok: ${product.stock})
            </option>
        `;
    });

    return html;
}

// ==========================================
// ADD PRODUCT ROW
// ==========================================

function addProductRow() {

    const container =
        document.getElementById(
            'productItemsContainer'
        );

    const total =
        document.querySelectorAll(
            '.product-item'
        ).length;

    const html = `
        <div class="product-item card border-0 shadow-sm mb-3">

            <div class="card-body p-3">

                <div class="row align-items-end g-3">

                    <div class="col-md-7">

                        <label class="small text-muted mb-1">
                            Produk
                        </label>

                        <select
                            class="form-select product-select"
                            required>

                            ${productOptionsHtml}

                        </select>

                    </div>

                    <div class="col-md-3">

                        <label class="small text-muted mb-1">
                            Kuantitas
                        </label>

                        <input
                            type="number"
                            class="form-control"
                            value="1"
                            min="1"
                            required>

                    </div>

                    <div class="col-md-2">

                        <button
                            type="button"
                            class="btn btn-danger w-100"
                            onclick="removeProductRow(this)">

                            <i class="ph-trash"></i>

                        </button>

                    </div>

                </div>

            </div>

        </div>
    `;

    container.insertAdjacentHTML(
        'beforeend',
        html
    );
}
// ==========================================
// REMOVE PRODUCT ROW
// ==========================================

function removeProductRow(button) {

    const rows =
        document.querySelectorAll('.product-item');

    if (rows.length <= 1) {

        Swal.fire({
            icon: 'warning',
            title: 'Minimal 1 Produk',
            confirmButtonColor: '#5c6bc0'
        });

        return;
    }

    button.closest('.product-item').remove();
}

// ==========================================
// OPEN MODAL
// ==========================================

function openCreateOrderModal() {

    document.getElementById(
        'formCreateOrder'
    ).reset();

    const container =
        document.getElementById(
            'productItemsContainer'
        );

    container.innerHTML = `
        <div class="product-item card border-0 shadow-sm mb-3">

            <div class="card-body p-3">

                <div class="row align-items-end g-3">

                    <div class="col-md-7">

                        <label class="small text-muted mb-1">
                            Produk
                        </label>

                        <select
                            name="products[0][product_id]"
                            class="form-select product-select"
                            required>

                            <option value="">
                                -- Pilih Produk --
                            </option>

                        </select>

                    </div>

                    <div class="col-md-3">

                        <label class="small text-muted mb-1">
                            Kuantitas
                        </label>

                        <input
                            type="number"
                            name="products[0][quantity]"
                            class="form-control"
                            min="1"
                            value="1"
                            required>

                    </div>

                    <div class="col-md-2">

                        <button
                            type="button"
                            class="btn btn-danger w-100"
                            onclick="removeProductRow(this)">

                            <i class="ph-trash"></i>

                        </button>

                    </div>

                </div>

            </div>

        </div>
    `;

    // USERS
    axios.get('/api/users')

        .then(res => {

            let customerHtml = `
                <option value="">
                    -- Pilih Mitra --
                </option>
            `;

            res.data
                .filter(user =>
                    user.roles &&
                    user.roles[0].name === 'customer'
                )
                .forEach(user => {

                    customerHtml += `
                        <option value="${user.id}">
                            ${user.name}
                        </option>
                    `;
                });

            document.getElementById(
                'select_customer'
            ).innerHTML = customerHtml;
        });

    // PRODUCTS
    axios.get('/api/products')

        .then(res => {

            productOptionsHtml =
                generateProductOptions(res.data);

            document.querySelector(
                '.product-select'
            ).innerHTML = productOptionsHtml;
        });

    new bootstrap.Modal(
        document.getElementById('modalCreateOrder')
    ).show();
}

// ==========================================
// SUBMIT ORDER
// ==========================================

function submitAdminOrder(event) {

    event.preventDefault();

    const rows =
        document.querySelectorAll('.product-item');

    let products = [];

    rows.forEach((row, index) => {

        const select =
            row.querySelector('.product-select');

        const qty =
            row.querySelector(
                'input[type="number"]'
            );

        // DEBUG
        console.log(select.value);

        if (
            select.value !== '' &&
            qty.value !== ''
        ) {

            products.push({

                product_id:
                    parseInt(select.value),

                quantity:
                    parseInt(qty.value)
            });
        }
    });

    console.log(products);

    // VALIDASI
    if (products.length === 0) {

        Swal.fire({
            icon: 'warning',
            title: 'Produk belum dipilih',
            text: 'Silakan pilih minimal satu produk.',
            confirmButtonColor: '#5c6bc0'
        });

        return;
    }

    const payload = {

        customer_id: parseInt(
            document.getElementById(
                'select_customer'
            ).value
        ),

        request_type: 'delivery',

        notes: '',

        products: products
    };

    console.log(payload);

    axios.post(
        '/api/admin/orders',
        payload
    )

    .then(response => {

        bootstrap.Modal
            .getInstance(
                document.getElementById(
                    'modalCreateOrder'
                )
            )
            .hide();

        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Pesanan berhasil dibuat.',
            confirmButtonColor: '#5c6bc0'
        });

        fetchOrders();
    })

    .catch(error => {

        console.log(error.response);

        let msg =
            'Terjadi kesalahan';

        if (
            error.response &&
            error.response.data &&
            error.response.data.errors
        ) {

            const firstError =
                Object.values(
                    error.response.data.errors
                )[0];

            if(firstError.length){
                msg = firstError[0];
            }
        }

        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: msg,
            confirmButtonColor: '#d33'
        });
    });
}

// ==========================================
// FETCH ORDERS
// ==========================================

function fetchOrders() {

    axios.get('/api/orders')

        .then(response => {

            const orders = response.data;

            let html = '';

            if (!orders.length) {

                html = `
                    <tr>
                        <td colspan="5"
                            class="text-center py-5 text-muted">

                            Tidak ada data pesanan

                        </td>
                    </tr>
                `;

                document.getElementById(
                    'orderTableBody'
                ).innerHTML = html;

                return;
            }

            orders.forEach(order => {

                const createdAt =
                    new Date(order.created_at)
                    .toLocaleString('id-ID');

                const totalItem =
                    order.items
                    ? order.items.length
                    : 0;

                let badgeClass = 'bg-secondary';

                if(order.status?.name === 'Pending')
                    badgeClass = 'bg-warning';

                if(order.status?.name === 'Processed')
                    badgeClass = 'bg-info';

                if(order.status?.name === 'Shipping')
                    badgeClass = 'bg-primary';

                if(order.status?.name === 'Completed')
                    badgeClass = 'bg-success';

                if(order.status?.name === 'Rejected')
                    badgeClass = 'bg-danger';

                html += `
                    <tr>

                        <td class="ps-3">

                            <div class="fw-bold text-primary">
                                #${order.id}
                            </div>

                            <small class="text-muted">
                                ${createdAt}
                            </small>

                        </td>

                        <td>

                            <div class="fw-semibold">
                                ${order.user?.name ?? '-'}
                            </div>

                        </td>

                        <td class="text-center">

                            <span class="fw-bold">
                                ${totalItem}
                            </span>

                        </td>

                        <td class="text-center">

                            <span class="badge ${badgeClass}">

                                ${order.status?.name
                                    ?? 'Unknown'}

                            </span>

                        </td>

                        <td class="text-center pe-3">

                            <button
                                class="btn btn-light btn-sm rounded-circle"

                                onclick="showOrderDetail(${order.id})">

                                <i class="ph-eye"></i>

                            </button>

                        </td>

                    </tr>
                `;
            });

            document.getElementById(
                'orderTableBody'
            ).innerHTML = html;
        })

        .catch(error => {

            document.getElementById(
                'orderTableBody'
            ).innerHTML = `
                <tr>
                    <td colspan="5"
                        class="text-center py-5 text-danger">

                        Gagal mengambil data pesanan

                    </td>
                </tr>
            `;
        });
}

// ==========================================
// DETAIL
// ==========================================

function showOrderDetail(orderId) {

    axios.get('/api/orders')

        .then(response => {

            const order =
                response.data.find(
                    o => o.id == orderId
                );

            if(!order) return;

            let itemsHtml = '';

            order.items.forEach(item => {

                itemsHtml += `
                    <tr>

                        <td>
                            ${item.product?.name ?? '-'}
                        </td>

                        <td class="text-center">
                            ${item.quantity}
                        </td>

                        <td class="text-end">
                            Rp ${Number(
                                item.price_at_order
                            ).toLocaleString('id-ID')}
                        </td>

                    </tr>
                `;
            });

            document.getElementById(
                'detailContent'
            ).innerHTML = `
                <div class="p-4">

                    <div class="mb-4">

                        <h5 class="fw-bold mb-1">
                            Pesanan #${order.id}
                        </h5>

                        <div class="text-muted">
                            ${order.user?.name ?? '-'}
                        </div>

                    </div>

                    <div class="table-responsive">

                        <table class="table">

                            <thead>

                                <tr>

                                    <th>Produk</th>

                                    <th class="text-center">
                                        Qty
                                    </th>

                                    <th class="text-end">
                                        Harga
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                ${itemsHtml}

                            </tbody>

                        </table>

                    </div>

                </div>
            `;

            new bootstrap.Modal(
                document.getElementById(
                    'modalDetail'
                )
            ).show();
        });
}

fetchOrders();

</script>

<style>

.bg-indigo{
    background:#5c6bc0 !important;
}

.btn-indigo{
    background:#5c6bc0;
    color:#fff;
}

.product-item{
    border-radius:22px;
    background:#f8fafc;
    border:1px solid #edf2f7 !important;
}

#modalCreateOrder .modal-dialog{
    max-width:1100px;
}

#modalCreateOrder .modal-content{
    border-radius:24px;
    overflow:hidden;
}

#modalCreateOrder .modal-body{
    max-height:70vh;
    overflow-y:auto;
    padding:28px !important;
}

#modalCreateOrder .modal-body::-webkit-scrollbar{
    width:8px;
}

#modalCreateOrder .modal-body::-webkit-scrollbar-thumb{
    background:#cbd5e1;
    border-radius:20px;
}

#modalCreateOrder .form-select,
#modalCreateOrder .form-control{
    border-radius:16px;
    min-height:54px;
    border:1px solid #dbe4ee;
    padding:12px 16px;
}

#modalCreateOrder label{
    font-size:.78rem;
    font-weight:700;
    text-transform:uppercase;
    color:#64748b;
}

#modalCreateOrder .btn-danger{
    height:54px;
    border-radius:16px;
}

@media(max-width:768px){

    #modalCreateOrder .modal-dialog{
        margin:12px;
        max-width:100%;
    }

    #modalCreateOrder .modal-body{
        max-height:72vh;
        padding:20px !important;
    }
}

</style>

@endsection
