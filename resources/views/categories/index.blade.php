@extends('layouts.app')

@section('title', 'Kategori Barang')
@section('page-title', 'Kelola Kategori Barang')

@section('content')
<div class="row">
    <div class="col-lg-4 mb-4">
        <!-- Form Tambah Kategori -->
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-folder-plus text-primary me-2"></i>Tambah Kategori Baru</h6>
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Nama Kategori</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Sembako, Minuman..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Deskripsi (Opsional)</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Keterangan singkat kategori..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-save me-1"></i> Simpan Kategori
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Tabel Kategori -->
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-tags text-primary me-2"></i>Daftar Kategori</h6>
                <span class="badge bg-secondary">{{ $categories->count() }} Kategori</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Barang</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $idx => $cat)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td class="fw-semibold text-dark">{{ $cat->name }}</td>
                                <td class="text-muted small">{{ $cat->description ?: '-' }}</td>
                                <td><span class="badge bg-info-subtle text-info">{{ $cat->products_count }} Barang</span></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#editModal{{ $cat->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('categories.destroy', $cat->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" {{ $cat->products_count > 0 ? 'disabled title="Kategori masih memiliki barang"' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Edit Kategori -->
                            <div class="modal fade" id="editModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title fw-bold">Edit Kategori</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('categories.update', $cat->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Nama Kategori</label>
                                                    <input type="text" name="name" class="form-control" value="{{ $cat->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Deskripsi</label>
                                                    <textarea name="description" class="form-control" rows="3">{{ $cat->description }}</textarea>
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
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada kategori barang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
