@extends('layouts.app')

@section('title', 'Kelola Pengguna')
@section('page-title', 'Pengelolaan Akun & Hak Akses Staf')

@section('content')
<div class="row">
    <!-- Form Tambah User -->
    <div class="col-lg-4 mb-4">
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-plus text-primary me-2"></i>Tambah Pengguna Baru</h6>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" placeholder="Nama pengguna..." required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Alamat Email (Login)</label>
                    <input type="email" name="email" class="form-control" placeholder="email@kiosblessing.com" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Password Awal</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Peran / Hak Akses</label>
                    <select name="role" class="form-select" required>
                        <option value="kasir">Kasir (Hanya POS & Struk)</option>
                        <option value="admin">Admin (Kelola Barang & Stok)</option>
                        <option value="pemilik">Pemilik (Akses Penuh Seluruh Sistem)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Nomor Telepon (Opsional)</label>
                    <input type="text" name="phone" class="form-control" placeholder="08...">
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-save me-1"></i> Simpan Akun Pengguna
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Pengguna -->
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-people text-primary me-2"></i>Daftar Staf Kios</h6>
                <span class="badge bg-secondary">{{ $users->count() }} Akun</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Pengguna</th>
                            <th>Peran</th>
                            <th>Kontak</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $u->name }}</div>
                                    <small class="text-muted">{{ $u->email }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-role-{{ $u->role }} text-capitalize px-2 py-1">
                                        {{ $u->role }}
                                    </span>
                                </td>
                                <td class="small text-muted">{{ $u->phone ?: '-' }}</td>
                                <td>
                                    @if($u->is_active)
                                        <span class="badge bg-success-subtle text-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $u->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus akun ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            <!-- Modal Edit User -->
                            <div class="modal fade" id="editUserModal{{ $u->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title fw-bold">Edit Akun: {{ $u->name }}</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('users.update', $u->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Nama Lengkap</label>
                                                    <input type="text" name="name" class="form-control" value="{{ $u->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Alamat Email</label>
                                                    <input type="email" name="email" class="form-control" value="{{ $u->email }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Password Baru (Kosongkan jika tidak diubah)</label>
                                                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Peran / Hak Akses</label>
                                                    <select name="role" class="form-select" required>
                                                        <option value="kasir" {{ $u->role === 'kasir' ? 'selected' : '' }}>Kasir</option>
                                                        <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                                        <option value="pemilik" {{ $u->role === 'pemilik' ? 'selected' : '' }}>Pemilik</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Nomor Telepon</label>
                                                    <input type="text" name="phone" class="form-control" value="{{ $u->phone }}">
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="is_active" id="activeSwitch{{ $u->id }}" {{ $u->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label small fw-semibold" for="activeSwitch{{ $u->id }}">Akun Aktif (Dapat Login)</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
