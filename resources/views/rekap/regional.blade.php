@extends('layout.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">Rekapitulasi Biaya Kesehatan - PTPN I Regional</h3>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Jika $editItem ada (setelah redirect dari aksi edit), modal akan otomatis terbuka via JS, atau bisa ditampilkan langsung --}}
    @isset($editItem)
        {{-- Karena editItem dikirim sebagai objek RekapBiayaKesehatan, kita perlu memastikan bahwa ada kategori_biaya_id yang sesuai --}}
        {{-- Logika ini sepertinya untuk edit satu item. Kita akan menggunakan modal untuk edit per bulan_tahun. --}}
        {{-- Jadi bagian ini mungkin tidak terlalu diperlukan lagi setelah implementasi modal per bulan/tahun --}}
    @endisset

    <form method="GET" action="{{ route('rekap.regional.index') }}">
        <div class="row mb-3">
            <div class="col-md-3">
                @if (!$selectedTahun)
                    <div class="alert alert-info">
                        Silakan pilih Tahun terlebih dahulu untuk mengisi rekap biaya.
                    </div>
                @endif

                <label for="tahun" class="form-label">Tahun</label>
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
                    <button type="submit" class="btn btn-primary">Tampilkan</button>
                </div>
            @endunless
            {{-- Jika tahun sudah dipilih, tambahkan tombol Reset --}}
            @if ($selectedTahun)
                <div class="col-md-3 d-flex align-items-end">
                    <a href="{{ route('rekap.regional.index') }}" class="btn btn-secondary">Pilih Tahun Lain</a>
                </div>
            @endif
        </div>
    </form>

    @if ($selectedTahun)
        <form method="POST" action="{{ route('rekap.regional.store') }}">
            @csrf
            <input type="hidden" name="tahun" value="{{ $selectedTahun }}">

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <select name="bulan_id" id="bulan" class="form-select" required>
                        <option value="">-- Pilih Bulan --</option>
                        @foreach ($bulan as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

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
                            <td>
                                <input type="text" name="total_biaya_kesehatan[{{ $k->id }}]" class="form-control rupiah-input" min="0" inputmode="numeric" pattern="[0-9]*" required>
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
                        <th rowspan="2" class="text-center align-middle">Rekap Bulan</th>
                        <th colspan="{{ $kategori->count() }}" class="group-header text-center">DETAIL BIAYA</th>
                        <th rowspan="2" class="bg-warning text-dark text-center align-middle">TOTAL BIAYA KESEHATAN</th>
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
                            @foreach ($kategori as $k)
                                <td>{{ number_format($row['kategori'][$k->id] ?? 0, 0, ',', '.') }}</td>
                            @endforeach
                            <td class="bg-warning fw-bold">{{ number_format($row['total_biaya_kesehatan'] ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $row['validasi'] ?? '-' }}</td>
                            <td>
                                @if (empty($row['validasi']))
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $row['bulan_id'] }}{{ $row['tahun'] }}">
                                        Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#validasiModal{{ $row['bulan_id'] }}{{ $row['tahun'] }}">
                                        Validasi
                                    </button>
                                    <form action="{{ route('rekap.regional.destroy', ['tahun' => $row['tahun'], 'bulan_id' => $row['bulan_id']]) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus semua data untuk bulan ini?')">
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
                        <td class="fw-bold">TOTAL 1 TAHUN</td>
                        @foreach ($kategori as $k)
                            <td class="fw-bold">{{ number_format($annualTotals[$k->id] ?? 0, 0, ',', '.') }}</td>
                        @endforeach
                        <td class="bg-warning fw-bold">{{ number_format($annualTotals['all_kategoris_total'] ?? 0, 0, ',', '.') }}</td>
                        <td></td> {{-- Kolom Validasi --}}
                        <td></td> {{-- Kolom Aksi --}}
                    </tr>

                    {{-- Baris: BIAYA TERSEDIA --}}
                    <tr>
                        <td class="fw-bold">BIAYA TERSEDIA</td>
                        @foreach ($kategori as $k)
                            <td class="fw-bold">{{ number_format($biayaTersedia[$k->id] ?? 0, 0, ',', '.') }}</td>
                        @endforeach
                        <td class="bg-info fw-bold">
                            {{ number_format(array_sum(array_intersect_key($biayaTersedia, array_flip(array_keys($annualTotals)))) ?? 0, 0, ',', '.') }}                        </td>
                        </td>
                        <td></td> {{-- Kolom Validasi --}} 
                        <td> {{-- Kolom Aksi --}}
                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editBiayaTersediaModal">
                                Edit
                            </button>
                            <form action="{{ route('rekap.regional.biayaTersedia.destroy', ['tahun' => $selectedTahun]) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus semua data BIAYA TERSEDIA untuk tahun {{ $selectedTahun }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>

                    {{-- Baris: PERSENTASE --}}
                    <tr>
                        <td class="fw-bold">PERSENTASE</td>
                        @foreach ($kategori as $k)
                            <td class="fw-bold">
                                @php
                                    $totalTahun = $annualTotals[$k->id] ?? 0;
                                    $biayaAvail = $biayaTersedia[$k->id] ?? 0;
                                    $percentage = ($biayaAvail > 0) ? ($totalTahun / $biayaAvail) * 100 : 0;
                                @endphp
                                {{ number_format($percentage, 2) }}%
                            </td>
                        @endforeach
                        <td class="bg-success text-dark fw-bold">
                            @php
                                $totalKesehatanTahunan = $annualTotals['all_kategoris_total'] ?? 0;
                                $biayaAvailTotal = $biayaTersedia['all_kategoris_total'] ?? 0;
                                $percentageTotal = ($biayaAvailTotal > 0) ? ($totalKesehatanTahunan / $biayaAvailTotal) * 100 : 0;
                            @endphp
                            {{ number_format($percentageTotal, 2) }}%
                        </td>
                        <td></td>
                        <td></td>
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
                            <form method="POST" action="{{ route('rekap.regional.update', ['tahun' => $row['tahun'], 'bulan_id' => $row['bulan_id']]) }}">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel{{ $row['bulan_id'] }}{{ $row['tahun'] }}">Edit Data - {{ $row['bulan'] }} {{ $row['tahun'] }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    @foreach ($kategori as $k)
                                        <div class="mb-3">
                                            <label class="form-label">{{ $k->nama }}</label>
                                            <input type="text" name="total_biaya_kesehatan[{{ $k->id }}]" class="form-control rupiah-input" min="0" inputmode="numeric" pattern="[0-9]*" value="{{ $row['kategori'][$k->id] ?? 0 }}" required>
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
                            <form method="POST" action="{{ route('rekap.regional.validate', ['tahun' => $row['tahun'], 'bulan_id' => $row['bulan_id']]) }}">
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

        {{-- +++ AWAL DARI MODAL BARU UNTUK BIAYA TERSEDIA +++ --}}
            <div class="modal fade" id="editBiayaTersediaModal" tabindex="-1" aria-labelledby="editBiayaTersediaModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('rekap.regional.biayaTersedia.storeOrUpdate') }}">
                            @csrf
                            <input type="hidden" name="tahun" value="{{ $selectedTahun }}">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editBiayaTersediaModalLabel">Edit Biaya Tersedia - Tahun {{ $selectedTahun }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Masukkan nilai anggaran biaya yang tersedia untuk masing-masing kategori di tahun {{ $selectedTahun }}.</p>
                                @foreach ($kategori as $k)
                                    <div class="mb-3">
                                        <label class="form-label">{{ $k->nama }}</label>
                                        {{-- Gunakan nilai dari $biayaTersedia untuk mengisi value --}}
                                        <input type="text" name="total_tersedia[{{ $k->id }}]" class="form-control rupiah-input" min="0" inputmode="numeric" pattern="[0-9]*" value="{{ $biayaTersedia[$k->id] ?? 0 }}" required>
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
    @endif {{-- End of @if ($selectedTahun) --}}
</div>



@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Menggunakan fungsi formatRupiah dari referensi Anda
            function formatRupiah(angka) {
                if (angka === null || angka === undefined || angka === '') {
                    return '';
                }
                // Pastikan input adalah angka atau string angka bersih
                angka = String(angka).replace(/\D/g, ''); // Hapus semua non-digit
                if (angka === '') return '';

                // Gunakan toLocaleString untuk format yang lebih konsisten dan handling kasus besar
                return parseInt(angka).toLocaleString('id-ID');
            }

            // Menggunakan fungsi parseRupiah dari referensi Anda
            function parseRupiah(rp) {
                if (rp === null || rp === undefined || rp === '') {
                    return 0;
                }
                // Hapus semua karakter non-digit (termasuk titik pemisah ribuan)
                const cleanString = String(rp).replace(/\D/g, '');
                return parseInt(cleanString) || 0; // Kembalikan 0 jika tidak valid
            }

            function unformatRupiah(formattedRp) {
                if (formattedRp === null || formattedRp === undefined || formattedRp === '') {
                    return '';
                }
                return String(formattedRp).replace(/\D/g, ''); // Hapus semua karakter non-digit
            }

            const rupiahInputs = document.querySelectorAll('.rupiah-input');

            rupiahInputs.forEach(input => {
                // Initial formatting when page loads or modal opens (if value is already there)
                // Pastikan nilai awal juga diformat
                if (input.value) {
                    input.value = formatRupiah(input.value);
                }

                // *** PENCEGAHAN INPUT HURUF (KEYDOWN) ***
                input.addEventListener('keydown', function(e) {
                    // Pastikan hanya angka (0-9 dari keyboard biasa atau numpad)
                    // dan tidak ada shiftKey (untuk mencegah simbol di atas angka)
                    if ((e.shiftKey || e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault(); // Hentikan input jika bukan angka
                    }
                });

                // *** FORMATTING SAAT INPUT (INPUT EVENT) ***
                input.addEventListener('input', function(e) {
                    let cursorPosition = this.selectionStart; // Simpan posisi kursor
                    let originalValue = this.value;

                    let cleanValue = unformatRupiah(originalValue);
                    let formattedValue = formatRupiah(cleanValue);

                    this.value = formattedValue;

                    // Sesuaikan posisi kursor setelah formatting
                    // Ini adalah bagian yang tricky. Cara paling aman untuk pengalaman yang baik:
                    // Hitung jumlah titik di originalValue
                    const dotCountBefore = (originalValue.match(/\./g) || []).length;
                    // Hitung jumlah titik di formattedValue
                    const dotCountAfter = (formattedValue.match(/\./g) || []).length;

                    // Perbedaan jumlah titik akan mempengaruhi posisi kursor
                    const diffDot = dotCountAfter - dotCountBefore;
                    this.setSelectionRange(cursorPosition + diffDot, cursorPosition + diffDot);
                });

                // *** UNFORMAT SAAT FOKUS (opsional, untuk memudahkan edit angka mentah) ***
                input.addEventListener('focus', function() {
                    this.value = unformatRupiah(this.value);
                });

                // *** REFORMAT SAAT BLUR (opsional, untuk menampilkan format setelah selesai edit) ***
                input.addEventListener('blur', function() {
                    this.value = formatRupiah(this.value);
                });

                // *** UNFORMAT SAAT FORM SUBMIT (PENTING untuk backend) ***
                input.closest('form').addEventListener('submit', function() {
                    const formRupiahInputs = this.querySelectorAll('.rupiah-input');
                    formRupiahInputs.forEach(i => {
                        // Pastikan hanya input di form ini yang di-unformat
                        if (this.contains(i)) {
                            i.value = unformatRupiah(i.value);
                        }
                    });
                });
            });
        });
    </script>
@endpush
@endsection