@extends('layout.app')

@section('title', 'Daftar Obat - Admin')

@section('content')
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>Daftar Obat (Admin)</h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.obat.dashboard') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali ke Farmasi
                        </a>
                        <a href="{{ route('admin.obat.rekapitulasi') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-chart-bar"></i> Rekapitulasi
                        </a>
                        <a href="{{ route('admin.obat.create') }}" class="btn btn-primary btn-sm">
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

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <form method="GET" action="{{ route('admin.obat.index') }}" class="d-flex gap-2">
                                <select name="unit_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Semua Unit --</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}" {{ $unitId == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->nama }}
                                        </option>
                                    @endforeach
                                </select>

                                <input type="text" name="search" class="form-control" placeholder="Cari nama/jenis obat..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                <a href="{{ route('admin.obat.index') }}" class="btn btn-secondary" title="Reset Filter"><i class="fas fa-times"></i></a>
                            </form>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="text-muted">Total: {{ $obats->total() }} obat</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Nama Obat</th>
                                    <th>Unit</th>
                                    <th class="text-center">Jenis</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-center">Stok Awal</th>
                                    <th class="text-center">Stok Sisa</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($obats->isEmpty())
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            @if(request()->has('search') && request('search') != '')
                                                <p class="text-muted mb-0">Data obat tidak ditemukan untuk kata kunci <strong class="text-danger">"{{ request('search') }}"</strong>.</p>
                                            @elseif(request()->has('unit_id') && request('unit_id') != '')
                                                 <p class="text-muted mb-0">Data obat belum ada di unit ini.</p>
                                            @else
                                                <p class="text-muted mb-0">Data obat belum ada.</p>
                                            @endif
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($obats as $index => $obat)
                                        <tr>
                                            <td class="text-center">{{ $obats->firstItem() + $index }}</td>
                                            <td class="fw-medium">{{ $obat->nama_obat ?? '-' }}</td>
                                            <td><span class="badge bg-secondary">{{ $obat->unit->nama ?? 'N/A' }}</span></td>
                                            <td class="text-center">{{ $obat->jenis_obat ?? '-' }}</td>
                                            <td class="text-end fw-medium">Rp {{ number_format($obat->harga_satuan, 0, ',', '.') }}</td>
                                            <td class="text-center">{{ number_format($obat->stok_awal) }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $obat->stok_sisa <= 10 ? 'bg-danger' : ($obat->stok_sisa <= 50 ? 'bg-warning text-dark' : 'bg-success') }}">
                                                    {{ number_format($obat->stok_sisa) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.obat.show', $obat) }}" class="btn btn-info" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.obat.edit', $obat) }}" class="btn btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.obat.destroy', $obat) }}" method="POST" class="d-inline" onsubmit="return confirm('Anda yakin ingin menghapus obat ini secara permanen? Tindakan ini tidak bisa dibatalkan.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger" title="Hapus Permanen">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form> 
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if($obats->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                Menampilkan {{ $obats->firstItem() ?? 0 }} - {{ $obats->lastItem() ?? 0 }} dari {{ $obats->total() }} data
                            </div>
                            <div>
                                {{ $obats->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection