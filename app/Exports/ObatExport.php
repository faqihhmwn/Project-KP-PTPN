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
use Illuminate\Support\Collection; // Penting: import Collection

class ObatExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $includeDailyData; // Properti baru untuk opsi detail harian
    protected $rowNumber = 0;
    protected $unitId;
    protected $dateRange; // Array untuk menyimpan setiap tanggal dalam rentang

    public function __construct(Carbon $startDate, Carbon $endDate, bool $includeDailyData = false)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->includeDailyData = $includeDailyData; // Simpan nilai includeDailyData
        $this->unitId = auth()->user()->unit_id; // Ambil unit_id dari user yang sedang login

        // Generate array of Carbon dates for each day in the given range
        $this->dateRange = collect();
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $this->dateRange->push($currentDate->copy());
            $currentDate->addDay();
        }
    }

    public function collection(): Collection
    {
        // 1. Ambil semua obat untuk unit yang login
        $obats = Obat::where('unit_id', $this->unitId)
            ->orderBy('nama_obat')
            ->get();

        $processedData = new Collection();

        foreach ($obats as $obat) {
            // 2. Ambil stok awal dari rekapitulasi pada tanggal awal periode
            $stokAwalEntry = RekapitulasiObat::where('obat_id', $obat->id)
                ->where('unit_id', $this->unitId)
                ->where('tanggal', $this->startDate->format('Y-m-d'))
                ->first();

            // 3. Ambil semua data rekapitulasi dalam rentang tanggal
            $rekapData = RekapitulasiObat::where('obat_id', $obat->id)
                ->where('unit_id', $this->unitId)
                ->whereBetween('tanggal', [
                    $this->startDate->format('Y-m-d'),
                    $this->endDate->format('Y-m-d')
                ])
                ->orderBy('tanggal')
                ->get();

            // Inisialisasi data yang akan dihitung
            $dailyKeluar = []; // Untuk menyimpan jumlah keluar per tanggal
            $totalKeluarPeriode = 0;
            
            // Tentukan stok awal periode dari rekapitulasi atau stok awal obat
            $stokAwalPeriode = $stokAwalEntry ? $stokAwalEntry->stok_awal : $obat->stok_awal;
            
            // Inisialisasi sisa stok
            $sisaStok = $stokAwalPeriode;
            
            // Populate daily data dan hitung total keluar untuk periode
            foreach ($this->dateRange as $date) {
                $dateStr = $date->format('Y-m-d');
                $rekapForDay = $rekapData->where('tanggal', $dateStr)->first();
                
                if ($rekapForDay) {
                    $keluar = $rekapForDay->jumlah_keluar;
                    // Kurangi sisa stok dengan jumlah keluar
                    $sisaStok -= $keluar;
                } else {
                    $keluar = 0;
                }
                
                $dailyKeluar[$dateStr] = $keluar;
                $totalKeluarPeriode += $keluar;
            }
            
            // Set sisa stok akhir periode dari hasil pengurangan
            $sisaStokAkhirPeriode = $stokAwalPeriode - $totalKeluarPeriode;

            // Pastikan sisa stok tidak negatif
            $sisaStokAkhirPeriode = max(0, $sisaStokAkhirPeriode);
            
            // 5. Hitung Total Biaya Periode
            $totalBiayaPeriode = $sisaStokAkhirPeriode * $obat->harga_satuan;

            // 6. Tambahkan data yang sudah diproses ke objek obat untuk memudahkan mapping
            $obat->daily_keluar_data = $dailyKeluar;
            $obat->total_keluar_periode = $totalKeluarPeriode;
            $obat->stok_awal_periode = $stokAwalPeriode;
            $obat->sisa_stok_akhir_periode = $sisaStokAkhirPeriode;
            $obat->total_biaya_periode = $totalBiayaPeriode;

            $processedData->push($obat);
        }

        return $processedData;
    }

    public function headings(): array
    {
        $headers = [
            'No',           // Nomor urut
            'Nama Obat',    // Nama obat
            'Jenis',        // Jenis obat
            'Harga Satuan', // Harga per unit
            'Stok Awal'     // Stok awal dari sisa stok
        ];

        // Tambahkan semua tanggal dalam bulan tersebut
        foreach ($this->dateRange as $date) {
            $headers[] = $date->format('d'); // Hanya tanggal: 1, 2, 3, dst
        }

        // Tambah kolom Stok Sisa dan Biaya
        $headers[] = 'Stok Sisa';
        $headers[] = 'Biaya';

        return [$headers]; // Harus array of arrays jika hanya ada satu baris heading
    }

    public function map($obat): array
    {
        $this->rowNumber++;
        
        // Gunakan data yang sudah diproses di collection()
        $rowData = [
            $this->rowNumber,               // No
            $obat->nama_obat,               // Nama Obat
            $obat->jenis_obat ?? '-',       // Jenis
            $obat->harga_satuan,            // Harga Satuan
            $obat->stok_awal_periode,       // Stok Awal
        ];

        // Data penggunaan per tanggal
        foreach ($this->dateRange as $date) {
            $dateKey = $date->format('Y-m-d');
            $rowData[] = $obat->daily_keluar_data[$dateKey] ?? 0;  // Data harian
        }

        // Tambah kolom Stok Sisa dan Biaya
        $rowData[] = $obat->sisa_stok_akhir_periode;        // Stok Sisa
        $rowData[] = $obat->sisa_stok_akhir_periode * $obat->harga_satuan;  // Biaya

        return $rowData;
    }

    public function styles(Worksheet $sheet)
    {
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
                'startColor' => ['rgb' => '2196F3'], // Warna biru cerah
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
            // Alignment default untuk seluruh tabel di sini, bisa di-override nanti
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style untuk baris data (baris kedua dan seterusnya) - pastikan latar belakang putih
        $sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        // Rata kiri untuk kolom nama obat (B) dan jenis obat (C)
        $sheet->getStyle('B2:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Format angka untuk kolom harga satuan (D)
        $sheet->getStyle('D2:D' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
        
        // Format angka untuk kolom Total Biaya Periode (kolom terakhir)
        // Dapatkan indeks kolom terakhir secara dinamis
        $totalBiayaColIndex = count($this->headings()[0]); // Jumlah kolom = indeks + 1
        $totalBiayaCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalBiayaColIndex);
        $sheet->getStyle($totalBiayaCol . '2:' . $totalBiayaCol . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
        
        // Freeze pane untuk header
        $sheet->freezePane('A2');

        return [];
    }

    public function title(): string
    {
        return 'Rekapitulasi ' . $this->startDate->format('d M Y') . ' - ' . $this->endDate->format('d M Y');
    }
}