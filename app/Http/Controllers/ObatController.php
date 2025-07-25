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
class ObatController extends Controller
{
    public function index(Request $request)
    {
        // Load data obat dengan rekapitulasi terbaru
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

        // Get obats with their latest rekapitulasi
        $obats = $query->withLastRekapitulasi()
                      ->orderBy('nama_obat')
                      ->paginate(10);

        // Update stok_sisa for each obat based on latest rekapitulasi
        foreach ($obats as $obat) {
            if ($obat->last_rekapitulasi) {
                $obat->stok_sisa = $obat->last_rekapitulasi->sisa_stok;
                $obat->save();
            }
        }

        // Update stok_sisa untuk setiap obat berdasarkan rekapitulasi terbaru
        foreach ($obats as $obat) {
            $rekapTerbaru = $obat->rekapitulasiObat->first();
            if ($rekapTerbaru) {
                $obat->update(['stok_sisa' => $rekapTerbaru->sisa_stok]);
            }
        }

        if ($request->ajax()) {
            return view('partials.obat-table', compact('obats'))->render();
        }

        return view('obat.index', compact('obats'));
    }

    public function create()
    {
        return view('obat.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_obat' => 'required|string|max:255',
            'jenis_obat' => 'nullable|string|max:255',
            'harga_satuan' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:50',
            'keterangan' => 'nullable|string'
        ]);

        // Tambahkan unit_id dari user yang login dan set stok awal 0
        $validated['unit_id'] = auth()->user()->unit_id;
        $validated['stok_awal'] = 0;
        $validated['stok_sisa'] = 0;
        $validated['stok_masuk'] = 0;
        $validated['stok_keluar'] = 0;

        // Format nama obat menjadi kapital semua
        $validated['nama_obat'] = strtoupper($validated['nama_obat']);

        // Cek apakah nama obat sudah ada di unit yang sama
        $obatExists = \App\Models\Obat::where('unit_id', auth()->user()->unit_id)
            ->whereRaw('UPPER(nama_obat) = ?', [$validated['nama_obat']])
            ->exists();
            
        if ($obatExists) {
            return back()
                ->withInput()
                ->withErrors(['nama_obat' => 'Obat dengan nama tersebut sudah ada di unit Anda!']);
        }

        // Set stok_sisa = stok_awal for new obat (no transactions yet)
        $validated['stok_sisa'] = $validated['stok_awal'];
        $validated['stok_masuk'] = 0;
        $validated['stok_keluar'] = 0;

        \App\Models\Obat::create($validated);

