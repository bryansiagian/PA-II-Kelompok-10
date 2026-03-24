<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pesanan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background:#f4f9f9; }

        .card-order {
            border-radius:15px;
            box-shadow:0 5px 20px rgba(0,0,0,0.08);
        }

        .status {
            padding:5px 10px;
            border-radius:10px;
            font-size:12px;
        }

        .pending { background:orange; color:white; }
    </style>
</head>

<body>

<div class="container py-5">

    <h2 class="text-center mb-4 fw-bold">Riwayat Pesanan</h2>

    <div id="orderContainer" class="row gy-4"></div>

</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
axios.get('/api/my-orders', {
    headers: {
        'Authorization': 'Bearer {{ session("api_token") }}',
        'Accept': 'application/json'
    }
})
.then(res => {

    console.log(res.data); // 🔥 DEBUG

    let html = '';

    if(res.data.length === 0){
        html = `<div class="text-center">Belum ada pesanan</div>`;
    }

    res.data.forEach(order => {

        let items = '';

        order.items.forEach(i => {
            items += `<li>${i.product.name} (x${i.qty})</li>`;
        });

        html += `
        <div class="col-md-6">
            <div class="card p-4">

                <h5>Order #${order.id}</h5>

                <p>Rp ${new Intl.NumberFormat('id-ID').format(order.total)}</p>

                <ul>${items}</ul>

            </div>
        </div>
        `;
    });

    document.getElementById('orderContainer').innerHTML = html;

})
.catch(err => {
    console.log(err);
});
</script>

</body>
</html>
