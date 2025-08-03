<?php

namespace App\Http\Controllers;

use App\Exports\ObatExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use App\Models\Obat;
use App\Models\RekapitulasiObat;
use Illuminate\Support\Facades\Storage;

class RekapitulasiExportController extends Controller
{
    public function export(Request $request)
    {
        try {
            // Langkah 1: Cek apakah validasi berjalan
            //dd('Validasi akan dijalankan...');

            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            // Langkah 2: Cek apakah validasi berhasil
            //dd('Validasi berhasil, melanjutkan ke proses export.');
            
            // Ambil user saat ini dan unit_id nya
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login')->with('error', 'Sesi login sudah habis. Silakan login kembali.');
            }
            $userUnitId = $user->unit_id;

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Langkah 3: Cek apakah rentang tanggal melebihi 3 bulan
            //dd('Validasi rentang tanggal 3 bulan akan dijalankan...');
            if ($startDate->diffInMonths($endDate) > 3) {
                //dd('Rentang tanggal lebih dari 3 bulan. Proses dihentikan.');
                return back()->with('error', 'Range tanggal maksimal 3 bulan');
            }
            //dd('Rentang tanggal valid, melanjutkan...');

            // Ambil data obat untuk unit tersebut
            $obats = Obat::where('unit_id', $userUnitId)->get();
            
            // Pastikan setiap obat memiliki rekapitulasi dengan harga yang benar
            // foreach ($obats as $obat) {
            //     $rekap = RekapitulasiObat::firstOrCreate(
            //         [
            //             'obat_id' => $obat->id,
            //             'tanggal' => now()->format('Y-m-d'),
            //             'unit_id' => $userUnitId
            //         ],
            //         [
            //             'harga_satuan' => $obat->harga_satuan,
            //             'user_id' => $user->id
            //         ]
            //     );
            // }
            // ...
                foreach ($obats as $obat) {
                    $rekap = RekapitulasiObat::firstOrCreate(
                        [
                            'obat_id' => $obat->id,
                            'tanggal' => now()->format('Y-m-d'),
                            'unit_id' => $userUnitId
                        ],
                        [
                            'harga_satuan' => $obat->harga_satuan,
                            'user_id' => $user->id,
                            'bulan' => now()->month, // <-- Tambahkan baris ini
                            'tahun' => now()->year // <-- Tambahkan baris ini
                        ]
                    );
                }
// ...

            $filename = 'rekapitulasi-obat-' . 
                        $startDate->format('F-Y') . 
                        '.xlsx';

            // Langkah 4: Cek apakah kode berhasil sampai ke tahap download
            //dd('Semua proses selesai, akan mencoba mendownload file Excel.');

            return Excel::download(
                new ObatExport($startDate, $endDate),
                $filename
            );
        } catch (\Exception $e) {
            // Langkah 5: Jika terjadi error, tampilkan detailnya di layar
            dd('Terjadi kesalahan yang tidak terduga: ' . $e->getMessage());
        }
    }
}