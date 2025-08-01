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
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class LaporanKesehatanRekapExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $bulan;
    protected $tahun;
    protected $data;
    protected $units;
    protected $kategori;

    protected $sheetData = [];
    protected $mergeTitleCells = [];

    protected $unitId;

    public function __construct($bulan, $tahun, $unitId = null)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->unitId = $unitId;
        $this->units = $unitId
            ? Unit::where('id', $unitId)->get()
            : Unit::orderBy('id')->get();

        $this->kategori = Kategori::with('subkategori')->orderBy('id')->get();

        $this->data = LaporanBulanan::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->when($unitId, function ($q) use ($unitId) {
                $q->where('unit_id', $unitId);
            })
            ->get()
            ->groupBy(['subkategori_id', 'unit_id']);
    }

    public function headings(): array
    {
        // Baris 1: Judul besar
        // Baris 2: Subjudul bulan & tahun
        return [
            ["REKAPITULASI LAPORAN KESEHATAN"],
            ["Bulan: " . strtoupper($this->bulan) . " - Tahun: " . $this->tahun],
        ];
    }

    public function collection()
    {
        $collection = collect();

        $collection->push(collect(['']));
        $collection->push(collect(['']));

        foreach ($this->kategori as $kategori) {
            // Simpan index baris awal kategori ini
            $collection->push(collect()); // Tempatkan header kategori nanti di AfterSheet

            // Header kategori: nama unit + TOTAL
            $headerRow = collect(["Kat. " . strtoupper($kategori->nama)]);
            foreach ($this->units as $unit) {
                $headerRow->push($unit->nama);
            }
            $headerRow->push("TOTAL");
            $collection->push($headerRow);

            // Subkategori data
            $kategoriTotal = array_fill(0, $this->units->count(), 0);
            foreach ($kategori->subkategori as $sub) {
                $row = collect([$sub->nama]);
                $total = 0;
                foreach ($this->units as $i => $unit) {
                    $jumlah = $this->data->get($sub->id, collect())->get($unit->id, collect())->first()->jumlah ?? 0;
                    $row->push($jumlah);
                    $kategoriTotal[$i] += $jumlah;
                    $total += $jumlah;
                }
                $row->push($total ?: '-');
                $collection->push($row);
            }

            // Baris TOTAL kategori
            $totalRow = collect(['TOTAL']);
            $totalSum = 0;
            foreach ($kategoriTotal as $jumlah) {
                $value = $jumlah ?: '-';
                $totalRow->push($value);
                $totalSum += is_numeric($jumlah) ? $jumlah : 0;
            }
            $totalRow->push($totalSum ?: '-');
            $collection->push($totalRow);

            // Baris kosong antar kategori
            $collection->push(collect(['']));
        }

        // === Tambahan: Tabel Pekerja Disabilitas ===
        $collection->push(collect([''])); // Spasi sebelum tabel disabilitas
        $collection->push(collect(['PEKERJA DISABILITAS']));

        // Header: NAMA + Unit + TOTAL
        $headerDisabilitas = collect(['NAMA']);
        foreach ($this->units as $unit) {
            $headerDisabilitas->push($unit->nama);
        }
        $headerDisabilitas->push("TOTAL");
        $collection->push($headerDisabilitas);

        // Ambil data dari tabel input_manual
        $disabilitasData = \DB::table('input_manual')
            ->where('subkategori_id', 82) // PEKERJA DISABILITAS
            ->where('bulan', $this->bulan)
            ->where('tahun', $this->tahun)
            ->get();

        $index = 1;
        foreach ($disabilitasData as $row) {
            $namaLengkap = $index . '. ' . $row->nama . ' (' . ($row->keterangan ?? '-') . ')';
            $index++;
            $dataRow = collect([$namaLengkap]);
            $total = 0;
            foreach ($this->units as $unit) {
                $jumlah = ($row->unit_id == $unit->id) ? ($row->jumlah ?? 1) : '';
                $dataRow->push($jumlah);
                if (is_numeric($jumlah)) {
                    $total += $jumlah;
                }
            }
            $dataRow->push($total);
            $collection->push($dataRow);
        }

        // Baris total akhir
        $totalDisabilitas = array_fill(0, $this->units->count(), 0);
        foreach ($disabilitasData as $row) {
            foreach ($this->units as $i => $unit) {
                if ($row->unit_id == $unit->id) {
                    $totalDisabilitas[$i] += 1;
                }
            }
        }
        $totalRow = collect(['TOTAL']);
        $totalSum = 0;
        foreach ($totalDisabilitas as $jumlah) {
            $totalRow->push($jumlah ?: '-');
            $totalSum += is_numeric($jumlah) ? $jumlah : 0;
        }
        $totalRow->push($totalSum);
        $collection->push($totalRow);

        // Tambahkan baris kosong sebelum CUTI HAMIL
        $collection->push(collect(['']));
        $collection->push(collect(['CUTI HAMIL']));

        // Header tabel CUTI HAMIL
        $headerCutiHamil = collect(['NAMA KARYAWAN/TI(STATUS)']);
        foreach ($this->units as $unit) {
            $headerCutiHamil->push($unit->nama);
        }
        $headerCutiHamil->push('TOTAL');
        $collection->push($headerCutiHamil);

        // Ambil data CUTI HAMIL dari input_manual dengan subkategori_id = 83
        $cutiHamilData = \DB::table('input_manual')
            ->where('bulan', $this->bulan)
            ->where('tahun', $this->tahun)
            ->where('subkategori_id', 83)
            ->get();

        $index = 1;
        foreach ($cutiHamilData as $row) {
            $namaStatus = $index . '. ' . $row->nama . ' (' . ($row->status ?? '-') . ')';
            $index++;
            $dataRow = collect([$namaStatus]);
            $total = 0;
            foreach ($this->units as $unit) {
                $jumlah = ($row->unit_id == $unit->id) ? 1 : '';
                $dataRow->push($jumlah);
                if ($jumlah === 1) {
                    $total += 1;
                }
            }
            $dataRow->push($total);
            $collection->push($dataRow);
        }

        // Baris TOTAL CUTI HAMIL
        $totalCuti = array_fill(0, $this->units->count(), 0);
        foreach ($cutiHamilData as $row) {
            foreach ($this->units as $i => $unit) {
                if ($row->unit_id == $unit->id) {
                    $totalCuti[$i] += 1;
                }
            }
        }
        $totalRow = collect(['TOTAL']);
        $totalSum = 0;
        foreach ($totalCuti as $jumlah) {
            $jumlah = $jumlah ?: '-';
            $totalRow->push($jumlah);
            $totalSum += is_numeric($jumlah) ? $jumlah : 0;
        }
        $totalRow->push($totalSum ?: '-');
        $collection->push($totalRow);

        // Tambahkan baris kosong sebelum CUTI MELAHIRKAN
        $collection->push(collect(['']));
        $collection->push(collect(['CUTI MELAHIRKAN']));

        // Header tabel CUTI MELAHIRKAN
        $headerCutiMelahirkan = collect(['NAMA KARYAWAN/TI(STATUS)']);
        foreach ($this->units as $unit) {
            $headerCutiMelahirkan->push($unit->nama);
        }
        $headerCutiMelahirkan->push('TOTAL');
        $collection->push($headerCutiMelahirkan);

        // Ambil data CUTI MELAHIRKAN dari input_manual dengan subkategori_id = 84
        $cutiMelahirkanData = \DB::table('input_manual')
            ->where('bulan', $this->bulan)
            ->where('tahun', $this->tahun)
            ->where('subkategori_id', 84)
            ->get();

        $filledRows = 0;
        foreach ($cutiMelahirkanData as $index => $row) {
            $namaStatus = ($index + 1) . '. ' . $row->nama . ' (' . ($row->status ?? '-') . ')';
            $dataRow = collect([$namaStatus]);
            $total = 0;
            foreach ($this->units as $unit) {
                $jumlah = ($row->unit_id == $unit->id) ? 1 : '';
                $dataRow->push($jumlah);
                if ($jumlah === 1) {
                    $total += 1;
                }
            }
            $dataRow->push($total);
            $collection->push($dataRow);
            $filledRows++;
        }

        // Baris TOTAL CUTI MELAHIRKAN
        $totalMelahirkan = array_fill(0, $this->units->count(), 0);
        foreach ($cutiMelahirkanData as $row) {
            foreach ($this->units as $i => $unit) {
                if ($row->unit_id == $unit->id) {
                    $totalMelahirkan[$i] += 1;
                }
            }
        }
        $totalRow = collect(['TOTAL']);
        $totalSum = 0;
        foreach ($totalMelahirkan as $jumlah) {
            $jumlah = $jumlah ?: '-';
            $totalRow->push($jumlah);
            $totalSum += is_numeric($jumlah) ? $jumlah : 0;
        }
        $totalRow->push($totalSum ?: '-');
        $collection->push($totalRow);

        // Tambahkan baris kosong sebelum tabel CUTI KARYAWAN KARENA ISTRI MELAHIRKAN
        $collection->push(collect(['']));
        $collection->push(collect(['CUTI KARYAWAN KARENA ISTRI MELAHIRKAN']));

        // Header tabel
        $headerIstriMelahirkan = collect(['NAMA KARYAWAN/TI(STATUS)']);
        foreach ($this->units as $unit) {
            $headerIstriMelahirkan->push($unit->nama);
        }
        $headerIstriMelahirkan->push('TOTAL');
        $collection->push($headerIstriMelahirkan);

        // Ambil data dari tabel input_manual dengan subkategori_id = 85
        $istriMelahirkanData = \DB::table('input_manual')
            ->where('bulan', $this->bulan)
            ->where('tahun', $this->tahun)
            ->where('subkategori_id', 85)
            ->get();

        // Tambahkan data yang ditemukan
        $filledRows = 0;
        foreach ($istriMelahirkanData as $index => $row) {
            $namaStatus = ($index + 1) . '. ' . $row->nama . ' (' . ($row->status ?? '-') . ')';
            $dataRow = collect([$namaStatus]);
            $total = 0;
            foreach ($this->units as $unit) {
                $jumlah = ($row->unit_id == $unit->id) ? 1 : '';
                $dataRow->push($jumlah);
                if ($jumlah === 1) {
                    $total += 1;
                }
            }
            $dataRow->push($total);
            $collection->push($dataRow);
            $filledRows++;
        }

        // Baris TOTAL
        $totalIstriMelahirkan = array_fill(0, $this->units->count(), 0);
        foreach ($istriMelahirkanData as $row) {
            foreach ($this->units as $i => $unit) {
                if ($row->unit_id == $unit->id) {
                    $totalIstriMelahirkan[$i] += 1;
                }
            }
        }
        $totalRow = collect(['TOTAL']);
        $totalSum = 0;
        foreach ($totalIstriMelahirkan as $jumlah) {
            $jumlah = $jumlah ?: '-';
            $totalRow->push($jumlah);
            $totalSum += is_numeric($jumlah) ? $jumlah : 0;
        }
        $totalRow->push($totalSum ?: '-');
        $collection->push($totalRow);

        return $collection;
    }

    public function styles(Worksheet $sheet)
    {
        // Judul (A1), Subjudul (A2)
        $lastCol = Coordinate::stringFromColumnIndex($this->units->count() + 2);
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:{$lastCol}2");

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setItalic(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColIndex = $this->units->count() + 2;
                $highestColLetter = Coordinate::stringFromColumnIndex($highestColIndex);

                $borderStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ];

                // Apply border ke semua sel mulai dari row 3
                for ($row = 3; $row <= $highestRow; $row++) {
                    $isEmptyRow = true;
                    for ($col = 1; $col <= $highestColIndex; $col++) {
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        if (trim((string)$cell->getValue()) !== '') {
                            $isEmptyRow = false;
                            break;
                        }
                    }

                    // Baris yang TIDAK BOLEH diberi border
                    if (!$isEmptyRow) {
                        $range = 'A' . $row . ':' . $highestColLetter . $row;
                        $sheet->getStyle($range)->applyFromArray($borderStyle);
                    }
                }

                // Header kategori diberi bold dan align center
                for ($row = 3; $row <= $highestRow; $row++) {
                    $firstCell = $sheet->getCell("A{$row}");
                    $value = strtoupper(trim((string) $firstCell->getValue()));
                    $lastCell = $sheet->getCellByColumnAndRow($highestColIndex, $row);
                    $lastValue = trim((string) $lastCell->getValue());

                    if ($lastValue !== '' && $value !== 'TOTAL' && !in_array($value, ['NAMA', 'NAMA KARYAWAN/TI(STATUS)'])) {
                        $lastColLetter = Coordinate::stringFromColumnIndex($highestColIndex);
                        $sheet->getStyle("{$lastColLetter}{$row}")->getFont()->setBold(true);
                    }
                    // Cek apakah kolom TOTAL (kolom terakhir) berisi "-", dan ratakan ke kanan
                    $lastCell = $sheet->getCellByColumnAndRow($highestColIndex, $row);
                    if (trim((string)$lastCell->getValue()) === '-') {
                        $lastColLetter = Coordinate::stringFromColumnIndex($highestColIndex);
                        $sheet->getStyle("{$lastColLetter}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }
                    $headersKhusus = [
                        'NAMA',
                        'NAMA KARYAWAN/TI(STATUS)',
                    ];
                    if (in_array($value, $headersKhusus)) {
                        $sheet->getStyle("A{$row}:{$highestColLetter}{$row}")->getFont()->setBold(true);
                    }

                    if (stripos($value, 'KAT.') === 0) {
                        $sheet->getStyle("A{$row}:{$highestColLetter}{$row}")->getFont()->setBold(true);
                        $sheet->getStyle("A{$row}:{$highestColLetter}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    if ($value === 'TOTAL') {
                        $sheet->getStyle("A{$row}:{$highestColLetter}{$row}")->getFont()->setBold(true);
                        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                        // Buat simbol "-" di baris TOTAL jadi rata kanan
                        for ($col = 2; $col <= $highestColIndex; $col++) {
                            $cell = $sheet->getCellByColumnAndRow($col, $row);
                            if (trim((string)$cell->getValue()) === '-') {
                                $cellLetter = Coordinate::stringFromColumnIndex($col);
                                $sheet->getStyle("{$cellLetter}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                            }
                        }
                    }

                    // Tambahkan pengecekan bold untuk judul tabel tambahan
                    if (in_array($value, ['PEKERJA DISABILITAS', 'CUTI HAMIL', 'CUTI MELAHIRKAN', 'CUTI KARYAWAN KARENA ISTRI MELAHIRKAN'])) {
                        $sheet->getStyle("A{$row}:{$highestColLetter}{$row}")->getFont()->setBold(true);
                    }
                }
            },
        ];
    }
}
