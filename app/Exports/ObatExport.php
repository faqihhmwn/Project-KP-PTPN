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
        $obats = Obat::where('unit_id', $this->unitId)->get();

        $processedData = new Collection();

        foreach ($obats as $obat) {
            // 2. Ambil semua data rekapitulasi untuk obat ini dalam rentang tanggal
            // Lakukan query sekali untuk setiap obat dalam rentang tanggal
            $rekapData = RekapitulasiObat::where('obat_id', $obat->id)
                ->where('unit_id', $this->unitId)
                ->whereBetween('tanggal', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')])
                ->orderBy('tanggal')
                ->get();

            // Inisialisasi data yang akan dihitung
            $dailyKeluar = []; // Untuk menyimpan jumlah keluar per tanggal
            $totalKeluarPeriode = 0;
            $sisaStokAkhirPeriode = null;
            $totalBiayaPeriode = 0;

            // 3. Populate daily data dan hitung total keluar untuk periode
            foreach ($this->dateRange as $date) {
                $rekapForDay = $rekapData->firstWhere('tanggal', $date->format('Y-m-d'));
                $keluar = $rekapForDay ? $rekapForDay->jumlah_keluar : 0;
                
                $dailyKeluar[$date->format('Y-m-d')] = $keluar;
                $totalKeluarPeriode += $keluar;

                // Jika ini adalah tanggal terakhir dalam rentang, ambil sisa stoknya
                if ($date->equalTo($this->endDate)) {
                    $sisaStokAkhirPeriode = $rekapForDay ? $rekapForDay->sisa_stok : null;
                }
            }

            // 4. Hitung Stok Awal Periode
            // Ambil sisa_stok dari hari sebelumnya (tanggal sebelum startDate)
            $stokAwalPeriodeEntry = RekapitulasiObat::where('obat_id', $obat->id)
                ->where('unit_id', $this->unitId)
                ->where('tanggal', '<', $this->startDate->format('Y-m-d'))
                ->orderBy('tanggal', 'desc')
                ->first();
            
            // Jika ada entri sebelum periode, gunakan sisa_stok terakhir. Jika tidak, gunakan stok_awal dari tabel obat.
            $stokAwalPeriode = $stokAwalPeriodeEntry ? $stokAwalPeriodeEntry->sisa_stok : $obat->stok_awal;

            // Jika sisaStokAkhirPeriode masih null (misal tidak ada transaksi di endDate),
            //hitung sisa stok berdasarkan stok awal periode dan total keluar
            if (is_null($sisaStokAkhirPeriode)) {
                $sisaStokAkhirPeriode = max(0, $stokAwalPeriode - $totalKeluarPeriode);
                // Jika tidak ada rekapitulasi sama sekali dalam rentang, sisa stok akhir adalah stok awal periode.
                if ($rekapData->isEmpty() && $stokAwalPeriodeEntry === null) {
                    $sisaStokAkhirPeriode = $obat->stok_awal;
                } else if ($rekapData->isEmpty() && $stokAwalPeriodeEntry !== null) {
                    $sisaStokAkhirPeriode = $stokAwalPeriode;
                }
            }


            // 5. Hitung Total Biaya Periode
            $totalBiayaPeriode = $totalKeluarPeriode * $obat->harga_satuan;

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
            'No',
            'Nama Obat',
            'Jenis',
            'Harga Satuan',
            'Stok Awal Periode'
        ];

        // Jika opsi includeDailyData aktif, tambahkan kolom untuk setiap tanggal dalam rentang
        if ($this->includeDailyData) {
            foreach ($this->dateRange as $date) {
                $headers[] = $date->format('d/m'); // Contoh: 01/07, 02/07
            }
        }

        // Tambah kolom ringkasan
        $headers[] = 'Total Keluar Periode';
        $headers[] = 'Sisa Stok Akhir Periode';
        $headers[] = 'Total Biaya Periode';

        return [$headers]; // Harus array of arrays jika hanya ada satu baris heading
    }

    public function map($obat): array
    {
        $this->rowNumber++;
        
        $rowData = [
            $this->rowNumber,
            $obat->nama_obat,
            $obat->jenis_obat ?? '-',
            $obat->harga_satuan,
            $obat->stok_awal_periode,
        ];

        // Jika opsi includeDailyData aktif, tambahkan data keluar harian
        if ($this->includeDailyData) {
            foreach ($this->dateRange as $date) {
                // Ambil data dari properti daily_keluar_data yang sudah diproses di collection()
                $rowData[] = $obat->daily_keluar_data[$date->format('Y-m-d')] ?? 0;
            }
        }

        // Tambah data ringkasan periode
        $rowData[] = $obat->total_keluar_periode;
        $rowData[] = $obat->sisa_stok_akhir_periode;
        $rowData[] = $obat->total_biaya_periode;

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