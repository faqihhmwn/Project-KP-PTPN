<?php

namespace App\Exports;

use App\Models\RekapBpjsIuran;
use App\Models\KategoriIuran;
use App\Models\Bulan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;


class BpjsExport implements FromArray, WithTitle, WithStyles, WithColumnFormatting, WithColumnWidths
{
    public function array(): array
    {
        // 1. Ambil semua bulan (12 bulan)
        $bulans = Bulan::orderBy('id')->get();

        // 2. Ambil kategori biaya, kita kelompokkan:
        $kategori = KategoriIuran::whereIn('id', range(1, 6))->get();

        // 3. Susun header 3 baris
        $header1 = array_merge(['Bulan'], array_fill(0, 6, 'Jumlah Pembayaran'), ['Jumlah Total']);
        $header2 = array_merge([''], $kategori->pluck('nama')->toArray());
        $header3 = array_merge(['Jumlah'], $this->getTotalPerKategoriPerBulan($kategori, $bulans), [$this->getTotalAkhir()]);

        // Gabungkan semua baris ke array
        $rows = [
            $header1,
            $header2,
        ];

        // 6. Tambah baris per bulan
        foreach ($bulans as $bulan) {
            $row = [$bulan->nama];

            // Jumlah Pembayaran (id 1-6)
            foreach ($kategori as $k) {
                $value = RekapBpjsIuran::where('bulan_id', $bulan->id)
                    ->where('kategori_iuran_id', $k->id)
                    ->where('tahun', $this->tahun)
                    ->value('total_iuran_bpjs') ?? 0;
                $row[] = $value;
            }

            // Total per bulan
            $row[] = RekapBpjsIuran::where('bulan_id', $bulan->id)
                ->where('tahun', $this->tahun)
                ->whereNull('kategori_iuran_id')
                ->sum('total_iuran_bpjs') ?? 0;
            $rows[] = $row;
        }
        // Tambah baris summary ke bawah
        $rows[] = array_merge(['Jumlah'], $this->getTotalPerKategoriPerBulan($kategori, $bulans), [$this->getTotalAkhir()]);

        return $rows;
    }

    public function title(): string
    {
        return 'Rekap Iuran BPJS';
    }

    protected function getTotalPerKategoriPerBulan($kategori, $bulans)
    {
        $totals = [];
        foreach ($kategori as $k) {
            $sum = RekapBpjsIuran::where('kategori_iuran_id', $k->id)
                ->where('tahun', $this->tahun)
                ->sum('total_iuran_bpjs');
            $totals[] = $sum;
        }
        return $totals;
    }

    protected function getTotalAkhir()
    {
        $bulans = Bulan::orderBy('id')->get();
        $kategori = KategoriIuran::whereIn('id', range(1, 6))->pluck('id');

        $total = 0;

        foreach ($bulans as $bulan) {
            $sumPerBulan = RekapBpjsIuran::where('tahun', $this->tahun)
                ->where('bulan_id', $bulan->id)
                ->whereIn('kategori_iuran_id', $kategori)
                ->sum('total_iuran_bpjs');
            $total += $sumPerBulan;
        }
    return $total;
    }

    protected $tahun;
    public function __construct($tahun)
    {
        $this->tahun = $tahun;
    }

    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A1:H2')->applyFromArray([
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
        $sheet->mergeCells('B1:G1');
        $sheet->mergeCells('A1:A2');
        // $sheet->mergeCells('K1:K2');
        // $sheet->mergeCells('L1:L2');
        $sheet->mergeCells('H1:H2');

        // Table border
        $sheet->getStyle("A1:H15")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Highlight total rows
        $totalStartRow = count(Bulan::all()) + 3;
        $sheet->getStyle("A15:H15")->applyFromArray([
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
        ];
    }


}
