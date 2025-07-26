@extends('layout.app')

@section('title', 'Dashboard Obat')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $totalObat }}</h4>
                            <p class="mb-0">Total Obat Terdaftar</p> {{-- Sedikit lebih spesifik --}}
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-pills fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Anda bisa menambahkan card lain di sini, misalnya untuk Total Stok, Obat Kadaluarsa Mendekat, dll. --}}
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-2">
                            {{-- Perbarui rute ke 'obats.index' --}}
                            <a href="{{ route('obats.index') }}" class="btn btn-info w-100 mb-2 d-flex flex-column align-items-center justify-content-center">
                                <i class="bi bi-list-ul mb-1" style="font-size: 1.5rem;"></i>
                                Daftar Obat
                            </a>
                        </div>
                        <div class="col-md-2">
                            {{-- Perbarui rute ke 'obats.rekapitulasiBulanan' --}}
                            <a href="{{ route('obats.rekapitulasiBulanan') }}" class="btn btn-warning w-100 mb-2 d-flex flex-column align-items-center justify-content-center">
                                <i class="bi bi-bar-chart-line mb-1" style="font-size: 1.5rem;"></i>
                                Rekapitulasi Obat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Informasi Sistem</h5>
                </div>
                <div class="card-body">
                    <p>Sistem Manajemen Obat Puskesmas PTPN</p>
                    <p><strong>Total Obat Terdaftar:</strong> {{ $totalObat }} jenis obat</p>

                    <p><strong>Fitur Utama:</strong></p>
                    <ul>
                        {{-- Deskripsi fitur disesuaikan dengan sistem baru --}}
                        <li>Pencatatan transaksi stok obat (masuk, keluar, penyesuaian)</li>
                        <li>Rekapitulasi stok obat harian dan bulanan secara otomatis</li>
                        <li>Manajemen data dasar obat (tambah, edit, hapus)</li>
                        <li>Pencarian dan filter data obat yang mudah</li>
                        <li>Export laporan rekapitulasi ke format Excel</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection