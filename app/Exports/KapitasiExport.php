<?php

namespace App\Exports;

use App\Models\RekapDanaKapitasi;
use App\Models\Bulan;
use App\Models\KategoriKapitasi;
use App\Models\DanaMasuk;
use App\Models\SisaSaldoKapitasi;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class KapitasiExport implements FromArray, WithTitle, WithStyles, WithColumnFormatting, WithColumnWidths
{
    public function array(): array
    {
        // 1. Ambil semua bulan (12 bulan)
        $bulans = Bulan::orderBy('id')->get();

        // 2. Ambil kategori biaya, kita kelompokkan:
        $kategori = KategoriKapitasi::whereIn('id', range(1, 9))->orderBy('id')->get();

        $sisaSaldoAwal = SisaSaldoKapitasi::where('tahun', $this->tahun)->value('saldo_awal_tahun') ?? 0;
        $currentSaldo = $sisaSaldoAwal;


        // 3. Susun header 3 baris
        $header1 = array_merge(['BULAN'], ['DANA MASUK'], array_fill(0, 9, 'PEMBAYARAN'), ['Total Pembayaran Menggunakan Biaya Kapitasi'], ['SISA SALDO KAPITASI']);
        $header2 = array_merge([''], [''], $kategori->pluck('nama')->toArray(), [''], ['']);
        $header3 = array_merge(['TOTAL BIAYA'], [$this->getTotalDanaMasukTahunan()], $this->getTotalPerKategoriPerBulan($kategori, $bulans), [$this->getTotalAkhir()], [$currentSaldo]);

        // Gabungkan semua baris ke array
        $rows = [
            $header1,
            $header2,
        ];

        $sisaSaldoAwal = SisaSaldoKapitasi::where('tahun', $this->tahun)->value('saldo_awal_tahun') ?? 0;

        $saldoAwalRow = array_fill(0, 12, ''); // A-L kosong
        $saldoAwalRow[] = $sisaSaldoAwal;      // M berisi saldo awal
        $rows[] = $saldoAwalRow;

        $currentSaldo = $sisaSaldoAwal;

        // 6. Tambah baris per bulan
        foreach ($bulans as $bulan) {
            $row = [$bulan->nama];

            // Tambahkan ini untuk kolom "DANA MASUK"
            $danaMasuk = DanaMasuk::where('bulan_id', $bulan->id)
                ->where('tahun', $this->tahun)
                ->value('total_dana_masuk') ?? 0;
            $row[] = $danaMasuk;

            // Jumlah Pembayaran (id 1-9)
            foreach ($kategori as $k) {
                $value = RekapDanaKapitasi::where('bulan_id', $bulan->id)
                    ->where('kategori_kapitasi_id', $k->id)
                    ->where('tahun', $this->tahun)
                    ->value('total_biaya_kapitasi') ?? 0;
                $row[] = $value;
            }

            // Total per bulan
            $totalPembayaran = RekapDanaKapitasi::where('bulan_id', $bulan->id)
                ->where('tahun', $this->tahun)
                ->whereNull('kategori_kapitasi_id')
                ->sum('total_biaya_kapitasi') ?? 0;
                $row[] = $totalPembayaran; 

            // Hitung sisa saldo per bulan: saldo sebelumnya + dana masuk - total pembayaran
            $currentSaldo += $danaMasuk - $totalPembayaran;
            $row[] = $currentSaldo; // Tambahkan ke kolom terakhir

            $rows[] = $row;
        }
        // Tambah baris summary ke bawah
        $rows[] = array_merge(['TOTAL BIAYA'], [$this->getTotalDanaMasukTahunan()], $this->getTotalPerKategoriPerBulan($kategori, $bulans), [$this->getTotalAkhir()], [$currentSaldo]);

        return $rows;
    }


    public function title(): string
    {
        return 'Biaya Pemakaian Dana Kapitasi';
    }

    protected function getTotalDanaMasukTahunan()
    {
        return DanaMasuk::where('tahun', $this->tahun)
            ->sum('total_dana_masuk');
    }


    protected function getTotalPerKategoriPerBulan($kategori, $bulans)
    {
        $totals = [];
        foreach ($kategori as $k) {
            $sum = RekapDanaKapitasi::where('kategori_kapitasi_id', $k->id)
                ->where('tahun', $this->tahun)
                ->sum('total_biaya_kapitasi');
            $totals[] = $sum;
        }
        return $totals;
    }

    protected function getTotalAkhir()
    {
        $bulans = Bulan::orderBy('id')->get();
        $kategoriIds = KategoriKapitasi::whereIn('id', range(1, 9))->pluck('id');

        $total = 0;

        foreach ($bulans as $bulan) {
            $sumPerBulan = RekapDanaKapitasi::where('tahun', $this->tahun)
                ->where('bulan_id', $bulan->id)
                ->whereIn('kategori_kapitasi_id', $kategoriIds)
                ->sum('total_biaya_kapitasi');
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
        $sheet->mergeCells('C1:K1');
        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:B2');
        $sheet->mergeCells('L1:L2');
        $sheet->mergeCells('M1:M2');

        // Table border
        $sheet->getStyle("A1:M16")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Highlight total rows
        $totalStartRow = count(Bulan::all()) + 3;
        $sheet->getStyle("A16:M16")->applyFromArray([
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
            'M' => 20,
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
        ];
    }


}
