@extends('layout.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">Laporan Kategori Khusus</h3>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Form Input -->
    <form method="POST" action="{{ route('laporan.kategori-khusus.store') }}">
        @csrf

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="subkategori_id" class="form-label">Subkategori</label>
                <select name="subkategori_id" id="subkategori_id" class="form-select" required>
                    <option value="">-- Pilih Subkategori --</option>
                    @foreach ($subkategoris as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="nama" class="form-label">Nama Pekerja</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <input type="text" name="status" class="form-control" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>

    <!-- Tabel Data -->
    <hr class="my-4">
    <h5>Data Tersimpan</h5>

    <form method="GET" class="mb-3">
        <label for="filter" class="form-label">Filter Subkategori</label>
        <select name="filter" class="form-select w-25 d-inline" onchange="this.form.submit()">
            <option value="">Semua</option>
            @foreach ($subkategoris as $sub)
                <option value="{{ $sub->id }}" {{ request('filter') == $sub->id ? 'selected' : '' }}>
                    {{ $sub->nama }}
                </option>
            @endforeach
        </select>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Subkategori</th>
                <th>Nama</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->subkategori->nama }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>{{ $item->status }}</td>
                    <td>
                        <!-- Edit dan Hapus -->
                        <a href="{{ route('laporan.kategori-khusus.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('laporan.kategori-khusus.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Belum ada data</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
