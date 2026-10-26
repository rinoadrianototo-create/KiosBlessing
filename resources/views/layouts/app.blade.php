<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kios Blessing') — Manajemen Kios</title>
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #0d6efd;
            --primary-dark: #0a4292;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-light);
            color: #334155;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            height: 70px;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .sidebar-brand-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: #fff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-right: 0.75rem;
        }

        .sidebar-menu {
            padding: 1.25rem 0.75rem;
        }

        .menu-header {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            padding: 0.75rem 0.75rem 0.35rem;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            padding: 0.65rem 0.85rem;
            color: #64748b;
            font-weight: 500;
            border-radius: 8px;
            text-decoration: none;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
        }

        .nav-link-custom i {
            font-size: 1.15rem;
            margin-right: 0.85rem;
            width: 24px;
            text-align: center;
        }

        .nav-link-custom:hover {
            color: var(--primary-color);
            background-color: #eff6ff;
        }

        .nav-link-custom.active {
            color: #ffffff;
            background-color: var(--primary-color);
            font-weight: 600;
        }

        /* Content Area */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .top-navbar {
            height: 70px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .content-body {
            padding: 1.75rem 2rem;
            flex: 1;
        }

        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
            background: #ffffff;
        }

        .badge-role-pemilik { background-color: #e0e7ff; color: #4338ca; }
        .badge-role-admin { background-color: #dcfce7; color: #15803d; }
        .badge-role-kasir { background-color: #e0f2fe; color: #0369a1; }

        @media (max-width: 991.98px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            .sidebar.show {
                margin-left: 0;
            }
            .main-wrapper {
                margin-left: 0;
            }
            .top-navbar {
                padding: 0 1rem;
            }
            .content-body {
                padding: 1rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="bi bi-shop"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-0 text-dark">Kios Blessing</h6>
            <small class="text-muted" style="font-size: 0.75rem;">Manajemen Toko</small>
        </div>
    </div>

    <div class="sidebar-menu">
        @php
            $user = Auth::user();
            $role = $user ? $user->role : 'kasir';
        @endphp

        <!-- Navigasi Pemilik & Dashboard -->
        @if($role === 'pemilik')
            <div class="menu-header">Utama</div>
            <a href="{{ route('dashboard') }}" class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>
        @endif

        <!-- Transaksi POS (Kasir & Pemilik) -->
        @if(in_array($role, ['pemilik', 'kasir']))
            <div class="menu-header">Penjualan</div>
            <a href="{{ route('pos.index') }}" class="nav-link-custom {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                <i class="bi bi-cart4"></i>
                <span>Kasir (POS)</span>
            </a>
            <a href="{{ route('transactions.index') }}" class="nav-link-custom {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i>
                <span>Riwayat Transaksi</span>
            </a>
        @endif

        <!-- Manajemen Katalog & Stok (Pemilik & Admin) -->
        @if(in_array($role, ['pemilik', 'admin']))
            <div class="menu-header">Inventaris & Barang</div>
            <a href="{{ route('products.index') }}" class="nav-link-custom {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i>
                <span>Daftar Barang</span>
            </a>
            <a href="{{ route('categories.index') }}" class="nav-link-custom {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i>
                <span>Kategori Barang</span>
            </a>
            <a href="{{ route('stocks.index') }}" class="nav-link-custom {{ request()->routeIs('stocks.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right"></i>
                <span>Mutasi Stok Masuk/Keluar</span>
            </a>
        @endif

        <!-- Keuangan (Hanya Pemilik) -->
        @if($role === 'pemilik')
            <div class="menu-header">Keuangan</div>
            <a href="{{ route('finance.index') }}" class="nav-link-custom {{ request()->routeIs('finance.*') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i>
                <span>Pencatatan Keuangan</span>
            </a>
            <a href="{{ route('reports.index') }}" class="nav-link-custom {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Laporan & Ekspor</span>
            </a>
        @endif

        <!-- Pengaturan Akun & Sistem (Hanya Pemilik) -->
        @if($role === 'pemilik')
            <div class="menu-header">Sistem</div>
            <a href="{{ route('users.index') }}" class="nav-link-custom {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Kelola Pengguna</span>
            </a>
            <a href="{{ route('settings.index') }}" class="nav-link-custom {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i>
                <span>Pengaturan Kios</span>
            </a>
        @endif
    </div>
</aside>

<!-- Main Wrapper -->
<div class="main-wrapper">
    <!-- Top Navbar -->
    <header class="top-navbar">
        <div class="d-flex align-items-center">
            <button class="btn btn-light d-lg-none me-2" type="button" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>
            <h5 class="fw-bold mb-0 text-dark">@yield('page-title', 'Dashboard')</h5>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="d-none d-md-block text-end">
                <div class="fw-semibold small text-dark">{{ Auth::user()->name }}</div>
                <span class="badge badge-role-{{ Auth::user()->role }} text-capitalize px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                    <i class="bi bi-shield-check me-1"></i>{{ Auth::user()->role }}
                </span>
            </div>

            <div class="dropdown">
                <button class="btn btn-light rounded-circle p-2 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 42px; height: 42px;">
                    <i class="bi bi-person-circle fs-5 text-secondary"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                    <li class="px-3 py-2 border-bottom d-md-none">
                        <div class="fw-bold small">{{ Auth::user()->name }}</div>
                        <span class="badge badge-role-{{ Auth::user()->role }} text-capitalize">{{ Auth::user()->role }}</span>
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger py-2">
                                <i class="bi bi-box-arrow-right me-2"></i> Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="content-body">
        <!-- Flash Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terdapat kesalahan pengisian data:</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('show');
            });

            document.addEventListener('click', function(e) {
                if (window.innerWidth < 992 && !sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            });
        }
    });
</script>
@stack('scripts')
</body>
</html>
