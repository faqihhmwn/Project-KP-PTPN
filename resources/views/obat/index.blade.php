@extends('layout.app')

@section('title', 'Daftar Obat')

@section('content')
    <style>
        .table th,
        .table td {
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
        }

        .table td:nth-child(2),
        .table th:nth-child(2) {
            /* Nama Obat - allow wrapping */
            white-space: normal;
            max-width: 200px;
        }

        .table td:nth-child(3),
        .table th:nth-child(3) {
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
            max-height: 70vh;
            overflow-y: auto;
        }

        .table-striped>tbody>tr:nth-of-type(odd)>td {
            background-color: rgba(0, 0, 0, 0.025);
        }

        .table-hover>tbody>tr:hover>td {
            background-color: rgba(0, 0, 0, 0.075);
        }

        @media (max-width: 768px) {

            .table th,
            .table td {
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
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3>Daftar Obat</h3>
                        <div class="d-flex gap-2">
                            <a href="{{ route('obat.dashboard') }}" class="btn btn-info btn-sm">
                                <i class="fas fa-arrow-left"></i> Kembali ke Farmasi
                            </a>
                            <a href="{{ route('obat.rekapitulasi') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-chart-bar"></i> Rekapitulasi
                            </a>
                            {{-- <a href="{{ route('obat.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Tambah Obat
                            </a> --}}
                        </div>
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Search -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form method="GET" action="{{ route('obat.index') }}">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control"
                                            placeholder="Cari nama obat atau jenis obat..." value="{{ request('search') }}">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="fas fa-search"></i> Cari
                                        </button>
                                        @if (request('search'))
                                            <a href="{{ route('obat.index') }}" class="btn btn-outline-danger">
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
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center" style="width: 5%; min-width: 50px;">No</th>
                                        <th class="text-center" style="width: 12%;">Unit</th>
                                        <th style="width: 15%; min-width: 130px;">Nama Obat</th>
                                        <th class="text-center" style="width: 15%; min-width: 120px;">Jenis</th>
                                        <th class="text-center" style="width: 12%; min-width: 100px;">Expired Date</th>
                                        <th class="text-center" style="width: 12%; min-width: 60px;">Harga Satuan</th>
                                        <th class="text-center" style="width: 8%; min-width: 70px;">Satuan</th>
                                        <th class="text-center" style="width: 5%; min-width: 80px;">Stok Awal</th>
                                        <th class="text-center" style="width: 5%; min-width: 80px;">Stok Sisa</th>
                                        {{-- <th class="text-center" style="width: 10%; min-width: 140px;">Aksi</th> --}}
                                    </tr>
                                </thead>
                                <tbody class="table-group-divider">
                                    @forelse($obats as $index => $obat)
                                        <tr>
                                            <td class="text-center fw-medium">{{ $obats->firstItem() + $index }}</td>
                                            <td class="text-center">{{ $obat->unit->nama ?? '-' }}</td>
                                            <td class="fw-medium">{{ $obat->nama_obat ?? '-' }}</td>
                                            <td class="text-center">{{ $obat->jenis_obat ?? '-' }}</td>
                                            <td class="text-center">{{ $obat->expired_date ? \Carbon\Carbon::parse($obat->expired_date)->format('d/m/Y') : '-' }}</td>
                                            <td class="text-center fw-medium">Rp{{ number_format($obat->harga_satuan, 0, ',', '.') }}</td>
                                            <td class="text-center">{{ $obat->satuan }}</td>
                                            <td class="text-center">{{ number_format($obat->stok_awal) }}</td>
                                            @php
                                                $sisaStok = $obat->stokSisa($bulan, $tahun);
                                            @endphp
                                            <td class="text-center">
                                                <span
                                                    class="badge {{ $sisaStok <= 10 ? 'bg-danger' : ($sisaStok <= 50 ? 'bg-warning text-dark' : 'bg-success') }}">
                                                    {{ number_format($sisaStok) }}
                                                </span>
                                            </td>
                                            {{-- <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <!-- <a href="{{ route('obat.show', $obat) }}" class="btn btn-info btn-sm"
                                                        title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a> -->
                                                    <a href="{{ route('obat.edit', $obat) }}"
                                                        class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('obat.destroy', $obat) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <!-- <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('⚠️ PERINGATAN!\n\nApakah Anda yakin ingin MENGHAPUS PERMANEN obat ini?\n\n📌 {{ $obat->nama_obat }}\n\n❌ Semua data transaksi terkait juga akan dihapus!\n✅ Tindakan ini TIDAK BISA dibatalkan!\n\nKetik OK jika yakin:')"
                                                            title="Hapus Permanen">
                                                            <i class="fas fa-trash"></i>
                                                        </button> -->
                                                    </form>
                                                </div>
                                            </td> --}}
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-pills fa-3x mb-3"></i>
                                                    <p>Belum ada data obat.</p>
                                                    <!-- <a href="{{ route('obat.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus"></i> Tambah Obat Pertama
                                                    </a> -->
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        {{-- @if ($obats->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    Menampilkan {{ $obats->firstItem() ?? 0 }} - {{ $obats->lastItem() ?? 0 }}
                                    dari {{ $obats->total() }} data
                                </div>
                                <div>
                                    {{ $obats->appends(request()->query())->links() }} --}}
                                
                            @if ($obats->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                                <div class="mb-2">
                                    Menampilkan {{ $obats->firstItem() }} - {{ $obats->lastItem() }} dari {{ $obats->total() }} data
                                </div>
                                <div class="mb-2">
                                    <nav>
                                        <ul class="pagination pagination-sm justify-content-end mb-0">
                                            {{ $obats->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection