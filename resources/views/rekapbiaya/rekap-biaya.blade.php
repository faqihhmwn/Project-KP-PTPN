<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Biaya Kesehatan</title>
    <style>
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
</head>
<body>
  <div class="container">
    <h2 style="text-align:center;">Rekapitulasi Biaya Kesehatan</h2>

    {{-- Filter Tahun dan Unit --}}
    <form method="GET" action="{{ route('rekap-biaya.index') }}" class="form-filter">
      <input type="text" name="tahun" placeholder="Tahun (YYYY)" value="{{ request('tahun') }}" required pattern="\d{4}">
      <select name="unit" required>
        <option value="">-- Pilih Unit --</option>
        <option value="Kandir" {{ request('unit') == 'Kandir' ? 'selected' : '' }}>Kandir</option>
        <option value="Way Lima" {{ request('unit') == 'Way Lima' ? 'selected' : '' }}>Way Lima</option>
        <option value="Way Berulu" {{ request('unit') == 'Way Berulu' ? 'selected' : '' }}>Way Berulu</option>
        <option value="Kedaton" {{ request('unit') == 'Kedaton' ? 'selected' : '' }}>Kedaton</option>
        <option value="Bergen" {{ request('unit') == 'Bergen' ? 'selected' : '' }}>Bergen</option>
        <option value="Tulang Buyut" {{ request('unit') == 'Tulungbuyut' ? 'selected' : '' }}>Tulang Buyut</option>
        <option value="Musilandas" {{ request('unit') == 'Musilandas' ? 'selected' : '' }}>Musilandas</option>
        <option value="Tebenan" {{ request('unit') == 'Tebenan' ? 'selected' : '' }}>Tebenan</option>
        <option value="Beringin" {{ request('unit') == 'Beringin' ? 'selected' : '' }}>Beringin</option>
        <option value="Padang Pelawi" {{ request('unit') == 'Padang Pelawi' ? 'selected' : '' }}>Padang Pelawi</option>
        <option value="Ketahun" {{ request('unit') == 'Ketahun' ? 'selected' : '' }}>Ketahun</option>
        <option value="Senabing" {{ request('unit') == 'Senabing' ? 'selected' : '' }}>Senabing</option>

      </select>
      <button type="submit">Tampilkan</button>
    </form>

    {{-- ✅ Tombol Export CSV --}}
    @if(request('tahun') && request('unit'))
    <div style="margin-bottom: 10px; text-align:right;">
      <a href="{{ route('rekap-biaya.export', ['tahun' => request('tahun'), 'unit' => request('unit')]) }}"
        class="btn btn-success"
        style="background-color:#0077c0; color:white; padding: 8px 16px; border-radius: 5px; text-decoration: none;">
        Export CSV
      </a>
    </div>
    @endif

    <div class="table-wrapper" id="tableWrapper">
      <form method="POST" action="{{ route('rekap-biaya.store') }}">
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
            
            @php
              $bulanList = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
              @endphp
              @foreach($bulanList as $index => $bulan)
              
              @php
              $item = $data[$bulan] ?? null;
              @endphp
              <tr>
                <td>
                  <input type="text" name="data[{{ $index }}][bulan]" value="{{ $bulan }}" readonly>
                </td>
                
                @foreach(['gol_3_4','gol_1_2','kampanye','honor','pens_3_4','pens_1_2','direksi','dekom','pengacara','transport','hiperkes'] as $field)
                  <td><input type="text" name="data[{{ $index }}][{{ $field }}]" class="rupiah-input"
                    value="{{ old("data.$index.$field", isset($item) ? number_format($item->$field, 0, ',', '.') : '') }}">
                  </td>
                @endforeach
                <td><input type="text" name="data[{{ $index }}][total]" readonly
                  value="{{ old("data.$index.total", isset($item) ? number_format($item->total, 0, ',', '.') : '') }}"
                ></td>
              </tr>
            @endforeach

            <tr style="background-color: #f0f0f0; font-weight:bold;">
              <td>TOTAL PER TAHUN</td>
              @foreach(['gol_3_4','gol_1_2','kampanye','honor','pens_3_4','pens_1_2','direksi','dekom','pengacara','transport','hiperkes'] as $field)
              <td><input type="text" id="total_{{ $field }}" readonly></td>
              @endforeach
              <td><input type="text" id="total_total" readonly></td>
            </tr>

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

  <script>
    function scrollTable(direction) {
      const wrapper = document.getElementById("tableWrapper");
      const scrollAmount = 200;
      if (direction === 'left') {
        wrapper.scrollLeft -= scrollAmount;
      } else {
        wrapper.scrollLeft += scrollAmount;
      }
    }

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
        const totalField = document.getElementById(`total_${field}`);
        if (totalField) {
          totalField.value = formatRupiah(sum);
        }
      });
    }
    
    document.querySelectorAll('.rupiah-input').forEach(input => {
      input.addEventListener('input', hitungTotalPerTahun);
    });
    
    window.onload = hitungTotalPerTahun;
    
    </script>
</body>
</html>