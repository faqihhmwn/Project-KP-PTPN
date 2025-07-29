<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use App\Models\TransaksiObat;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ObatImport;
use App\Exports\ObatExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ObatController extends Controller
{
    public function index(Request $request)
    {
        // 1. Secara eksplisit gunakan guard 'web' untuk mendapatkan user
        $user = Auth::guard('web')->user();

        // 2. Lakukan pengecekan jika user tidak ditemukan (pengaman tambahan)
        if (!$user) {
            // Arahkan ke halaman login jika tidak ada user yang terotentikasi
            return redirect()->route('login');
        }

        // 3. Ambil unit_id dari user yang sudah dipastikan ada
        $userUnitId = $user->unit_id;

        // Load data obat milik unit tersebut
        $query = \App\Models\Obat::with(['rekapitulasiObat' => function ($query) {
            // Urutkan berdasarkan tanggal terbaru
            $query->orderBy('tanggal', 'desc');
        }])->where('unit_id', $userUnitId); // Filter berdasarkan unit user

        // Fitur pencarian
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_obat', 'like', "%{$request->search}%")
                    ->orWhere('jenis_obat', 'like', "%{$request->search}%");
            });
        }

        $obats = $query->latest()->paginate(10);
        $bulan = now()->month;
        $tahun = now()->year;

        return view('obat.index', compact('obats', 'bulan', 'tahun'));
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
            'stok_awal' => 'required|integer|min:0',
            'keterangan' => 'nullable|string'
        ]);

        // Format nama obat menjadi kapital semua
        $validated['nama_obat'] = strtoupper($validated['nama_obat']);

        // Cek apakah nama obat sudah ada (case-insensitive)
        $obatExists = \App\Models\Obat::whereRaw('UPPER(nama_obat) = ?', [$validated['nama_obat']])
            ->where('unit_id', Auth::user()->unit_id)
            ->exists();

        if ($obatExists) {
            return back()
                ->withInput()
                ->withErrors(['nama_obat' => 'Obat sudah ada!']);
        }

        // Tambahan data stok dan unit_id
        $validated['stok_sisa'] = $validated['stok_awal'];
        $validated['stok_masuk'] = 0;
        $validated['stok_keluar'] = 0;
        $validated['unit_id'] = Auth::user()->unit_id;

        Obat::create($validated);

        return redirect()->route('obat.index')
            ->with('success', 'Obat berhasil ditambahkan.');
    }


    public function show(Obat $obat)
    {
        $obat->load('unit');
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

        return view('obat.show', compact(
            'obat',
            'bulanIni',
            'bulanLalu',
            'totalPenggunaanBulanIni',
            'totalBiayaBulanIni',
            'totalPenggunaanBulanLalu',
            'totalBiayaBulanLalu'
        ));
    }

    public function edit(Obat $obat)
    {
        $obat->load('unit'); // Memuat relasi unit
        return view('obat.edit', compact('obat'));
    }

    public function update(Request $request, Obat $obat)
    {
        $validated = $request->validate([
            'nama_obat' => 'required|string|max:255',
            'jenis_obat' => 'nullable|string|max:255',
            'harga_satuan' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:50',
            'keterangan' => 'nullable|string'
        ]);

        $obat->update($validated);
        $this->updateStokObat($obat);

        return redirect()->to($request->query('return_url', route('obat.index')))
            ->with('success', 'Obat berhasil diperbarui');
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
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Sesi login sudah habis. Silakan login kembali.');
        }

        $userUnitId = $user->unit_id;


        if ($request->get('export') == '1') {
            return $this->exportExcel($request);
        }

        $userUnitId = Auth::user()->unit_id;

        $obats = Obat::query()
            ->where('unit_id', $userUnitId)
            ->with(['transaksiObats' => function ($query) use ($bulan, $tahun) {
                $query->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun);
            }])
            ->get();

        // Generate data untuk setiap hari dalam bulan
        $daysInMonth = Carbon::createFromDate($tahun, (int)$bulan, 1)->daysInMonth;

        return view('obat.rekapitulasi', compact('obats', 'bulan', 'tahun', 'daysInMonth'));
    }

    public function addTransaksi(Request $request, Obat $obat)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'tipe_transaksi' => 'required|in:masuk,keluar',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
            'petugas' => 'nullable|string'
        ]);

        $transaksi = new TransaksiObat([
            'obat_id' => $obat->id,
            'tanggal' => $validated['tanggal'],
            'tipe_transaksi' => $validated['tipe_transaksi'],
            'keterangan' => $validated['keterangan'],
            'petugas' => $validated['petugas']
        ]);

        if ($validated['tipe_transaksi'] === 'masuk') {
            $transaksi->jumlah_masuk = $validated['jumlah'];
        } else {
            // Check stok tersedia
            if ($obat->stok_sisa < $validated['jumlah']) {
                return back()->withErrors(['jumlah' => 'Stok tidak mencukupi.']);
            }
            $transaksi->jumlah_keluar = $validated['jumlah'];
        }

        $transaksi->save();
        $this->updateStokObat($obat);

        return back()->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function updateTransaksiHarian(Request $request, Obat $obat)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jumlah_keluar' => 'required|integer|min:0'
        ]);

        if ($validated['jumlah_keluar'] > 0) {
            if ($obat->stok_sisa < $validated['jumlah_keluar']) {
                return response()->json(['error' => 'Stok tidak mencukupi'], 422);
            }

            TransaksiObat::updateOrCreate(
                [
                    'obat_id' => $obat->id,
                    'tanggal' => $validated['tanggal'],
                    'tipe_transaksi' => 'keluar'
                ],
                [
                    'jumlah_keluar' => $validated['jumlah_keluar'],
                    'total_biaya' => $validated['jumlah_keluar'] * $obat->harga_satuan
                ]
            );

            $this->updateStokObat($obat);
        }

        return response()->json(['success' => true]);
    }

    private function updateStokObat(Obat $obat)
    {
        try {
            // Check if transaksi_obats table exists and has the required columns
            $totalMasuk = $obat->transaksiObats()
                ->where('tipe_transaksi', 'masuk')
                ->sum('jumlah_masuk') ?? 0;

            $totalKeluar = $obat->transaksiObats()
                ->where('tipe_transaksi', 'keluar')
                ->sum('jumlah_keluar') ?? 0;

            $obat->update([
                'stok_masuk' => $totalMasuk,
                'stok_keluar' => $totalKeluar,
                'stok_sisa' => $obat->stok_awal + $totalMasuk - $totalKeluar
            ]);
        } catch (\Exception $e) {
            // If there's an error (like missing columns), just set stok_sisa = stok_awal
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
            $unitId = Auth::user()->unit_id;

            $totalObat = Obat::where('unit_id', $unitId)->count();

            $transaksiHariIni = TransaksiObat::whereHas('obat', function ($query) use ($unitId) {
                $query->where('unit_id', $unitId);
            })
                ->whereDate('tanggal', \Carbon\Carbon::today())
                ->count();
        } catch (\Exception $e) {
            // Fallback values if database error
            $totalObat = 0;
            $transaksiHariIni = 0;
        }

        return view('obat.dashboard', compact(
            'totalObat',
            'transaksiHariIni'
        ));
    }


    // public function import(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx,xls,csv',
    //     ]);

    //     Excel::import(new ObatImport, $request->file('file'));

    //     return redirect()->back()->with('success', 'Data obat berhasil diimpor.');
    // }

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
            Log::error('Export error: ' . $e->getMessage());
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
