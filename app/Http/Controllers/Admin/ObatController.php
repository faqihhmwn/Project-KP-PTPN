<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use App\Models\TransaksiObat;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ObatImport;
use App\Exports\ObatExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Unit;

class ObatController extends Controller
{
    public function index(Request $request)
{
    $user = Auth::guard('admin')->user();

    if (!$user) {
        return redirect()->route('login');
    }

    $unitIdDipilih = $request->get('unit_id');
    $search = $request->get('search');

    // Ambil semua unit untuk dropdown filter
    $units = \App\Models\Unit::all();

    // Query dasar: join ke unit
    $query = Obat::with(['rekapitulasiObat' => function ($query) {
        $query->orderBy('tanggal', 'desc');
    }, 'unit']);

    // Filter berdasarkan unit jika dipilih
    if ($unitIdDipilih) {
        $query->where('unit_id', $unitIdDipilih);
    }

    // Filter berdasarkan pencarian nama / jenis
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('nama_obat', 'like', "%{$search}%")
              ->orWhere('jenis_obat', 'like', "%{$search}%");
        });
    }

    $obats = $query->latest()->paginate(10);
    $bulan = now()->month;
    $tahun = now()->year;

    return view('admin.obat.index', compact('obats', 'units', 'bulan', 'tahun', 'unitIdDipilih', 'search'));
}

    public function create()
    {
        $units = Unit::all();
        return view('admin.obat.create', compact('units'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'nama_obat' => 'required|string|max:255',
        'jenis_obat' => 'nullable|string|max:255',
        'harga_satuan' => 'required|numeric|min:0',
        'satuan' => 'required|string|max:50',
        'stok_awal' => 'required|integer|min:0',
        'keterangan' => 'nullable|string',
        'unit_id' => 'required|exists:units,id', // Validasi unit yang dipilih
    ]);

    $validated['nama_obat'] = strtoupper($validated['nama_obat']);

    // Cek duplikasi nama obat dalam unit yang dipilih
    $obatExists = Obat::whereRaw('UPPER(nama_obat) = ?', [$validated['nama_obat']])
        ->where('unit_id', $validated['unit_id'])
        ->exists();

    if ($obatExists) {
        return back()->withInput()->withErrors(['nama_obat' => 'Obat sudah ada pada unit yang dipilih!']);
    }

    $validated['stok_sisa'] = $validated['stok_awal'];
    $validated['stok_masuk'] = 0;
    $validated['stok_keluar'] = 0;

    Obat::create($validated);

    return redirect()->route('admin.obat.index')->with('success', 'Obat berhasil ditambahkan.');
}

    public function show(Obat $obat)
    {
        $obat->load('unit');
        $obat->load('rekapitulasiObat');
        $now = Carbon::now();

        $bulanIni = $obat->rekapitulasiObat()
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->orderBy('tanggal', 'desc')
            ->get();

        $bulanLalu = $obat->rekapitulasiObat()
            ->whereMonth('tanggal', $now->copy()->subMonth()->month)
            ->whereYear('tanggal', $now->copy()->subMonth()->year)
            ->orderBy('tanggal', 'desc')
            ->get();

        $lastUpdateBulanIni = $obat->rekapitulasiObat()
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->latest('created_at')
            ->first();

        $lastUpdateBulanLalu = $obat->rekapitulasiObat()
            ->whereMonth('tanggal', $now->copy()->subMonth()->month)
            ->whereYear('tanggal', $now->copy()->subMonth()->year)
            ->latest('created_at')
            ->first();

        $totalPenggunaanBulanIni = $bulanIni->sum('jumlah_keluar');
        $totalBiayaBulanIni = $totalPenggunaanBulanIni * $obat->harga_satuan;

        $totalPenggunaanBulanLalu = $bulanLalu->sum('jumlah_keluar');
        $totalBiayaBulanLalu = $totalPenggunaanBulanLalu * $obat->harga_satuan;

        return view('admin.obat.show', compact(
            'obat', 'bulanIni', 'bulanLalu',
            'totalPenggunaanBulanIni', 'totalBiayaBulanIni',
            'totalPenggunaanBulanLalu', 'totalBiayaBulanLalu',
            'lastUpdateBulanIni', 'lastUpdateBulanLalu'
        ));
    }

    public function edit(Obat $obat)
    {
        $obat->load('unit'); // Memuat relasi unit
        return view('admin.obat.edit', compact('obat'));
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

        return redirect()->to($request->get('return_url', route('admin.obat.index')))
            ->with('success', 'Obat berhasil diperbarui');
    }

    public function destroy(Obat $obat)
    {
        try {
            $obat->transaksiObats()->delete();
            $obat->delete();

            return redirect()->route('admin.obat.index')
                ->with('success', 'Obat dan semua transaksi terkait berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.obat.index')
                ->with('error', 'Gagal menghapus obat: ' . $e->getMessage());
        }
    }

    public function rekapitulasi(Request $request)
{
    $bulan = $request->get('bulan', Carbon::now()->month);
    $tahun = $request->get('tahun', Carbon::now()->year);
    $unitFilter = $request->get('unit_id');

    $user = Auth::guard('admin')->user();
    if (!$user) {
        return redirect()->route('admin.login')->with('error', 'Sesi login admin sudah habis. Silakan login kembali.');
    }

    $units = Unit::all();

    if ($request->get('export') == '1') {
        return $this->exportExcel($request);
    }

    // ✅ Ambil data obat dari unit yang dipilih
    $obats = Obat::query()
        ->when($unitFilter, function ($query, $unitFilter) {
    return $query->where('unit_id', $unitFilter);
})
->with(['transaksiObats' => function ($query) use ($bulan, $tahun) {
    $query->whereMonth('tanggal', $bulan)
          ->whereYear('tanggal', $tahun);
}, 'unit'])
        ->get();

    $daysInMonth = Carbon::createFromDate($tahun, (int)$bulan, 1)->daysInMonth;

    return view('admin.obat.rekapitulasi', compact(
        'obats', 'bulan', 'tahun', 'daysInMonth', 'units', 'unitFilter'
    ));
}

public function dashboard(Request $request)
{
    try {
        // Ambil unit_id dari request jika dipilih, jika tidak, gunakan unit admin login (opsional default)
        $unitId = $request->get('unit_id');

        // Ambil semua unit untuk dropdown
        $units = \App\Models\Unit::all();

        // Jika tidak ada filter unit, default ambil semua
        $obatQuery = Obat::query();
        $transaksiQuery = TransaksiObat::query();

        if ($unitId) {
            $obatQuery->where('unit_id', $unitId);

            $transaksiQuery->whereHas('obat', function ($query) use ($unitId) {
                $query->where('unit_id', $unitId);
            });
        }

        // Hitung total obat & transaksi hari ini
        $totalObat = $obatQuery->count();
        $transaksiHariIni = $transaksiQuery->whereDate('tanggal', Carbon::today())->count();

    } catch (\Exception $e) {
        $totalObat = 0;
        $transaksiHariIni = 0;
        $units = collect();
    }

    return view('admin.obat.dashboard', compact('totalObat', 'transaksiHariIni', 'units'));
}

    private function updateStokObat(Obat $obat)
    {
        try {
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
            $obat->update([
                'stok_masuk' => 0,
                'stok_keluar' => 0,
                'stok_sisa' => $obat->stok_awal
            ]);
        }
    }

    public function showRekapitulasi(Request $request, Obat $obat)
    {
        $bulan = $request->get('bulan', Carbon::now()->month);
        $tahun = $request->get('tahun', Carbon::now()->year);

        $rekapHarian = \App\Models\RekapitulasiObat::where('obat_id', $obat->id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('jumlah_keluar', '>', 0)
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('admin.obat.detail_rekapitulasi', [
            'obat' => $obat,
            'rekapHarian' => $rekapHarian,
            'bulan' => $bulan,
            'tahun' => $tahun
        ]);
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
}
