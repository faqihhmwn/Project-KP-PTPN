<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use App\Models\Unit;
use App\Models\TransaksiObat;
use App\Models\RekapitulasiObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ObatImport;
use App\Exports\ObatExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ObatController extends Controller
{
    /**
     * Menampilkan daftar obat.
     * Filter berdasarkan unit_id pengguna yang login dan pencarian.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Obat::query();

        // Filter berdasarkan unit_id user yang login
        $query->where('unit_id', auth()->user()->unit_id);

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama_obat', 'like', "%{$request->search}%")
                  ->orWhere('jenis_obat', 'like', "%{$request->search}%");
            });
        }

        $obats = $query->orderBy('nama_obat')->paginate(10);

        // Jika request adalah AJAX, kembalikan partial view
        if ($request->ajax()) {
            return view('partials.obat-table', compact('obats'))->render();
        }

        // Menggunakan 'obats.index' untuk konsistensi nama view
        return view('obats.index', compact('obats'));
    }

    /**
     * Menampilkan form untuk membuat obat baru.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Menggunakan 'obats.create' untuk konsistensi nama view
        return view('obats.create');
    }

    /**
     * Menyimpan obat baru ke database.
     * Memproses stok awal sebagai transaksi 'masuk' jika disediakan.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
     * Mendapatkan data rekapitulasi obat untuk bulan tertentu
     *
     * @param Obat $obat
     * @param int $bulan
     * @param int $tahun
     * @return array
     */
    private function getRekapitulasiData(Obat $obat, $bulan, $tahun)
    {
        $rekapitulasi = $obat->rekapitulasiObat()
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get();

        return [
            'stok_awal' => $obat->getStokAwal($bulan, $tahun),
            'jumlah_keluar' => $rekapitulasi->sum('jumlah_keluar'),
            'total_biaya' => $rekapitulasi->sum('total_biaya'),
            'data_harian' => $rekapitulasi->keyBy('tanggal')
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_obat' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Check if medicine with same name exists in the same unit
                    $exists = Obat::where('nama_obat', $value)
                        ->where('unit_id', Auth::user()->unit_id)
                        ->exists();
                    
                    if ($exists) {
                        $fail('Obat dengan nama ini sudah ada di unit Anda.');
                    }
                },
            ],
            'jenis_obat' => 'nullable|string|max:255',
            'harga_satuan' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:50',
            'stok_awal' => 'nullable|integer|min:0', // Input dari form "Stok Awal" untuk obat baru
            'keterangan' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();

            // Buat obat baru
            // Pastikan 'stok_awal' dari form disimpan ke kolom stok_awal di tabel obats
            // Dan 'stok_terakhir' diinisialisasi dengan stok_awal
            $obat = Obat::create([
                'nama_obat' => ucwords(strtolower($request->nama_obat)), // Proper case (huruf pertama tiap kata kapital)
                'jenis_obat' => ucwords(strtolower($request->jenis_obat)), // Proper case (huruf pertama tiap kata kapital)
                'harga_satuan' => $request->harga_satuan,
                'satuan' => $request->satuan,
                'keterangan' => $request->keterangan,
                'unit_id' => Auth::user()->unit_id,
                'stok_awal' => $request->stok_awal ?? 0, // Simpan stok_awal dari form
                'stok_terakhir' => $request->stok_awal ?? 0, // Inisialisasi stok_terakhir dengan stok_awal
            ]);

            // Jika ada stok_awal yang diinput dan lebih dari 0, catat sebagai transaksi masuk awal
            // Ini akan memperbarui rekapitulasi dan stok_terakhir lagi, tapi tidak masalah
            if ($request->filled('stok_awal') && $request->stok_awal > 0) {
                // Gunakan tanggal saat ini untuk transaksi stok awal
                $this->processStockChange(
                    $obat,
                    'masuk', // Jenis transaksi: 'masuk'
                    $request->stok_awal, // Jumlah yang masuk
                    'Stok Awal Obat Baru', // Keterangan transaksi
                    Carbon::now()->toDateString() // Teruskan tanggal saat ini
                );
            }

            DB::commit();

            return redirect()->route('obats.index')->with('success', 'Obat berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding new obat: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan obat: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail obat.
     *
     * @param Obat $obat
     * @return \Illuminate\View\View
     */
    public function show(Obat $obat)
    {
        // Verifikasi bahwa obat milik unit yang sama dengan user yang login
        if ($obat->unit_id !== auth()->user()->unit_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $now = Carbon::now();
        
        // Ambil info penggunaan bulan ini (gunakan metode dari Model Obat)
        $infoPenggunaanBulanIni = $obat->getInfoPenggunaan($now->month, $now->year);
        // Ambil info penggunaan bulan lalu (gunakan metode dari Model Obat)
        $infoPenggunaanBulanLalu = $obat->getInfoPenggunaan($now->copy()->subMonth()->month, $now->copy()->subMonth()->year);

        // Menggunakan 'obats.show' untuk konsistensi nama view
        return view('obats.show', compact(
            'obat',
            'infoPenggunaanBulanIni', // Kirim langsung array info
            'infoPenggunaanBulanLalu' // Kirim langsung array info
        ));
    }

    /**
     * Menampilkan form untuk mengedit obat.
     *
     * @param Obat $obat
     * @return \Illuminate\View\View
     */
    public function edit(Obat $obat)
    {
        // Pastikan obat milik unit user yang login
        if ($obat->unit_id !== Auth::user()->unit_id) {
            abort(403, 'Unauthorized action.');
        }
        // Menggunakan 'obats.edit' untuk konsistensi nama view
        return view('obats.edit', compact('obat'));
    }

    /**
     * Memperbarui informasi obat dan memproses transaksi stok.
     *
     * @param Request $request
     * @param Obat $obat
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Obat $obat)
    {
        // Pastikan obat milik unit user yang login
        if ($obat->unit_id !== Auth::user()->unit_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'nama_obat' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($obat) {
                    // Check if medicine with same name exists in the same unit, excluding current medicine
                    $exists = Obat::where('nama_obat', $value)
                        ->where('unit_id', Auth::user()->unit_id)
                        ->where('id', '!=', $obat->id)
                        ->exists();
                    
                    if ($exists) {
                        $fail('Obat dengan nama ini sudah ada di unit Anda.');
                    }
                },
            ],
            'jenis_obat' => 'nullable|string|max:255',
            'harga_satuan' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:50',
            'keterangan' => 'nullable|string',
            // Validasi untuk transaksi stok
            'jumlah_masuk' => 'nullable|integer|min:0',
            'jumlah_keluar' => 'nullable|integer|min:0',
            'tanggal_transaksi' => 'required|date', // Tanggal transaksi harus selalu ada
            'keterangan_transaksi' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Update informasi dasar obat
            $obat->update([
                'nama_obat' => ucwords(strtolower($validated['nama_obat'])), // Proper case (huruf pertama tiap kata kapital)
                'jenis_obat' => ucwords(strtolower($validated['jenis_obat'])), // Proper case (huruf pertama tiap kata kapital)
                'harga_satuan' => $validated['harga_satuan'],
                'satuan' => $validated['satuan'],
                'keterangan' => $validated['keterangan'],
            ]);

            $jumlahMasuk = (int) $request->input('jumlah_masuk', 0);
            $jumlahKeluar = (int) $request->input('jumlah_keluar', 0);
            $tanggalTransaksi = $request->input('tanggal_transaksi');
            $keteranganTransaksi = $request->input('keterangan_transaksi');

            // Logika validasi stok di sisi server (jika diperlukan selain JS di frontend)
            if ($jumlahMasuk > 0 && $jumlahKeluar > 0) {
                throw new \Exception('Tidak bisa menambah dan mengurangi stok sekaligus dalam satu transaksi.');
            }
            if ($jumlahKeluar > $obat->stok_terakhir) {
                throw new \Exception('Jumlah keluar tidak boleh melebihi stok saat ini.');
            }

            // Proses transaksi masuk jika ada
            if ($jumlahMasuk > 0) {
                $this->processStockChange(
                    $obat,
                    'masuk',
                    $jumlahMasuk,
                    $keteranganTransaksi . ' (Update)', // Tambahkan keterangan dari form
                    $tanggalTransaksi // Teruskan tanggal transaksi
                );
            }

            // Proses transaksi keluar jika ada
            if ($jumlahKeluar > 0) {
                $this->processStockChange(
                    $obat,
                    'keluar',
                    $jumlahKeluar,
                    $keteranganTransaksi . ' (Update)', // Tambahkan keterangan dari form
                    $tanggalTransaksi // Teruskan tanggal transaksi
                );
            }

            DB::commit();
            return redirect()->route('obats.index')
                ->with('success', 'Informasi obat dan transaksi stok berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating obat info: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui informasi obat: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus obat dari database.
     * Juga menghapus semua transaksi dan rekapitulasi terkait.
     *
     * @param Obat $obat
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Obat $obat)
    {
        // Verifikasi bahwa obat milik unit yang sama dengan user
        if ($obat->unit_id !== auth()->user()->unit_id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            // Hapus semua transaksi dan rekapitulasi terkait terlebih dahulu
            $obat->transaksiObats()->delete(); // Hapus semua transaksi
            $obat->rekapitulasiObat()->delete(); // Hapus semua rekapitulasi

            // Hapus obat
            $obat->delete();
            
            DB::commit(); // Commit transaction
            return redirect()->route('obats.index')
                ->with('success', 'Obat dan semua data terkait berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction
            Log::error('Error deleting obat: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('obats.index')
                ->with('error', 'Gagal menghapus obat: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan rekapitulasi penggunaan obat bulanan.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function rekapitulasiBulanan(Request $request)
    {
        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);

        // Ambil semua obat untuk unit user yang login
        $obats = Obat::where('unit_id', Auth::user()->unit_id)
            ->orderBy('nama_obat')
            ->get(); // Ambil semua obat, rekapitulasi akan di-load per obat nanti
            
        if ($obats->isEmpty()) {
            // Menggunakan 'obats.rekapitulasi' untuk konsistensi nama view
            return view('obats.rekapitulasi', [
                'rekapitulasiDataFrontend' => [],
                'bulan' => $bulan,
                'tahun' => $tahun,
                'daysInMonth' => Carbon::create($tahun, $bulan, 1)->daysInMonth,
                'obats' => collect() // Kirim koleksi kosong jika tidak ada data
            ]);
        }

        $rekapitulasiDataFrontend = []; // Ini adalah array yang akan dikirim ke view

        $daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth;

        foreach ($obats as $obat) {
            $dataHarianKeluar = []; // Untuk menyimpan 'jumlah_keluar' per tanggal
            $dataHarianMasuk = []; // Untuk menyimpan 'jumlah_masuk_hari_ini' per tanggal

            // Inisialisasi data harian dengan 0 untuk semua tanggal di bulan
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $dataHarianKeluar[$i] = 0;
                $dataHarianMasuk[$i] = 0;
            }

            // Dapatkan semua entri rekapitulasi bulanan untuk obat ini
            $rekapBulananEntries = RekapitulasiObat::where('obat_id', $obat->id)
                ->where('unit_id', Auth::user()->unit_id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->orderBy('tanggal', 'asc')
                ->get();

            // Isi data harian jumlah_keluar dan jumlah_masuk_hari_ini
            foreach ($rekapBulananEntries as $rekap) {
                $day = Carbon::parse($rekap->tanggal)->day;
                $dataHarianKeluar[$day] = $rekap->jumlah_keluar;
                $dataHarianMasuk[$day] = $rekap->jumlah_masuk_hari_ini;
            }

            // --- PERBAIKAN LOGIKA STOK AWAL BULAN ---
            $stokAwalBulan = 0;
            $firstDayOfCurrentMonth = Carbon::create($tahun, $bulan, 1)->toDateString();
            
            // 1. Coba cari entri rekapitulasi untuk hari pertama bulan ini
            $firstRekapEntryToday = RekapitulasiObat::where('obat_id', $obat->id)
                ->where('unit_id', Auth::user()->unit_id)
                ->whereDate('tanggal', $firstDayOfCurrentMonth)
                ->first();

            if ($firstRekapEntryToday) {
                // Jika ada entri untuk hari pertama bulan ini, gunakan stok_awal dari entri tersebut
                $stokAwalBulan = $firstRekapEntryToday->stok_awal;
            } else {
                // 2. Jika tidak ada entri untuk hari pertama bulan ini, cari entri rekapitulasi terakhir di bulan sebelumnya
                $lastDayOfPreviousMonth = Carbon::create($tahun, $bulan, 1)->subDay()->toDateString();
                $lastRekapEntryPreviousMonth = RekapitulasiObat::where('obat_id', $obat->id)
                    ->where('unit_id', Auth::user()->unit_id)
                    ->whereDate('tanggal', '<=', $lastDayOfPreviousMonth) // Cari yang terakhir sebelum bulan ini
                    ->orderBy('tanggal', 'desc')
                    ->first();

                if ($lastRekapEntryPreviousMonth) {
                    // Jika ada entri di bulan sebelumnya, stok awal bulan ini adalah sisa stok akhir bulan sebelumnya
                    $stokAwalBulan = $lastRekapEntryPreviousMonth->sisa_stok;
                } else {
                    // 3. Jika tidak ada rekapitulasi sama sekali di bulan ini maupun bulan sebelumnya,
                    // gunakan stok_terakhir dari tabel 'obats' itu sendiri (ini untuk obat baru atau bulan pertama transaksi)
                    $stokAwalBulan = $obat->stok_terakhir; 
                }
            }
            // --- AKHIR PERBAIKAN LOGIKA STOK AWAL BULAN ---


            // Hitung Sisa Stok Akhir Bulan
            $sisaStokAkhirBulanEntry = $rekapBulananEntries->sortByDesc('tanggal')->first();
            if ($sisaStokAkhirBulanEntry) {
                $sisaStokAkhirBulan = $sisaStokAkhirBulanEntry->sisa_stok;
            } else {
                // Jika tidak ada rekapitulasi sama sekali di bulan ini,
                // sisa stok akhir bulan diasumsikan sama dengan stok awal bulan.
                $sisaStokAkhirBulan = $stokAwalBulan;
            }

            // Hitung Total Biaya Bulan Ini (menggunakan method di model Obat)
            $infoPenggunaanBulanIni = $obat->getInfoPenggunaan($bulan, $tahun);
            $totalBiayaBulan = $infoPenggunaanBulanIni['biaya'];

            // === PENTING: Map ke variabel yang diharapkan view Anda ===
            $rekapitulasiDataFrontend[] = [
                'id' => $obat->id,
                'Nama Obat' => $obat->nama_obat,
                'Jenis' => $obat->jenis_obat,
                'Harga Satuan' => $obat->harga_satuan, // Pastikan ini di-format di view jika perlu
                'Stok Awal' => $stokAwalBulan, // Menggunakan stokAwalBulan yang sudah dihitung
                'data_keluar_per_tanggal' => $dataHarianKeluar,
                'data_masuk_per_tanggal' => $dataHarianMasuk,
                'Sisa Stok' => $sisaStokAkhirBulan,
                'Total Biaya' => $totalBiayaBulan,
                'obat_obj' => $obat, // Opsional: kirim objek obat penuh jika ada aksi di view yang butuh ini
            ];
        }

        // Menggunakan 'obats.rekapitulasi' untuk konsistensi nama view
        return view('obats.rekapitulasi', [
            'rekapitulasiDataFrontend' => $rekapitulasiDataFrontend,
            'bulan' => $bulan,
            'bulanNama' => Carbon::createFromDate($tahun, $bulan, 1)->format('F'), // Tambahkan nama bulan
            'tahun' => $tahun,
            'daysInMonth' => $daysInMonth,
            'obats' => $obats // Mengirim koleksi obat ke view
        ]);
    }

    /**
     * Memproses perubahan stok manual (misalnya dari halaman edit obat).
     *
     * @param Request $request
     * @param Obat $obat
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStokManual(Request $request, Obat $obat)
    {
        // Pastikan obat milik unit user yang login
        if ($obat->unit_id !== Auth::user()->unit_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'jumlah' => 'required|integer|min:1',
            'tipe_perubahan' => 'required|in:masuk,keluar,penyesuaian',
            'referensi' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $this->processStockChange(
                $obat,
                $request->tipe_perubahan,
                $request->jumlah,
                $request->referensi ?? 'Penyesuaian Stok Manual'
            );

            DB::commit();

            return redirect()->back()->with('success', 'Stok obat berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating stock manually for obat ID ' . $obat->id . ': ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Gagal memperbarui stok: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan dashboard obat.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        try {
            // Hitung total obat berdasarkan unit_id user yang login
            $totalObat = Obat::where('unit_id', auth()->user()->unit_id)->count();
        } catch (\Exception $e) {
            // Fallback values if database error
            Log::error('Error fetching total obat for dashboard: ' . $e->getMessage());
            $totalObat = 0;
        }
        // Menggunakan 'obats.dashboard' untuk konsistensi nama view
        return view('obats.dashboard', compact('totalObat'));
    }

    /**
     * Mengimpor data obat dari file Excel/CSV.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new ObatImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data obat berhasil diimpor.');
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    /**
     * Memproses input stok harian (biasanya dari tabel rekapitulasi).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function inputHarian(Request $request)
    {
        try {
            $request->validate([
                'obat_id' => 'required|exists:obats,id',
                'tanggal' => 'required|date',
                'stok_keluar' => 'required|integer|min:0',
                'stok_masuk' => 'required|integer|min:0',
            ]);

            DB::beginTransaction();

            $obat = Obat::findOrFail($request->obat_id);

            // Proses stok keluar jika ada
            if ($request->stok_keluar > 0) {
                $this->processStockChange(
                    $obat,
                    'keluar',
                    $request->stok_keluar,
                    'Input Manual Harian (Keluar)',
                    $request->tanggal // Teruskan tanggal transaksi
                );
            }

            // Proses stok masuk jika ada
            if ($request->stok_masuk > 0) {
                $this->processStockChange(
                    $obat,
                    'masuk',
                    $request->stok_masuk,
                    'Input Manual Harian (Masuk)',
                    $request->tanggal // Teruskan tanggal transaksi
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in inputHarian: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mengekspor data obat ke Excel.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'include_daily' => 'nullable|boolean'
        ]);

        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $includeDailyData = $request->boolean('include_daily', false);
            
            // Validasi maksimal 3 bulan
            if ($startDate->diffInMonths($endDate) > 3) {
                return redirect()->back()->with('error', 'Range tanggal maksimal 3 bulan.');
            }

            $filename = "laporan-obat-{$startDate->format('Y-m-d')}-to-{$endDate->format('Y-m-d')}.xlsx";
            
            return Excel::download(
                new ObatExport($startDate, $endDate, $includeDailyData), 
                $filename
            );
            
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Gagal mengexport data: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail rekapitulasi harian untuk obat tertentu.
     *
     * @param Request $request
     * @param Obat $obat
     * @return \Illuminate\View\View
     */
    public function showRekapitulasi(Request $request, Obat $obat)
    {
        // Pastikan obat milik unit user yang login
        if ($obat->unit_id !== Auth::user()->unit_id) {
            abort(403, 'Unauthorized action.');
        }

        // Get bulan & tahun from request or use current
        $bulan = $request->get('bulan', Carbon::now()->month);
        $tahun = $request->get('tahun', Carbon::now()->year);

        // Get rekap harian for selected month for this specific obat
        // Gunakan metode getRekapitulasiBulanan dari model Obat
        $rekapHarian = $obat->getRekapitulasiBulanan($bulan, $tahun);

        // Ambil info penggunaan bulan ini dan bulan lalu untuk tampilan di detail rekap
        $infoPenggunaanBulanIni = $obat->getInfoPenggunaan($bulan, $tahun);
        $infoPenggunaanBulanLalu = $obat->getInfoPenggunaan(Carbon::create($tahun, $bulan, 1)->subMonth()->month, Carbon::create($tahun, $bulan, 1)->subMonth()->year);


        // Menggunakan 'obats.detail_rekapitulasi' untuk konsistensi nama view
        return view('obats.detail_rekapitulasi', [
            'obat' => $obat,
            'rekapHarian' => $rekapHarian,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'penggunaanBulanIni' => $infoPenggunaanBulanIni['jumlah'], // Untuk kompatibilitas dengan view
            'totalBiayaBulanIni' => $infoPenggunaanBulanIni['biaya'], // Untuk kompatibilitas dengan view
            'penggunaanBulanLalu' => $infoPenggunaanBulanLalu['jumlah'], // Untuk kompatibilitas dengan view
            'totalBiayaBulanLalu' => $infoPenggunaanBulanLalu['biaya'], // Untuk kompatibilitas dengan view
            'lastUpdateBulanIni' => $infoPenggunaanBulanIni['last_update'], // Untuk kompatibilitas dengan view
            'lastUpdateBulanLalu' => $infoPenggunaanBulanLalu['last_update'], // Untuk kompatibilitas dengan view
        ]);
    }

    // --- Private Methods (Helper Functions) ---

    /**
     * Memproses perubahan stok (masuk, keluar, penyesuaian).
     * Akan mencatat TransaksiObat, memperbarui RekapitulasiObat harian,
     * dan memperbarui stok_terakhir di tabel Obat.
     *
     * @param Obat $obat
     * @param string $tipeTransaksi 'masuk', 'keluar', 'penyesuaian'
     * @param int $jumlah Jumlah obat yang berubah. Untuk penyesuaian, bisa negatif untuk pengurangan.
     * @param string $keterangan Keterangan transaksi (misal: "Penjualan ke Pasien X", "Penerimaan PO Y")
     * @param string|null $tanggalTransaksi Tanggal transaksi, default hari ini.
     * @return void
     * @throws \Exception Jika stok tidak mencukupi untuk transaksi 'keluar'.
     */
    private function processStockChange(Obat $obat, string $tipeTransaksi, int $jumlah, string $keterangan, ?string $tanggalTransaksi = null)
    {
        $currentDate = $tanggalTransaksi ? Carbon::parse($tanggalTransaksi)->toDateString() : Carbon::now()->toDateString();
        $userUnitId = Auth::user()->unit_id; // Ambil unit_id user sekali saja

        // Pastikan obat milik unit yang sedang login
        if ($obat->unit_id !== $userUnitId) {
            throw new \Exception('Obat tidak ditemukan di unit Anda.');
        }

        // 1. Validasi stok untuk transaksi 'keluar' atau 'penyesuaian' pengurangan
        if ($tipeTransaksi == 'keluar') {
            if ($obat->stok_terakhir < $jumlah) {
                throw new \Exception('Stok tidak mencukupi untuk transaksi keluar ini. Stok saat ini: ' . $obat->stok_terakhir);
            }
        } elseif ($tipeTransaksi == 'penyesuaian' && $jumlah < 0) { // Jika penyesuaian pengurangan
            if ($obat->stok_terakhir < abs($jumlah)) {
                throw new \Exception('Stok tidak mencukupi untuk penyesuaian pengurangan. Stok saat ini: ' . $obat->stok_terakhir);
            }
        }

        // 2. Catat Transaksi di tabel transaksi_obats
        TransaksiObat::create([
            'obat_id' => $obat->id,
            'tanggal_transaksi' => $currentDate, // Menggunakan tanggal transaksi yang diteruskan
            'tipe_transaksi' => $tipeTransaksi, // Menggunakan 'tipe_transaksi' agar konsisten dengan DB
            'jumlah' => $jumlah,
            'keterangan' => $keterangan, // Simpan keterangan transaksi
            'user_id' => Auth::id(), // Simpan ID pengguna yang melakukan transaksi
        ]);

        // 3. Perbarui atau buat Rekapitulasi Harian di tabel rekapitulasi_obats
        $rekapitulasi = RekapitulasiObat::where('obat_id', $obat->id)
            ->where('unit_id', $userUnitId)
            ->whereDate('tanggal', $currentDate)
            ->first();

        if (!$rekapitulasi) {
            // Jika belum ada entri rekapitulasi untuk hari ini, buat yang baru
            // Untuk transaksi stok awal, gunakan nilai stok awal yang diinput
            $isStokAwal = $tipeTransaksi === 'masuk' && $keterangan === 'Stok Awal Obat Baru';
            
            if ($isStokAwal) {
                $stokAwalHariIni = $jumlah; // Gunakan jumlah yang diinput sebagai stok awal
            } else {
                // Untuk transaksi lain, hitung dari hari sebelumnya
                $stokAwalHariIni = $this->getStokAkhirHariSebelumnya($obat, $currentDate);
            }

            $rekapitulasi = new RekapitulasiObat([
                'obat_id' => $obat->id,
                'unit_id' => $userUnitId,
                'tanggal' => $currentDate,
                'stok_awal' => $stokAwalHariIni,
                'jumlah_masuk_hari_ini' => 0,
                'jumlah_keluar' => 0,
                'sisa_stok' => $stokAwalHariIni, // Awalnya sisa stok sama dengan stok awal
                'total_biaya' => 0,
                'bulan' => Carbon::parse($currentDate)->month,
                'tahun' => Carbon::parse($currentDate)->year,
            ]);
        }

        // Perbarui jumlah masuk/keluar dan sisa_stok di entri rekapitulasi
        if ($tipeTransaksi == 'masuk') {
            $rekapitulasi->jumlah_masuk_hari_ini += $jumlah;
            $rekapitulasi->sisa_stok += $jumlah;
        } elseif ($tipeTransaksi == 'keluar') {
            $rekapitulasi->jumlah_keluar += $jumlah;
            $rekapitulasi->sisa_stok -= $jumlah;
            $rekapitulasi->total_biaya += ($jumlah * $obat->harga_satuan);
        } elseif ($tipeTransaksi == 'penyesuaian') {
            if ($jumlah > 0) { // Jika jumlah positif, berarti penambahan
                $rekapitulasi->jumlah_masuk_hari_ini += $jumlah;
            } else { // Jika jumlah negatif, berarti pengurangan
                $rekapitulasi->jumlah_keluar += abs($jumlah); // Tambahkan nilai absolut ke jumlah_keluar
                $rekapitulasi->total_biaya += (abs($jumlah) * $obat->harga_satuan); // Hitung biaya untuk pengurangan
            }
            $rekapitulasi->sisa_stok += $jumlah; // Langsung tambahkan/kurangi jumlah (bisa positif/negatif)
        }

        $rekapitulasi->save();

        // 4. Perbarui stok_terakhir di tabel Obat (ini adalah single source of truth untuk stok aktual)
        $obat->stok_terakhir = $rekapitulasi->sisa_stok;
        $obat->save();
    }

    /**
     * Mengambil stok akhir dari hari sebelumnya atau stok_terakhir obat jika belum ada rekapitulasi.
     *
     * @param Obat $obat
     * @param string $currentDate Tanggal saat ini dalam format Y-m-d.
     * @return int
     */
    private function getStokAkhirHariSebelumnya(Obat $obat, string $currentDate)
    {
        $targetDate = Carbon::parse($currentDate)->subDay()->toDateString();
        $userUnitId = Auth::user()->unit_id;

        // Cari entri rekapitulasi untuk hari sebelumnya dari tanggal target
        $rekapSebelumnya = RekapitulasiObat::where('obat_id', $obat->id)
            ->where('unit_id', $userUnitId)
            ->whereDate('tanggal', '<=', $targetDate) // Cari tanggal <= targetDate
            ->orderBy('tanggal', 'desc') // Ambil yang paling baru
            ->first();

        if ($rekapSebelumnya) {
            return $rekapSebelumnya->sisa_stok; // Stok akhir sebelumnya adalah stok awal hari ini
        }

        // Jika belum ada rekapitulasi sama sekali sebelum tanggal target,
        // Ambil stok_terakhir dari tabel 'obats' itu sendiri
        return $obat->stok_terakhir ?? 0;
    }

    // Metode untuk menambah stok (jika masih digunakan, disarankan pakai updateStokManual)
    // public function tambahStok(Request $request, Obat $obat)
    // {
    //     // Anda bisa memanggil processStockChange di sini
    //     // $this->processStockChange($obat, 'masuk', $request->jumlah_tambah, $request->keterangan);
    //     // return redirect()->back()->with('success', 'Stok berhasil ditambahkan.');
    // }
}
