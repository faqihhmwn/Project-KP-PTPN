@extends('layout.app')

@section('content')
    <div class="container mt-4">
        <h3 class="mb-4">Laporan Penyakit Kronis</h3>

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
                    <form method="POST" action="{{ route('laporan.penyakit-kronis.update', $editItem->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control" value="{{ $editItem->jumlah }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('laporan.penyakit-kronis.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        @endisset
        
        <!-- Form Input Laporan -->
        <form method="POST" action="{{ route('laporan.penyakit-kronis.store') }}">
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
                @include('laporan.modal-laporan')
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row->subkategori->nama }}</td>
                        <td>{{ $row->jumlah }}</td>
                        <td>{{ DateTime::createFromFormat('!m', $row->bulan)->format('F') }}</td>
                        <td>{{ $row->tahun }}</td>
                        <td>{{ $row->unit->nama }}</td>
                        <td>
                            <a href="{{ route('laporan.penyakit-kronis.edit', $row->id) }}" class="btn btn-sm btn-warning"
                                data-bs-toggle="modal" data-bs-target="#editModal{{ $row->id }}">
                                Edit
                            </a>
                            <form action="{{ route('laporan.penyakit-kronis.destroy', $row->id) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
