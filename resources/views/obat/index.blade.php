@extends('layout.app')

@section('title', 'Daftar Obat')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>Daftar Obat</h3>
                    <div class="d-flex gap-2">
                        <a href="/farmasi/dashboard-obat" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali ke Farmasi
                        </a>
                        <a href="{{ route('obat.dashboard') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="{{ route('obat.rekapitulasi') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-chart-bar"></i> Rekapitulasi
                        </a>
                        <a href="{{ route('obat.create') }}" class="btn btn-primary btn-sm">
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

                    <!-- Search -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('obat.index') }}">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Cari nama obat atau jenis obat..." 
                                           value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    @if(request('search'))
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
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Obat</th>
                                    <th>Jenis</th>
                                    <th>Harga Satuan</th>
                                    <th>Satuan</th>
                                    <th>Stok Awal</th>
                                    <th>Stok Sisa</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($obats as $index => $obat)
                                    <tr>
                                        <td>{{ $obats->firstItem() + $index }}</td>
                                        <td>{{ $obat->jenis_obat ?? '-' }}</td>
                                        <td class="text-end">Rp {{ number_format($obat->harga_satuan, 0, ',', '.') }}</td>
                                        <td>{{ $obat->satuan }}</td>
                                        <td class="text-center">{{ number_format($obat->stok_awal) }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $obat->stok_sisa <= 10 ? 'bg-danger' : ($obat->stok_sisa <= 50 ? 'bg-warning' : 'bg-success') }}">
                                                {{ number_format($obat->stok_sisa) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('obat.show', $obat) }}" class="btn btn-info btn-sm" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('obat.edit', $obat) }}" class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-success btn-sm" 
                                                        onclick="showTransaksiModal({{ $obat->id }}, '{{ $obat->nama_obat }}')" 
                                                        title="Tambah Transaksi">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                                <form action="{{ route('obat.destroy', $obat) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('⚠️ PERINGATAN!\n\nApakah Anda yakin ingin MENGHAPUS PERMANEN obat ini?\n\n📌 {{ $obat->nama_obat }}\n\n❌ Semua data transaksi terkait juga akan dihapus!\n✅ Tindakan ini TIDAK BISA dibatalkan!\n\nKetik OK jika yakin:')" 
                                                            title="Hapus Permanen">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-pills fa-3x mb-3"></i>
                                                <p>Belum ada data obat.</p>
                                                <a href="{{ route('obat.create') }}" class="btn btn-primary">
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

<!-- Modal Transaksi -->
<div class="modal fade" id="transaksiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Transaksi Obat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="transaksiForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Obat</label>
                        <input type="text" class="form-control" id="namaObatDisplay" readonly>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal" class="form-label">Tanggal</label>
                                <input type="date" class="form-control" name="tanggal" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipe_transaksi" class="form-label">Tipe Transaksi</label>
                                <select class="form-select" name="tipe_transaksi" required>
                                    <option value="">Pilih Tipe</option>
                                    <option value="masuk">Stok Masuk</option>
                                    <option value="keluar">Stok Keluar</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="jumlah" class="form-label">Jumlah</label>
                        <input type="number" class="form-control" name="jumlah" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="petugas" class="form-label">Petugas</label>
                        <input type="text" class="form-control" name="petugas">
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showTransaksiModal(obatId, namaObat) {
    document.getElementById('namaObatDisplay').value = namaObat;
    document.getElementById('transaksiForm').action = `/obat/${obatId}/transaksi`;
    
    const modal = new bootstrap.Modal(document.getElementById('transaksiModal'));
    modal.show();
}
</script>

@endsection