@extends('layouts.portal')

@section('content')
<div class="container mt-4">
    <!-- Hero -->
    <div class="p-5 mb-5 bg-dark text-white rounded-4 shadow-lg"
         style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1586015555751-63bb77f4322a?w=1200'); background-size: cover;">
        <h1 class="fw-bold">Katalog Logistik Farmasi</h1>
    </div>

    <div id="loading" class="text-center py-5">
        <div class="spinner-border text-primary"></div>
    </div>

    <div class="row g-4" id="drugCatalog"></div>
</div>

<script>
let allDrugs = [];

/**
 * ✅ ADD TO CART (FIXED)
 */
function addToCart(id, name, stock) {

    // ❗ VALIDASI STOK
    if (stock <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Stok Kosong',
            text: 'Tidak bisa memesan karena stok habis'
        });
        return;
    }

    axios.post('/api/cart', { product_id: id })
        .then(() => {
            updateCartBadge();

            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'success',
                title: name + ' masuk keranjang',
                showConfirmButton: false,
                timer: 2000
            });
        })
        .catch(() => {
            Swal.fire('Error', 'Gagal menambah keranjang', 'error');
        });
}

/**
 * LOAD DATA
 */
function fetchInitialData() {
    axios.get('/api/drugs')
        .then(res => {
            allDrugs = res.data;
            renderCatalog(allDrugs);
            document.getElementById('loading').classList.add('d-none');
        });
}

/**
 * ✅ RENDER (SUDAH HANDLE STOK)
 */
function renderCatalog(data) {
    let html = '';

    data.forEach(d => {
        const img = d.image || 'https://placehold.co/400x300';
        const safeName = d.name.replace(/'/g, "\\'");

        html += `
        <div class="col-md-3">
            <div class="card shadow-sm">

                <img src="${img}" style="height:180px;object-fit:cover">

                <div class="card-body">
                    <h6>${d.name}</h6>
                    <small>Stok: ${d.stock}</small>

                    <div class="mt-3">

                        ${
                            d.stock > 0
                            ? `<button onclick="addToCart(${d.id}, '${safeName}', ${d.stock})"
                                    class="btn btn-primary w-100">
                                    Pesan
                               </button>`
                            : `<button class="btn btn-secondary w-100" disabled>
                                    Stok Habis
                               </button>`
                        }

                    </div>
                </div>
            </div>
        </div>`;
    });

    document.getElementById('drugCatalog').innerHTML = html;
}

document.addEventListener('DOMContentLoaded', fetchInitialData);
</script>

@endsection
