@extends('layout.app')

@section('content')
<div class="container py-4">
    {{-- Welcome --}}
    <div class="alert alert-success" id="welcome-alert">
        Selamat datang, {{ $authUser->name }}! Anda masuk sebagai Admin Pusat.
    </div>

    @if (session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if (session('error'))<div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-3" id="dashboardTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $tab === 'laporan' ? 'active' : '' }}" href="{{ route('dashboard', ['tab' => 'laporan']) }}">
                Laporan
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $tab === 'obat' ? 'active' : '' }}" href="{{ route('dashboard', ['tab' => 'obat']) }}">
                Daftar Obat
            </a>
        </li>
    </ul>

    <div class="tab-content" id="dashboardTabContent">

        {{-- ======================= TAB LAPORAN ======================= --}}
        @if ($tab === 'laporan')
        <div class="tab-pane fade show active" id="laporan" role="tabpanel">

            {{-- Filter untuk Laporan --}}
            <form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-end mb-4">
                <input type="hidden" name="tab" value="laporan">
                <div class="col-md-3">
                    <label for="unit_id" class="form-label">Pilih Unit</label>
                    <select name="unit_id" class="form-select" id="unit_id">
                        <option value="">Semua Unit</option>
                        @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" {{ $unitId == $unit->id ? 'selected' : '' }}>
                            {{ $unit->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <select name="bulan" class="form-select" id="bulan">
                        <option value="">Semua Bulan</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ ($bulan == $i) ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                            </option>
                            @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <select name="tahun" class="form-select" id="tahun">
                        <option value="">Semua Tahun</option>
                        @for ($y = date('Y'); $y >= 2000; $y--)
                        <option value="{{ $y }}" {{ ($tahun == $y) ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search" class="form-label">Cari Subkategori</label>
                    <input type="text" name="search" class="form-control" id="search" value="{{ $searchSubkategori }}" placeholder="Ketik nama subkategori...">
                </div>
                <div class="col-md-12">
                    <button class="btn btn-primary mt-2 w-100" type="submit">Tampilkan Laporan</button>
                </div>
            </form>

            {{-- Fitur Validasi & Export --}}
            <div class="card my-4">
                <div class="card-body">
                    <h5 class="card-title">Validasi & Export</h5>
                    <p class="card-text text-muted small">Pilih Bulan dan Tahun pada filter di atas untuk mengaktifkan tombol validasi dan export.</p>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('dashboard.validate') }}" class="w-100">
                            @csrf
                            <input type="hidden" name="bulan" value="{{ $bulan }}" id="bulan_hidden">
                            <input type="hidden" name="tahun" value="{{ $tahun }}" id="tahun_hidden">
                            <button type="submit" id="validate_button" class="btn btn-info w-100" disabled>
                                <i class="bi bi-check-circle-fill"></i> Validasi Periode
                            </button>
                        </form>
                        <a href="{{ route('dashboard.export-rekap', ['bulan' => $bulan, 'tahun' => $tahun, 'unit_id' => $unitId]) }}" class="btn btn-outline-success w-100 @if(!$bulan || !$tahun) disabled @endif" target="_blank">
                            <i class="fas fa-file-excel"></i> Export Rekap Laporan
                        </a>
                    </div>
                </div>
            </div>

            {{-- Konten Laporan --}}
            <div class="row g-4">
                @foreach ($ringkasan as $kategori)
                @php
                $slug = \Illuminate\Support\Str::slug($kategori['nama'], '-');
                $colors = ['primary', 'success', 'warning', 'info', 'danger', 'secondary'];
                $color = $colors[$loop->index % count($colors)];
                $filteredSubs = collect($kategori['subkategori'])->filter(function ($sub) {
                return request('search') === null ||
                stripos($sub['nama'], request('search')) !== false;
                });
                @endphp

                @if ($filteredSubs->count())
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title text-{{ $color }}">{{ $kategori['nama'] }}</h5>
                                <span class="badge bg-{{ $color }} rounded-pill">Total: {{ $filteredSubs->sum('total') }}</span>
                            </div>
                            <ul class="list-group list-group-flush small">
                                @foreach ($filteredSubs as $sub)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    {{ $sub['nama'] }}
                                    <span class="badge bg-light text-dark">{{ $sub['total'] }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>

            {{-- Chart --}}
            <div class="mt-5">
                <canvas id="kategoriChart"></canvas>
            </div>
        </div>
        @endif

        {{-- ======================= TAB OBAT ======================= --}}
        @if ($tab === 'obat')
        <div class="tab-pane fade show active" id="obat" role="tabpanel">

            {{-- Filter untuk Obat --}}
            <form method="GET" action="{{ route('dashboard') }}" class="mb-3 row g-2 align-items-end">
                <input type="hidden" name="tab" value="obat">
                @if ($is_admin)
                <div class="col-md-4">
                    <label for="unit_id_obat" class="form-label">Pilih Unit</label>
                    <select name="unit_id_obat" class="form-select" id="unit_id_obat">
                        <option value="">Semua Unit</option>
                        @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" {{ $unitIdObat == $unit->id ? 'selected' : '' }}>
                            {{ $unit->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-4">
                    <label for="search_nama" class="form-label">Nama Obat</label>
                    <input type="text" name="search_nama" class="form-control" value="{{ $searchNamaObat }}" placeholder="Masukkan nama obat...">
                </div>
                <div class="col-md-4">
                    <label for="search_jenis" class="form-label">Jenis Obat</label>
                    <input type="text" name="search_jenis" id="search_jenis" class="form-control" value="{{ request('search_jenis') }}"
                        placeholder="Masukkan jenis obat...">
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary mt-2 w-100">Tampilkan Daftar Obat</button>
                </div>
            </form>

            {{-- Konten Daftar Obat --}}
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Unit</th>
                            <th>Nama Obat</th>
                            <th>Jenis</th>
                            <th>Harga</th>
                            <th>Stok Awal</th>
                            <th>Stok Sisa</th>
                            <th>Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($obats as $obat)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $obat->unit->nama ?? 'N/A' }}</td>
                            <td>{{ $obat->nama_obat }}</td>
                            <td>{{ $obat->jenis_obat }}</td>
                            <td>{{ number_format($obat->harga_satuan) }}</td>
                            <td>{{ $obat->stok_awal }}</td>
                            <td>{{ $obat->stok_sisa }}</td>
                            <td>{{ $obat->satuan }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data obat yang cocok.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Script --}}
<script>
    setTimeout(() => {
        const alert = document.getElementById('welcome-alert');
        if (alert) {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = 0;
            setTimeout(() => alert.remove(), 500);
        }
    }, 2000);
</script>

@if ($tab === 'laporan')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('kategoriChart').getContext('2d');
    const kategoriChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($ringkasan, 'nama')) !!},
            datasets: [{
                label: 'Jumlah Laporan',
                data: {!! json_encode(array_column($ringkasan, 'total')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Script tombol validasi
    document.addEventListener('DOMContentLoaded', function () {
        const validateButton = document.getElementById('validate_button');
        const bulanFilter = document.getElementById('bulan');
        const tahunFilter = document.getElementById('tahun');
        const bulanHidden = document.getElementById('bulan_hidden');
        const tahunHidden = document.getElementById('tahun_hidden');

        function checkValidatability() {
            const bulan = bulanFilter.value;
            const tahun = tahunFilter.value;

            // update hidden values
            if (bulanHidden && tahunHidden) {
                bulanHidden.value = bulan;
                tahunHidden.value = tahun;
            }

            if (validateButton && bulan && tahun) {
                validateButton.disabled = false;
                const bulanName = bulanFilter.options[bulanFilter.selectedIndex].text;
                validateButton.innerHTML = `<i class="bi bi-check-circle-fill"></i> Validasi Laporan ${bulanName.trim()} ${tahun} untuk SEMUA UNIT`;
            } else {
                validateButton.disabled = true;
                validateButton.innerHTML = `<i class="bi bi-check-circle-fill"></i> Validasi Periode`;
            }
        }

        checkValidatability();
        bulanFilter.addEventListener('change', checkValidatability);
        tahunFilter.addEventListener('change', checkValidatability);
    });
</script>
@endif
@endsection