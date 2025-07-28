@extends('layout.app')

@section('content')
<div class="container mt-4">
    {{-- PERUBAHAN JUDUL HALAMAN --}}
    <h3 class="mb-4">Laporan Kependudukan (Unit {{ $authUser->unit->nama ?? '' }})</h3>

    {{-- Notifikasi --}}
    @if (session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if (session('error'))<div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    {{-- Form Input User --}}
    <div class="card mb-4">
        <div class="card-header fw-bold">Input Laporan Bulanan</div>
        <div class="card-body">
            <form method="POST" action="{{ route('laporan.kependudukan.store') }}">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" value="{{ $authUser->unit->nama }}" disabled>
                    </div>
                    <div class="col-md-3"><label for="bulan" class="form-label">Bulan</label><select name="bulan" id="bulan" class="form-select" required><option value="">-- Pilih Bulan --</option>@foreach (range(1, 12) as $b)<option value="{{ $b }}">{{ DateTime::createFromFormat('!m', $b)->format('F') }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label for="tahun" class="form-label">Tahun</label><select name="tahun" id="tahun" class="form-select" required><option value="">-- Pilih Tahun --</option>@for ($t = date('Y'); $t >= 2020; $t--)<option value="{{ $t }}">{{ $t }}</option>@endfor</select></div>
                </div>

                <fieldset id="input-form-content">
                    <div id="data-exists-alert" class="alert alert-warning" style="display: none;">Data untuk periode ini sudah ada. Silahkan gunakan fitur "Edit" untuk mengubahnya.</div>
                    <div id="approval-status-alert" class="alert alert-danger" style="display: none;">Data untuk periode ini sudah disetujui dan tidak dapat diubah.</div>
                    <table class="table table-bordered">
                        <thead><tr><th>Subkategori</th><th>Jumlah</th></tr></thead>
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
                    <button type="submit" class="btn btn-primary">Simpan Data</button>
                </fieldset>
            </form>
        </div>
    </div>

    <hr class="my-5">
    <h5 class="fw-bold">Data Tersimpan</h5>

    {{-- Fitur Filter --}}
    <div class="card mb-3">
        <div class="card-body">
            <p class="fw-bold">Filter Data Laporan</p>
            <form id="filter-form" method="GET" action="{{ route('laporan.kependudukan.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3"><label>Unit</label><input type="text" class="form-control" value="{{ $authUser->unit->nama }}" disabled></div>
                <div class="col-md-3"><label>Filter Bulan</label><select name="bulan" class="form-select"><option value="">Semua Bulan</option>@foreach (range(1, 12) as $b)<option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}</option>@endforeach</select></div>
                <div class="col-md-3"><label>Filter Tahun</label><select name="tahun" class="form-select"><option value="">Semua Tahun</option>@for ($y = date('Y'); $y >= 2020; $y--)<option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>@endfor</select></div>
                <div class="col-md-3"><label>Filter Subkategori</label><select name="subkategori_id" class="form-select"><option value="">Semua Subkategori</option>@foreach ($subkategori as $sub)<option value="{{ $sub->id }}" {{ $subkategoriId == $sub->id ? 'selected' : '' }}>{{ $sub->nama }}</option>@endforeach</select></div>
                <div class="col-md-12 mt-3"><button type="submit" class="btn btn-primary w-100">Tampilkan</button></div>
            </form>
        </div>
    </div>

    {{-- Container untuk memuat tabel data via AJAX --}}
    <div id="data-content-container">
        @include('laporan.partials.kependudukan_table')
    </div>
</div>

{{-- Skrip AJAX dan Dinamis --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // AJAX untuk Filter dan Paginasi
    function fetchData(url) {
        $.ajax({
            url: url,
            success: function(data) {
                $('#data-content-container').html(data);
                window.history.pushState({ path: url }, '', url);
            },
            error: function() { alert('Gagal memuat data.'); }
        });
    }

    $(document).on('click', '#data-content-container .pagination a', function(event) {
        event.preventDefault(); 
        fetchData($(this).attr('href'));
    });

    $('#filter-form').on('submit', function(event) {
        event.preventDefault();
        var url = $(this).attr('action') + '?' + $(this).serialize();
        fetchData(url);
    });

    // Logika untuk menonaktifkan form input jika periode sudah diapprove
    const bulanSelect = document.getElementById('bulan');
    const tahunSelect = document.getElementById('tahun');
    const formContent = document.getElementById('input-form-content'); 
    const statusAlert = document.getElementById('approval-status-alert');
    const existsAlert = document.getElementById('data-exists-alert');
    const approvals = @json($approvals);
    const existingData = @json($existingData);
    const unitId = {{ $authUser->unit_id }};

    function checkPeriodStatus() {
        if (!bulanSelect.value || !tahunSelect.value) {
            formContent.disabled = false;
            statusAlert.style.display = 'none';
            existsAlert.style.display = 'none';
            return;
        }

        const key = `${unitId}-${bulanSelect.value}-${tahunSelect.value}`;
        
        if (approvals[key]) {
            formContent.disabled = true;
            statusAlert.style.display = 'block';
            existsAlert.style.display = 'none';
        } else if (existingData[key]) {
            formContent.disabled = true;
            existsAlert.style.display = 'block';
            statusAlert.style.display = 'none';
        } else {
            formContent.disabled = false;
            statusAlert.style.display = 'none';
            existsAlert.style.display = 'none';
        }
    }

    if(bulanSelect && tahunSelect) {
        bulanSelect.addEventListener('change', checkPeriodStatus);
        tahunSelect.addEventListener('change', checkPeriodStatus);
        checkPeriodStatus();
    }
});
</script>
@endsection