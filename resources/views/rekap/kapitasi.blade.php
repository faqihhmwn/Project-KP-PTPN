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

        <form method="GET" action="{{ route('rekap.kapitasi.index') }}">
            <div class="row mb-3">
                <div class="col-md-3">
                    @if (!$selectedTahun)
                        <div class="alert alert-info">
                            Silakan pilih tahun terlebih dahulu untuk melihat rekapitulasi biaya pemakaian dana kapitasi.
                        </div>
                    @endif

                    <label for="tahun" class="form-label">Pilih Tahun</label>
                    <select name="tahun" id="tahun" class="form-select" {{ $selectedTahun ? 'disabled' : '' }} required>
                        <option value="">-- Pilih Tahun --</option>
                        @foreach ($tahun as $t)
                            <option value="{{ $t }}" {{ $selectedTahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Jika tahun belum dipilih, tampilkan tombol --}}
                @unless ($selectedTahun)
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary"> <i class="fas fa-filter"></i> Tampilkan</button>
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

        {{-- START: Kondisi ini membungkus semua konten di bawahnya jika tahun sudah dipilih --}}
        @if ($selectedTahun)
                <div class="row mb-4">
                    {{-- CARD: Sisa Saldo Awal Tahun --}}
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-body">
                                <div class="card-title text-primary">Sisa Saldo Awal Tahun</div>
                                <div class="saldo-info">
                                    <p class="card-text h4">{{ number_format($saldoAwalTahun ?? 0, 0, ',', '.') }}</p>
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#editSaldoAwalModal">
                                        Edit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>


                    {{-- CARD: Sisa Saldo Saat Ini --}}
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-body">
                                <div class="card-title text-success">Sisa Saldo Saat Ini</div>
                                <div class="saldo-info">
                                    <p class="card-text h4">{{ number_format($saldoSaatIni ?? 0, 0, ',', '.') }}</p>
                                    <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#rincianSaldoModal">
                                        Lihat Rincian
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


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

                    {{-- Input Total Dana Masuk --}}
                    <div class="mb-4">
                        <label for="total_dana_masuk" class="form-label fw-bold">Total Dana Masuk (Rp)</label>
                        <input type="text" name="total_dana_masuk" id="total_dana_masuk" class="form-control rupiah-input" required
                            min="0" value="{{ old('total_dana_masuk') }}">
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
                                        <input type="text" name="total_biaya_kapitasi[{{ $k->id }}]" class="form-control rupiah-input" required>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary w-auto"> <i class="fas fa-save"></i> Simpan</button>
                </form>

                <hr class="my-5">
                <h5>Data Tersimpan Tahun {{ $selectedTahun }}</h5>
                <form action="{{ route('rekap.kapitasi.kapitasi.export', ['tahun' => $selectedTahun]) }}" method="GET" class="mb-3">
                    <input type="hidden" name="tahun" value="{{ $selectedTahun }}">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-nowrap">
                        <thead>
                            <tr>
                                <th rowspan="2" class="text-center align-middle">Bulan</th>
                                <th rowspan="2" class="bg-info fw-bold text-center align-middle">DANA MASUK</th>
                                <th colspan="{{ $kategori->count() }}" class="group-header text-center">PEMBAYARAN</th>
                                <th rowspan="2" class="bg-warning fw-bold text-center align-middle">Total Pembayaran Menggunakan
                                    Biaya Kapitasi</th>
                                <th rowspan="2" class="bg-green fw-bold text-center align-middle">VALIDASI</th>
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
                                    <td class="bg-primary text-white text-end">
                                        {{ number_format($row['total_dana_masuk'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    @foreach ($kategori as $k)
                                        <td>{{ number_format($row['kategori'][$k->id] ?? 0, 0, ',', '.') }}</td>
                                    @endforeach
                                    <td class="bg-warning fw-bold">{{ number_format($row['total_biaya_kapitasi'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">{{ $row['validasi'] ?? '-' }}</td>
                                    <td>
                                        @if (empty($row['validasi']))
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#editModal{{ $row['bulan_id'] }}{{ $row['tahun'] }}"><i class="fas fa-edit"></i>
                                                Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                data-bs-target="#validasiModal{{ $row['bulan_id'] }}{{ $row['tahun'] }}"> <i class="fas fa-lock"></i>
                                                Validasi
                                            </button>
                                            <form action="{{ route('rekap.kapitasi.destroy', ['tahun' => $row['tahun'], 'bulan_id' => $row['bulan_id']]) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus semua data untuk bulan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus </button>
                                            </form>
                                        @else
                                            <span class="badge bg-success">Tervalidasi</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Baris: TOTAL 1 TAHUN --}}
                            <tr>
                                <td class="bg-warning fw-bold">TOTAL BIAYA</td>
                                {{-- Total Dana Masuk --}}
                                <td class="bg-info fw-bold text-end">
                                    {{ number_format($annualTotals['total_dana_masuk'] ?? 0, 0, ',', '.') }}
                                </td>
                                @foreach ($kategori as $k)
                                    <td class="bg-warning fw-bold">{{ number_format($annualTotals[$k->id] ?? 0, 0, ',', '.') }}</td>
                                @endforeach
                                <td class="bg-warning fw-bold">
                                    {{ number_format($annualTotals['all_kategoris_total'] ?? 0, 0, ',', '.') }}
                                </td>
                                <td></td> {{-- Kolom Validasi --}}
                                <td></td> {{-- Kolom Aksi --}}
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Modals for Edit and Validate (Outside the table loop for better structure) --}}
                @foreach ($grouped as $row)
                    @if (empty($row['validasi']))
                        <div class="modal fade" id="editModal{{ $row['bulan_id'] }}{{ $row['tahun'] }}" tabindex="-1"
                            aria-labelledby="editModalLabel{{ $row['bulan_id'] }}{{ $row['tahun'] }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    {{-- Action for update should target the correct route with bulan_id and tahun --}}
                                    <form method="POST"
                                        action="{{ route('rekap.kapitasi.update', ['tahun' => $row['tahun'], 'bulan_id' => $row['bulan_id']]) }}"
                                        id="formModalEdit">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel{{ $row['bulan_id'] }}{{ $row['tahun'] }}">Edit Data -
                                                {{ $row['bulan'] }} {{ $row['tahun'] }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Dana Masuk</label>
                                                <input type="text" name="total_dana_masuk" class="form-control rupiah-input" min="0"
                                                    value="{{ number_format($row['total_dana_masuk'] ?? 0, 0, ',', '.') }}"
                                                    required>
                                            </div>
                                            @foreach ($kategori as $k)
                                                <div class="mb-3">
                                                    <label class="form-label">{{ $k->nama }}</label>
                                                    <input type="text" name="total_biaya_kapitasi[{{ $k->id }}]"
                                                        class="form-control rupiah-input"
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

                    {{-- Modal Validasi --}}
                    {{-- Modalnya tetap di dalam loop grouped agar ID modal unik --}}
                    @if (empty($row['validasi']))
                        <div class="modal fade" id="validasiModal{{ $row['bulan_id'] }}{{ $row['tahun'] }}" tabindex="-1"
                            aria-labelledby="validasiModalLabel{{ $row['bulan_id'] }}{{ $row['tahun'] }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST"
                                        action="{{ route('rekap.kapitasi.validate', ['tahun' => $row['tahun'], 'bulan_id' => $row['bulan_id']]) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="validasiModalLabel{{ $row['bulan_id'] }}{{ $row['tahun'] }}">
                                                Konfirmasi Validasi</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body">
                                            Apakah Anda yakin data untuk <strong>{{ $row['bulan'] }} {{ $row['tahun'] }}</strong> sudah
                                            selesai diisi dan ingin menyimpannya secara permanen?
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

                {{-- Modal Edit Saldo Awal --}}
                <div class="modal fade" id="editSaldoAwalModal" tabindex="-1" aria-labelledby="editSaldoAwalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('rekap.kapitasi.updateSaldoAwal', ['tahun' => $selectedTahun]) }}"
                            id="formSaldoAwal">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editSaldoAwalLabel">Edit Sisa Saldo Awal Tahun {{ $selectedTahun }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <label for="saldo_awal_tahun" class="form-label">Saldo Awal Tahun</label>
                                    <input type="text" class="form-control rupiah-input" name="saldo_awal_tahun"
                                        id="saldo_awal_tahun" value="{{ number_format($saldoAwalTahun ?? 0, 0, ',', '.') }}"
                                        required>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary"> <i class="fas fa-save"></i> Simpan</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal Rincian Saldo Bulanan --}}
            <div class="modal fade" id="rincianSaldoModal" tabindex="-1" aria-labelledby="rincianSaldoLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rincianSaldoLabel">Rincian Sisa Saldo per Bulan - {{ $selectedTahun }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Bulan</th>
                                        <th>Sisa Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($sisaSaldoPerBulan as $item)
                                        <tr>
                                            <td>{{ $item['nama_bulan'] }}</td>
                                            <td>{{ number_format($item['sisa_saldo'], 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center">Belum ada data sisa saldo per bulan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif {{-- END: Kondisi selectedTahun --}}
    </div> {{-- Tutup div.container mt-4 --}}
@endsection

@push('styles')
    <style>
        /* Card dasar dengan warna netral lembut */
        .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #f0f4f8; /* abu muda */
            border-radius: 12px;
            padding: 20px;
        }

        /* Judul dalam card */
        .card-title {
            flex: 1;
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        /* Angka dan tombol disusun sejajar */
        .saldo-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .saldo-info p {
            font-size: 1.4rem;
            font-weight: bold;
            color: #0d47a1; /* biru tua */
            margin: 0;
        }

        /* Jika card berwarna hijau (sisa saldo) */
        .card.border-success .saldo-info p {
            color: #1b5e20; /* hijau tua */
        }

        .btn-sm {
            font-size: 0.8rem;
            padding: 5px 10px;
        }

        /* Card khusus untuk masing-masing warna (opsional dipakai) */
        .card-blue {
            background-color: #1E88E5;
            color: white;
        }

        .card-yellow {
            background-color: #FBC02D;
            color: black;
        }

        .card-grey {
            background-color: #757575;
            color: white;
        }

        .card-teal {
            background-color: #4DB6AC;
            color: white;
        }

        .card-green {
            background-color: #43A047;
            color: white;
        }
    </style>
@endpush


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const saldoInput = document.getElementById('saldo_awal_tahun', 'total_biaya_kapitasi', 'total_dana_masuk');
            const form = document.getElementById('formSaldoAwal');

            if (saldoInput) {
                // Format awal saat load
                saldoInput.value = formatRupiah(saldoInput.value);

                // Format saat diketik
                saldoInput.addEventListener('input', function () {
                    const angka = parseRupiah(this.value);
                    this.value = formatRupiah(angka);
                });
            }

            if (form) {
                form.addEventListener('submit', function () {
                    if (saldoInput) {
                        saldoInput.value = parseRupiah(saldoInput.value);
                    }
                });
            }

                // Helper fungsi rupiah
            function formatRupiah(angka) {
                angka = angka.toString().replace(/[^0-9\-]/g, '');

                let isNegative = angka.startsWith('-');
                let numeric = angka.replace('-', '');

                let formatted = numeric.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                return isNegative ? '-' + formatted : formatted;
            }

            function parseRupiah(rupiah) {
                rupiah = rupiah.toString().replace(/\./g, '');
                return parseInt(rupiah.replace(/[^0-9\-]/g, '')) || 0;
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
        const rupiahInputs = document.querySelectorAll('.rupiah-input');

        // Format saat mengetik
        rupiahInputs.forEach(input => {
            input.addEventListener('input', function () {
                let value = this.value.replace(/[^\d\-]/g, '');
                this.value = formatRupiah(value);
            });
        });

        function formatRupiah(angka) {
            return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Bersihkan titik sebelum submit (agar bisa disimpan sebagai BIGINT)
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function () {
                const inputs = form.querySelectorAll('.rupiah-input');
                inputs.forEach(input => {
                    input.value = input.value.replace(/\./g, '');
                });
            });
        });
    });
    </script>
@endpush