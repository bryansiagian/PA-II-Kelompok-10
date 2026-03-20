{{-- <!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Warehouse System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Konfigurasi Axios Global
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + '{{ session('api_token') }}';
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    </script>
    <style>
        .sidebar { min-height: 100vh; background: #2c3e50; color: white; }
        .nav-link { color: #bdc3c7; }
        .nav-link.active { color: white; font-weight: bold; background: #34495e; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block sidebar p-3">
            <h4>WMS Drug</h4><hr>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="/dashboard">🏠 Dashboard</a></li>

                @if(Auth::user()->role->name == 'admin')
                <li class="nav-item"><a class="nav-link" href="/admin/users">👥 Kelola User</a></li>
                <li class="nav-item"><a class="nav-link" href="/admin/logs">📜 Audit Log</a></li>
                @endif

                @if(Auth::user()->role->name == 'operator')
                <li class="nav-item"><a class="nav-link" href="/operator/drugs">💊 Stok Obat</a></li>
                <li class="nav-item"><a class="nav-link" href="/operator/requests">📥 Permintaan Stok</a></li>
                @endif

                @if(Auth::user()->role->name == 'customer')
                <li class="nav-item"><a class="nav-link" href="/customer/requests">📝 Buat Request</a></li>
                <li class="nav-item"><a class="nav-link" href="/customer/tracking">📦 Lacak Kiriman</a></li>
                @endif

                @if(Auth::user()->role->name == 'courier')
                <li class="nav-item"><a class="nav-link" href="/courier/tasks">🚚 Tugas Kirim</a></li>
                @endif
            </ul>
            <form action="/logout" method="POST" class="mt-5">@csrf <button class="btn btn-danger btn-sm w-100">Logout</button></form>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto p-4">
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> --}}