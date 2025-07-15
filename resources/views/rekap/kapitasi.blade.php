@extends('layout.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">Rekapitulasi Biaya - PTPN I Regional 7</h3>
    <p>Silakan sesuaikan isi halaman ini nanti dengan form input data rekap biaya kesehatan per unit.</p>
</div>
@endsection

        {{-- // $tahun = range(date('Y'), 2000); 
        // $selectedTahun = request('tahun') ?? date('Y'); // default ke tahun ini 
        // $bulan = Bulan::orderBy('id')->get(); // Ambil semua bulan

        // $selectedBulan = $request->bulan;

        // if ($selectedBulan) {
        //     $rawData = RekapBiayaKesehatan::with(['kategoriBiaya', 'bulan'])
        //         ->where('tahun', $selectedTahun)
        //         ->where('bulan_id', $selectedBulan)
        //         ->get();
        // } else {
        //     $rawData = collect(); // kosong
        // }

        // $kategori = KategoriBiaya::orderBy('id')->get(); // Ambil semua kategori
        // $data = RekapBiayaKesehatan::with(['kategoriBiaya', 'bulan']) // atau ->with('subkategori') jika kamu pakai subkategori
        //     ->where('tahun', $selectedTahun)
        //     ->orderBy('bulan_id')
        //     ->orderBy('kategori_biaya_id')
        //     ->get();
        
        // Ambil semua data rekap untuk tahun terpilih
        // $rawData = RekapBiayaKesehatan::with(['kategoriBiaya', 'bulan'])
        // ->where('tahun', $selectedTahun)
        // ->get();

        // Transform ke format: [$bulan_id][$kategori_id] = jumlah
        // // $grouped = [];
        // foreach ($rawData as $row) {
        //     $bulanId = $row->bulan_id;
        //     $kategoriId = $row->kategori_biaya_id;

        //     //menyimpan 1 i per kombinasi bulan
        //     $grouped[$bulanId]['bulan'] = $row->bulan->nama;
        //     $grouped[$bulanId]['tahun'] = $row->tahun;
        //     $grouped[$bulanId]['kategori'][$kategoriId] = $row->jumlah;
        //     $grouped[$bulanId]['validasi'] = $row->validasi ?? null;
        // }

    //     $grouped = [];

    //     foreach ($rawData as $row) {
    //         $bulanId = $row->bulan_id;
    //         $kategoriId = $row->kategori_biaya_id;

    //         // ✅ Simpan hanya satu ID per bulan, gunakan ID dari salah satu entri
    //         if (!isset($grouped[$bulanId]['id'])) {
    //             $grouped[$bulanId]['id'] = $row->id;
    //         }

    //         $grouped[$bulanId]['bulan'] = $row->bulan->nama;
    //         $grouped[$bulanId]['tahun'] = $row->tahun;
    //         $grouped[$bulanId]['kategori'][$kategoriId] = $row->jumlah;
    //         $grouped[$bulanId]['validasi'] = $row->validasi ?? null;
    //     }
    // } --}}
