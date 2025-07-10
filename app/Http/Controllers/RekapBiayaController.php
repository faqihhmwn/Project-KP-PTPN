<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RekapBulan;
use App\Models\RekapTahun;
use Illuminate\Support\Facades\Response;
use App\Models\Unit;

class RekapBiayaController extends Controller
{
    /**
     * Tampilkan form rekap biaya
     */
public function show(Request $request)
{
    $tahun = $request->input('tahun');
    $unit = $request->input('unit');

    if (!$tahun || !$unit) {
        return redirect()->route('rekap.form')->withErrors(['msg' => 'Tahun dan Unit wajib diisi']);
    }

    $unitModel = Unit::where('name', $unit)->first();
    if (!$unitModel) return back()->withErrors(['unit' => 'Unit tidak ditemukan']);
    $unitId = $unitModel->id;

    $data = RekapBulan::where('tahun', $tahun)
        ->where('unit_id', $unitId)
        ->orderByRaw("FIELD(bulan, 'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec')")
        ->get()
        ->keyBy('bulan');

    $jumlah = RekapTahun::where('tahun', $tahun)->where('unit_id', $unitId)->first();

    return view('rekapbiaya.rekap-biaya', compact('data', 'tahun', 'unit', 'jumlah'));
}

    /**
     * Simpan data rekap biaya baru
     */
    public function store(Request $request)
    {
        // ✅ VALIDASI TAHUN DAN UNIT
        $request->validate([
            'tahun' => 'required|digits:4|integer|min:2000|max:' . date('Y'),
            'unit' => 'required'
        ]);

        $unit = Unit::where('name', $request->unit)->first();
        if (!$unit) {
            return back()->withErrors(['unit' => 'Unit tidak ditemukan']);
}

        $data = $request->input('data');

        foreach ($data as $row) {
            RekapBulan::updateOrCreate(
                [
                    'bulan' => $row['bulan'],
                    'tahun' => $request->input('tahun'),
                    'unit_id' => $unit->id
                ],
                [
                    'gol_3_4'   => $this->parseRupiah($row['gol_3_4'] ?? 0),
                    'gol_1_2'   => $this->parseRupiah($row['gol_1_2'] ?? 0),
                    'kampanye'  => $this->parseRupiah($row['kampanye'] ?? 0),
                    'honor'     => $this->parseRupiah($row['honor'] ?? 0),
                    'pens_3_4'  => $this->parseRupiah($row['pens_3_4'] ?? 0),
                    'pens_1_2'  => $this->parseRupiah($row['pens_1_2'] ?? 0),
                    'direksi'   => $this->parseRupiah($row['direksi'] ?? 0),
                    'dekom'     => $this->parseRupiah($row['dekom'] ?? 0),
                    'pengacara' => $this->parseRupiah($row['pengacara'] ?? 0),
                    'transport' => $this->parseRupiah($row['transport'] ?? 0),
                    'hiperkes'  => $this->parseRupiah($row['hiperkes'] ?? 0),
                    'total'     => $this->parseRupiah($row['total'] ?? 0)
                ]
            );
        }
            //simpan rekap jumlah (manual)
            if ($request->has('jumlah')) {
            RekapTahun::updateOrCreate(
                ['tahun' => $request->tahun, 
                'unit_id' => $unit->id
            ],

                [
                    'gol_3_4'   => $this->parseRupiah($request->input('jumlah.gol_3_4') ?? 0),
                    'gol_1_2'   => $this->parseRupiah($request->input('jumlah.gol_1_2') ?? 0),
                    'kampanye'  => $this->parseRupiah($request->input('jumlah.kampanye') ?? 0),
                    'honor'     => $this->parseRupiah($request->input('jumlah.honor') ?? 0),
                    'pens_3_4'  => $this->parseRupiah($request->input('jumlah.pens_3_4') ?? 0),
                    'pens_1_2'  => $this->parseRupiah($request->input('jumlah.pens_1_2') ?? 0),
                    'direksi'   => $this->parseRupiah($request->input('jumlah.direksi') ?? 0),
                    'dekom'     => $this->parseRupiah($request->input('jumlah.dekom') ?? 0),
                    'pengacara' => $this->parseRupiah($request->input('jumlah.pengacara') ?? 0),
                    'transport' => $this->parseRupiah($request->input('jumlah.transport') ?? 0),
                    'hiperkes'  => $this->parseRupiah($request->input('jumlah.hiperkes') ?? 0),
                    'total'     => $this->parseRupiah($request->input('jumlah.total') ?? 0),
                ]
            );

        }

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    /**
     * Update satu baris rekap biaya
     */
    public function update(Request $request, $id)
    {
        $rekap = RekapBulan::findOrFail($id);
        $rekap->update($request->all());

        return response()->json(['message' => 'Data berhasil diperbarui']);
    }

    /**
     * Hapus data
     */
    public function destroy($id)
    {
        $rekap = RekapBulan::findOrFail($id);
        $rekap->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    public function filterForm()
    {
        $tahunList = range(2000, now()->year);
        $units = ['Kandir','Way Lima','Way Berulu','Kedaton','Bergen','Tulungbuyut','Musilandas','Tebenan','Beringin','Padang Pelawi','Ketahun','Senabing'];
        return view('rekapbiaya.filter-form', compact('units', 'tahunList'));
    }


    //Export CSV
    public function export(Request $request)
{
    $tahun = $request->input('tahun');
    $unit = $request->input('unit');

    $data = RekapBulan::where('tahun', $tahun)
    ->whereHas('unit', function ($query) use ($unit) {
        $query->where('name', $unit);
    })
    ->orderByRaw("FIELD(bulan, 'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec')")
    ->get();

    $filename = "rekap-biaya-{$unit}-{$tahun}.csv";

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0"
    ];

    $columns = ['Bulan', 'Gol. III-IV', 'Gol. I-II', 'Kampanye', 'Honor', 'Pens. III-IV', 'Pens. I-II', 'Direksi', 'Dekom', 'Pengacara', 'Transport', 'Hiperkes', 'Total'];

    $callback = function () use ($data, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($data as $row) {
            fputcsv($file, [
                $row->bulan, $row->gol_3_4, $row->gol_1_2, $row->kampanye, $row->honor,
                $row->pens_3_4, $row->pens_1_2, $row->direksi, $row->dekom, $row->pengacara,
                $row->transport, $row->hiperkes, $row->total
            ]);
        }

        fclose($file);
    };

    return Response::stream($callback, 200, $headers);
}


    /**
     * Fungsi bantu: konversi format "1.000.000" ke integer
     */
    private function parseRupiah($value)
    {
        return (int)str_replace('.', '', $value);
    }
}