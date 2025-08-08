@extends('layout.app')
@section('title', 'Rekapitulasi Obat Bulanan')

@section('content')

    <style>
        .table-container {
            background-color: white;
            padding: 20px;
                $hargaSatuan = $rekapitulasi->harga_satuan ?? $obat->harga_satuan;
                $totalBiaya += $jumlahKeluar * $hargaSatuan;     
            border-radius: 10px;
            overflow-x: auto;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table {
            width: max-content;
            min-width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #0077c0;
            color: white;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
            white-space: nowrap;
            font-size: 12px;
        }

        th {
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        input[type="number"],
        input[type="text"] {
            width: 60px;
            padding: 4px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #e3f2fd;
        }
    </style>


    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">Rekapitulasi Obat Bulanan</h2>
        <a href="/admin/obat/dashboard" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Farmasi
        </a>
    </div>

    <!-- Controls -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <!-- Filter Bulan/Tahun -->
                <div class="col-md-6">
                    <form method="GET" class="d-flex gap-2 align-items-center" id="filterForm">
    <select name="unit_id" class="form-select" required>
        <option value="">Pilih Unit</option>
        @foreach ($units as $unitItem)
            <option value="{{ $unitItem->id }}" {{ request('unit_id') == $unitItem->id ? 'selected' : '' }}>
                {{ $unitItem->nama }}
            </option>
        @endforeach
    </select>

    <select name="bulan" class="form-select">
        @for ($i = 1; $i <= 12; $i++)
            <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                {{ \Carbon\Carbon::createFromDate(null, $i, 1)->format('F') }}
            </option>
        @endfor
    </select>

    <select name="tahun" class="form-select">
        @for ($year = 2025; $year <= 2035; $year++)
            <option value="{{ $year }}" {{ $tahun == $year ? 'selected' : '' }}>{{ $year }}</option>
        @endfor
    </select>

    <button type="submit" class="btn btn-primary">
        <i class="fas fa-filter"></i> Filter
    </button>
</form>
@if (!request('unit_id'))
    <div class="alert alert-warning mt-3">
        <strong>Silakan pilih unit terlebih dahulu untuk menampilkan data rekapitulasi.</strong>
    </div>
@endif

                </div>
            </div>
            
            <!-- Search Box -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="Cari obat berdasarkan nama atau jenis...">
                        <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Container -->
<div class="table-container">
    <div id="rekapNotif" class="alert d-none mb-3"></div>
    <table>
        <thead>
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Unit</th>
                <th rowspan="2">Nama Obat</th>
                <th rowspan="2">Jenis</th>
                <th rowspan="2">Harga Satuan</th>
                <th rowspan="2">Stok Awal</th>
                <th rowspan="2">Bulan</th>
                <th rowspan="2">Tahun</th>
                <th colspan="{{ $daysInMonth }}">Penggunaan Harian (Tanggal)</th>
                <th rowspan="2">Sisa Stok</th>
                <th rowspan="2">Total Biaya</th>
                <th rowspan="2">Aksi</th>
            </tr>
            <tr>
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    <th>{{ $day }}</th>
                @endfor
            </tr>
        </thead>
        <tbody id="obatTableBody">
            @forelse($obats as $index => $obat)
                <tr data-obat-row="{{ $obat->id }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $obat->unit->nama ?? 'N/A' }}</td>
                    <td>{{ $obat->nama_obat }}</td>
                    <td>{{ $obat->jenis_obat ?? '-' }}</td>
                    @php
                        $rekapHargaSatuan = \App\Models\RekapitulasiObat::where('obat_id', $obat->id)
                            ->where('unit_id', request('unit_id'))
                            ->where('bulan', $bulan)
                            ->where('tahun', $tahun)
                            ->value('harga_satuan') ?? $obat->harga_satuan;

                        $hargaSatuan = $rekapHargaSatuan;
                        $stokAwal = $obat->stokAwal($bulan, $tahun);
                        $totalBiaya = 0;
                        $totalJumlahKeluar = 0;
                        $totalJumlahMasuk = 0;
                    @endphp
                    <td>Rp {{ number_format($hargaSatuan, 0, ',', '.') }}</td>
                    <td class="stok-awal" data-obat-id="{{ $obat->id }}">{{ $stokAwal }}</td>
                    <td>{{ \Carbon\Carbon::createFromDate(null, $bulan, 1)->format('F') }}</td>
                    <td>{{ $tahun }}</td>
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $tanggal = \Carbon\Carbon::createFromDate($tahun, $bulan, $day)->format('Y-m-d');
                            $rekapitulasi = \App\Models\RekapitulasiObat::where('obat_id', $obat->id)
                                ->where('unit_id', request('unit_id'))
                                ->where('tanggal', $tanggal)
                                ->first();
                            $jumlahKeluar = $rekapitulasi->jumlah_keluar ?? 0;
                            $jumlahMasuk = \App\Models\PenerimaanObat::where('obat_id', $obat->id)
                                ->where('unit_id', request('unit_id'))
                                ->where('tanggal_masuk', $tanggal)
                                ->sum('jumlah_masuk');

                            $totalBiaya += $jumlahKeluar * $hargaSatuan;
                            $totalJumlahKeluar += $jumlahKeluar;
                            $totalJumlahMasuk += $jumlahMasuk;
                        @endphp
                        <td>
                            <div style="display: flex; flex-direction: column;">
                                <input type="number" class="daily-input" inputmode="numeric" min="0"
                                    value="{{ $jumlahKeluar }}"
                                    data-obat-id="{{ $obat->id }}"
                                    data-tanggal="{{ $tanggal }}"
                                    data-harga-satuan="{{ $hargaSatuan }}"
                                    data-jumlah-masuk="{{ $jumlahMasuk }}"
                                    data-stok-awal="{{ $stokAwal }}"
                                    onchange="updateStokDanBiaya({{ $obat->id }})">
                                @if ($jumlahMasuk > 0)
                                    <small class="text-success fw-bold">+{{ $jumlahMasuk }}</small>
                                @endif
                            </div>
                        </td>
                    @endfor
                    <td class="sisa-stok" id="sisa-stok-{{ $obat->id }}">
                        {{ max(0, $stokAwal + $totalJumlahMasuk - $totalJumlahKeluar) }}
                    </td>
                    <td class="total-biaya" id="total-biaya-{{ $obat->id }}">
                        <strong>Rp {{ number_format($totalBiaya, 0, ',', '.') }}</strong>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.obat.rekapitulasi.detail', ['obat' => $obat->id]) }}?bulan={{ $bulan }}&tahun={{ $tahun }}"
                                class="btn btn-info btn-sm" title="Detail Rekapitulasi">
                                <i class="fas fa-chart-bar"></i>
                            </a>
                            <a href="{{ route('admin.obat.edit', $obat->id) }}"
                                class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('admin.obat.destroy', $obat) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('⚠️ Apakah Anda yakin ingin menghapus obat ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 11 + $daysInMonth }}" class="text-center py-4">
                        <i class="fas fa-box-open fa-2x text-muted mb-2"></i><br>
                        Belum ada data obat untuk bulan {{ \Carbon\Carbon::create()->month($bulan)->format('F') }} {{ $tahun }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

 {{ $obats->links('pagination::bootstrap-5') }}

    @include('obat.modal-penerimaan-obat')
</div>

<div id="validasiInfo" class="alert alert-success mt-3 d-none">
        <i class="fas fa-lock"></i> Data bulan ini telah divalidasi dan dikunci. Semua input, edit, dan hapus
            dinonaktifkan untuk menjaga integritas laporan.
    </div>
    
    <div class="d-flex justify-content-end align-items-center gap-2 mt-3">
            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalTambahStok">
                <i class="fas fa-plus"></i> Tambah Stok Obat
            </button>
            <button id="simpanRekapBtn" class="btn btn-primary ms-2">
                <i class="fas fa-save"></i> Simpan Rekapitulasi
            </button>
        </div>

<script>
    function updateStokDanBiaya(obatId) {
        const inputs = document.querySelectorAll(`input[data-obat-id='${obatId}']`);
        let totalKeluar = 0;
        let totalMasuk = 0;
        let stokAwal = 0;
        let hargaSatuan = 0;

        inputs.forEach(input => {
            const keluar = parseInt(input.value) || 0;
            const masuk = parseInt(input.dataset.jumlahMasuk) || 0;
            hargaSatuan = parseInt(input.dataset.hargaSatuan) || 0;
            stokAwal = parseInt(input.dataset.stokAwal) || 0;

            totalKeluar += keluar;
            totalMasuk += masuk;
        });

        const sisa = Math.max(0, stokAwal + totalMasuk - totalKeluar);
        const totalBiaya = totalKeluar * hargaSatuan;

        const sisaEl = document.getElementById(`sisa-stok-${obatId}`);
        const biayaEl = document.getElementById(`total-biaya-${obatId}`);

        if (sisaEl) sisaEl.innerText = sisa;
        if (biayaEl) biayaEl.innerHTML = '<strong>Rp ' + totalBiaya.toLocaleString('id-ID') + '</strong>';

    }
</script>


    

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.obat.export') }}" method="GET" id="exportForm" target="_blank">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                    value="{{ request('tahun') && request('bulan') ? \Carbon\Carbon::create(request('tahun'), request('bulan'))->startOfMonth()->format('Y-m-d') : \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                    value="{{ request('tahun') && request('bulan') ? \Carbon\Carbon::create(request('tahun'), request('bulan'))->endOfMonth()->format('Y-m-d') : \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}"
                                    required>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_daily"
                                        name="include_daily" value="1"> -->
                                    <label class="form-check-label" for="include_daily">
                                        Sertakan data harian (maksimal 31 hari)
                                    </label>
                                    <small class="form-text text-muted">
                                        Data harian akan ditampilkan jika range tanggal kurang dari 32 hari
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i>
                            <strong>Catatan:</strong>
                            <ul class="mb-0 mt-2">
                                <li>File akan didownload dalam format Excel (.xlsx)</li>
                                <li>Data akan ditampilkan dalam bentuk rekap perbulan</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download"></i> Download Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- SCRIPTS DIPINDAHKAN KE BAWAH AGAR PASTI TERLOAD -->
    <script>
        // Deklarasi variabel global untuk bulan dan tahun
        const CURRENT_BULAN = {{ $bulan }};
        const CURRENT_TAHUN = {{ $tahun }};

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#obatTableBody tr');
            
            rows.forEach(row => {
                const obatName = row.getAttribute('data-obat-name');
                const obatJenis = row.getAttribute('data-obat-jenis');
                
                if (!obatName && !obatJenis) return; // Skip if no data attributes
                
                const matchesSearch = obatName.includes(searchTerm) || 
                                    obatJenis.includes(searchTerm);
                
                row.style.display = matchesSearch ? '' : 'none';
            });
        });

        function clearSearch() {
            searchInput.value = '';
            const event = new Event('input');
            searchInput.dispatchEvent(event);
        }

        // Update sisa stok secara dinamis saat input harian berubah
        function updateSisaStok(obatId) {
            const row = document.querySelector(`tr[data-obat-row='${obatId}']`);
            if (!row) return;

            const stokAwalCell = row.querySelector('.stok-awal');
            let stokAwal = 0;

            if (stokAwalCell) {
                stokAwal = parseInt(stokAwalCell.textContent.replace(/[^\d]/g, '')) || 0;
            }

            let totalKeluar = 0;
            let totalMasuk = 0;

            row.querySelectorAll('td').forEach(cell => {
                const input = cell.querySelector('.daily-input');
                const masukLabel = cell.querySelector('small.text-success');

                if (input) {
                    totalKeluar += parseInt(input.value) || 0;
                }

                if (masukLabel) {
                    const masukText = masukLabel.textContent.replace(/[^\d]/g, '');
                    totalMasuk += parseInt(masukText) || 0;
                }
            });

            const sisaStok = stokAwal + totalMasuk - totalKeluar;

            const sisaStokCell = row.querySelector('.sisa-stok');
            if (sisaStokCell) {
                sisaStokCell.textContent = sisaStok < 0 ? 0 : sisaStok;
            }
        }


        // Update total biaya secara dinamis saat input harian berubah
        function updateTotalBiaya(obatId) {
            const row = document.querySelector(`tr[data-obat-row='${obatId}']`);
            if (!row) return;
            let totalBiaya = 0;
            row.querySelectorAll('.daily-input').forEach(input => {
                const jumlahKeluar = parseInt(input.value) || 0;
                const hargaSatuan = parseInt(input.getAttribute('data-harga-satuan')) || 0;
                totalBiaya += jumlahKeluar * hargaSatuan;
            });
            const totalBiayaCell = row.querySelector('.total-biaya');
            if (totalBiayaCell) {
                totalBiayaCell.innerHTML = `<strong>Rp ${totalBiaya.toLocaleString('id-ID')}</strong>`;
            }
        }

        // Inisialisasi update sisa stok saat halaman pertama kali dimuat dan setiap input berubah
        document.addEventListener('DOMContentLoaded', function() {
            // Filter form handling
            const filterForm = document.getElementById('filterForm');
            const bulanSelect = document.getElementById('bulanSelect');
            const tahunSelect = document.getElementById('tahunSelect');

            if (filterForm) {
                bulanSelect.addEventListener('change', function() {
                    // Set semua input ke 0 sebelum submit form
                    document.querySelectorAll('.daily-input').forEach(input => {
                        input.value = '0';
                    });
                    filterForm.submit();
                });

                tahunSelect.addEventListener('change', function() {
                    // Set semua input ke 0 sebelum submit form
                    document.querySelectorAll('.daily-input').forEach(input => {
                        input.value = '0';
                    });
                    filterForm.submit();
                });
            }

            // Handle inputs for all rows
            document.querySelectorAll('tr[data-obat-row]').forEach(row => {
                const obatId = row.getAttribute('data-obat-row');
                updateSisaStok(obatId);
                updateTotalBiaya(obatId);

                // Add event listeners to each input
                row.querySelectorAll('.daily-input').forEach(input => {
                    // Set initial value to 0 if empty
                    if (!input.value || input.value.trim() === '') {
                        input.value = '0';
                    }

                    // Update calculations on input
                    input.addEventListener('input', function() {
                        updateSisaStok(obatId);
                        updateTotalBiaya(obatId);
                    });

                    // Handle focus
                    input.addEventListener('focus', function() {
                        // Clear the input if it's 0
                        if (this.value === '0') {
                            this.value = '';
                        }
                    });

                    // Handle blur (unfocus)
                    input.addEventListener('blur', function() {
                        // Set to 0 if empty
                        if (!this.value || this.value.trim() === '') {
                            this.value = '0';
                        }
                        updateSisaStok(obatId);
                        updateTotalBiaya(obatId);
                    });
                });
            });

            // --- VALIDASI BULAN (LOCKING) ---
            const bulan = CURRENT_BULAN;
            const tahun = CURRENT_TAHUN;
            const lockKey = `obat_validasi_${tahun}_${bulan}`;
            const isLocked = localStorage.getItem(lockKey) === '1';
            const validasiBtn = document.getElementById('validasiBulanBtn');
            const unvalidasiBtn = document.getElementById('unvalidasiBulanBtn');
            const unvalidasiBtnInAlert = document.getElementById('unvalidasiBtnInAlert');
            const validasiInfo = document.getElementById('validasiInfo');

            function setLockedState(locked) {
                // Set all daily inputs to readonly
                document.querySelectorAll('.daily-input').forEach(input => {
                    input.readOnly = locked;
                });
                // Disable edit & delete buttons
                document.querySelectorAll('a.btn-warning, form .btn-danger').forEach(btn => {
                    btn.disabled = locked;
                    if (locked) {
                        btn.classList.add('disabled');
                        btn.setAttribute('tabindex', '-1');
                        btn.setAttribute('aria-disabled', 'true');
                    } else {
                        btn.classList.remove('disabled');
                        btn.removeAttribute('tabindex');
                        btn.removeAttribute('aria-disabled');
                    }
                });
                // Hide or show validasi/unvalidasi buttons and info
                if (locked) {
                    if (validasiBtn) validasiBtn.classList.add('d-none');
                    if (unvalidasiBtn) unvalidasiBtn.classList.remove('d-none');
                    if (validasiInfo) validasiInfo.classList.remove('d-none');
                } else {
                    if (validasiBtn) validasiBtn.classList.remove('d-none');
                    if (unvalidasiBtn) unvalidasiBtn.classList.add('d-none');
                    if (validasiInfo) validasiInfo.classList.add('d-none');
                }
            }

            setLockedState(isLocked);

            function handleUnvalidasi() {
                if (confirm('Anda yakin ingin membatalkan validasi? Semua data akan dapat diubah kembali.')) {
                    localStorage.removeItem(lockKey);
                    setLockedState(false);
                }
            }

            if (validasiBtn) {
                validasiBtn.addEventListener('click', function() {
                    if (confirm('Setelah divalidasi, data bulan ini akan dikunci. Lanjutkan?')) {
                        localStorage.setItem(lockKey, '1');
                        setLockedState(true);
                        location.reload();
                    }
                });
            }

            const batalkanValidasiBtn = document.getElementById('batalkanValidasiBtn');

            if (batalkanValidasiBtn) {
                if (isLocked) {
                    batalkanValidasiBtn.classList.remove('d-none');
                } else {
                    batalkanValidasiBtn.classList.add('d-none');
                }

                batalkanValidasiBtn.addEventListener('click', function() {
                    if (confirm('Batalkan validasi bulan ini? Anda bisa mengubah data kembali.')) {
                        localStorage.removeItem(lockKey);
                        setLockedState(false);
                        batalkanValidasiBtn.classList.add('d-none');
                    }
                });
            }

        });

        // --- SIMPAN REKAPITULASI (MANUAL SAVE, BULK) ---
        document.getElementById('simpanRekapBtn').addEventListener('click', async function() {
            const notif = document.getElementById('rekapNotif');
            const saveBtn = this;

            try {
                // Disable button during save
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

                notif.classList.add('d-none');
                notif.classList.remove('alert-success', 'alert-danger');

                // Kumpulkan data dari seluruh baris obat
                const rows = document.querySelectorAll('tr[data-obat-row]');
                const bulk = [];

                for (const row of rows) {
                    const obatId = row.getAttribute('data-obat-row');
                    const harga = parseInt(row.getAttribute('data-harga')) || 0;
                    const stokAwalCell = row.querySelector('.stok-awal');
                    const stokAwal = parseInt(stokAwalCell?.textContent.replace(/[^\d]/g, '')) || 0;

                    // Untuk setiap input harian
                    const inputs = row.querySelectorAll('.daily-input');
                    inputs.forEach((input) => {
                        const tanggal = input.getAttribute('data-tanggal');
                        const jumlahKeluar = parseInt(input.value) || 0;

                        if (tanggal) { // Pastikan tanggal ada
                            // Hitung sisa stok hanya untuk tanggal ini
                            let totalKeluar = 0;
                            inputs.forEach((inp) => {
                                if (inp.getAttribute('data-tanggal') <= tanggal) {
                                    totalKeluar += parseInt(inp.value) || 0;
                                }
                            });

                            bulk.push({
                                obat_id: obatId,
                                tanggal: tanggal,
                                bulan: CURRENT_BULAN,
                                tahun: CURRENT_TAHUN,
                                jumlah_keluar: jumlahKeluar,
                                stok_awal: stokAwal,
                                sisa_stok: Math.max(0, stokAwal - totalKeluar),
                                total_biaya: jumlahKeluar * harga
                            });
                        }
                    });
                }

                // Kirim data ke backend
                const response = await fetch('{{ route('admin.obat.rekapitulasi-obat.input-harian') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        bulk: bulk,
                        bulan: CURRENT_BULAN,
                        tahun: CURRENT_TAHUN,
                        unit_id: document.querySelector('[name="unit_id"]').value
                    })
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Terjadi kesalahan saat menyimpan data');
                }

                // Tampilkan notifikasi sukses
                notif.textContent = '✅ Data rekapitulasi berhasil disimpan!';
                notif.classList.remove('d-none', 'alert-danger');
                notif.classList.add('alert-success');

                // Refresh halaman setelah 1 detik
                setTimeout(() => {
    window.location.href = window.location.pathname +
        '?unit_id={{ request("unit_id") }}' +
        '&bulan=' + CURRENT_BULAN +
        '&tahun=' + CURRENT_TAHUN;
}, 1000);


            } catch (error) {
                console.error('Error saving data:', error);
                notif.textContent = '❌ ' + (error.message || 'Terjadi kesalahan saat menyimpan data');
                notif.classList.remove('d-none', 'alert-success');
                notif.classList.add('alert-danger', 'd-block');
            } finally {
                // Selalu reset tombol save
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Rekapitulasi';
            }
        });
    </script>


    <script>
        // Validasi export modal
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = new Date(this.value);
            const endDateInput = document.getElementById('end_date');
            const endDate = new Date(endDateInput.value);

            if (endDate < startDate) {
                endDateInput.value = this.value;
            }

            // Set minimum date untuk end date
            endDateInput.min = this.value;

            // Set maksimum 3 bulan dari start date
            const maxDate = new Date(startDate);
            maxDate.setMonth(maxDate.getMonth() + 3);
            endDateInput.max = maxDate.toISOString().split('T')[0];
        });

        document.getElementById('end_date').addEventListener('change', function() {
            const endDate = new Date(this.value);
            const startDate = new Date(document.getElementById('start_date').value);

            // Check if range is more than 3 months
            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const diffMonths = diffDays / 30;

            if (diffMonths > 3) {
                alert('Range tanggal maksimal 3 bulan!');
                const maxDate = new Date(startDate);
                maxDate.setMonth(maxDate.getMonth() + 3);
                this.value = maxDate.toISOString().split('T')[0];
            }

            // Auto check/uncheck daily data based on range
            const includeDailyCheckbox = document.getElementById('include_daily');
            if (diffDays <= 31) {
                includeDailyCheckbox.disabled = false;
            } else {
                includeDailyCheckbox.checked = false;
                includeDailyCheckbox.disabled = true;
            }
        });

        // function searchObat() {
        //     const searchInput = document.getElementById('searchObat');
        //     const searchTerm = searchInput.value.toLowerCase();
        //     const tableRows = document.querySelectorAll('#obatTableBody tr');

        //     tableRows.forEach(row => {
        //         const obatName = row.getAttribute('data-obat-name') || '';
        //         const obatJenis = row.getAttribute('data-obat-jenis') || '';

        //         if (obatName.includes(searchTerm) || obatJenis.includes(searchTerm)) {
        //             row.style.display = '';
        //         } else {
        //             row.style.display = 'none';
        //         }
        //     });
        // }

        function updateTransaksi(input) {
            const obatId = input.getAttribute('data-obat-id');
            const tanggal = input.getAttribute('data-tanggal');
            const jumlahKeluar = parseInt(input.value) || 0;
            const row = input.closest('tr[data-obat-row]');
            const stokAwalCell = row.querySelector('.stok-awal');
            let stokAwal = 0;
            if (stokAwalCell) {
                stokAwal = parseInt(stokAwalCell.textContent.replace(/[^\d]/g, '')) || 0;
            }
            let totalKeluar = 0;
            row.querySelectorAll('.daily-input').forEach(inp => {
                totalKeluar += parseInt(inp.value) || 0;
            });
            if (totalKeluar > stokAwal) {
                alert('Input melebihi kapasitas stok awal!');
                return;
            }
            // Hitung sisa stok dan total biaya
            const sisaStok = stokAwal - totalKeluar;
            const harga = parseInt(row.getAttribute('data-harga')) || 0;
            let totalBiaya = 0;
            row.querySelectorAll('.daily-input').forEach(inp => {
                totalBiaya += (parseInt(inp.value) || 0) * harga;
            });
            // Kirim data ke endpoint rekapitulasi
            fetch('/obat/rekapitulasi-obat/input-harian', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        obat_id: obatId,
                        tanggal: tanggal,
                        jumlah_keluar: jumlahKeluar,
                        stok_awal: stokAwal,
                        sisa_stok: sisaStok < 0 ? 0 : sisaStok,
                        total_biaya: totalBiaya,
                        bulan: {{ $bulan }},
                        tahun: {{ $tahun }}
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Data tersimpan
                })
                .catch(error => {
                    // Error
                });
        }

        // Auto-save ketika user berhenti mengetik
        let typingTimer;
        const doneTypingInterval = 1000; // 1 detik

        document.querySelectorAll('.daily-input').forEach(input => {
            input.addEventListener('keyup', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    updateTransaksi(this);
                    // Update total biaya juga saat auto-save
                    const row = input.closest('tr[data-obat-row]');
                    if (row) {
                        const obatId = row.getAttribute('data-obat-row');
                        updateTotalBiaya(obatId);
                    }
                }, doneTypingInterval);
            });

            input.addEventListener('keydown', function() {
                clearTimeout(typingTimer);
            });
        });
        // Hapus auto-save, hanya simpan manual lewat tombol
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnSimpan = document.getElementById('btnSimpanPenerimaan');
            let isSubmitting = false;

            // Prevent double binding
            if (btnSimpan.dataset.bound === "true") return;
            btnSimpan.dataset.bound = "true";

            btnSimpan.addEventListener('click', function() {
                if (isSubmitting) return;
                isSubmitting = true;

                const obatId = document.getElementById('obat_id_penerimaan').value;
                const jumlahMasuk = document.getElementById('jumlah_masuk').value;
                const tanggalMasuk = document.getElementById('tanggal_masuk').value;
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const route = document.querySelector('meta[name="route-penerimaan-obat-store"]')
                    .getAttribute('content');

                if (!obatId || !jumlahMasuk || !tanggalMasuk) {
                    alert('❗ Semua kolom wajib diisi.');
                    isSubmitting = false;
                    return;
                }

                fetch(route, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            obat_id: obatId,
                            jumlah_masuk: jumlahMasuk,
                            tanggal_masuk: tanggalMasuk
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        isSubmitting = false;

                        // Pastikan `data.success` benar boolean true
                        if (data.success === true) {
                            alert(data.message || '✅ Stok berhasil ditambahkan.');
                            location.reload();
                        } else {
                            alert('❌ Gagal menambahkan stok: ' + (data.message ||
                                'Terjadi kesalahan.'));
                        }
                    })
                    .catch(error => {
                        isSubmitting = false;
                        console.error('❌ Error:', error);
                        alert('❌ Terjadi kesalahan saat mengirim data ke server.');
                    });
            });
        });
    </script>

    {{-- Mencegah nilai negaitf --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.daily-input').forEach(function(input) {
                input.addEventListener('input', function() {
                    if (parseInt(this.value) < 0) {
                        this.value = 0;
                    }
                });
            });
        });
    </script>


@endsection