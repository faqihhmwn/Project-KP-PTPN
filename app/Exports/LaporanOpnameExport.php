<?php

namespace App\Exports;

use App\Models\LaporanBulanan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanOpnameExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $unitId;
    protected $bulan;
    protected $tahun;
    protected $subkategoriId;

    public function __construct($unitId, $bulan, $tahun, $subkategoriId)
    {
        $this->unitId = $unitId;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->subkategoriId = $subkategoriId;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        // Ganti KATEGORI_ID menjadi 3 untuk Opname
        $query = LaporanBulanan::query()
            ->with(['unit', 'subkategori'])
            ->where('kategori_id', 3); // ID 3 untuk Kategori Opname

        if ($this->unitId) {
            $query->where('unit_id', $this->unitId);
        }
        if ($this->bulan) {
            $query->where('bulan', $this->bulan);
        }
        if ($this->tahun) {
            $query->where('tahun', $this->tahun);
        }
        if ($this->subkategoriId) {
            $query->where('subkategori_id', $this->subkategoriId);
        }

        return $query->orderBy('id', 'asc');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Unit',
            'Subkategori (Opname)',
            'Jumlah',
            'Bulan',
            'Tahun',
        ];
    }

    /**
     * @param LaporanBulanan $laporan
     * @return array
     */
    public function map($laporan): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $laporan->unit->nama ?? 'N/A',
            $laporan->subkategori->nama ?? 'N/A',
            $laporan->jumlah,
            \Carbon\Carbon::createFromDate(null, (int)$laporan->bulan, 1)->format('F'),
            $laporan->tahun,
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Memberi gaya pada header (baris pertama)
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        // Memberi garis pada seluruh tabel
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:F' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        return [];
    }
}
