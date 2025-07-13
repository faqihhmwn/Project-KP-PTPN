@extends('layout.app')

@section('content')

<div class="container mt-4">
    <h3 class="mb-4">Rekapitulasi Biaya Kesehatan - PTPN Regional 7</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Filter Tahun -->
    <form method="GET" class="row mb-4">
        <div class="col-md-2">
        <label for="tahun" class="form-label">Tahun</label>
        <select name="tahun" id="tahun" class="form-select" required>
            <option value="">-- Pilih Tahun --</option>
            @foreach($tahunList as $tahun)
            <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
            @endforeach
            </select>
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Tampilkan</button>
        </div>
    </form>

    <!-- Form Input -->
    <form method="POST" action="{{ route('regional7.store') }}">
        @csrf
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="bulan_id" class="form-label">Bulan</label>
                <select name="bulan_id" class="form-select" required>
                    <option value="">-- Pilih Bulan --</option>
                    @foreach ($bulans as $bulan)
                        <option value="{{ $bulan->id }}">{{ $bulan->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @php
            $fields = [
                'gol_3_4' => 'Gol. III-IV',
                'gol_1_2' => 'Gol. I-II',
                'kampanye' => 'Kampanye',
                'honor_ila_os' => 'Honor + ILA + OS',
                'pens_3_4' => 'Pens. III-IV',
                'pens_1_2' => 'Pens. I-II',
                'direksi' => 'Direksi',
                'dekom' => 'Dekom',
                'pengacara' => 'Pengacara',
                'transport' => 'Transport',
                'hiperkes' => 'Hiperkes',
            ];
        @endphp

        <div class="row">
            @foreach ($fields as $key => $label)
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ $label }}</label>
                    <input type="text" name="{{ $key }}" class="form-control rupiah" required>
                </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
    </form>

    <!-- Rekap Tabel -->
    <hr class="my-4">
    <div class="table-responsive position-relative" style="overflow-x: auto;">
    <table class="table table-bordered w-auto" id="rekapTableRegional">
        <thead class="table-primary text-center">
            <tr>
                <th>Rekap Bulan</th>
                @foreach ($fields as $label)
                    <th>{{ $label }}</th>
                @endforeach
                <th>Total Biaya Kesehatan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalTahun = array_fill_keys(array_keys($fields), 0);
                $totalTahun['total'] = 0;
            @endphp
            @foreach ($bulans as $bulan)
                <tr>
                    <td>{{ substr($bulan->nama, 0, 3) }}</td>
                    @php
                        $row = $data[$bulan->id][0] ?? null;
                        $subtotal = 0;
                        $isValidated = $row?->is_validated ?? false;
                    @endphp
                    @foreach ($fields as $key => $label)
                        @php
                            $value = $row ? $row->$key : 0;
                            $subtotal += $value;
                            $totalTahun[$key] += $value;
                        @endphp
                        <td class="text-end">{{ number_format($value, 0, ',', '.') }}</td>
                    @endforeach
                    <td class="text-end fw-bold">{{ number_format($subtotal, 0, ',', '.') }}</td>
                    
                    <!-- Kolom Validasi -->
                    <td class="text-center">
                        @if($row)
                            @if($isValidated)
                                <span class="badge bg-success">Tervalidasi</span>
                            @else
                                <form method="POST" action="{{ route('rekap.validate', ['bulan_id' => $bulan->id, 'tahun' => $tahun]) }}" onsubmit="return confirm('Yakin ingin validasi bulan ini? Setelah divalidasi tidak bisa diedit.')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Validasi</button>
                                </form>
                            @endif
                        @else
                            <span class="text-muted">Belum Tervalidasi</span>
                        @endif
                    </td>
                    
                    @php $totalTahun['total'] += $subtotal; 
                    @endphp
                </tr>
            @endforeach

            <!-- Total 1 Tahun -->
            <tr class="table-warning">
                <td><strong>Total 1 Tahun</strong></td>
                @foreach ($fields as $key => $label)
                    <td class="text-end fw-bold">{{ number_format($totalTahun[$key], 0, ',', '.') }}</td>
                @endforeach
                <td class="text-end fw-bold">{{ number_format($totalTahun['total'], 0, ',', '.') }}</td>
            </tr>

            <!-- Biaya Tersedia -->
            <tr class="table-info">
                <td><strong>Biaya Tersedia</strong></td>
                @foreach ($fields as $key => $label)
                    <td>
                        <input type="text" name="biaya_tersedia[{{ $key }}]" class="form-control rupiah biaya-terseida-input" data-kolom="{{ $key }}">
                    </td>
                @endforeach
                <td>
                    <input type="text" id="biayaTersediaTotal" class="form-control rupiah">
                </td>
            </tr>

            <!-- Rekap Persentase -->
            <tr class="table-light">
                <td><strong>Rekap Persentase</strong></td>
                @foreach ($fields as $key => $label)
                <td class="text-end"><span id="persentase-{{ $key }}">0%</span></td>
                @endforeach
                <td class="text-end"><span id="persentase-total">0%</span></td>
            </tr>        
        </tbody>
    </table>
</div>

<div id="custom-scrollbar" style="overflow-x: auto; width: 100%;">
    <div style="width: 1800px; height: 20px;"></div>
</div>

@endsection

@push('scripts')
<script>
    const customScrollbar = document.getElementById('custom-scrollbar');
    customScrollbar.addEventListener('scroll', function () {
        tableContainer.scrollLeft = this.scrollLeft;
    });

    const tableContainer = document.querySelector('.table-responsive');
    document.getElementById('scrollLeft').addEventListener('click', () => {
        tableContainer.scrollBy({ left: -300, behavior: 'smooth' });
    });

    document.getElementById('scrollRight').addEventListener('click', () => {
        tableContainer.scrollBy({ left: 300, behavior: 'smooth' });
    });

    function updatePersentase(kolom, tersedia, terpakai) {
        let persen = tersedia > 0 ? (terpakai / tersedia * 100).toFixed(2) : 0;
        document.getElementById(`persentase-${kolom}`).innerText = persen + '%';
    }

    function updateTotalPersentase() {
        let tersedia = parseInt(document.getElementById('biayaTersediaTotal').value.replace(/\D/g, '')) || 0;
        let total = {{ $totalTahun['total'] ?? 0 }};
        let persen = tersedia > 0 ? (total / tersedia * 100).toFixed(2) : 0;
        document.getElementById('persentase-total').innerText = persen + '%';
    }

    // Format semua input rupiah
    document.querySelectorAll('.rupiah').forEach(input => {
        input.addEventListener('input', function () {
            let val = this.value.replace(/\D/g, '');
            this.value = new Intl.NumberFormat('id-ID').format(val);
        });
    });

    // Hitung persentase per kolom
    document.querySelectorAll('.biaya-tersedia-input').forEach(input => {
        input.addEventListener('input', function () {
            let kolom = this.dataset.kolom;
            let tersedia = parseInt(this.value.replace(/\D/g, '')) || 0;
            let terpakai = {{ Js::from($totalTahun) }}[kolom];
            updatePersentase(kolom, tersedia, terpakai);
        });
    });

    // Hitung total persentase saat total biaya tersedia berubah
    document.getElementById('biayaTersediaTotal')?.addEventListener('input', function () {
        updateTotalPersentase();
    });

    function formatRupiah(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function parseRupiah(rp) {
        return parseInt(rp.replace(/\./g, '')) || 0;
    }
</script>
@endpush
