@extends('layout.app')

@section('title', 'Edit Obat')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h4 class="mb-0">Edit Obat: {{ $obat->nama_obat }}</h4>
                    <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger pb-0">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Form untuk mengupdate data dasar obat --}}
                    <form action="{{ route('obats.update', $obat->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3 text-secondary"><i class="fas fa-edit me-2"></i>Informasi Dasar Obat</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_obat" class="form-label">Nama Obat <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama_obat') is-invalid @enderror"
                                           id="nama_obat" name="nama_obat" value="{{ old('nama_obat', $obat->nama_obat) }}" required>
                                    @error('nama_obat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jenis_obat" class="form-label">Jenis/Kategori Obat</label>
                                    <input type="text" class="form-control @error('jenis_obat') is-invalid @enderror"
                                           id="jenis_obat" name="jenis_obat" value="{{ old('jenis_obat', $obat->jenis_obat) }}"
                                           placeholder="Contoh: Antibiotik, Analgesik, dll">
                                    @error('jenis_obat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="harga_satuan" class="form-label">Harga Satuan <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control @error('harga_satuan') is-invalid @enderror"
                                               id="harga_satuan" name="harga_satuan" value="{{ old('harga_satuan', $obat->harga_satuan) }}"
                                               min="0" step="0.01" required>
                                    </div>
                                    @error('harga_satuan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="satuan" class="form-label">Satuan <span class="text-danger">*</span></label>
                                    <select class="form-select @error('satuan') is-invalid @enderror"
                                            id="satuan" name="satuan" required>
                                        <option value="">Pilih Satuan</option>
                                        <option value="tablet" {{ old('satuan', $obat->satuan) == 'tablet' ? 'selected' : '' }}>Tablet</option>
                                        <option value="kapsul" {{ old('satuan', $obat->satuan) == 'kapsul' ? 'selected' : '' }}>Kapsul</option>
                                        <option value="botol" {{ old('satuan', $obat->satuan) == 'botol' ? 'selected' : '' }}>Botol</option>
                                        <option value="ml" {{ old('satuan', $obat->satuan) == 'ml' ? 'selected' : '' }}>ML</option>
                                        <option value="tube" {{ old('satuan', $obat->satuan) == 'tube' ? 'selected' : '' }}>Tube</option>
                                        <option value="ampul" {{ old('satuan', $obat->satuan) == 'ampul' ? 'selected' : '' }}>Ampul</option>
                                        <option value="vial" {{ old('satuan', $obat->satuan) == 'vial' ? 'selected' : '' }}>Vial</option>
                                        <option value="strip" {{ old('satuan', $obat->satuan) == 'strip' ? 'selected' : '' }}>Strip</option>
                                        <option value="pcs" {{ old('satuan', $obat->satuan) == 'pcs' ? 'selected' : '' }}>Pcs</option>
                                    </select>
                                    @error('satuan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror"
                                      id="keterangan" name="keterangan" rows="3"
                                      placeholder="Keterangan tambahan tentang obat ini...">{{ old('keterangan', $obat->keterangan) }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        {{-- Section untuk transaksi stok --}}
                        <h5 class="mb-3 text-secondary"><i class="fas fa-exchange-alt me-2"></i>Kelola Stok Obat</h5>

                        <div class="alert alert-info py-2">
                            <h6><i class="fas fa-info-circle"></i> Stok Saat Ini:
                                <span class="badge {{ $obat->stok_terakhir <= 10 ? 'bg-danger' : ($obat->stok_terakhir <= 50 ? 'bg-warning text-dark' : 'bg-success') }}">
                                    {{ number_format($obat->stok_terakhir) }} {{ $obat->satuan }}
                                </span>
                            </h6>
                            <small>Stok akan otomatis terhitung berdasarkan transaksi masuk dan keluar.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jumlah_masuk" class="form-label">Jumlah Masuk (Penambahan Stok)</label>
                                    <input type="number" class="form-control @error('jumlah_masuk') is-invalid @enderror"
                                           id="jumlah_masuk" name="jumlah_masuk" value="{{ old('jumlah_masuk', 0) }}" min="0" placeholder="0">
                                    <small class="form-text text-muted">Isi jika ada penambahan stok obat. Contoh: penerimaan dari distributor.</small>
                                    @error('jumlah_masuk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jumlah_keluar" class="form-label">Jumlah Keluar (Penggunaan Stok)</label>
                                    <input type="number" class="form-control @error('jumlah_keluar') is-invalid @enderror"
                                           id="jumlah_keluar" name="jumlah_keluar" value="{{ old('jumlah_keluar', 0) }}" min="0" placeholder="0">
                                    <small class="form-text text-muted">Isi jika ada penggunaan/pengeluaran stok obat. Contoh: penggunaan pasien.</small>
                                    @error('jumlah_keluar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_transaksi" class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal_transaksi') is-invalid @enderror"
                                   id="tanggal_transaksi" name="tanggal_transaksi" value="{{ old('tanggal_transaksi', date('Y-m-d')) }}" required>
                            @error('tanggal_transaksi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="keterangan_transaksi" class="form-label">Keterangan Transaksi</label>
                            <textarea class="form-control @error('keterangan_transaksi') is-invalid @enderror"
                                      id="keterangan_transaksi" name="keterangan_transaksi" rows="2"
                                      placeholder="Contoh: Penerimaan dari Puskesmas A, Digunakan oleh pasien B, Stok Rusak, dll.">{{ old('keterangan_transaksi') }}</textarea>
                            @error('keterangan_transaksi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Obat & Stok
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk memformat input harga satuan (memastikan hanya angka)
    document.getElementById('harga_satuan').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9.]/g, ''); // Hanya angka dan titik
        if (parseFloat(this.value) < 0) this.value = 0;
    });

    // Validasi live untuk jumlah masuk dan jumlah keluar
    const jumlahMasukInput = document.getElementById('jumlah_masuk');
    const jumlahKeluarInput = document.getElementById('jumlah_keluar');
    const stokSaatIni = {{ $obat->stok_terakhir ?? 0 }};

    function validateStokInputs() {
        const valMasuk = parseInt(jumlahMasukInput.value) || 0;
        const valKeluar = parseInt(jumlahKeluarInput.value) || 0;

        if (valMasuk > 0 && valKeluar > 0) {
            jumlahMasukInput.classList.add('is-invalid');
            jumlahKeluarInput.classList.add('is-invalid');
            jumlahMasukInput.nextElementSibling.innerText = 'Tidak bisa menambah dan mengurangi stok sekaligus dalam satu transaksi.';
            jumlahKeluarInput.nextElementSibling.innerText = 'Tidak bisa menambah dan mengurangi stok sekaligus dalam satu transaksi.';
            jumlahMasukInput.nextElementSibling.style.color = 'red';
            jumlahKeluarInput.nextElementSibling.style.color = 'red';
        } else {
            jumlahMasukInput.classList.remove('is-invalid');
            jumlahKeluarInput.classList.remove('is-invalid');
            // Kembalikan pesan default jika tidak ada error konflik
            jumlahMasukInput.nextElementSibling.innerText = 'Isi jika ada penambahan stok obat. Contoh: penerimaan dari distributor.';
            jumlahKeluarInput.nextElementSibling.innerText = 'Isi jika ada penggunaan/pengeluaran stok obat. Contoh: penggunaan pasien.';
            jumlahMasukInput.nextElementSibling.style.color = 'gray'; // Atau warna default text-muted
            jumlahKeluarInput.nextElementSibling.style.color = 'gray';
        }

        // Validasi jumlah keluar tidak boleh melebihi stok saat ini
        if (valKeluar > stokSaatIni) {
            jumlahKeluarInput.classList.add('is-invalid');
            jumlahKeluarInput.nextElementSibling.innerText = `Jumlah keluar (${valKeluar}) tidak boleh melebihi stok saat ini (${stokSaatIni}).`;
            jumlahKeluarInput.nextElementSibling.style.color = 'red';
        } else if (valMasuk === 0 || valKeluar === 0) { // Hanya reset jika tidak ada konflik
            jumlahKeluarInput.classList.remove('is-invalid');
            // Kembalikan pesan default jika tidak ada error stok
            jumlahKeluarInput.nextElementSibling.innerText = 'Isi jika ada penggunaan/pengeluaran stok obat. Contoh: penggunaan pasien.';
            jumlahKeluarInput.nextElementSibling.style.color = 'gray';
        }
    }

    jumlahMasukInput.addEventListener('input', validateStokInputs);
    jumlahKeluarInput.addEventListener('input', validateStokInputs);

    // Pastikan nilai default adalah 0 dan tidak negatif
    jumlahMasukInput.addEventListener('change', function() {
        if (this.value === '' || parseFloat(this.value) < 0) {
            this.value = 0;
        }
    });
    jumlahKeluarInput.addEventListener('change', function() {
        if (this.value === '' || parseFloat(this.value) < 0) {
            this.value = 0;
        }
    });
});
</script>

@endsection