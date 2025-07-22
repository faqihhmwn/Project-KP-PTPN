<?php

namespace App\Exports;

use App\Models\Bulan;
use App\Models\KategoriBiaya;
use App\Models\RekapBiayaKesehatan;
use App\Models\BiayaTersedia;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;


class RekapBiayaKesehatanExport implements FromArray, WithTitle, WithStyles, WithColumnFormatting, WithColumnWidths
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
        $bulans = Bulan::orderBy('id')->get();
        $kategoriIds = KategoriBiaya::whereIn('id', range(1, 11))->pluck('id');

        $total = 0;

        foreach ($bulans as $bulan) {
            $sumPerBulan = RekapBiayaKesehatan::where('tahun', $this->tahun)
                ->where('bulan_id', $bulan->id)
                ->whereIn('kategori_biaya_id', $kategoriIds)
                ->sum('total_biaya_kesehatan');
            $total += $sumPerBulan;
        }
    return $total;
    }

    protected function getBiayaTersedia($kategoriReal, $kategoriNonReal)
    {
        $row = ['BIAYA TERSEDIA'];
        $total = 0;

        foreach ($kategoriReal->merge($kategoriNonReal) as $kategori) {
            $value = BiayaTersedia::where('kategori_biaya_id', $kategori->id)
                ->where('tahun', $this->tahun)
                ->value('total_tersedia') ?? 0;
            $row[] = $value;
            $total += $value;
        }

        $row[] = $total; // Kolom TOTAL BIAYA KESEHATAN diisi
        return $row;
    }

    protected function getPersentaseBaris($kategoriReal, $kategoriNonReal)
    {
        $row = ['PERSENTASE'];
        $totalRealisasi = 0;
        $totalTersedia = 0;

        foreach ($kategoriReal->merge($kategoriNonReal) as $kategori) {
            $realisasi = RekapBiayaKesehatan::where('kategori_biaya_id', $kategori->id)
                ->where('tahun', $this->tahun)
                ->sum('total_biaya_kesehatan');
            $tersedia = BiayaTersedia::where('kategori_biaya_id', $kategori->id)
                ->where('tahun', $this->tahun)
                ->value('total_tersedia') ?? 0;

            $persentase = $tersedia > 0 ? round(($realisasi / $tersedia) * 100, 2) . '%' : '0%';
            $row[] = $persentase;

            $totalRealisasi += $realisasi;
            $totalTersedia += $tersedia;
        }

        $totalPersentase = $totalTersedia > 0 ? round(($totalRealisasi / $totalTersedia) * 100, 2) . '%' : '0%';
        $row[] = $totalPersentase;

        return $row;
    }

    protected $tahun;
    public function __construct($tahun)
    {
        $this->tahun = $tahun;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = 15 + 12; // atau bisa hitung dari count($bulans) + baris summary

        // Header style
        $sheet->getStyle('A1:M2')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'],
            ],
        ]);

        // Merge cells untuk header
        $sheet->mergeCells('B1:J1');
        $sheet->mergeCells('A1:A2');
        // $sheet->mergeCells('K1:K2');
        // $sheet->mergeCells('L1:L2');
        $sheet->mergeCells('M1:M2');

        // Table border
        $sheet->getStyle("A1:M17")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Highlight total rows
        $totalStartRow = count(Bulan::all()) + 3;
        $sheet->getStyle("A15:M17")->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF2CC'],
            ],
            'font' => ['bold' => true],
        ]);

        return [];
    }


    public function columnWidths(): array
    {
        return [
            'A' => 20,  // Kolom REKAP BULAN
            'B' => 15,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 15,
            'M' => 15,
            'N' => 15,
            'O' => 15,
            'P' => 15,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'N' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'O' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'P' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

}
