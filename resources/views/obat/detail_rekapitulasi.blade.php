@extends('layout.app')
@section('title', 'Detail Rekapitulasi Obat')

@section('content')
<style>
    .info-card {
        transition: all 0.3s ease;
    }
    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    }
    .stat-card {
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .no-data {
        background-color: #f8f9fa;
        color: #6c757d;
        border-radius: 10px;
        padding: 20px;
    }
    .table-responsive {
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
</style>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary mb-0">{{ $obat->nama_obat }}</h2>
            <p class="text-muted mb-0">Rekapitulasi Penggunaan Obat - {{ \Carbon\Carbon::createFromDate(null, (int)$bulan, 1)->format('F') }} {{ $tahun }}</p>
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Info Cards -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card info-card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary d-flex align-items-center mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Informasi Obat
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="150" class="text-muted"><strong>Nama Obat</strong></td>
                                <td class="text-dark">: {{ $obat->nama_obat }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>Jenis/Kategori</strong></td>
                                <td class="text-dark">: {{ $obat->jenis_obat ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>Harga Satuan</strong></td>
                                <td class="text-dark">: Rp {{ number_format($obat->harga_satuan, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>Satuan</strong></td>
                                <td class="text-dark">: {{ $obat->satuan }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card info-card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary d-flex align-items-center mb-3">
                        <i class="fas fa-chart-bar me-2"></i>
                        Statistik Bulan Ini
                    </h5>
                    <div class="row text-center g-3">
                        <div class="col-6">
                            <div class="p-3 rounded bg-primary bg-opacity-10 stat-card">
                                <h3 class="text-primary mb-0">{{ $rekapHarian->sum('jumlah_keluar') }}</h3>
                                <small class="text-muted">Total Keluar</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded bg-success bg-opacity-10 stat-card">
                                <h3 class="text-success mb-0">Rp {{ number_format($rekapHarian->sum('jumlah_keluar') * $obat->harga_satuan, 0, ',', '.') }}</h3>
                                <small class="text-muted">Total Biaya</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rekapitulasi Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title text-primary d-flex align-items-center mb-4">
                <i class="fas fa-table me-2"></i>
                Rekapitulasi Harian {{ \Carbon\Carbon::createFromDate(null, (int)$bulan, 1)->format('F') }} {{ $tahun }}
            </h5>
            
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead>
                        <tr class="text-center">
                            <th style="width: 15%;">Tanggal</th>
                            <th style="width: 20%;">Hari</th>
                            <th style="width: 25%;">Jumlah Keluar</th>
                            <th style="width: 40%;">Total Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalHari = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
                            $totalKeluar = 0;
                            $totalBiaya = 0;
                        @endphp

                        @for($day = 1; $day <= $totalHari; $day++)
                            @php
                                $currentDate = \Carbon\Carbon::createFromDate($tahun, $bulan, $day);
                                $rekap = $rekapHarian->firstWhere('tanggal', $currentDate->format('Y-m-d'));
                                $jumlahKeluar = $rekap ? $rekap->jumlah_keluar : 0;
                                $biaya = $jumlahKeluar * $obat->harga_satuan;
                                $totalKeluar += $jumlahKeluar;
                                $totalBiaya += $biaya;
                            @endphp
                            <tr @if($jumlahKeluar > 0) class="table-light" @endif>
                                <td class="text-center">{{ $currentDate->format('d') }}</td>
                                <td>{{ $currentDate->format('l') }}</td>
                                <td class="text-center">
                                    @if($jumlahKeluar > 0)
                                        <span class="badge bg-primary">{{ $jumlahKeluar }} {{ $obat->satuan }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($biaya > 0)
                                        <span class="text-success">Rp {{ number_format($biaya, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endfor
                        <tr class="table-primary fw-bold">
                            <td colspan="2" class="text-center">Total</td>
                            <td class="text-center">{{ $totalKeluar }} {{ $obat->satuan }}</td>
                            <td class="text-end">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
