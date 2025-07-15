@extends('layout.app')

@section('content')
    <div class="container mt-4">
        <h3 class="mb-4">Laporan Kependudukan</h3>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Form Edit Jumlah -->
        @isset($editItem)
            <div class="card mb-4">
                <div class="card-header">Edit Jumlah untuk Subkategori: <strong>{{ $editItem->subkategori->nama }}</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('laporan.kependudukan.update', $editItem->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control" value="{{ $editItem->jumlah }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('laporan.kependudukan.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        @endisset


        <!-- Form Input Laporan -->
        <form method="POST" action="{{ route('laporan.kependudukan.store') }}">
            @csrf
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <select name="bulan" id="bulan" class="form-select" required>
                        <option value="">-- Pilih Bulan --</option>
                        @foreach (range(1, 12) as $b)
                            <option value="{{ $b }}">{{ DateTime::createFromFormat('!m', $b)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <select name="tahun" id="tahun" class="form-select" required>
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
                                <input type="number" name="jumlah[{{ $sub->id }}]" class="form-control"
                                    min="0" required>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>


        <!-- Tabel Data yang Sudah Diinputkan -->
        <hr class="my-5">
        <h5>Data Tersimpan</h5>

        {{-- Fitur Search --}}
        <form method="GET" action="{{ route('laporan.kependudukan.index') }}" class="row g-3 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari Subkategori"
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
        </form>


        {{-- Fitur Filter --}}
        <form method="GET" action="{{ route('laporan.kependudukan.index') }}" class="row g-2 mb-3 align-items-end">

            <!-- Filter Bulan -->
            <div class="col-md-3">
                <label for="bulan" class="form-label">Filter Bulan</label>
                <select name="bulan" class="form-select">
                    <option value="">-- Filter Bulan --</option>
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tahun -->
            <div class="col-md-3">
                <label for="tahun" class="form-label">Filter Tahun</label>
                <select name="tahun" class="form-select">
                    <option value="">-- Filter Tahun --</option>
                    @foreach (range(now()->year, 2020) as $y)
                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>
                            {{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Button -->
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>


        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Subkategori</th>
                    <th>Jumlah</th>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>Unit</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $i => $row)
                    @include('laporan.modal.modal-kependudukan')
                    <tr>
                        <td>{{ $data->firstItem() + $i }}</td>
                        <td>{{ $row->subkategori->nama }}</td>
                        <td>{{ $row->jumlah }}</td>
                        <td>{{ DateTime::createFromFormat('!m', $row->bulan)->format('F') }}</td>
                        <td>{{ $row->tahun }}</td>
                        <td>{{ $row->unit->nama }}</td>
                        <td>
                            <a href="{{ route('laporan.kependudukan.edit', $row->id) }}" class="btn btn-sm btn-warning"
                                data-bs-toggle="modal" data-bs-target="#editModal{{ $row->id }}">
                                Edit
                            </a>
                            {{-- <form action="{{ route('laporan.kependudukan.destroy', $row->id) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form> --}}
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-center mt-3">
            {{ $data->links('pagination::bootstrap-5') }}
        </div>

    </div>
@endsection
