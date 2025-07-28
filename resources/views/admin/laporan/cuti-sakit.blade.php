@extends('layout.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">Laporan Cuti Sakit (Admin)</h3>

    {{-- Notifikasi --}}
    @if (session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if (session('error'))<div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    {{-- Form Input Admin --}}
    <div class="card mb-4">
        <div class="card-header fw-bold">Input Data Atas Nama Unit</div>
        <div class="card-body">
            <form method="POST" action="{{ route('laporan.cuti-sakit.store') }}">
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
                            <option value="">-- Pilih Bulan --</option>
                            @foreach (range(1, 12) as $b)
                                <option value="{{ $b }}">{{ DateTime::createFromFormat('!m', $b)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="tahun_input" class="form-label">Tahun</label>
                        <select name="tahun" id="tahun_input" class="form-select" required>
                            <option value="">-- Pilih Tahun --</option>
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
                            <td>
                                {{-- ==== PERUBAHAN INPUT FIELD JUMLAH ==== --}}
                                <input type="number" name="jumlah[{{ $sub->id }}]" class="form-control" min="0" value="" placeholder="Masukkan jumlah..." required>
                            </td>
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

    {{-- Fitur Filter --}}
    <div class="card mb-3">
        <div class="card-body">
            <p class="fw-bold">Filter Data Laporan</p>
            <form id="filter-form" method="GET" action="{{ route('laporan.cuti-sakit.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3"><label>Filter Unit</label><select name="unit_id" class="form-select"><option value="">Semua Unit</option>@foreach ($units as $unit)<option value="{{ $unit->id }}" {{ $unitId == $unit->id ? 'selected' : '' }}>{{ $unit->nama }}</option>@endforeach</select></div>
                <div class="col-md-3"><label>Filter Bulan</label><select name="bulan" class="form-select"><option value="">Semua Bulan</option>@foreach (range(1, 12) as $b)<option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}</option>@endforeach</select></div>
                <div class="col-md-3"><label>Filter Tahun</label><select name="tahun" class="form-select"><option value="">Semua Tahun</option>@for ($y = date('Y'); $y >= 2020; $y--)<option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>@endfor</select></div>
                <div class="col-md-3"><label>Filter Subkategori</label><select name="subkategori_id" class="form-select"><option value="">Semua Subkategori</option>@foreach ($subkategori as $sub)<option value="{{ $sub->id }}" {{ $subkategoriId == $sub->id ? 'selected' : '' }}>{{ $sub->nama }}</option>@endforeach</select></div>
                <div class="col-md-12 mt-3"><button type="submit" class="btn btn-primary w-100">Tampilkan</button></div>
            </form>
        </div>
    </div>

    {{-- Container untuk memuat tombol approve DAN tabel data via AJAX --}}
    <div id="data-content-container">
        @include('admin.laporan.partials.cuti-sakit_admin_content')
    </div>
</div>

{{-- Skrip AJAX untuk Filter dan Paginasi --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    function fetchData(url) {
        $.ajax({
            url: url,
            success: function(data) {
                // Target container yang berisi tombol approve dan tabel
                $('#data-content-container').html(data);
                // Perbarui URL di browser tanpa reload
                window.history.pushState({ path: url }, '', url);
            },
            error: function() {
                alert('Gagal memuat data. Silakan coba lagi.');
            }
        });
    }

    // Menangani klik pada link paginasi
    $(document).on('click', '#data-content-container .pagination a', function(event) {
        event.preventDefault(); 
        fetchData($(this).attr('href'));
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