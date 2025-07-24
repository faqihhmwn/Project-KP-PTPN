<?php

namespace App\Exports;

use App\Models\Unit;
use App\Models\Kategori;
use App\Models\LaporanBulanan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class LaporanKesehatanRekapExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $bulan;
    protected $tahun;
    protected $data;
    protected $units;
    protected $kategori;
    protected $mergeCells = [];
    protected $categoryRows = []; // To store the row numbers of categories

    public function __construct($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->units = Unit::orderBy('id')->get();
        $this->kategori = Kategori::with('subkategori')->orderBy('id')->get();
        
        $this->data = LaporanBulanan::where('bulan', $this->bulan)
            ->where('tahun', $this->tahun)
            ->get()
            ->groupBy(['subkategori_id', 'unit_id']);
    }

    public function collection()
    {
        $collection = collect();

        foreach ($this->kategori as $kategori) {
            // +2 because of 1-based indexing and the main header row
            $startMergeRow = $collection->count() + 2; 
            
            // Record the row number for styling later
            $this->categoryRows[] = $startMergeRow;

            $collection->push([
                'uraian' => $kategori->nama,
            ]);
            
            $endMergeColIndex = 1 + $this->units->count();
            $endMergeColLetter = Coordinate::stringFromColumnIndex($endMergeColIndex);
            $this->mergeCells[] = 'A' . $startMergeRow . ':' . $endMergeColLetter . $startMergeRow;

            foreach ($kategori->subkategori as $sub) {
                $rowData = [
                    'uraian' => $sub->nama,
                ];

                $total = 0;
                foreach ($this->units as $unit) {
                    $jumlah = $this->data->get($sub->id, collect())->get($unit->id, collect())->first()->jumlah ?? 0;
                    $rowData[$unit->nama] = $jumlah;
                    $total += $jumlah;
                }
                $rowData['jumlah'] = $total;
                $collection->push($rowData);
            }
        }
        return $collection;
    }

    public function headings(): array
    {
        $unitHeaders = $this->units->pluck('nama')->toArray();
        return array_merge(['URAIAN'], $unitHeaders, ['JUMLAH']);
    }
    
    public function styles(Worksheet $sheet)
    {
        // Style for the main header (Row 1)
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        // --- THIS IS THE NEW CODE ---
        // Apply bold style to all category rows
        foreach ($this->categoryRows as $row) {
            $sheet->getStyle("{$row}:{$row}")->getFont()->setBold(true);
        }
        // --- END OF NEW CODE ---
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                foreach ($this->mergeCells as $merge) {
                    $event->sheet->getDelegate()->mergeCells($merge);
                }

                $lastColIndex = 2 + $this->units->count();
                $lastColLetter = Coordinate::stringFromColumnIndex($lastColIndex);
                
                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A1:' . $lastColLetter . $lastRow)
                    ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            },
        ];
    }
}