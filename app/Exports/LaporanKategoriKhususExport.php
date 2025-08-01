<?php

namespace App\Exports;

use App\Models\InputManual;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanKategoriKhususExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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

    public function query()
    {
        $query = InputManual::query()
            ->with(['unit', 'subkategori'])
            ->whereHas('subkategori', function ($q) {
                $q->where('kategori_id', 21); // Kategori Khusus
            });

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

    public function headings(): array
    {
        return [
            'No',
            'Unit',
            'Subkategori',
            'Nama',
            'Status',
            'Jenis Disabilitas',
            'Keterangan',
            'Bulan',
            'Tahun',
        ];
    }

    public function map($item): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $item->unit->nama ?? 'N/A',
            $item->subkategori->nama ?? 'N/A',
            $item->nama,
            $item->status,
            $item->jenis_disabilitas ?? '-',
            $item->keterangan ?? '-',
            \Carbon\Carbon::createFromDate(null, (int)$item->bulan, 1)->format('F'),
            $item->tahun,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:I' . $lastRow)
            ->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        return [];
    }
}
