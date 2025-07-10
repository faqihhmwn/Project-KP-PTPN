@extends('layout.app')
@section('title', 'Detail Obat')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>Detail Obat: {{ $obat->nama_obat }}</h3>
                    <div class="btn-group">
                        <a href="{{ route('obat.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <a href="{{ route('obat.edit', $obat) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button type="button" class="btn btn-success" 
                                onclick="showTransaksiModal({{ $obat->id }}, '{{ $obat->nama_obat }}')">
                            <i class="fas fa-plus"></i> Tambah Transaksi
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Informasi Obat -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5><i class="fas fa-pill"></i> Informasi Obat</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Nama Obat:</strong></td>
                                            <td>{{ $obat->nama_obat }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jenis/Kategori:</strong></td>
                                            <td>{{ $obat->jenis_obat ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Harga Satuan:</strong></td>
                                            <td>Rp {{ number_format($obat->harga_satuan, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Satuan:</strong></td>
                                            <td>{{ $obat->satuan }}</td>
                                        </tr>
        
                                        <tr>
                                            <td><strong>Keterangan:</strong></td>
                                            <td>{{ $obat->keterangan ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-info text-white">
                                    <h5><i class="fas fa-boxes"></i> Informasi Stok</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6 mb-3">
                                            <div class="p-3 bg-light rounded">
                                                <h4 class="text-primary">{{ number_format($obat->stok_awal) }}</h4>
                                                <small class="text-muted">Stok Awal</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="p-3 {{ $obat->stok_sisa <= 10 ? 'bg-danger' : ($obat->stok_sisa <= 50 ? 'bg-warning' : 'bg-success') }} text-white rounded">
                                                <h4>{{ number_format($obat->stok_sisa) }}</h4>
                                                <small>Stok Sisa</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistik Penggunaan -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-warning text-white">
                                    <h6><i class="fas fa-calendar-alt"></i> Penggunaan Bulan Ini</h6>
                                </div>
                                <div class="card-body">
                                    @php
                                        $totalBulanIni = $bulanIni->where('tipe_transaksi', 'keluar')->sum('jumlah_keluar');
                                        $biayaBulanIni = $totalBulanIni * $obat->harga_satuan;
                                    @endphp
                                    <h4 class="text-warning">{{ number_format($totalBulanIni) }} {{ $obat->satuan }}</h4>
                                    <p class="mb-0">Total Biaya: Rp {{ number_format($biayaBulanIni, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-secondary text-white">
                                    <h6><i class="fas fa-history"></i> Penggunaan Bulan Lalu</h6>
                                </div>
                                <div class="card-body">
                                    @php
                                        $totalBulanLalu = $bulanLalu->where('tipe_transaksi', 'keluar')->sum('jumlah_keluar');
                                        $biayaBulanLalu = $totalBulanLalu * $obat->harga_satuan;
                                    @endphp
                                    <h4 class="text-secondary">{{ number_format($totalBulanLalu) }} {{ $obat->satuan }}</h4>
                                    <p class="mb-0">Total Biaya: Rp {{ number_format($biayaBulanLalu, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Transaksi -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-list"></i> Riwayat Transaksi (10 Terakhir)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Tipe</th>
                                            <th>Jumlah Masuk</th>
                                            <th>Jumlah Keluar</th>
                                            <th>Total Biaya</th>
                                            <th>Petugas</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($obat->transaksiObats->sortByDesc('tanggal')->take(10) as $transaksi)
                                            <tr>
                                                <td>{{ $transaksi->tanggal->format('d/m/Y') }}</td>
                                                <td>
                                                    @if($transaksi->tipe_transaksi == 'masuk')
                                                        <span class="badge bg-success">Masuk</span>
                                                    @else
                                                        <span class="badge bg-danger">Keluar</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    {{-- Kolom jumlah_masuk tidak digunakan, tampilkan '-' --}} -
                                                </td>
                                                <td class="text-center">
                                                    {{ $transaksi->jumlah_keluar > 0 ? number_format($transaksi->jumlah_keluar) : '-' }}
                                                </td>
                                                <td class="text-end">
                                                    {{-- Kolom total_biaya tidak digunakan, tampilkan '-' --}} -
                                                </td>
                                                <td>{{ $transaksi->petugas ?? '-' }}</td>
                                                <td>{{ $transaksi->keterangan ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">Belum ada transaksi</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
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
            <form action="{{ route('obat.transaksi.store', $obat) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Obat</label>
                        <input type="text" class="form-control" value="{{ $obat->nama_obat }}" readonly>
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
                                    <option value="masuk">Stok Awal</option>
                                    <option value="keluar">Stok Keluar</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="jumlah" class="form-label">Jumlah</label>
                        <input type="number" class="form-control" name="jumlah" min="1" required>
                        <small class="text-muted">Stok tersedia: {{ $obat->stok_sisa }} {{ $obat->satuan }}</small>
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
    const modal = new bootstrap.Modal(document.getElementById('transaksiModal'));
    modal.show();
}
</script>

@endsection