<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pustakawan - SIPUS-Del</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f3f4f6; }
        .btn-del { background-color: #4C1D95; color: #ffffff; }
        .btn-del:hover { background-color: #3B0764; color: #ffffff; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow-sm border-0" style="max-width: 400px; width: 100%;">
        <div class="card-body p-4">
            <h4 class="fw-bold text-center mb-1 text-purple" style="color: #4C1D95;">SIPUS-Del</h4>
            <p class="text-center text-muted small mb-4">Masuk sebagai Pustakawan</p>

            @if(session('success'))
                <div class="alert alert-success small">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger small">{{ session('error') }}</div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email Pustakawan</label>
                    <input type="email" name="email" class="form-control" value="{{ Cookie::get('sipus_last_email') ?? old('email') }}" required placeholder="pustakawan@del.ac.id">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="password123">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label small" for="remember">Ingat Saya</label>
                </div>
                <button type="submit" class="btn btn-del w-100">Login Masuk</button>
            </form>
            <div class="text-center mt-3">
                <a href="{{ route('buku.index') }}" class="small text-decoration-none text-muted">← Kembali ke Katalog</a>
            </div>
        </div>
    </div>
</body>
</html>