@extends('layout.app')

@section('title', 'Daftar Obat')

@section('content')
<style>
    .table th, .table td {
        vertical-align: middle;
        padding: 12px 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .table th {
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa; /* Tambahkan background agar sticky header terlihat */
    }
    
    .table td:nth-child(2), .table th:nth-child(2) {
        /* Nama Obat - allow wrapping */
        white-space: normal;
        max-width: 200px;
    }
    
    .table td:nth-child(3), .table th:nth-child(3) {
        /* Jenis - allow wrapping */
        white-space: normal;
        max-width: 150px;
    }
    
    .btn-group-sm .btn {
        margin: 0 1px;
        padding: 0.25rem 0.5rem;
    }
    
    .badge {
        font-size: 0.875em;
        padding: 0.375rem 0.75rem;
        min-width: 45px;
        display: inline-block;
    }
    
    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        max-height: 70vh; /* Sesuaikan tinggi jika perlu */
        overflow-y: auto;
    }
    
    .table-striped > tbody > tr:nth-of-type(odd) > td {
        background-color: rgba(0, 0, 0, 0.025);
    }
    
    .table-hover > tbody > tr:hover > td {
        background-color: rgba(0, 0, 0, 0.075);
    }
    
    @media (max-width: 768px) {
        .table th, .table td {
            padding: 8px 4px;
            font-size: 0.875rem;
        }
        
        .btn-group-sm .btn {
            padding: 0.2rem 0.4rem;
        }
        
        .btn-group-sm .btn i {
            font-size: 0.75rem;
        }
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h3 class="mb-0">Daftar Obat</h3>
                    <div class="d-flex gap-2">
                        {{-- Perbarui rute --}}
                        <a href="{{ route('obats.dashboard') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                        </a>
                        <a href="{{ route('obats.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Tambah Obat
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Search -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            {{-- Perbarui rute --}}
                            <form method="GET" action="{{ route('obats.index') }}">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Cari nama obat atau jenis obat..." 
                                           value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    @if(request('search'))
                                        {{-- Perbarui rute --}}
                                        <a href="{{ route('obats.index') }}" class="btn btn-outline-danger">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="text-muted">Total: {{ $obats->total() }} obat</span>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead style="background-color: #3f62a4ff;">
                                <tr>
                                    <th class="text-center" style="width: 5%; min-width: 50px;">No</th>
                                    <th style="width: 15%; min-width: 130px;">Nama Obat</th>
                                    <th class="text-center" style="width: 15%; min-width: 120px;">Jenis</th>
                                    <th class="text-center" style="width: 12%; min-width: 100px;">Harga Satuan</th>
                                    <th class="text-center" style="width: 8%; min-width: 70px;">Satuan</th>
                                    <th class="text-center" style="width: 5%; min-width: 80px;">Stok Saat Ini</th> {{-- Label diperbarui --}}
                                    <th class="text-center" style="width: 10%; min-width: 140px;">Aksi</th> {{-- Kolom aksi diaktifkan --}}
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                @forelse($obats as $index => $obat)
                                    <tr>
                                        <td class="text-center fw-medium">{{ $obats->firstItem() + $index }}</td>
                                        <td class="fw-medium">{{ $obat->nama_obat ?? '-' }}</td>
                                        <td class="text-center">{{ $obat->jenis_obat ?? '-' }}</td>
                                        <td class="text-center fw-medium">Rp {{ number_format($obat->harga_satuan, 0, ',', '.') }}</td>
                                        <td class="text-center">{{ $obat->satuan }}</td>
                                        <td class="text-center">
                                            {{-- Menggunakan stok_terakhir dari model Obat --}}
                                            <span class="badge {{ $obat->stok_terakhir <= 10 ? 'bg-danger' : ($obat->stok_terakhir <= 50 ? 'bg-warning text-dark' : 'bg-success') }}">
                                                {{ number_format($obat->stok_terakhir) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Aksi Obat">
                                                {{-- Tombol Detail --}}
                                                <a href="{{ route('obats.show', $obat->id) }}" class="btn btn-info text-black" title="Detail Obat">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                {{-- Tombol Edit --}}
                                                <a href="{{ route('obats.edit', $obat->id) }}" class="btn btn-warning text-dark" title="Edit Obat">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                {{-- Tombol Hapus --}}
                                                <button type="button" class="btn btn-danger" title="Hapus Obat" data-bs-toggle="modal" data-bs-target="#deleteModal" data-obat-id="{{ $obat->id }}" data-obat-name="{{ $obat->nama_obat }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4"> {{-- Sesuaikan colspan jika ada kolom baru --}}
                                            <div class="text-muted">
                                                <i class="fas fa-pills fa-3x mb-3"></i>
                                                <p>Belum ada data obat.</p>
                                                {{-- Perbarui rute --}}
                                                <a href="{{ route('obats.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus"></i> Tambah Obat Pertama
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($obats->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                Menampilkan {{ $obats->firstItem() ?? 0 }} - {{ $obats->lastItem() ?? 0 }} 
                                dari {{ $obats->total() }} data
                            </div>
                            <div>
                                {{ $obats->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal (Tambahkan ini di bagian paling bawah file Blade Anda) -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus Obat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus obat <strong id="obatNameToDelete"></strong>?
                Tindakan ini akan menghapus semua data transaksi dan rekapitulasi terkait obat ini.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            const obatId = button.getAttribute('data-obat-id');
            const obatName = button.getAttribute('data-obat-name');

            const modalTitle = deleteModal.querySelector('.modal-title');
            const obatNameToDelete = deleteModal.querySelector('#obatNameToDelete');
            const deleteForm = deleteModal.querySelector('#deleteForm');

            obatNameToDelete.textContent = obatName;
            // Pastikan rute DELETE sesuai dengan yang di routes/web.php
            deleteForm.action = `{{ url('/obats') }}/${obatId}`;
        });
    });
</script>
@endpush

@endsection