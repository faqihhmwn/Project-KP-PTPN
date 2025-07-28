@extends('layout.app')
@section('title', 'Rekapitulasi Obat Bulanan')

@section('content')

    <style>
        .table-container {
            background-color: white;
            padding: 20px;
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
        <a href="/obat/dashboard" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Farmasi
        </a>
    </div>

    <!-- Controls -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <!-- Filter Bulan/Tahun -->
                <div class="col-md-6">
                    <form method="GET" class="d-flex gap-2" id="filterForm">
                        <select name="bulan" class="form-select" id="bulanSelect">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(null, $i, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        <select name="tahun" class="form-select" id="tahunSelect">
                            @php
                                $currentYear = 2025;
                                $endYear = $currentYear + 10;
                            @endphp
                            @for ($year = $currentYear; $year <= $endYear; $year++)
                                <option value="{{ $year }}" {{ $tahun == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <div id="rekapNotif" class="alert d-none mb-3"></div>
        <!-- Tambahkan CSRF Token untuk keamanan -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <table>
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Nama Obat</th>
                    <th rowspan="2">Jenis</th>
                    <th rowspan="2">Harga Satuan</th>
                    <th rowspan="2">Stok Awal</th>
                    <th rowspan="2">Unit</th>
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
                    <tr data-obat-name="{{ strtolower($obat->nama_obat ?? '') }}"
                        data-obat-jenis="{{ strtolower($obat->jenis_obat ?? '') }}" data-obat-row="{{ $obat->id }}"
                        data-harga="{{ $obat->harga_satuan ?? 0 }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $obat->nama_obat }}</td>
                        <td>{{ $obat->jenis_obat ?? '-' }}</td>
                        <td>Rp {{ number_format($obat->harga_satuan ?? 0, 0, ',', '.') }}</td>
                        @php
                            // Ambil sisa stok dari bulan sebelumnya
                            $bulanSebelumnya = $bulan == 1 ? 12 : $bulan - 1;
                            $tahunSebelumnya = $bulan == 1 ? $tahun - 1 : $tahun;

                            $rekapBulanSebelumnya = \App\Models\RekapitulasiObat::where('obat_id', $obat->id)
                                ->where('unit_id', Auth::user()->unit_id)
                                ->where('bulan', $bulanSebelumnya)
                                ->where('tahun', $tahunSebelumnya)
                                ->orderBy('tanggal', 'desc')
                                ->first();

                            $stokAwal = $rekapBulanSebelumnya ? $rekapBulanSebelumnya->sisa_stok : $obat->stok_awal;
                        @endphp
                        <td class="stok-awal" data-obat-id="{{ $obat->id }}">
                            {{ $obat->stokAwal($bulan, $tahun) }}
                        </td>
                        <td>{{ $obat->unit->nama ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::createFromDate(null, $bulan, 1)->format('F') }}</td>
                        <td>{{ $tahun }}</td>
                        @php $totalBiaya = 0; @endphp
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $jumlahKeluar = 0;
                                $tanggal = \Carbon\Carbon::createFromDate($tahun, (int) $bulan, $day);
                                $rekapitulasi = \App\Models\RekapitulasiObat::where('obat_id', $obat->id)
                                    ->where('unit_id', Auth::user()->unit_id)
                                    ->where('tanggal', $tanggal->format('Y-m-d'))
                                    ->where('bulan', $bulan)
                                    ->where('tahun', $tahun)
                                    ->first();

                                if ($rekapitulasi) {
                                    $jumlahKeluar = $rekapitulasi->jumlah_keluar;
                                }
                                $totalBiaya += $jumlahKeluar * ($obat->harga_satuan ?? 0);
                            @endphp
                            <td>
                                <input type="number" class="daily-input" type="text" inputmode="numeric"
                                    pattern="[0-9]*" value="{{ $jumlahKeluar }}" data-obat-id="{{ $obat->id }}"
                                    data-tanggal="{{ $tanggal->format('Y-m-d') }}">
                            </td>
                        @endfor
                        <td class="sisa-stok" id="sisa-stok-{{ $obat->id }}">
                            {{ $obat->stokSisa() }}
                        </td>
                        <td class="total-biaya" id="total-biaya-{{ $obat->id }}"><strong>Rp
                                {{ number_format($totalBiaya, 0, ',', '.') }}</strong></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('obat.rekapitulasi.detail', ['obat' => $obat->id]) }}?bulan={{ $bulan }}&tahun={{ $tahun }}"
                                    class="btn btn-info btn-sm" title="Detail Rekapitulasi">
                                    <i class="fas fa-chart-bar"></i>
                                </a>

                                <a href="{{ route('obat.edit', ['obat' => $obat->id, 'return_url' => url()->current()]) }}"
                                    class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('obat.destroy', $obat) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('⚠️ PERINGATAN!\n\nApakah Anda yakin ingin MENGHAPUS PERMANEN obat ini?\n\n📌 {{ $obat->nama_obat }}\n\n❌ Semua data transaksi terkait juga akan dihapus!\n✅ Tindakan ini TIDAK BISA dibatalkan!\n\nKetik OK jika yakin:')"
                                        title="Hapus Permanen">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 11 + $daysInMonth }}" style="text-align: center; padding: 20px;">
                            Belum ada data obat untuk bulan {{ \Carbon\Carbon::create()->month($bulan)->format('F') }}
                            {{ $tahun }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="d-flex justify-content-end align-items-center gap-2 mt-3">
            <button id="validasiBulanBtn" class="btn btn-success">
                <i class="fas fa-lock"></i> Validasi Data Bulan Ini
            </button>
            <button id="unvalidasiBulanBtn" class="btn btn-warning d-none">
                <i class="fas fa-unlock"></i> Batalkan Validasi
            </button>
            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button id="simpanRekapBtn" class="btn btn-primary ms-2">
                <i class="fas fa-save"></i> Simpan Rekapitulasi
            </button>
        </div>
        <div id="validasiInfo" class="alert alert-success mt-3 d-none">
            <i class="fas fa-lock"></i> Data bulan ini telah divalidasi dan dikunci. Semua input, edit, dan hapus
            dinonaktifkan untuk menjaga integritas laporan.
            <button class="btn btn-sm btn-warning float-end" id="unvalidasiBtnInAlert">
                <i class="fas fa-unlock"></i> Batalkan Validasi
            </button>
        </div>
    </div>

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
                <form action="{{ route('obat.export') }}" method="GET" id="exportForm" target="_blank">
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
                                        name="include_daily" value="1">
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
                                <li>Data harian hanya akan disertakan jika range kurang dari 32 hari</li>
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
            row.querySelectorAll('.daily-input').forEach(input => {
                totalKeluar += parseInt(input.value) || 0;
            });
            const sisaStok = stokAwal - totalKeluar;
            const sisaStokCell = row.querySelector('.sisa-stok');
            if (sisaStokCell) {
                sisaStokCell.textContent = sisaStok < 0 ? 0 : sisaStok;
            }
        }

        // Update total biaya secara dinamis saat input harian berubah
        function updateTotalBiaya(obatId) {
            const row = document.querySelector(`tr[data-obat-row='${obatId}']`);
            if (!row) return;
            const harga = parseInt(row.getAttribute('data-harga')) || 0;
            let totalBiaya = 0;
            row.querySelectorAll('.daily-input').forEach(input => {
                const jumlahKeluar = parseInt(input.value) || 0;
                totalBiaya += jumlahKeluar * harga;
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
                    if (confirm(
                            'Setelah divalidasi, semua data bulan ini akan dikunci dan tidak dapat diubah. Lanjutkan?'
                        )) {
                        localStorage.setItem(lockKey, '1');
                        setLockedState(true);
                    }
                });
            }

            // Add event listeners for unvalidation buttons
            if (unvalidasiBtn) {
                unvalidasiBtn.addEventListener('click', handleUnvalidasi);
            }
            if (unvalidasiBtnInAlert) {
                unvalidasiBtnInAlert.addEventListener('click', handleUnvalidasi);
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
                const response = await fetch('{{ route('obat.rekapitulasi-obat.input-harian') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        bulk: bulk,
                        bulan: CURRENT_BULAN,
                        tahun: CURRENT_TAHUN
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
                        '?bulan=' + CURRENT_BULAN +
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


@endsection
