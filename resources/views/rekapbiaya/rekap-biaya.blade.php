@extends('layout.app')

@section('title', 'Rekapitulasi Biaya Kesehatan')

@section('content')
<style>
/* Pindahkan semua style dari <head> ke sini */
.table-wrapper {
    overflow-x: auto;
    position: relative;
    margin-top: 20px;
    border: 1px solid #ccc;
    padding: 10px;
}
table {
    width: max-content;
    border-collapse: collapse;
    margin: 0 auto;
}
th, td {
    border: 1px solid #999;
    padding: 8px;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
}
th {
    background-color: #0077c0;
    color: white;
}
th[colspan] {
    background-color: #005f99;
    font-weight: bold;
}
th.group-header {
    background-color: #004c78;
}
input[type="text"] {
    padding: 6px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
    min-width: 100px;
}
input[readonly] {
    background-color: #f5f5f5;
}
.submit-btn {
    margin-top: 20px;
    padding: 10px 20px;
    background-color: #0077c0;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}
.submit-btn:hover {
    background-color: #005f99;
}
.scroll-buttons {
    display: flex;
    justify-content: center;
    margin: 10px 0;
}
.scroll-buttons button {
    margin: 0 5px;
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
    background-color: #0077c0;
    color: white;
    cursor: pointer;
    font-weight: bold;
}
.scroll-buttons button:hover {
    background-color: #005f99;
}
.form-filter {
    display: flex;
    gap: 1rem;
    margin-bottom: 10px;
    align-items: center;
    justify-content: center;
}
.form-filter input,
.form-filter select,
.form-filter button {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ccc;
}
</style>

<div class="container">
    <h2 class="text-center">Rekapitulasi Biaya Kesehatan</h2>

    {{-- Filter Tahun dan Unit --}}
    <form method="GET" action="{{ route('rekap.show') }}" class="form-filter">
        <input type="text" name="tahun" placeholder="Tahun (YYYY)" value="{{ request('tahun') }}" required pattern="\d{4}">
        <select name="unit" required>
            <option value="">-- Pilih Unit --</option>
            @foreach(['Kandir','Way Lima','Way Berulu','Kedaton','Bergen','Tulungbuyut','Musilandas','Tebenan','Beringin','Padang Pelawi','Ketahun','Senabing'] as $unitName)
                <option value="{{ $unitName }}" {{ request('unit') == $unitName ? 'selected' : '' }}>{{ $unitName }}</option>
            @endforeach
        </select>
        <button type="submit">Tampilkan</button>
    </form>

    {{-- Tombol Export --}}
    @if(request('tahun') && request('unit'))
    <div style="margin-bottom: 10px; text-align:right;">
        <a href="{{ route('rekap.export', ['tahun' => request('tahun'), 'unit' => request('unit')]) }}"
            class="btn btn-success"
            style="background-color:#07c216; color:white; padding: 8px 16px; border-radius: 5px; text-decoration: none;">
            Export CSV
        </a>
    </div>
    @endif

    <div class="table-wrapper" id="tableWrapper">
        <form method="POST" action="{{ route('rekap.store') }}">
            @csrf
            <input type="hidden" name="tahun" value="{{ request('tahun') }}">
            <input type="hidden" name="unit" value="{{ request('unit') }}">

            <table>
                <thead>
                    <tr>
                        <th rowspan="2">Rekap Bulan</th>
                        <th colspan="9" class="group-header">REAL BIAYA</th>
                        <th rowspan="2">Transport</th>
                        <th rowspan="2"> Jml. Biaya Hiperkes</th>
                        <th rowspan="2">TOTAL BIAYA KESEHATAN</th>
                    </tr>
                    <tr>
                        <th>Gol. III-IV</th><th>Gol. I-II</th><th>Kampanye</th>
                        <th>Honor + ILA + OS</th><th>Pens. III-IV</th><th>Pens. I-II</th>
                        <th>Direksi</th><th>Dekom</th><th>Pengacara</th>
                    </tr>
                </thead>
                <tbody>
                    @php $bulanList = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; @endphp
                    @foreach($bulanList as $show => $bulan)
                        @php $item = $data[$bulan] ?? null; @endphp
                        <tr>
                            <td><input type="text" name="data[{{ $show }}][bulan]" value="{{ $bulan }}" readonly></td>
                            @foreach(['gol_3_4','gol_1_2','kampanye','honor','pens_3_4','pens_1_2','direksi','dekom','pengacara','transport','hiperkes'] as $field)
                                <td><input type="text" name="data[{{ $show }}][{{ $field }}]" class="rupiah-input"
                                    value="{{ old("data.$show.$field", isset($item) ? number_format($item->$field, 0, ',', '.') : '') }}">
                                </td>
                            @endforeach
                            <td><input type="text" name="data[{{ $show }}][total]" readonly
                                value="{{ old("data.$show.total", isset($item) ? number_format($item->total, 0, ',', '.') : '') }}">
                            </td>
                        </tr>
                    @endforeach

                    {{-- Total Baris Per Tahun --}}
                    <tr style="background-color: #f0f0f0; font-weight:bold;">
                        <td>TOTAL 1 TAHUN</td>
                        @foreach(['gol_3_4','gol_1_2','kampanye','honor','pens_3_4','pens_1_2','direksi','dekom','pengacara','transport','hiperkes'] as $field)
                            <td><input type="text" id="totalTahun{{ $field }}"  name="total_tahun[{{ $field }}]"  readonly></td>
                        @endforeach
                        <td><input type="text" id="totalTahuntotal" name="total_tahun[total]" readonly></td>
                    </tr>

                    {{-- Jumlah Manual --}}
                    <tr style="background-color:#e0f7fa; font-weight:bold;">
                        <td>JUMLAH</td>
                        @foreach(['gol_3_4','gol_1_2','kampanye','honor','pens_3_4','pens_1_2','direksi','dekom','pengacara','transport','hiperkes'] as $field)
                            <td><input type="text" name="jumlah[{{ $field }}]" class="rupiah-input"
                                value="{{ old("jumlah.$field", isset($jumlah) ? number_format($jumlah->$field, 0, ',', '.') : '') }}">
                            </td>
                        @endforeach
                        <td><input type="text" name="jumlah[total]" class="rupiah-input"
                            value="{{ old('jumlah.total', isset($jumlah) ? number_format($jumlah->total, 0, ',', '.') : '') }}"></td>
                    </tr>
                </tbody>
            </table>

            <div style="text-align:center;">
                <button type="submit" class="submit-btn">Simpan Rekap</button>
            </div>
        </form>
    </div>
</div>

{{-- Script JS --}}
<script>
    function formatRupiah(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function parseRupiah(rp) {
        return parseInt(rp.replace(/\./g, '')) || 0;
    }

    function hitungTotal(row) {
        let total = 0;
        const inputs = row.querySelectorAll('input');
        inputs.forEach(input => {
            const name = input.getAttribute('name') || '';
            if (!name.includes('[bulan]') && !name.includes('[total]')) {
                const value = parseRupiah(input.value);
                total += value;
            }
        });
        const totalInput = row.querySelector('input[name$="[total]"]');
        if (totalInput) {
            totalInput.value = formatRupiah(total);
        }
    }

    document.querySelectorAll('tbody tr').forEach(row => {
        const inputs = row.querySelectorAll('input');
        inputs.forEach(input => {
            const name = input.getAttribute('name') || '';
            if (!name.includes('[bulan]') && !name.includes('[total]')) {
                input.setAttribute('type', 'text');
                input.addEventListener('input', function () {
                    const angka = parseRupiah(this.value);
                    this.value = formatRupiah(angka);
                    hitungTotal(row);
                    hitungTotalPerTahun();
                });
            }
        });
    });

    function hitungTotalPerTahun() {
        const fields = ['gol_3_4','gol_1_2','kampanye','honor','pens_3_4','pens_1_2','direksi','dekom','pengacara','transport','hiperkes','total'];
        fields.forEach(field => {
            let sum = 0;
            document.querySelectorAll(`input[name^="data"][name$="[${field}]"]`).forEach(input => {
                sum += parseRupiah(input.value);
            });
            const totalField = document.getElementById(`totalTahun${field}`);
            if (totalField) {
                totalField.value = formatRupiah(sum);
            }
        });
    }

    window.onload = hitungTotalPerTahun;
</script>
@endsection
