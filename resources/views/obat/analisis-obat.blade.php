@extends('layout.app')

@section('title', 'Analisis Penggunaan Obat')

@section('content')
    <div class="container">
        <h4 class="my-3">📊 Analisis Penggunaan Obat</h4>

        <form method="GET" action="{{ route('obat.analisis.obat') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Dari Tanggal</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                    class="form-control">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Sampai Tanggal</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label for="obat" class="form-label">Nama Obat</label>
                <input type="text" name="obat" id="obat" value="{{ request('obat') }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label for="jenis" class="form-label">Jenis Obat</label>
                <input type="text" name="jenis" id="jenis" value="{{ request('jenis') }}" class="form-control">
            </div>

            <div class="col-md-12 d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Filter</button>
                {{-- <a href="{{ route('obat.analisis.obat.export', request()->all()) }}" class="btn btn-success">Export</a> --}}
            </div>
        </form>

        <form action="{{ route('obat.analisis.obat.export') }}" method="GET" class="d-inline">
            <input type="hidden" name="obat" value="{{ request('obat') }}">
            <input type="hidden" name="jenis" value="{{ request('jenis') }}">
            <input type="hidden" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}">
            <input type="hidden" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}">
            <button type="submit" class="btn btn-success mb-3">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </button>
        </form>



        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Nama Obat</th>
                        <th>Jenis</th>
                        <th>Tanggal</th>
                        <th>Jumlah Keluar</th>
                        {{-- <th>Total Biaya</th> --}}
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $row)
                        <tr>
                            <td>{{ $row->obat->nama_obat }}</td>
                            <td>{{ $row->obat->jenis_obat ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d M Y') }}</td>
                            <td>{{ $row->jumlah_keluar }}</td>
                            {{-- <td>Rp {{ number_format($row->jumlah_keluar * ($row->obat->harga_satuan ?? 0), 0, ',', '.') }}
                            </td> --}}
                            <td>{{ $row->unit->nama ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
