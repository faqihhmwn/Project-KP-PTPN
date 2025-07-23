@extends('layout.app')

@section('content')
    <div class="container py-4">
        {{-- Welcome --}}
        <div class="alert alert-success" id="welcome-alert">
            Selamat datang, {{ $authUser->name }}!
            @if ($is_admin)
                Anda masuk sebagai Admin Pusat.
            @else
                Anda masuk dari unit {{ $authUser->unit->nama ?? '-' }}.
            @endif
        </div>

        {{-- FILTER untuk LAPORAN --}}
        @if ($tab === 'laporan')
            <form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-end mb-4">
                @if ($is_admin)
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
                @else
                    <div class="col-md-3">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" value="{{ $authUser->unit->nama ?? '' }}" disabled>
                        <input type="hidden" name="unit_id" value="{{ $authUser->unit_id }}">
                    </div>
                @endif

                <div class="col-md-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <select name="bulan" class="form-select">
                        <option value="">Semua Bulan</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <select name="tahun" class="form-select">
                        <option value="">Semua Tahun</option>
                        @for ($y = date('Y'); $y >= 2000; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="search" class="form-label">Cari Subkategori</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                        placeholder="Ketik nama subkategori...">
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary mt-2">Tampilkan</button>
                </div>
            </form>
        @endif

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-3" id="dashboardTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab === 'laporan' ? 'active' : '' }}"
                    href="{{ route('dashboard', array_merge(request()->except('search'), ['tab' => 'laporan'])) }}">
                    Laporan
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab === 'obat' ? 'active' : '' }}"
                    href="{{ route('dashboard', array_merge(request()->except('search'), ['tab' => 'obat'])) }}">
                    Daftar Obat
                </a>
            </li>
        </ul>

        <div class="tab-content" id="dashboardTabContent">

            {{-- Laporan --}}
            @if ($tab === 'laporan')
                <div class="tab-pane fade show active" id="laporan" role="tabpanel">
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
                                                <h5 class="card-title text-{{ $color }}">{{ $kategori['nama'] }}
                                                </h5>
                                                <span class="badge bg-{{ $color }} rounded-pill">
                                                    Total: {{ $filteredSubs->sum('total') }}
                                                </span>
                                            </div>
                                            <ul class="list-group list-group-flush small">
                                                @foreach ($filteredSubs as $sub)
                                                    <li
                                                        class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        {{ $sub['nama'] }}
                                                        <span class="badge bg-light text-dark">{{ $sub['total'] }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            @if (!$is_admin)
                                                <div class="mt-3 text-end">
                                                    <a href="{{ url('/laporan/' . $slug) }}"
                                                        class="btn btn-sm btn-outline-{{ $color }}">
                                                        Lihat Detail
                                                    </a>
                                                </div>
                                            @endif
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

            {{-- Obat --}}
            @if ($tab === 'obat')
                <div class="tab-pane fade show active" id="obat" role="tabpanel">
                    <form method="GET" action="{{ route('dashboard') }}" class="mb-3 row">
                        <input type="hidden" name="tab" value="obat">
                        @if ($is_admin)
                            <input type="hidden" name="unit_id" value="{{ $unitId }}">
                        @else
                            <input type="hidden" name="unit_id" value="{{ $authUser->unit_id }}">
                        @endif

                        <div class="col-md-6">
                            <label for="search" class="form-label">Cari Nama Obat</label>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                placeholder="Ketik nama obat...">
                        </div>
                        <div class="col-md-2 align-self-end">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-primary">
                                <tr>
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
                                        <td>{{ $obat->nama_obat }}</td>
                                        <td>{{ $obat->jenis_obat }}</td>
                                        <td>{{ number_format($obat->harga_satuan) }}</td>
                                        <td>{{ $obat->stok_awal }}</td>
                                        <td>{{ $obat->stok_sisa }}</td>
                                        <td>{{ $obat->satuan }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">Tidak ada data obat.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Alert Fade --}}
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

    {{-- Chart Script --}}
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
    @endif
@endsection
