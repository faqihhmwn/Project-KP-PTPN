@extends('layout.app')

@section('title', 'Dashboard Obat')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.obat.dashboard') }}">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="unit_id" class="form-label fw-bold">Tampilkan Data Untuk Unit:</label>
                        <select name="unit_id" id="unit_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua Unit --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" {{ $unitId == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $totalObat }}</h4>
                            <p class="mb-0">
                                Total Obat
                                @if($unitId && $units->find($unitId))
                                    ({{ $units->find($unitId)->nama }})
                                @endif
                            </p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-pills fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                            <a href="{{ route('admin.obat.index') }}" class="btn btn-info w-100 mb-2 d-flex flex-column align-items-center justify-content-center">
                                <i class="bi bi-list-ul mb-1" style="font-size: 1.5rem;"></i>
                                Daftar Obat
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.obat.rekapitulasi') }}" class="btn btn-warning w-100 mb-2 d-flex flex-column align-items-center justify-content-center">
                                <i class="bi bi-bar-chart-line mb-1" style="font-size: 1.5rem;"></i>
                                Rekapitulasi
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
                    <p>
                        <strong>Total Obat Terdaftar:</strong> {{ $totalObat }} jenis obat
                        @if($unitId && $units->find($unitId))
                            di unit {{ $units->find($unitId)->nama }}
                        @else
                            di semua unit
                        @endif
                    </p>
                    <p><strong>Fitur Utama:</strong></p>
                    <ul>
                        <li>Rekapitulasi obat bulanan dengan input harian</li>
                        <li>Manajemen stok otomatis</li>
                        <li>Search dan filter obat</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection