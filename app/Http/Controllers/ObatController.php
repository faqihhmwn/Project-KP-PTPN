<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use App\Models\TransaksiObat;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ObatImport;
use App\Exports\ObatExport;

class ObatController extends Controller
{
    public function index(Request $request)
    {
        $query = Obat::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama_obat', 'like', "%{$request->search}%")
                  ->orWhere('jenis_obat', 'like', "%{$request->search}%");
            });
        }

        $obats = $query->orderBy('nama_obat')->paginate(10);

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
            'stok_awal' => 'required|integer|min:0',
            'expired_date' => 'nullable|date|after:today',
            'keterangan' => 'nullable|string'
        ]);

        $validated['stok_sisa'] = $validated['stok_awal'];

        Obat::create($validated);

        return redirect()->route('obat.index')
            ->with('success', 'Obat berhasil ditambahkan.');
    }

    public function show(Obat $obat)
    {
        $obat->load('transaksiObats');
        
        // Data untuk chart/statistik
        $bulanIni = $obat->transaksiObats()
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->get();
            
        $bulanLalu = $obat->transaksiObats()
            ->whereMonth('tanggal', Carbon::now()->subMonth()->month)
            ->whereYear('tanggal', Carbon::now()->subMonth()->year)
            ->get();
        
        return view('obat.show', compact('obat', 'bulanIni', 'bulanLalu'));
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
            'expired_date' => 'nullable|date|after:today',
            'keterangan' => 'nullable|string'
        ]);

        $obat->update($validated);
        $this->updateStokObat($obat);

        return redirect()->route('obat.index')
            ->with('success', 'Obat berhasil diperbarui.');
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
            ->with(['transaksiObats' => function($query) use ($bulan, $tahun) {
                $query->whereMonth('tanggal', $bulan)
                      ->whereYear('tanggal', $tahun);
            }])
            ->get();

        // Generate data untuk setiap hari dalam bulan
        $daysInMonth = Carbon::create($tahun, $bulan)->daysInMonth;
        
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
        $totalMasuk = $obat->transaksiObats()->where('tipe_transaksi', 'masuk')->sum('jumlah_masuk');
        $totalKeluar = $obat->transaksiObats()->where('tipe_transaksi', 'keluar')->sum('jumlah_keluar');
        
        $obat->update([
            'stok_masuk' => $totalMasuk,
            'stok_keluar' => $totalKeluar,
            'stok_sisa' => $obat->stok_awal + $totalMasuk - $totalKeluar
        ]);
    }

    public function dashboard()
    {
        try {
            $totalObat = Obat::count();
            $transaksiHariIni = TransaksiObat::whereDate('tanggal', Carbon::today())->count();
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
}
