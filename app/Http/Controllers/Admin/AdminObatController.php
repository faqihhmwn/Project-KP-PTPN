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
use App\Models\RekapitulasiObat;

class AdminObatController extends Controller
{
    public function index(Request $request)
    {
        $unitId = $request->input('unit_id');
        $search = $request->input('search');
        $bulan = now()->month;
        $tahun = now()->year;

        $query = Obat::with(['rekapitulasiObat' => function ($query) {
            $query->orderBy('tanggal', 'desc');
        }]);

        if ($unitId) {
            $query->where('unit_id', $unitId);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_obat', 'like', "%$search%")
                        ->orWhere('jenis_obat', 'like', "%$search%");
                });
            }

            // $obats = $query->latest()->paginate(10);
            $obats = $query->orderBy('nama_obat')->paginate(10);

        } else {
            // Tidak ada unit dipilih
            // $obats = collect(); // bukan paginator
            $obats = Obat::whereRaw('1 = 0')->paginate(10);
            
        }

        $units = Unit::all();

        return view('admin.obat.index', compact('obats', 'bulan', 'tahun', 'unitId', 'units'));
    }


    public function create()
    {
        return view('admin.obat.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_obat' => 'required|string|max:255',
            'jenis_obat' => 'nullable|string|max:255',
            'harga_satuan' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:50',
            'stok_awal' => 'required|integer|min:0',
            'keterangan' => 'nullable|string|max:500',
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

        // ✅ Tambahkan 1 entri obat untuk admin (unit_id = null)
        $obatAdmin = Obat::create([
            'nama_obat' => $validated['nama_obat'],
            'jenis_obat' => $validated['jenis_obat'] ?? null,
            'harga_satuan' => $validated['harga_satuan'],
            'satuan' => $validated['satuan'],
            'keterangan' => $validated['keterangan'] ?? null,
            'unit_id' => null,
            'stok_awal' => $validated['stok_awal'],
        ]);

        // Opsional: jika ingin juga rekap admin (biasanya tidak)
        /*
    RekapitulasiObat::create([
        'obat_id' => $obatAdmin->id,
        'unit_id' => null,
        'tanggal' => now()->startOfMonth(),
        'bulan' => now()->format('m'),
        'tahun' => now()->format('Y'),
        'jumlah_masuk' => $validated['stok_awal'],
        'jumlah_keluar' => 0,
        'keterangan' => 'Stok awal dari admin',
    ]);
    */

        // ✅ Tambahkan ke semua unit
        $units = Unit::all();
        foreach ($units as $unit) {
            $obat = Obat::create([
                'nama_obat' => $validated['nama_obat'],
                'jenis_obat' => $validated['jenis_obat'] ?? null,
                'harga_satuan' => $validated['harga_satuan'],
                'satuan' => $validated['satuan'],
                'keterangan' => $validated['keterangan'] ?? null,
                'unit_id' => $unit->id,
                'stok_awal' => $validated['stok_awal'],
            ]);

            RekapitulasiObat::create([
                'obat_id' => $obat->id,
                'unit_id' => $unit->id,
                'tanggal' => now()->startOfMonth(),
                'bulan' => now()->format('m'),
                'tahun' => now()->format('Y'),
                'jumlah_masuk' => $validated['stok_awal'],
                'jumlah_keluar' => 0,
                'keterangan' => 'Stok awal dari admin',
            ]);
        }

        return redirect()->route('admin.obat.index', ['unit_id' => $obat->unit_id])
            ->with('success', 'Obat berhasil ditambahkan ke semua unit.');
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

        return view('admin.obat.show', compact(
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
        $obat->load('unit'); // Memuat relasi unit
        return view('admin.obat.edit', compact('obat'));
    }

    public function update(Request $request, Obat $obat)
    {
        $messages = [
            'expired_date.after_or_equal' => 'Tanggal kadaluarsa harus sama dengan atau setelah hari ini.',
            'expired_date.date' => 'Format tanggal kadaluarsa tidak valid.'
        ];

        $validated = $request->validate([
            'jenis_obat' => 'nullable|string|max:255',
            'harga_satuan' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:50',
            'keterangan' => 'nullable|string',
            'expired_date' => 'nullable|date|after_or_equal:today',
        ], $messages);

        // Ambil semua entri obat dengan nama yang sama (termasuk admin dan unit)
        $obats = Obat::where('nama_obat', $obat->nama_obat)->get();

        foreach ($obats as $item) {
            $item->update([
                'jenis_obat' => $validated['jenis_obat'] ?? $item->jenis_obat,
                'harga_satuan' => $validated['harga_satuan'],
                'satuan' => $validated['satuan'],
                'keterangan' => $validated['keterangan'] ?? $item->keterangan,
                'expired_date' => $validated['expired_date'] ?? $item->expired_date,
            ]);
        }

        return redirect()->route('admin.obat.index', ['unit_id' => $obat->unit_id])
            ->with('success', 'Obat berhasil diperbarui di semua unit.');
    }


    public function destroy(Obat $obat)
    {
        try {
            // Cari semua obat dengan nama yang sama
            $allObatSama = Obat::where('nama_obat', $obat->nama_obat)->get();

            foreach ($allObatSama as $item) {
                // Hapus semua transaksi terkait
                $item->transaksiObats()->delete();

                // Hapus rekapitulasi terkait
                $item->rekapitulasiObat()->delete();

                // Hapus obat
                $item->delete();
            }

            return redirect()->route('admin.obat.index', ['unit_id' => $obat->unit_id])
                ->with('success', 'Obat dan semua data terkait berhasil dihapus dari semua unit.');
        } catch (\Exception $e) {
            return redirect()->route('admin.obat.index')
                ->with('error', 'Gagal menghapus obat: ' . $e->getMessage());
        }
    }


    public function rekapitulasi(Request $request)
    {
        $bulan = $request->get('bulan', Carbon::now()->month);
        $tahun = $request->get('tahun', Carbon::now()->year);
        $unitId = $request->get('unit_id'); // Tambahkan unit_id dari filter

        $user = Auth::guard('admin')->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Sesi login sudah habis. Silakan login kembali.');
        }

        // Jika klik tombol export
        if ($request->get('export') == '1') {
            return $this->exportExcel($request);
        }

        $daysInMonth = Carbon::createFromDate($tahun, (int)$bulan, 1)->daysInMonth;
        $units = \App\Models\Unit::all(); // Ambil semua unit

        // Jika unit belum dipilih, kirim view tanpa data obat
        if (!$unitId) {
            $obats = Obat::whereRaw('1 = 0')->paginate(10);
            // $obats = collect(); // ← tambahkan ini untuk mencegah error
            $rekapitulasi = collect(); // jika di view juga menggunakan $rekapitulasi
            $isLocked = false;

            return view('admin.obat.rekapitulasi', compact(
                'units',
                'unitId',
                'bulan',
                'tahun',
                'daysInMonth',
                'obats',
                'rekapitulasi',
                'isLocked'
            ));
        }


        // Jika unit dipilih, ambil data obat & rekap
        $obats = Obat::where('unit_id', $unitId)
            ->with(['rekapitulasiObatByUnit' => function ($query) use ($bulan, $tahun) {
                $query->where('bulan', $bulan)->where('tahun', $tahun);
            }])
            ->get();

        $rekapitulasi = RekapitulasiObat::with(['obat', 'user'])
            ->where('unit_id', $unitId)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get();

        $isLocked = \Illuminate\Support\Facades\Storage::exists("validasi/obat_validasi_{$tahun}_{$bulan}.lock");

        return view('admin.obat.rekapitulasi', compact(
            'obats',
            'rekapitulasi',
            'bulan',
            'tahun',
            'daysInMonth',
            'unitId',
            'units',
            'isLocked'
        ));
    }


    public function dashboard()
    {
        try {
            $unitId = Auth::guard('admin')->user()->unit_id;

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

        return view('admin.obat.dashboard', compact(
            'totalObat',
            'transaksiHariIni'
        ));
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

        return view('admin.obat.detail_rekapitulasi', [
            'obat' => $obat,
            'rekapHarian' => $rekapHarian,
            'bulan' => $bulan,
            'tahun' => $tahun
        ]);
    }
}
