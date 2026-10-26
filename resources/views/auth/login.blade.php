<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Kios Blessing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0d6efd 0%, #0a4292 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            background: #ffffff;
        }
        .brand-header {
            background: linear-gradient(135deg, #f8faff 0%, #eef4ff 100%);
            padding: 2.25rem 2rem 1.75rem;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }
        .brand-icon {
            width: 64px;
            height: 64px;
            background: #0d6efd;
            color: #fff;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 10px 20px rgba(13, 110, 253, 0.3);
            margin-bottom: 0.75rem;
        }
        .role-badge-btn {
            font-size: 0.8rem;
            padding: 0.35rem 0.65rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .role-badge-btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 col-xl-4">
            <div class="card login-card">
                <div class="brand-header">
                    <div class="brand-icon">
                        <i class="bi bi-shop"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">Kios Blessing</h3>
                    <p class="text-muted small mb-0">Sistem Manajemen & Kasir Penjualan</p>
                </div>

                <div class="card-body p-4 p-md-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center small" role="alert">
                            <i class="bi bi-check-circle-fill me-2 fs-6"></i>
                            <div>{{ session('success') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center small" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                            <div>{{ $errors->first() }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('login.post') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small">Email Pengguna</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" id="email" name="email" class="form-control bg-light border-start-0 @error('email') is-invalid @enderror" value="{{ old('email', 'pemilik@kiosblessing.com') }}" placeholder="nama@kiosblessing.com" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold text-secondary small mb-0">Password</label>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" id="password" name="password" class="form-control bg-light border-start-0" value="password123" placeholder="••••••••" required>
                                <button class="btn btn-light border border-start-0 text-muted" type="button" onclick="togglePassword()">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label small text-secondary" for="remember">Ingat Saya</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
                        </button>
                    </form>

                    <!-- Quick Fill Role for Easy Testing -->
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="small fw-bold text-secondary mb-2 d-flex align-items-center">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Uji Coba Cepat (Pilih Akun):
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm role-badge-btn" onclick="fillAccount('pemilik@kiosblessing.com')">
                                <i class="bi bi-person-badge"></i> Pemilik
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm role-badge-btn" onclick="fillAccount('admin@kiosblessing.com')">
                                <i class="bi bi-gear"></i> Admin
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm role-badge-btn" onclick="fillAccount('kasir@kiosblessing.com')">
                                <i class="bi bi-cart"></i> Kasir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center text-white-50 mt-3 small">
                &copy; {{ date('Y') }} Kios Blessing. Hak Cipta Dilindungi.
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function fillAccount(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'password123';
    }

    function togglePassword() {
        const pwd = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            pwd.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }
</script>
</body>
</html>
