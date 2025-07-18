@extends('layout.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">Laporan Kependudukan (Admin)</h3>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header fw-bold">Input Data Atas Nama Unit</div>
        <div class="card-body">
            <form method="POST" action="{{ route('laporan.kependudukan.store') }}">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="unit_id_input" class="form-label">Unit</label>
                        <select name="unit_id" id="unit_id_input" class="form-select" required>
                            <option value="">-- Pilih Unit --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="bulan_input" class="form-label">Bulan</label>
                        <select name="bulan" id="bulan_input" class="form-select" required>
                            @foreach (range(1, 12) as $b)
                                <option value="{{ $b }}">{{ DateTime::createFromFormat('!m', $b)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="tahun_input" class="form-label">Tahun</label>
                        <select name="tahun" id="tahun_input" class="form-select" required>
                            @for ($t = date('Y'); $t >= 2020; $t--)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Subkategori</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subkategori as $sub)
                        <tr>
                            <td>{{ $sub->nama }}</td>
                            <td><input type="number" name="jumlah[{{ $sub->id }}]" class="form-control" min="0" value="0" required></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary mt-3">Simpan Data</button>
            </form>
        </div>
    </div>
    
    <hr class="my-5">
    <h5 class="fw-bold">Data Tersimpan</h5>

    {{-- Fitur Filter & Approve --}}
    <div class="card mb-3">
        <div class="card-body">
            <p class="fw-bold">Filter dan Persetujuan</p>
            <form id="filter-form" method="GET" class="row g-3 mb-3 align-items-end">
                <div class="col-md-3"><label>Filter Unit</label><select name="unit_id" class="form-select"><option value="">Semua Unit</option>@foreach ($units as $unit)<option value="{{ $unit->id }}" {{ $unitId == $unit->id ? 'selected' : '' }}>{{ $unit->nama }}</option>@endforeach</select></div>
                <div class="col-md-3"><label>Filter Bulan</label><select name="bulan" class="form-select"><option value="">Semua Bulan</option>@foreach (range(1, 12) as $b)<option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}</option>@endforeach</select></div>
                <div class="col-md-3"><label>Filter Tahun</label><select name="tahun" class="form-select"><option value="">Semua Tahun</option>@for ($y = date('Y'); $y >= 2020; $y--)<option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>@endfor</select></div>
                <div class="col-md-3">
                    <label>Filter Subkategori</label>
                    <select name="subkategori_id" class="form-select">
                        <option value="">Semua Subkategori</option>
                        @foreach ($subkategori as $sub)
                            <option value="{{ $sub->id }}" {{ $subkategoriId == $sub->id ? 'selected' : '' }}>{{ $sub->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mt-3">
                    <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
                </div>
            </form>
            
            @if($unitId && $bulan && $tahun)
                @php $isApproved = isset($approvals[$unitId . '-' . $bulan . '-' . $tahun]); @endphp
                @if(!$isApproved)
                <form action="{{ route('laporan.kependudukan.approve') }}" method="POST" onsubmit="return confirm('Yakin ingin menyetujui dan mengunci data untuk periode ini?')">@csrf<input type="hidden" name="unit_id" value="{{ $unitId }}"><input type="hidden" name="bulan" value="{{ $bulan }}"><input type="hidden" name="tahun" value="{{ $tahun }}"><button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Approve Periode Ini</button></form>
                @else
                 <div class="alert alert-info d-flex justify-content-between align-items-center"><span>Periode ini telah disetujui pada {{ $approvals[$unitId . '-' . $bulan . '-' . $tahun]->approved_at->format('d M Y H:i') }}.</span><form action="{{ route('laporan.kependudukan.unapprove') }}" method="POST" onsubmit="return confirm('Yakin ingin MEMBATALKAN persetujuan?')">@csrf<input type="hidden" name="unit_id" value="{{ $unitId }}"><input type="hidden" name="bulan" value="{{ $bulan }}"><input type="hidden" name="tahun" value="{{ $tahun }}"><button type="submit" class="btn btn-danger btn-sm">Un-approve</button></form></div>
                @endif
            @endif
        </div>
    </div>

    {{-- Container untuk tabel yang akan di-refresh via AJAX --}}
    <div id="data-tersimpan-container">
        @include('admin.laporan.partials.kependudukan_table')
    </div>
</div>

{{-- Script untuk AJAX Pagination --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    $(document).on('click', '#data-tersimpan-container .pagination a', function(event) {
        event.preventDefault();
        var url = $(this).attr('href');
        
        $.ajax({
            url: url,
            success: function(data) {
                $('#data-tersimpan-container').html(data);
                window.history.pushState({ path: url }, '', url);
            },
            error: function() {
                alert('Gagal memuat data. Silakan coba lagi.');
            }
        });
    }

        // Menangani klik pada link paginasi
    $(document).on('click', '#data-tersimpan-container .pagination a', function(event) {
        event.preventDefault(); 
        var url = $(this).attr('href');
        fetchData(url);
    });

    // Menangani submit form filter
    $('#filter-form').on('submit', function(event) {
        event.preventDefault();
        var url = $(this).attr('action') + '?' + $(this).serialize();
        fetchData(url);
    });
});
</script>
@endsection