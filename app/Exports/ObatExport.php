<?php

namespace App\Exports;

use App\Models\Obat;
use App\Models\RekapitulasiObat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ObatExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $daysInMonth;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = Carbon::parse($startDate);
        $this->endDate = Carbon::parse($endDate);
        $this->daysInMonth = $this->startDate->daysInMonth;
    }

    public function collection()
    {
        return Obat::with(['rekapitulasiObat' => function($query) {
            $query->whereBetween('tanggal', [
                $this->startDate->format('Y-m-d'),
                $this->endDate->format('Y-m-d')
            ])
            ->where('bulan', $this->startDate->month)
            ->where('tahun', $this->startDate->year)
            ->orderBy('tanggal');
        }])->get();
    }

    public function headings(): array
    {
        $headers = [];
        
        // Baris pertama - header utama
        $row = [
            'No',
            'Nama Obat',
            'Jenis',
            'Harga Satuan',
            'Stok Awal'
        ];

        // Tambah kolom untuk setiap tanggal dalam bulan
        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $row[] = $day;
        }

        // Tambah kolom ringkasan
        $row[] = 'Total Keluar';
        $row[] = 'Sisa Stok';
        $row[] = 'Total Biaya';

        $headers[] = $row;
        
        return $headers;
    }

    public function map($obat): array
    {
        // Data dasar obat
        $row = [
            $obat->id,
            $obat->nama_obat,
            $obat->jenis_obat ?? '-',
            $obat->harga_satuan,
        ];

        // Hitung stok awal
        $stokAwal = $obat->rekapitulasiObat()
            ->where('tanggal', '<', $this->startDate->format('Y-m-d'))
            ->orderBy('tanggal', 'desc')
            ->first();
        $stokAwalValue = $stokAwal ? $stokAwal->sisa_stok : $obat->stok_awal;
        $row[] = $stokAwalValue;

        $totalKeluar = 0;

            // Data penggunaan harian
            for ($day = 1; $day <= $this->daysInMonth; $day++) {
                $tanggal = Carbon::create($this->startDate->year, $this->startDate->month, $day);
                $rekapHarian = RekapitulasiObat::where('obat_id', $obat->id)
                    ->where('tanggal', $tanggal->format('Y-m-d'))
                    ->where('bulan', $this->startDate->month)
                    ->where('tahun', $this->startDate->year)
                    ->first();
                
                $jumlahKeluar = $rekapHarian ? $rekapHarian->jumlah_keluar : 0;
                $totalKeluar += $jumlahKeluar;
                $row[] = $jumlahKeluar;
            }        // Tambah data ringkasan
        $sisaStok = max(0, $stokAwalValue - $totalKeluar);
        $totalBiaya = $totalKeluar * $obat->harga_satuan;

        $row[] = $totalKeluar;
        $row[] = $sisaStok;
        $row[] = $totalBiaya;

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        // Dapatkan kolom terakhir langsung dari worksheet
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // Style untuk header (baris pertama saja)
        $headerRange = 'A1:' . $lastColumn . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2196F3'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style untuk seluruh tabel (termasuk header)
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style untuk baris data (baris kedua dan seterusnya)
        $sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        // Rata kiri untuk kolom nama dan jenis obat
        $sheet->getStyle('B2:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Format angka untuk kolom harga dan total biaya
        $sheet->getStyle('D2:D' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($lastColumn . '2:' . $lastColumn . $lastRow)->getNumberFormat()->setFormatCode('#,##0');

        // Freeze pane untuk header
        $sheet->freezePane('A2');

        return [];
    }

    public function title(): string
    {
        return 'Rekapitulasi ' . $this->startDate->format('F Y');
    }
}
