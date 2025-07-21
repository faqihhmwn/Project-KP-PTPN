@extends('layout.app')

@section('content')
    <div class="container mt-4">
        <h3 class="mb-4">Laporan Kategori Khusus</h3>

        {{-- Flash Success --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Form Tambah --}}
        <form method="POST" action="{{ route('laporan.kategori-khusus.store') }}">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="subkategori_id" class="form-label">Subkategori</label>
                    <select name="subkategori_id" id="subkategori_id" class="form-select" required>
                        <option value="">-- Pilih Subkategori --</option>
                        @foreach ($subkategoris as $sub)
                            <option value="{{ $sub->id }}" {{ old('subkategori_id') == $sub->id ? 'selected' : '' }}>
                                {{ $sub->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="nama" class="form-label">Nama Pekerja</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="Pekerja Tetap">Pekerja Tetap</option>
                        <option value="PKWT">PKWT</option>
                        <option value="Honor">Honor</option>
                        <option value="OS">OS</option>
                    </select>
                </div>

                <div class="col-md-3" id="jenisDisabilitasGroup" style="display: none;">
                    <label class="form-label">Jenis Disabilitas</label>
                    <select name="jenis_disabilitas" class="form-select">
                        <option value="">-- Pilih Jenis --</option>
                        <option value="Fisik">Fisik</option>
                        <option value="Intelektual">Intelektual</option>
                        <option value="Sensorik">Sensorik</option>
                        <option value="Mental">Mental</option>
                    </select>
                </div>

                <div class="col-md-3" id="keteranganGroup" style="display: none;">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control">
                </div>

                <div class="col-md-2">
                    <label for="bulan" class="form-label">Bulan</label>
                    <select name="bulan" class="form-select" required>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ old('bulan', now()->month) == $i ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="tahun" class="form-label">Tahun</label>
                    <input type="number" name="tahun" class="form-control" value="{{ old('tahun', now()->year) }}"
                        required>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary mt-3">Simpan</button>
                </div>
            </div>
        </form>

        {{-- Filter dan Tabel Data --}}
        <hr class="my-4">
        <h5>Data Tersimpan</h5>

        <form method="GET" class="mb-3 row g-3">
            <div class="col-md-3">
                <label for="filter" class="form-label">Filter Subkategori</label>
                <select name="filter" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($subkategoris as $sub)
                        <option value="{{ $sub->id }}" {{ request('filter') == $sub->id ? 'selected' : '' }}>
                            {{ $sub->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Bulan</label>
                <select name="bulan" class="form-select">
                    <option value="">Semua</option>
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Tahun</label>
                <select name="tahun" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($tahunList as $tahun)
                        <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                            {{ $tahun }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary">Terapkan</button>
            </div>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Subkategori</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th>Jenis Disabilitas</th>
                    <th>Keterangan</th>
                    <th>Bulan</th>
                    <th>Tahun</th>
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
                        <td>{{ $item->jenis_disabilitas ?? '-' }}</td>
                        <td>{{ $item->keterangan ?? '-' }}</td>
                        <td>{{ $item->bulan }}</td>
                        <td>{{ $item->tahun }}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                data-bs-target="#editModal{{ $item->id }}">Edit</button>
                        </td>
                    </tr>


                    {{-- Fitur Modal --}}
                    @include('laporan.modal.modal-kategori-khusus', [
                        'item' => $item,
                        'subkategoris' => $subkategoris,
                    ])


                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Script Dinamis --}}
    <script>
        const subSelect = document.getElementById('subkategori_id');
        const jenisGroup = document.getElementById('jenisDisabilitasGroup');
        const ketGroup = document.getElementById('keteranganGroup');

        function toggleCreateFields() {
            const val = parseInt(subSelect.value);
            jenisGroup.style.display = (val === 82) ? 'block' : 'none';
            ketGroup.style.display = [82, 83, 84, 85].includes(val) ? 'block' : 'none';
        }

        subSelect.addEventListener('change', toggleCreateFields);
        window.addEventListener('DOMContentLoaded', toggleCreateFields);

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.edit-subkategori').forEach(select => {
                const id = select.dataset.id;
                const jenisGroup = document.querySelector(`.jenis-disabilitas-group-${id}`);
                const ketGroup = document.querySelector(`.keterangan-group-${id}`);

                function toggleEditFields() {
                    const val = parseInt(select.value);
                    jenisGroup.style.display = (val === 82) ? 'block' : 'none';
                    ketGroup.style.display = [82, 83, 84, 85].includes(val) ? 'block' : 'none';
                }

                toggleEditFields(); // initial
                select.addEventListener('change', toggleEditFields);
            });
        });
    </script>
@endsection