        return redirect()->route('obat.index')
            ->with('success', 'Obat berhasil ditambahkan.');
    }

    public function show(Obat $obat)
    {
        // Verifikasi bahwa obat belongs to unit yang sama dengan user
        if ($obat->unit_id !== auth()->user()->unit_id) {
            abort(403, 'Unauthorized action.');
        }

        // Load relasi rekapitulasiObat
        $obat->load('rekapitulasiObat');
        
        $now = Carbon::now();
        
        // Data bulan ini
        $bulanIni = $obat->rekapitulasiObat()
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->orderBy('tanggal', 'desc')
            ->get();
            
        // Data bulan lalu    
        $bulanLalu = $obat->rekapitulasiObat()
            ->whereMonth('tanggal', $now->copy()->subMonth()->month)
            ->whereYear('tanggal', $now->copy()->subMonth()->year)
            ->orderBy('tanggal', 'desc')
            ->get();
        
        // Ambil tanggal update terakhir bulan ini
        $lastUpdateBulanIni = $obat->rekapitulasiObat()
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->latest('created_at')
            ->first();
            
        // Ambil tanggal update terakhir bulan lalu
        $lastUpdateBulanLalu = $obat->rekapitulasiObat()
            ->whereMonth('tanggal', $now->copy()->subMonth()->month)
            ->whereYear('tanggal', $now->copy()->subMonth()->year)
            ->latest('created_at')
            ->first();
        
        // Hitung total penggunaan bulan ini
        $totalPenggunaanBulanIni = $bulanIni->sum('jumlah_keluar');
        $totalBiayaBulanIni = $totalPenggunaanBulanIni * $obat->harga_satuan;
        
        // Hitung total penggunaan bulan lalu
        $totalPenggunaanBulanLalu = $bulanLalu->sum('jumlah_keluar');
        $totalBiayaBulanLalu = $totalPenggunaanBulanLalu * $obat->harga_satuan;

        return view('obat.show', compact(
            'obat',
            'bulanIni',
            'bulanLalu',
            'totalPenggunaanBulanIni',
            'totalBiayaBulanIni',
            'totalPenggunaanBulanLalu',
            'totalBiayaBulanLalu',
            'lastUpdateBulanIni',
            'lastUpdateBulanLalu'
        ));
    }

    public function edit(Obat $obat)
    {
        return view('obat.edit', compact('obat'));
    }

    public function update(Request $request, Obat $obat)
    {
        $validated = $request->validate([
            'nama_obat' => 'required|string|max:255',
            'jenis_obat' => 'nullable|string|max:255',
            'harga_satuan' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:50',
            'stok_awal' => 'required|integer|min:0',
            'keterangan' => 'nullable|string'
        ]);

        // Get the old stok_awal value before updating
        $oldStokAwal = $obat->stok_awal;

        try {
            DB::beginTransaction();

            // Update basic obat information
            $obat->update($validated);

            // Calculate the difference in stok_awal
            $stokAwalDiff = $validated['stok_awal'] - $oldStokAwal;

            // Update all rekapitulasi records for this obat
            if ($stokAwalDiff != 0) {
                $now = Carbon::now();
                
                // Update current month's rekapitulasi
                $rekapitulasi = RekapitulasiObat::where('obat_id', $obat->id)
                    ->where('unit_id', auth()->user()->unit_id)
                    ->where('bulan', $now->month)
                    ->where('tahun', $now->year)
                    ->orderBy('tanggal', 'asc')
                    ->get();

                foreach ($rekapitulasi as $rekap) {
                    $rekap->stok_awal += $stokAwalDiff;
                    $rekap->sisa_stok = $rekap->stok_awal - $rekap->jumlah_keluar;
                    $rekap->save();
                }

                // If no rekapitulasi exists for current month, create one
                if ($rekapitulasi->isEmpty()) {
                    RekapitulasiObat::create([
                        'obat_id' => $obat->id,
                        'unit_id' => auth()->user()->unit_id,
                        'tanggal' => $now->format('Y-m-d'),
                        'bulan' => $now->month,
                        'tahun' => $now->year,
                        'stok_awal' => $validated['stok_awal'],
                        'jumlah_keluar' => 0,
                        'sisa_stok' => $validated['stok_awal']
                    ]);
                }
            }

            // Update final stok calculations
            $this->updateStokObat($obat);

            DB::commit();
            return redirect()->route('obat.index')
                ->with('success', 'Obat berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui obat: ' . $e->getMessage());
        }
    }

    public function destroy(Obat $obat)
    {
        try {
            // Hapus semua transaksi terkait terlebih dahulu
            $obat->transaksiObats()->delete();
            
            // Hapus obat
            $obat->delete();
            
            return redirect()->route('obat.index')
                ->with('success', 'Obat dan semua transaksi terkait berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('obat.index')
                ->with('error', 'Gagal menghapus obat: ' . $e->getMessage());
        }
    }

    public function rekapitulasi(Request $request)
    {
        $bulan = $request->get('bulan', Carbon::now()->month);
        $tahun = $request->get('tahun', Carbon::now()->year);
        
        // Check if export is requested
        if ($request->get('export') == '1') {
            return $this->exportExcel($request);
        }
        
        $obats = Obat::query()
            ->where('unit_id', auth()->user()->unit_id)
            ->with(['transaksiObats' => function($query) use ($bulan, $tahun) {
                $query->whereMonth('tanggal', $bulan)
                      ->whereYear('tanggal', $tahun);
            }])
            ->with('unit') // Load unit relationship
            ->get();

        // Generate data untuk setiap hari dalam bulan
        $daysInMonth = Carbon::createFromDate($tahun, (int)$bulan, 1)->daysInMonth;
        
        return view('obat.rekapitulasi', compact('obats', 'bulan', 'tahun', 'daysInMonth'));
    }

    // public function addTransaksi(Request $request, Obat $obat)
    // {
    //     $validated = $request->validate([
    //         'tanggal' => 'required|date',
    //         'tipe_transaksi' => 'required|in:masuk,keluar',
    //         'jumlah' => 'required|integer|min:1',
    //         'keterangan' => 'nullable|string',
    //         'petugas' => 'nullable|string'
    //     ]);

    //     $transaksi = new TransaksiObat([
    //         'obat_id' => $obat->id,
    //         'tanggal' => $validated['tanggal'],
    //         'tipe_transaksi' => $validated['tipe_transaksi'],
    //         'keterangan' => $validated['keterangan'],
    //         'petugas' => $validated['petugas']
    //     ]);

    //     if ($validated['tipe_transaksi'] === 'masuk') {
    //         $transaksi->jumlah_masuk = $validated['jumlah'];
    //     } else {
    //         // Check stok tersedia
    //         if ($obat->stok_sisa < $validated['jumlah']) {
    //             return back()->withErrors(['jumlah' => 'Stok tidak mencukupi.']);
    //         }
    //         $transaksi->jumlah_keluar = $validated['jumlah'];
    //     }

    //     $transaksi->save();
    //     $this->updateStokObat($obat);

    //     return back()->with('success', 'Transaksi berhasil ditambahkan.');
    // }

    // public function getRekapitulasiData(Request $request)
    // {
    //     $bulan = $request->get('bulan', Carbon::now()->month);
    //     $tahun = $request->get('tahun', Carbon::now()->year);
        
    //     $rekapitulasi = RekapitulasiObat::where('unit_id', auth()->user()->unit_id)
    //         ->whereMonth('tanggal', $bulan)
    //         ->whereYear('tanggal', $tahun)
    //         ->with('obat')
    //         ->get()
    //         ->groupBy('obat_id');
            
    //     return response()->json($rekapitulasi);
    // }

    // public function updateTransaksiHarian(Request $request, Obat $obat)
    // {
    //     $validated = $request->validate([
    //         'tanggal' => 'required|date',
    //         'jumlah_keluar' => 'required|integer|min:0'
    //     ]);

    //     // Verify that the obat belongs to the user's unit
    //     if ($obat->unit_id !== auth()->user()->unit_id) {
    //         return response()->json(['error' => 'Unauthorized'], 403);
    //     }

    //     $tanggal = Carbon::parse($validated['tanggal']);
    //     $bulan = $tanggal->month;
    //     $tahun = $tanggal->year;

    //     if ($validated['jumlah_keluar'] > 0) {
    //         // Dapatkan stok awal dari bulan ini
    //         $stokAwal = $this->getStokAwalBulan($obat, $bulan, $tahun);

    //         if ($stokAwal < $validated['jumlah_keluar']) {
    //             return response()->json(['error' => 'Stok tidak mencukupi'], 422);
    //         }

    //         // Update rekapitulasi
    //         $rekapitulasi = $obat->rekapitulasiObat()->updateOrCreate(
    //             [
    //                 'tanggal' => $validated['tanggal'],
    //                 'bulan' => $bulan,
    //                 'tahun' => $tahun
    //             ],
    //             [
    //                 'stok_awal' => $stokAwal,
    //                 'jumlah_keluar' => $validated['jumlah_keluar'],
    //                 'sisa_stok' => $stokAwal - $validated['jumlah_keluar'],
    //                 'total_biaya' => $validated['jumlah_keluar'] * $obat->harga_satuan
    //             ]
    //         );

    //         // Recalculate stok untuk hari-hari berikutnya
    //         $rekapitulasiSetelahnya = $obat->rekapitulasiObat()
    //             ->where('bulan', $bulan)
    //             ->where('tahun', $tahun)
    //             ->where('tanggal', '>', $validated['tanggal'])
    //             ->orderBy('tanggal', 'asc')
    //             ->get();

    //         $stokSebelumnya = $rekapitulasi->sisa_stok;
    //         foreach ($rekapitulasiSetelahnya as $rekap) {
    //             $rekap->stok_awal = $stokSebelumnya;
    //             $rekap->sisa_stok = $stokSebelumnya - $rekap->jumlah_keluar;
    //             $rekap->save();
    //             $stokSebelumnya = $rekap->sisa_stok;
    //         }

    //         // Update stok di tabel obat dengan nilai terkini
    //         $latestRekap = $obat->rekapitulasiObat()
    //             ->orderBy('tahun', 'desc')
    //             ->orderBy('bulan', 'desc')
    //             ->orderBy('tanggal', 'desc')
    //             ->first();

    //         $obat->update([
    //             'stok_sisa' => $latestRekap->sisa_stok,
    //             'stok_keluar' => $obat->stok_keluar + $validated['jumlah_keluar']
    //         ]);
    //     }

    //     return response()->json(['success' => true]);
    // }

    private function getStokAwalBulan(Obat $obat, $bulan, $tahun)
    {
        // Ambil rekapitulasi terakhir dari bulan sebelumnya
        $lastMonth = Carbon::createFromDate($tahun, $bulan, 1)->subMonth();
        
        $lastRekapitulasi = $obat->rekapitulasiObat()
            ->where(function($query) use ($lastMonth) {
                $query->where('tahun', $lastMonth->year)
                    ->where('bulan', $lastMonth->month);
            })
            ->orderBy('tanggal', 'desc')
            ->first();

        // Jika ada rekapitulasi bulan lalu, gunakan sisa stoknya
        // Jika tidak, gunakan stok awal dari data obat
        return $lastRekapitulasi ? $lastRekapitulasi->sisa_stok : $obat->stok_awal;
    }

    public function tambahStok(Request $request, Obat $obat)
    {
        // Verifikasi bahwa obat belongs to unit yang sama dengan user
        if ($obat->unit_id !== auth()->user()->unit_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'jumlah_tambah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Buat transaksi masuk
            $transaksi = new TransaksiObat([
                'obat_id' => $obat->id,
                'tanggal' => Carbon::now(),
                'tipe_transaksi' => 'masuk',
                'jumlah_masuk' => $validated['jumlah_tambah'],
                'keterangan' => $validated['keterangan']
            ]);
            $transaksi->save();

            // Update stok obat
            $obat->increment('stok_masuk', $validated['jumlah_tambah']);
            $obat->increment('stok_sisa', $validated['jumlah_tambah']);

            // Update rekapitulasi untuk bulan ini
            $now = Carbon::now();
            $rekapitulasi = RekapitulasiObat::where('obat_id', $obat->id)
                ->where('unit_id', auth()->user()->unit_id)
                ->where('bulan', $now->month)
                ->where('tahun', $now->year)
                ->orderBy('tanggal', 'desc')
                ->first();

            if ($rekapitulasi) {
                $rekapitulasi->increment('stok_awal', $validated['jumlah_tambah']);
                $rekapitulasi->increment('sisa_stok', $validated['jumlah_tambah']);
            } else {
                RekapitulasiObat::create([
                    'obat_id' => $obat->id,
                    'unit_id' => auth()->user()->unit_id,
                    'tanggal' => $now->format('Y-m-d'),
                    'bulan' => $now->month,
                    'tahun' => $now->year,
                    'stok_awal' => $validated['jumlah_tambah'],
                    'jumlah_keluar' => 0,
                    'sisa_stok' => $validated['jumlah_tambah']
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Stok berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambahkan stok: ' . $e->getMessage());
        }
    }

    private function updateStokObat(Obat $obat)
    {
        try {
            $now = Carbon::now();
            $currentMonth = $now->month;
            $currentYear = $now->year;

            // Get last month's final stock
            $lastMonth = Carbon::now()->subMonth();
            $lastMonthStock = RekapitulasiObat::where('obat_id', $obat->id)
                ->where('unit_id', auth()->user()->unit_id)
                ->where('bulan', $lastMonth->month)
                ->where('tahun', $lastMonth->year)
                ->orderBy('tanggal', 'desc')
                ->first();

            // Calculate initial stock for current month
            $stokAwal = $lastMonthStock ? $lastMonthStock->sisa_stok : $obat->stok_awal;

            // Get current month's transactions
            $currentMonthTransactions = $obat->transaksiObats()
                ->whereMonth('tanggal', $currentMonth)
                ->whereYear('tanggal', $currentYear)
                ->get();

            $totalMasuk = $currentMonthTransactions
                ->where('tipe_transaksi', 'masuk')
                ->sum('jumlah_masuk') ?? 0;
                
            $totalKeluar = $currentMonthTransactions
                ->where('tipe_transaksi', 'keluar')
                ->sum('jumlah_keluar') ?? 0;

            // Update obat with current month's data
            $obat->update([
                'stok_masuk' => $totalMasuk,
                'stok_keluar' => $totalKeluar,
                'stok_sisa' => max(0, $stokAwal + $totalMasuk - $totalKeluar)
            ]);

            // Update or create rekapitulasi for current month
            RekapitulasiObat::updateOrCreate(
                [
                    'obat_id' => $obat->id,
                    'unit_id' => auth()->user()->unit_id,
                    'bulan' => $currentMonth,
                    'tahun' => $currentYear,
                    'tanggal' => $now->format('Y-m-d'),
                ],
                [
                    'stok_awal' => $stokAwal,
                    'jumlah_keluar' => $totalKeluar,
                    'sisa_stok' => max(0, $stokAwal + $totalMasuk - $totalKeluar)
                ]
            );

        } catch (\Exception $e) {
            \Log::error('Error updating stock: ' . $e->getMessage());
            // If there's an error, set basic values
            $obat->update([
                'stok_masuk' => 0,
                'stok_keluar' => 0,
                'stok_sisa' => $obat->stok_awal
            ]);
        }
    }

    public function dashboard()
    {
        try {
            // Hitung total obat berdasarkan unit_id user yang login
            $totalObat = Obat::where('unit_id', auth()->user()->unit_id)->count();
        } catch (\Exception $e) {
            // Fallback values if database error
            $totalObat = 0;
        }
        
        return view('obat.dashboard', compact('totalObat'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new ObatImport, $request->file('file'));

        return redirect()->back()->with('success', 'Data obat berhasil diimpor.');
    }

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
            \Log::error('Export error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengexport data: ' . $e->getMessage());
        }
    }

    public function showRekapitulasi(Request $request, Obat $obat)
    {
        // Get bulan & tahun from request or use current
        $bulan = $request->get('bulan', Carbon::now()->month);
        $tahun = $request->get('tahun', Carbon::now()->year);

        // Get rekap harian for selected month
        $rekapHarian = \App\Models\RekapitulasiObat::where('obat_id', $obat->id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('jumlah_keluar', '>', 0)  // Only show days with transactions
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('obat.detail_rekapitulasi', [
            'obat' => $obat,
            'rekapHarian' => $rekapHarian,
            'bulan' => $bulan,
            'tahun' => $tahun
        ]);
    }
}
