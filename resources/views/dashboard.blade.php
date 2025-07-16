@extends('layout.app')

@section('content')
<div class="container py-4">
    {{-- Welcome --}}
    <div class="alert alert-success" id="welcome-alert">
        Selamat datang, {{ Auth::user()->name }} dari unit {{ Auth::user()->unit->nama ?? '-' }}!
    </div>

    {{-- Filter Periode dan Search --}}
    <form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-end mb-4">
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
            <input type="text" name="search" class="form-control" id="search" value="{{ request('search') }}" placeholder="Ketik nama subkategori...">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary mt-2" type="submit">Tampilkan</button>
        </div>
    </form>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-3" id="dashboardTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="ringkasan-tab" data-bs-toggle="tab" data-bs-target="#ringkasan" type="button" role="tab">Ringkasan</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="grafik-tab" data-bs-toggle="tab" data-bs-target="#grafik" type="button" role="tab">Grafik</button>
        </li>
    </ul>
    <div class="tab-content" id="dashboardTabContent">
        {{-- Ringkasan Tab --}}
        <div class="tab-pane fade show active" id="ringkasan" role="tabpanel">
            <div class="row g-4">
                @foreach ($ringkasan as $kategori)
                    @php
                        $slug = \Illuminate\Support\Str::slug($kategori['nama'], '-');
                        $colors = ['primary', 'success', 'warning', 'info', 'danger', 'secondary'];
                        $color = $colors[$loop->index % count($colors)];
                        $filteredSubs = collect($kategori['subkategori'])->filter(function ($sub) {
                            return request('search') === null || stripos($sub['nama'], request('search')) !== false;
                        });
                    @endphp

                    @if ($filteredSubs->count())
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="card-title text-{{ $color }}">{{ $kategori['nama'] }}</h5>
                                        <span class="badge bg-{{ $color }} rounded-pill">
                                            Total: {{ $filteredSubs->sum('total') }}
                                        </span>
                                    </div>
                                    <ul class="list-group list-group-flush small">
                                        @foreach ($filteredSubs as $sub)
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                {{ $sub['nama'] }}
                                                <span class="badge bg-light text-dark">{{ $sub['total'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="mt-3 text-end">
                                        <a href="{{ url('/laporan/' . $slug) }}" class="btn btn-sm btn-outline-{{ $color }}">
                                            Lihat Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Grafik Tab --}}
        <div class="tab-pane fade" id="grafik" role="tabpanel">
            <canvas id="kategoriChart"></canvas>
        </div>
    </div>
</div>

{{-- Alert Dismiss --}}
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

{{-- Chart.js --}}
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
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endsection