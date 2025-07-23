@extends('layout.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">Laporan Kategori Khusus (Admin)</h3>

    {{-- Notifikasi --}}
    @if (session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if (session('error'))<div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    {{-- Form Input Admin --}}
    <div class="card mb-4">
        <div class="card-header fw-bold">Input Data Atas Nama Unit</div>
        <div class="card-body">
            <form method="POST" action="{{ route('laporan.kategori-khusus.store') }}">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-4"><label class="form-label">Unit</label><select name="unit_id" class="form-select" required><option value="">-- Pilih Unit --</option>@foreach($units as $unit)<option value="{{ $unit->id }}">{{ $unit->nama }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label for="bulan_input" class="form-label">Bulan</label><select name="bulan" class="form-select" required><option value="">-- Pilih Bulan --</option>@foreach(range(1,12) as $b)<option value="{{$b}}">{{date('F',mktime(0,0,0,$b,1))}}</option>@endforeach</select></div>
                    <div class="col-md-4"><label for="tahun_input" class="form-label">Tahun</label><select name="tahun" class="form-select" required><option value="">-- Pilih Tahun --</option>@for($t=date('Y');$t>=2020;$t--)<option value="{{$t}}">{{$t}}</option>@endfor</select></div>
                    <div class="col-md-4"><label for="subkategori_id_input" class="form-label">Subkategori</label><select name="subkategori_id" id="subkategori_id_input" class="form-select" required><option value="">-- Pilih Subkategori --</option>@foreach ($subkategoris as $sub)<option value="{{ $sub->id }}">{{ $sub->nama }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label for="nama" class="form-label">Nama Pekerja</label><input type="text" name="nama" class="form-control" placeholder="Masukkan nama..." required></div>
                    <div class="col-md-4"><label for="status" class="form-label">Status</label><select name="status" class="form-select" required><option value="">-- Pilih Status --</option><option value="Pekerja Tetap">Pekerja Tetap</option><option value="PKWT">PKWT</option><option value="Honor">Honor</option><option value="OS">OS</option></select></div>
                    <div class="col-md-4" id="jenisDisabilitasGroup" style="display: none;"><label class="form-label">Jenis Disabilitas</label><select name="jenis_disabilitas" id="jenis_disabilitas_input" class="form-select"><option value="">-- Pilih Jenis --</option><option value="Fisik">Fisik</option><option value="Intelektual">Intelektual</option><option value="Sensorik">Sensorik</option><option value="Mental">Mental</option></select></div>
                    <div class="col-md-4" id="keteranganGroup" style="display: none;"><label class="form-label">Keterangan</label><input type="text" name="keterangan" class="form-control" placeholder="Masukkan keterangan..."></div>
                    <div class="col-md-12"><button type="submit" class="btn btn-primary mt-3">Simpan</button></div>
                </div>
            </form>
        </div>
    </div>
    
    <hr class="my-5">
    <h5 class="fw-bold">Data Tersimpan</h5>

    {{-- Kotak Filter --}}
    <div class="card mb-3">
        <div class="card-body">
            <p class="fw-bold">Filter Data Laporan</p>
            <form id="filter-form" method="GET" action="{{ route('laporan.kategori-khusus.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3"><label>Filter Unit</label><select name="unit_id" class="form-select"><option value="">Semua Unit</option>@foreach ($units as $unit)<option value="{{ $unit->id }}" {{ $unitId == $unit->id ? 'selected' : '' }}>{{ $unit->nama }}</option>@endforeach</select></div>
                <div class="col-md-3"><label>Filter Bulan</label><select name="bulan" class="form-select"><option value="">Semua Bulan</option>@foreach (range(1, 12) as $b)<option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}</option>@endforeach</select></div>
                <div class="col-md-3"><label>Filter Tahun</label><select name="tahun" class="form-select"><option value="">Semua Tahun</option>@for ($y = date('Y'); $y >= 2020; $y--)<option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>@endfor</select></div>
                <div class="col-md-3"><label>Filter Subkategori</label><select name="subkategori_id" id="filter_subkategori_id" class="form-select"><option value="">Semua Subkategori</option>@foreach ($subkategoris as $sub)<option value="{{ $sub->id }}" {{ $subkategoriId == $sub->id ? 'selected' : '' }}>{{ $sub->nama }}</option>@endforeach</select></div>
                <div class="col-md-3" id="filterJenisDisabilitasGroup" style="display: none;"><label>Filter Jenis Disabilitas</label><select name="jenis_disabilitas" class="form-select"><option value="">Semua Jenis</option><option value="Fisik" {{ $jenisDisabilitas == 'Fisik' ? 'selected' : '' }}>Fisik</option><option value="Intelektual" {{ $jenisDisabilitas == 'Intelektual' ? 'selected' : '' }}>Intelektual</option><option value="Sensorik" {{ $jenisDisabilitas == 'Sensorik' ? 'selected' : '' }}>Sensorik</option><option value="Mental" {{ $jenisDisabilitas == 'Mental' ? 'selected' : '' }}>Mental</option></select></div>
                <div class="col-md-3"><label>Filter Status</label><select name="status" class="form-select"><option value="">Semua Status</option><option value="Pekerja Tetap" {{$status == 'Pekerja Tetap' ? 'selected' : ''}}>Pekerja Tetap</option><option value="PKWT" {{$status == 'PKWT' ? 'selected' : ''}}>PKWT</option><option value="Honor" {{$status == 'Honor' ? 'selected' : ''}}>Honor</option><option value="OS" {{$status == 'OS' ? 'selected' : ''}}>OS</option></select></div>
                <div class="col-md-3"><label>Cari Nama</label><input type="text" name="search_name" class="form-control" placeholder="Masukkan nama..." value="{{ $searchName }}"></div>
                <div class="col-md-12 mt-3"><button type="submit" class="btn btn-primary w-100">Tampilkan</button></div>
            </form>
        </div>
    </div>

    {{-- Container untuk memuat konten AJAX --}}
    <div id="data-content-container">
        @include('admin.laporan.partials.kategori-khusus_admin_content')
    </div>
</div>

{{-- Skrip AJAX dan Dinamis --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // AJAX
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

    // Form Dinamis untuk Form Input
    const subSelectInput = document.getElementById('subkategori_id_input');
    const jenisGroupInput = document.getElementById('jenisDisabilitasGroup');
    const ketGroupInput = document.getElementById('keteranganGroup');
    const jenisSelectInput = document.getElementById('jenis_disabilitas_input');

    function toggleCreateFields() {
        if (!subSelectInput) return;
        const val = parseInt(subSelectInput.value);
        const isDisabilitas = (val === 82);
        
        jenisGroupInput.style.display = isDisabilitas ? 'block' : 'none';
        jenisSelectInput.required = isDisabilitas;
        ketGroupInput.style.display = [82, 83, 84, 85].includes(val) ? 'block' : 'none';
    }

    subSelectInput.addEventListener('change', toggleCreateFields);
    toggleCreateFields();

    // Filter Kondisional
    const filterSubSelect = document.getElementById('filter_subkategori_id');
    const filterJenisGroup = document.getElementById('filterJenisDisabilitasGroup');

    function toggleFilterFields() {
        if (!filterSubSelect) return;
        const val = parseInt(filterSubSelect.value);
        filterJenisGroup.style.display = (val === 82) ? 'block' : 'none';
    }
    
    filterSubSelect.addEventListener('change', toggleFilterFields);
    toggleFilterFields();

    // Form Dinamis untuk Modal Edit
    $(document).on('change', '.edit-subkategori', function() {
        const id = $(this).data('id');
        const val = parseInt($(this).val());
        const jenisGroup = $(`.jenis-disabilitas-group-${id}`);
        const jenisInput = $(`.jenis-disabilitas-input-${id}`);
        const ketGroup = $(`.keterangan-group-${id}`);
        
        const isDisabilitas = (val === 82);
        jenisGroup.css('display', isDisabilitas ? 'block' : 'none');
        jenisInput.prop('required', isDisabilitas);
        ketGroup.css('display', [82, 83, 84, 85].includes(val) ? 'block' : 'none');
    });
});
</script>
@endsection