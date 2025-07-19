@extends('layout.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">Rekapitulasi Biaya Pemakaian Dana Kapitasi</h3>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @isset ($editItem)
    {{-- Blok editItem belum digunakan --}}
    @endisset

        <form method="GET" action="{{ route('rekap.kapitasi.index') }}">
        <div class="row mb-3">
            <div class="col-md-3">
                @if (!$selectedTahun)
                <div class="alert alert-info">
                    Silakan pilih tahun terlebih dahulu untuk melihat rekapitulasi biaya pemakaian dana kapitasi.
                </div>
                @endif

                <label for="tahun" class="form-label">Pilih Tahun</label>
                <select name="tahun" id="tahun" class="form-select" {{  $selectedTahun ? 'disabled': '' }} required>
                    <option value="">-- Pilih Tahun --</option>
                    @foreach ($tahun as $t)
                        <option value="{{ $t }}" {{ $selectedTahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select> 
            </div>

            {{-- Jika tahun belum dipilih, tampilkan tombol --}}
            @unless ($selectedTahun)
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Tampilkan</button>
                </div>
            @endunless
            {{-- Jika tahun sudah dipilih, tambahkan tombol Reset --}}
            @if ($selectedTahun)
                <div class="col-md-3 d-flex align-items-end">
                    <a href="{{ route('rekap.kapitasi.index') }}" class="btn btn-secondary">Pilih Tahun Lain</a>
                </div>
            @endif
        </div>
    </form>

    @if ($selectedTahun)
        <form method="POST" action="{{ route('rekap.kapitasi.store') }}" id="mainRekapForm">
            @csrf
            <input type="hidden" name="tahun" value="{{ $selectedTahun }}">

            <div class="row-mb-3">
            <div class="col-md-3">
                <label for="bulan" class="form-label">Bulan</label>
                <select name="bulan_id" id="bulan" class="form-select" required>
                    <option value="">-- Pilih Bulan --</option>
                    @foreach ($bulan as $b)
                        <option value="{{ $b->id }}" {{ $selectedBulan == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif

        {{-- Input Total Dana Masuk --}}
        <div class="mb-4">
            <label for="total_dana_masuk" class="form-label fw-bold">Total Dana Masuk (Rp)</label>
            <input type="text"
                name="total_dana_masuk"
                id="total_dana_masuk"
                class="form-control rupiah-input"
                required
                pattern="[0-9]*"
                min="0"
                value="{{ old('total_dana_masuk') }}">
        </div>


        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Kategori Dana Kapitasi</th>
                    <th>Jumlah Rp.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kategori as $k)
                    <tr>
                        <td>{{ $k->nama }}</td>
                        <td>
                            <input type="text" name="total_biaya_kapitasi[{{ $k->id }}]" class="form-control rupiah-input" min="0" pattern="[0-9]*" required>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary w-auto">Simpan</button>
    </form>

    <hr class="my-5">
    <h5>Data Tersimpan Tahun {{ $selectedTahun }}</h5>
    <div class="table-responsive">
        <table class="table table-striped table-bordered text-nowrap">
            <thead>
                <tr>
                    <th rowspan="2" class="text-center align-middle">Bulan</th>
                    <th rowspan="2" class="text-center align-middle">DANA MASUK</th>
                    <th colspan="{{ $kategori->count() }}" class="group-header text-center">PEMBAYARAN</th>
                    <th rowspan="2" class="bg-warning text-dark text-center align-middle">Total Pembayaran Menggunakan Biaya Kapitasi</th>
                    <th rowspan="2" class="bg-green text-dark text-center align-middle">VALIDASI</th>
                    <th rowspan="2" class="text-center align-middle">Aksi</th>
                </tr>
                <tr>
                    @foreach ($kategori as $k)
                        <th>{{ $k->nama }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($grouped as $row)
                        <tr>
                            <td>{{ $row['bulan'] }} {{ $row['tahun'] }}</td>
                            <td class="bg-primary text-white text-end">{{ number_format($row['total_dana_masuk'] ?? 0, 0, ',', '.') }}</td>
                            @foreach ($kategori as $k)
                                <td>{{ number_format($row['kategori'][$k->id] ?? 0, 0, ',', '.') }}</td>
                            @endforeach
                            <td class="bg-warning fw-bold">{{ number_format($row['total_biaya_kapitasi'] ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $row['validasi'] ?? '-' }}</td>
                            <td>
                                @if (empty($row['validasi']))
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $row['bulan_id'] }}{{ $row['tahun'] }}">
                                        Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#validasiModal{{ $row['bulan_id'] }}{{ $row['tahun'] }}">
                                        Validasi
                                    </button>
                                    <form action="{{ route('rekap.kapitasi.destroy', ['tahun' => $row['tahun'], 'bulan_id' => $row['bulan_id']]) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus semua data untuk bulan ini?')">
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

                {{-- Baris: TOTAL 1 TAHUN --}}
                <tr>
                    <td class="fw-bold">TOTAL BIAYA</td>
                    {{-- Total Dana Masuk --}}
                    <td class="bg-primary text-white fw-bold text-end">
                        {{ number_format($annualTotals['total_dana_masuk'] ?? 0, 0, ',', '.') }}
                    </td>
                    @foreach ($kategori as $k)
                        <td class="fw-bold">{{ number_format($annualTotals[$k->id] ?? 0, 0, ',', '.') }}</td>
                    @endforeach
                        <td class="bg-warning fw-bold">{{ number_format($annualTotals['all_kategoris_total'] ?? 0, 0, ',', '.') }}</td>
                    <td></td> {{-- Kolom Validasi --}}
                    <td></td> {{-- Kolom Aksi --}}
                </tr>
            </tbody>
        </table>
    </div>

        {{-- Modals for Edit and Validate (Outside the table loop for better structure) --}}
        @foreach ($grouped as $row)
            @if (empty($row['validasi']))
                <div class="modal fade" id="editModal{{ $row['bulan_id'] }}{{ $row['tahun'] }}" tabindex="-1" aria-labelledby="editModalLabel{{ $row['bulan_id'] }}{{ $row['tahun'] }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            {{-- Action for update should target the correct route with bulan_id and tahun --}}
                            <form method="POST" action="{{ route('rekap.kapitasi.update', ['tahun' => $row['tahun'], 'bulan_id' => $row['bulan_id']]) }}">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel{{ $row['bulan_id'] }}{{ $row['tahun'] }}">Edit Data - {{ $row['bulan'] }} {{ $row['tahun'] }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Dana Masuk</label>
                                        <input type="text" name="total_dana_masuk" class="form-control rupiah-input" min="0" pattern="[0-9]*" 
                                            value="{{ number_format($row['total_dana_masuk'] ?? 0, 0, ',', '.') }}" required>
                                    </div>
                                    @foreach ($kategori as $k)
                                        <div class="mb-3">
                                            <label class="form-label">{{ $k->nama }}</label>
                                            <input type="text" name="total_biaya_kapitasi[{{ $k->id }}]" class="form-control rupiah-input" min="0" pattern="[0-9]*" value="{{ $row['kategori'][$k->id] ?? 0 }}" required>
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

            {{-- Modal Validasi --}}
            {{-- Modalnya tetap di dalam loop grouped agar ID modal unik --}}
            @if (empty($row['validasi']))
                <div class="modal fade" id="validasiModal{{ $row['bulan_id'] }}{{ $row['tahun'] }}" tabindex="-1" aria-labelledby="validasiModalLabel{{ $row['bulan_id'] }}{{ $row['tahun'] }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('rekap.kapitasi.validate', ['tahun' => $row['tahun'], 'bulan_id' => $row['bulan_id']]) }}">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="validasiModalLabel{{ $row['bulan_id'] }}{{ $row['tahun'] }}">Konfirmasi Validasi</h5>
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
    </div>
@endsection



