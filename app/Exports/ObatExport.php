<?php

namespace App\Exports;

use App\Models\RekapitulasiObat;
use App\Models\PenerimaanObat;
use App\Models\Obat;
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
    protected $unitId;
    protected $daysInMonth;
    protected $rowNumber = 0;
    protected $rekapData;

    public function __construct($startDate, $endDate, $unitId)
    {
        $this->startDate = Carbon::parse($startDate);
        $this->endDate = Carbon::parse($endDate);
        $this->unitId = $unitId;
        $this->daysInMonth = $this->startDate->daysInMonth;

        // Preload all rekap data for efficiency
        $this->rekapData = RekapitulasiObat::with('obat.unit')
            ->where('unit_id', $this->unitId)
            ->whereBetween('tanggal', [$this->startDate->startOfMonth()->toDateString(), $this->endDate->endOfMonth()->toDateString()])
            ->get()
            ->groupBy('obat_id');
    }

    public function collection()
    {
        // Ambil semua obat yang memiliki data rekap dalam rentang waktu ini
        $obatIds = $this->rekapData->keys();
        return Obat::with('unit')->whereIn('id', $obatIds)->get();
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

        $rekapList = $this->rekapData[$obat->id] ?? collect();

        $hargaSatuan = $rekapList->first()?->harga_satuan ?? $obat->harga_satuan;

        $row = [
            $this->rowNumber,
            $obat->nama_obat,
            $obat->jenis_obat ?? '-',
            $hargaSatuan,
            $obat->satuan,
            $obat->unit->nama ?? '-',
            $obat->expired_date ? Carbon::parse($obat->expired_date)->format('d/m/Y') : '-',
            $obat->keterangan ?? '-',
        ];

        // ✅ Ambil stok_awal dari tanggal awal bulan dari rekapitulasi_obats
        $rekapAwal = $rekapList->firstWhere(function ($rekap) {
            return Carbon::parse($rekap->tanggal)->isSameDay($this->startDate->copy()->startOfMonth());
        });

        $stokAwalValue = $rekapAwal ? $rekapAwal->stok_awal : 0;
        $row[] = $stokAwalValue;
        $totalKeluar = 0;
        $totalMasuk = 0;

        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $tanggal = Carbon::create($this->startDate->year, $this->startDate->month, $day)->format('Y-m-d');

            // ✅ Ambil data rekap dari list yang sudah dikelompokkan
            $rekap = $rekapList->first(function ($item) use ($tanggal) {
                return Carbon::parse($item->tanggal)->format('Y-m-d') === $tanggal;
            });
            $keluar = $rekap ? $rekap->jumlah_keluar : 0;

            // ✅ Ambil jumlah_masuk dari penerimaan_obats
            $masuk = PenerimaanObat::where('obat_id', $obat->id)
                ->where('unit_id', $this->unitId)
                ->whereDate('tanggal_masuk', $tanggal)
                ->sum('jumlah_masuk');

            $row[] = $keluar;
            $row[] = $masuk;

            $totalKeluar += $keluar;
            $totalMasuk += $masuk;
        }

            // ✅ Ambil sisa_stok dari rekap terakhir di bulan ini
            $sisaStok = $stokAwalValue + $totalMasuk - $totalKeluar;

            // ✅ Total biaya berdasarkan jumlah_keluar * harga_satuan (dari rekap)
            $totalBiaya = $rekapList->sum(function ($rekap) {
                return $rekap->jumlah_keluar * ($rekap->harga_satuan ?? 0);
            });

        $sisaStok = $stokAwalValue + $totalMasuk - $totalKeluar;

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

        $sheet->freezePane('I3');
    }

    public function title(): string
    {
        return 'Rekap ' . $this->startDate->format('F Y');
    }
}
