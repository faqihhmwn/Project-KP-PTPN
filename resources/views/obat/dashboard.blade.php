@extends('layout.app')

@section('title', 'Dashboard Obat')

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $totalObat }}</h4>
                            <p class="mb-0">Total Obat</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-pills fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <a href="{{ route('obat.create') }}" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-plus"></i><br>
                                Tambah Obat
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('obat.index') }}" class="btn btn-info w-100 mb-2">
                                <i class="fas fa-list"></i><br>
                                Daftar Obat
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('obat.rekapitulasi') }}" class="btn btn-warning w-100 mb-2">
                                <i class="fas fa-chart-bar"></i><br>
                                Rekapitulasi
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('obat.rekapitulasi') }}?export=1&periode=1" class="btn btn-secondary w-100 mb-2">
                                <i class="fas fa-file-export"></i><br>
                                Export Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Info -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Informasi Sistem</h5>
                </div>
                <div class="card-body">
                    <p>Sistem Manajemen Obat Puskesmas PTPN Kandir</p>
                    <p><strong>Total Obat Terdaftar:</strong> {{ $totalObat }} jenis obat</p>

                    <p><strong>Fitur Utama:</strong></p>
                    <ul>
                        <li>Rekapitulasi obat bulanan dengan input harian</li>
                        <li>Manajemen stok otomatis</li>
                        <li>Export laporan CSV</li>
                        <li>Search dan filter obat</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Data Obat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('obat.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Pilih File Excel</label>
                        <input type="file" class="form-control" name="file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Format yang didukung: .xlsx, .xls, .csv</div>
                    </div>
                    <div class="alert alert-info">
                        <strong>Format Excel:</strong><br>
                        - Kolom A: Nama Obat<br>
                        - Kolom B: Jenis Obat<br>
                        - Kolom C: Harga Satuan<br>
                        - Kolom D: Satuan<br>
                        - Kolom E: Stok Awal
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection