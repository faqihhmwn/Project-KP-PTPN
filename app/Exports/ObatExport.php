<?php

namespace App\Exports;

use App\Models\Obat;
use App\Models\RekapitulasiObat;
use App\Models\PenerimaanObat;
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
    protected $rowNumber = 0;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = Carbon::parse($startDate);
        $this->endDate = Carbon::parse($endDate);
        $this->daysInMonth = $this->startDate->daysInMonth;
    }

    public function collection()
    {
        return Obat::with(['unit'])->get();
    }

    public function headings(): array
    {
        $row1 = [
            'No', 'Nama Obat', 'Jenis', 'Harga Satuan', 'Satuan', 'Unit', 'Tanggal Kadaluarsa', 'Catatan', 'Stok Awal'
        ];
        $row2 = array_fill(0, count($row1), '');

        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $row1[] = $day;
            $row1[] = '';
            $row2[] = 'Keluar';
            $row2[] = 'Masuk';
        }

        $row1 = array_merge($row1, ['Total Keluar', 'Total Masuk', 'Sisa Stok', 'Total Biaya']);
        $row2 = array_merge($row2, ['', '', '', '']);

        return [$row1, $row2];
    }

    public function map($obat): array
    {
        $this->rowNumber++;

        $row = [
            $this->rowNumber,
            $obat->nama_obat,
            $obat->jenis_obat ?? '-',
            $obat->harga_satuan,
            $obat->satuan,
            $obat->unit->nama ?? '-',
            $obat->expired_date ? Carbon::parse($obat->expired_date)->format('d/m/Y') : '-',
            $obat->keterangan ?? '-',
        ];

        // Hitung stok awal
        $stokAwal = RekapitulasiObat::where('obat_id', $obat->id)
            ->where('tanggal', '<', $this->startDate->format('Y-m-d'))
            ->orderBy('tanggal', 'desc')
            ->first();
        $stokAwalValue = $stokAwal ? $stokAwal->sisa_stok : $obat->stok_awal;
        $row[] = $stokAwalValue;

        $totalKeluar = 0;
        $totalMasuk = 0;

        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $tanggal = Carbon::create($this->startDate->year, $this->startDate->month, $day)->format('Y-m-d');

            $rekap = RekapitulasiObat::where('obat_id', $obat->id)
                ->where('tanggal', $tanggal)
                ->first();
            $keluar = $rekap ? $rekap->jumlah_keluar : 0;

            $masuk = PenerimaanObat::where('obat_id', $obat->id)
                ->where('tanggal_masuk', $tanggal)
                ->sum('jumlah_masuk');

            $row[] = $keluar;
            $row[] = $masuk;

            $totalKeluar += $keluar;
            $totalMasuk += $masuk;

            // Menggunakan harga dari rekapitulasi jika ada
            if ($rekap && $rekap->harga_satuan) {
                $hargaPerTanggal = $rekap->harga_satuan;
            } else {
                $hargaPerTanggal = $obat->harga_satuan;
            }
        }

        $sisaStok = $stokAwalValue + $totalMasuk - $totalKeluar;
        
        // Hitung total biaya menggunakan harga historis dari rekapitulasi
        $rekapitulasiList = RekapitulasiObat::where('obat_id', $obat->id)
            ->whereBetween('tanggal', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')])
            ->get();
            
        $totalBiaya = $rekapitulasiList->sum(function($rekap) {
            return $rekap->jumlah_keluar * ($rekap->harga_satuan ?? $rekap->obat->harga_satuan);
        });

        $row[] = $totalKeluar;
        $row[] = $totalMasuk;
        $row[] = $sisaStok;
        $row[] = $totalBiaya;

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // Merge header atas
        for ($col = 1; $col <= 8; $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->mergeCells("{$colLetter}1:{$colLetter}2");
        }

        $colIndex = 9;
        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $colLetter1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $colLetter2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->mergeCells("{$colLetter1}1:{$colLetter2}1");
            $colIndex += 2;
        }

        foreach (['Total Keluar', 'Total Masuk', 'Sisa Stok', 'Total Biaya'] as $i => $title) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + $i);
            $sheet->mergeCells("{$col}1:{$col}2");
        }

        // Styling
        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '000000']
                ]
            ],
        ]);

        $sheet->getStyle('A1:' . $lastColumn . '2')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2196F3'],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ]
        ]);

        $sheet->freezePane('I3'); // Setelah kolom ke-8
    }

    public function title(): string
    {
        return 'Rekap ' . $this->startDate->format('F Y');
    }
}
