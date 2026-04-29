@extends('layouts.dashboard')
@section('content')
<h3>Buat Permintaan Obat Baru</h3>
<div class="card p-4 shadow-sm mb-4">
    <form id="reqForm">
        <div class="mb-3">
            <label>Pilih Obat</label>
            <select class="form-control" id="drugSelect"></select>
        </div>
        <div class="mb-3">
            <label>Jumlah</label>
            <input type="number" id="qty" class="form-control">
        </div>
        <button type="button" onclick="submitRequest()" class="btn btn-primary">Kirim Request</button>
    </form>
</div>
<script>
    axios.get('/api/drugs').then(res => {
        let opt = '';
        res.data.forEach(d => opt += `<option value="${d.id}">${d.name} (Stok: ${d.stock})</option>`);
        document.getElementById('drugSelect').innerHTML = opt;
    });

    function submitRequest() {
        let data = {
            items: [{ drug_id: document.getElementById('drugSelect').value, quantity: document.getElementById('qty').value }],
            notes: 'Permintaan rutin'
        };
        axios.post('/api/requests', data).then(res => { alert('Request dikirim!'); location.reload(); });
    }
</script>
@endsection