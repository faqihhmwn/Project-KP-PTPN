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
                        <a href="{{ request()->get('return_url', route('obat.index')) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <a href="{{ route('obat.edit', $obat) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
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


                </div>
            </div>
        </div>
    </div>
</div>



@endsection