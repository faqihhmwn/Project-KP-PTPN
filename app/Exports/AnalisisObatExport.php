<?php

namespace App\Exports;

use App\Models\RekapitulasiObat;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithTitle;

class AnalisisObatExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Analisis Obat';
    }

    public function query()
    {
        $query = RekapitulasiObat::query(); // Hapus ->with()

        if ($this->request->filled('obat')) {
            $query->whereHas('obat', function ($q) {
                $q->where('nama_obat', 'like', '%' . $this->request->obat . '%');
            });
        }

        if ($this->request->filled('jenis')) {
            $query->whereHas('obat', function ($q) {
                $q->where('jenis_obat', 'like', '%' . $this->request->jenis . '%');
            });
        }

        if ($this->request->filled('start_date') && $this->request->filled('end_date')) {
            $start = Carbon::parse($this->request->start_date)->startOfDay();
            $end = Carbon::parse($this->request->end_date)->endOfDay();
            $query->whereBetween('tanggal', [$start, $end]);
        }

        return $query->orderBy('tanggal', 'asc');
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Obat',
            'Jenis Obat',
            'Stok Awal',
            'Jumlah Masuk',
            'Jumlah Keluar',
            'Sisa Stok',
            'Unit',
        ];
    }

    public function map($item): array
    {
        $item->loadMissing(['obat', 'unit']); 

        return [
            Carbon::parse($item->tanggal)->format('d-m-Y'),
            $item->obat->nama_obat ?? '-',
            $item->obat->jenis_obat ?? '-',
            $item->stok_awal,
            $item->jumlah_masuk,
            $item->jumlah_keluar,
            $item->sisa_stok,
            $item->unit->nama ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2']
            ]],
        ];
    }
}
