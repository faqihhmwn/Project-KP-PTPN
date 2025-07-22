<?php

namespace App\Exports;

use App\Models\Bulan;
use App\Models\KategoriBiaya;
use App\Models\RekapBiayaKesehatan;
use App\Models\BiayaTersedia;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class RekapBiayaKesehatanExport implements FromArray, WithTitle
{
    public function array(): array
    {
        // 1. Ambil semua bulan (12 bulan)
        $bulans = Bulan::orderBy('id')->get();

        // 2. Ambil kategori biaya, kita kelompokkan:
        $kategoriReal = KategoriBiaya::whereIn('id', range(1, 9))->get();
        $kategoriNonReal = KategoriBiaya::whereIn('id', [10, 11])->get();

        // 3. Susun header 3 baris
        $header1 = array_merge(['REKAP BULAN'], array_fill(0, 9, 'REAL BIAYA'), [''], [''], ['TOTAL BIAYA KESEHATAN']);
        $header2 = array_merge([''], $kategoriReal->pluck('nama')->toArray(), $kategoriNonReal->pluck('nama')->toArray(), ['']);
        $header3 = array_merge(['TOTAL 1 TAHUN'], $this->getTotalPerKategoriPerBulan($kategoriReal, $bulans), $this->getTotalPerKategori($kategoriNonReal), [$this->getTotalAkhir()]);

        // 4. Baris BIAYA TERSEDIA
        $biayaTersedia = $this->getBiayaTersedia($kategoriReal, $kategoriNonReal);

        // 5. Baris PERSENTASE (nilai dinamis)
        $persentase = $this->getPersentaseBaris($kategoriReal, $kategoriNonReal);

        // Gabungkan semua baris ke array
        $rows = [
            $header1,
            $header2,
        ];

        // 6. Tambah baris per bulan
        foreach ($bulans as $bulan) {
            $row = [$bulan->nama];

            // Real biaya (id 1-9)
            foreach ($kategoriReal as $kategori) {
                $value = RekapBiayaKesehatan::where('bulan_id', $bulan->id)
                    ->where('kategori_biaya_id', $kategori->id)
                    ->where('tahun', $this->tahun)
                    ->value('total_biaya_kesehatan') ?? 0;
                $row[] = $value;
            }

            // Non-real biaya (id 10-11)
            foreach ($kategoriNonReal as $kategori) {
                $value = RekapBiayaKesehatan::where('bulan_id', $bulan->id)
                    ->where('kategori_biaya_id', $kategori->id)
                    ->where('tahun', $this->tahun)
                    ->value('total_biaya_kesehatan') ?? 0;
                $row[] = $value;
            }

            // Total per bulan
            $row[] = RekapBiayaKesehatan::where('bulan_id', $bulan->id)
                ->where('tahun', $this->tahun)
                ->whereNull('kategori_biaya_id')
                ->sum('total_biaya_kesehatan');
            $rows[] = $row;
        }
        // Tambah baris summary ke bawah
        $rows[] = array_merge(['TOTAL 1 TAHUN'], $this->getTotalPerKategoriPerBulan($kategoriReal, $bulans), $this->getTotalPerKategori($kategoriNonReal), [$this->getTotalAkhir()]);
        $rows[] = $this->getBiayaTersedia($kategoriReal, $kategoriNonReal);
        $rows[] = $this->getPersentaseBaris($kategoriReal, $kategoriNonReal);

        return $rows;
    }

    public function title(): string
    {
        return 'Rekap Biaya Kesehatan';
    }

    protected function getTotalPerKategoriPerBulan($kategoriReal, $bulans)
    {
        $totals = [];
        foreach ($kategoriReal as $kategori) {
            $sum = RekapBiayaKesehatan::where('kategori_biaya_id', $kategori->id)
                ->where('tahun', $this->tahun)
                ->sum('total_biaya_kesehatan');
            $totals[] = $sum;
        }
        return $totals;
    }

    protected function getTotalPerKategori($kategoriNonReal)
    {
        $totals = [];
        foreach ($kategoriNonReal as $kategori) {
            $sum = RekapBiayaKesehatan::where('kategori_biaya_id', $kategori->id)
                ->where('tahun', $this->tahun)
                ->sum('total_biaya_kesehatan');
            $totals[] = $sum;
        }
        return $totals;
    }

    protected function getTotalAkhir()
    {
        return RekapBiayaKesehatan::where('tahun', $this->tahun)
        ->sum('total_biaya_kesehatan');
    }

    protected function getBiayaTersedia($kategoriReal, $kategoriNonReal)
    {
        $row = ['BIAYA TERSEDIA'];
        foreach ($kategoriReal->merge($kategoriNonReal) as $kategori) {
            $row[] = BiayaTersedia::where('kategori_biaya_id', $kategori->id)
                ->where('tahun', $this->tahun)
                ->value('total_tersedia') ?? 0;
        }
        $row[] = ''; // Kolom TOTAL BIAYA KESEHATAN
        return $row;
    }

    protected function getPersentaseBaris($kategoriReal, $kategoriNonReal)
    {
        $row = ['PERSENTASE'];
        foreach ($kategoriReal->merge($kategoriNonReal) as $kategori) {
            $realisasi = RekapBiayaKesehatan::where('kategori_biaya_id', $kategori->id)
                ->where('tahun', $this->tahun)
                ->sum('total_biaya_kesehatan');
            $tersedia = BiayaTersedia::where('kategori_biaya_id', $kategori->id)
                ->where('tahun', $this->tahun)
                ->value('total_tersedia') ?? 0;

            $persentase = $tersedia > 0 ? round(($realisasi / $tersedia) * 100, 2) . '%' : '0%';
            $row[] = $persentase;
        }
        $row[] = '';
        return $row;
    }

    protected $tahun;
    public function __construct($tahun)
    {
        $this->tahun = $tahun;
    }

}
