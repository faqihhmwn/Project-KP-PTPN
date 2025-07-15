@extends('layout.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">Rekapitulasi Biaya Kesehatan - PTPN I Regional </h3>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Form Edit Jumlah -->
    @isset($editItem)
        <div class="card mb-4">
            <div class="card-header">Edit Jumlah untuk Kategori Biaya: <strong>{{ $editItem->subkategori->nama }}</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('rekap.regional.update', $editItem->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="jumlah" class="form-label">Jumlah</label>
                        <input type="number" name="jumlah" class="form-control" value="{{ $editItem->jumlah }}" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('rekap.regional.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    @endisset

    <!-- Form Pilih Tahun dan Bulan -->
    <!-- Form Pilih Tahun dulu -->
    <form method="GET" action="{{ route('rekap.regional.index') }}">
        {{-- @csrf --}}
        <div class="row mb-3">
            <div class="col-md-3">
                {{-- @if ($selectedTahun && $selectedBulan) --}}
                    {{-- tampilkan form input --}}
                {{-- @else --}}
                    <div class="alert alert-info">
                        Silakan pilih Tahun terlebih dahulu untuk mengisi rekap biaya.
                    </div>
                {{-- @endif --}}

                <label for="tahun" class="form-label">Tahun</label>
                <select name="tahun" id="tahun" class="form-select" required>
                    <option value="">-- Pilih Tahun --</option>
                    @for ($tahun = date('Y'); $tahun >= 2000; $tahun--)
                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                    @endfor
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
            </div>

    @if ($selectedTahun)
        <form method="POST" action="{{ route('rekap.regional.store') }}">
            @csrf
            <input type="hidden" name="tahun" value="{{ $selectedTahun }}">

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <select name="bulan" id="bulan" class="form-select" required>
                        <option value="">-- Pilih Bulan --</option>
                        @foreach ($bulan as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        {{-- </form> --}}

    <!-- Form Input Jumlah (Setelah Tahun & Bulan Dipilih) -->
    {{-- @if ($selectedTahun && $selectedBulan)
        <form method="POST" action="{{ route('rekap.regional.store') }}">
            @csrf 
            <input type="hidden" name="tahun" value="{{ $selectedTahun }}">
            <input type="hidden" name="bulan_id" value="{{ $selectedBulan }}"> --}}

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Kategori Biaya</th>
                        <th>Jumlah Rp.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kategori as $k)
                        <tr>
                            <td>{{ $k->nama }}</td>
                            <td><input type="number" name="jumlah[{{ $k->id }}]" class="form-control" min="0" required>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <button type="submit" class="btn btn-primary w-auto">Simpan</button>
        </form>
    @endif

    <!-- Tabel Data Tersimpan -->
    <hr class="my-5">
    <h5>Data Tersimpan</h5>
    <table class="table table-striped table-bordered text-nowrap">
        <thead>
            <tr>
                <th rowspan="2" class="text-center align-middle">Rekap Bulan</th>
                <th colspan="9" class="group-header text-center">REAL BIAYA</th>
                <th rowspan="2" class="align-middle">Transport</th>
                <th rowspan="2" class="align-middle"> Jml. Biaya Hiperkes</th>
                <th rowspan="2" class="bg-warning text-dark text-center align-middle">TOTAL BIAYA KESEHATAN</th>
                <th rowspan="2" class="bg-green text-dark text-center align-middle">VALIDASI</th>
                <th rowspan="2" class="text-center align-middle">Aksi</th>
            </tr>
            <tr>
                <th>Gol. III-IV</th>
                <th>Gol. I-II</th>
                <th>Kampanye</th>
                <th>Honor + ILA + OS</th>
                <th>Pens. III-IV</th>
                <th>Pens. I-II</th>
                <th>Direksi</th>
                <th>Dekom</th>
                <th>Pengacara</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($grouped as $row)
                <tr>
                    <td>{{ $row['bulan'] }} {{ $row['tahun'] }}</td>
                    <td>{{ number_format($row['kategori'][1] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($row['kategori'][2] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($row['kategori'][3] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($row['kategori'][4] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($row['kategori'][5] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($row['kategori'][6] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($row['kategori'][7] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($row['kategori'][8] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($row['kategori'][9] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($row['kategori'][10] ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($row['kategori'][11] ?? 0, 0, ',', '.') }}</td>
                    <td class="bg-warning fw-bold">{{ number_format(collect($row['kategori'])->sum(), 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row['validasi'] ?? '-' }}</td>
                    <td>
                        {{-- Tombol Edit --}}
                        @if (empty($row['validasi']))
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $row['id'] }}">
                                Edit
                            </button>

                            {{-- Tombol Validasi --}}
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#validasiModal{{ $row['id'] }}">
                                Validasi
                            </button>
                        {{-- <a href="#" class="btn btn-sm btn-warning">Edit</a> --}}
                        {{-- <form action="{{ route('rekap.regional.destroy', $row['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')"> --}}
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                        @else
                            <span class="badge bg-success">Tervalidasi</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            
            @foreach ($grouped as $row)
                @if (empty($row['validasi']))
                <!-- Modal Edit -->
                <div class="modal fade" id="editModal{{ $row['id'] }}" tabindex="-1" aria-labelledby="editModalLabel{{ $row['id'] }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('rekap.regional.update', $row['id']) }}">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel{{ $row['id'] }}">Edit Data - {{ $row['bulan'] }} {{ $row['tahun'] }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    @foreach ($kategori as $k)
                                        <div class="mb-3">
                                            <label class="form-label">{{ $k->nama }}</label>
                                            <input type="number" name="jumlah[{{ $k->id }}]" class="form-control" min="0"
                                                value="{{ $row['kategori'][$k->id] ?? 0 }}" required>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach

            @foreach ($grouped as $row)
                @if (empty($row['validasi']))
                <!-- Modal Validasi -->
                <div class="modal fade" id="validasiModal{{ $row['id'] }}" tabindex="-1" aria-labelledby="validasiModalLabel{{ $row['id'] }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('rekap.regional.validate', $row['id']) }}">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="validasiModalLabel{{ $row['id'] }}">Konfirmasi Validasi</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    Apakah Anda yakin data untuk <strong>{{ $row['bulan'] }} {{ $row['tahun'] }}</strong> sudah selesai diisi dan ingin menyimpannya secara permanen?
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Ya, Validasi</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach


        </tbody>
    </table>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inputs = document.querySelectorAll('input[name^="jumlah"]');

            inputs.forEach(input => {
                input.addEventListener('input', function (e) {
                    let value = this.value.replace(/\./g, '');
                    if (!isNaN(value) && value !== '') {
                        this.value = parseInt(value).toLocaleString('id-ID');
                    } else {
                        this.value = '';
                    }
                });

                // Saat submit, hilangkan format agar tidak kacau
                input.form.addEventListener('submit', function () {
                    inputs.forEach(i => {
                        i.value = i.value.replace(/\./g, '');
                    });
                });
            });
        });
    </script>
@endpush

@endsection
