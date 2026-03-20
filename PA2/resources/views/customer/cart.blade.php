<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background:#f4f9f9; }

        .card-cart {
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: 0.3s;
        }

        .card-cart:hover {
            transform: translateY(-5px);
        }

        .btn-main {
            background:#3fbbc0;
            color:white;
            border-radius:25px;
            font-weight:600;
        }

        .btn-main:hover {
            background:#329ea2;
            color:white;
        }
    </style>
</head>

<body>

<div class="container py-5">

    <h2 class="fw-bold mb-4 text-center">Keranjang Saya</h2>

    <div id="cartContainer" class="row gy-4"></div>

    <div class="text-center mt-5">
        <button onclick="checkout()" class="btn btn-main px-5 py-3">
            Checkout
        </button>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
axios.get('/api/cart', {
    headers: {
        Authorization: 'Bearer {{ session("api_token") }}'
    }
})
.then(res => {

    let html = '';

    res.data.forEach(item => {

        html += `
        <div class="col-md-6 col-lg-4">
            <div class="card card-cart p-4 text-center">

                <h5 class="fw-bold">${item.product.name}</h5>

                <p class="text-primary fw-bold mb-1">
                    Rp ${new Intl.NumberFormat('id-ID').format(item.product.price)}
                </p>

                <p class="text-muted">
                    Jumlah: <strong>${item.qty}</strong>
                </p>

                <button onclick="hapus(${item.id})" class="btn btn-danger btn-sm mt-2">
                    Hapus
                </button>

            </div>
        </div>
        `;
    });

    document.getElementById('cartContainer').innerHTML = html;

})
.catch(err => {
    console.log(err);
});


function hapus(id) {
    axios.delete('/api/cart/' + id, {
        headers: {
            Authorization: 'Bearer {{ session("api_token") }}'
        }
    })
    .then(() => location.reload());
}


function checkout() {
    axios.post('/api/checkout', {}, {
        headers: {
            Authorization: 'Bearer {{ session("api_token") }}'
        }
    })
    .then(res => {
        alert('Checkout berhasil! Order ID: ' + res.data.order_id);
        location.reload();
    })
    .catch(err => {
        alert(err.response.data.message);
    });
}
</script>

</body>
</html>
