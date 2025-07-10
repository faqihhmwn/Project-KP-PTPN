@extends('layout.app')

@section('content')
<div class="container mt-4">
    <h4>Filter Rekap Biaya Kesehatan</h4>
    <form action="{{ route('rekap.show') }}" method="GET">
    <div class="row mb-3">
        <div class="col-md-4">
        <label for="tahun" class="form-label">Tahun</label>
        <select name="tahun" id="tahun" class="form-select" required>
            <option value="">-- Pilih Tahun --</option>
            @foreach($tahunList as $tahun)
            <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                {{ $tahun }}
            </option>
            @endforeach
            </select>
            </div>
            
            <div class="col-md-4">
                <label for="unit" class="form-label">Unit</label>
            <select name="unit" id="unit" class="form-select" required>
            <option value="">-- Pilih Unit --</option>
            @foreach($units as $unit)
            <option value="{{ $unit }}">{{ $unit }}</option>
            @endforeach
        </select>
    </div>
</div>

    <button type="submit" class="btn btn-primary">Tampilkan Rekap</button>
</form>
</div>
@endsection
