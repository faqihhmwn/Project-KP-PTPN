@extends('layout.app')

@section('title', 'Tambah Obat Baru')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Tambah Obat Baru</h4>
                    <a href="/obat/dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Farmasi
                    </a>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('obat.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_obat" class="form-label">Nama Obat <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama_obat') is-invalid @enderror" 
                                           id="nama_obat" name="nama_obat" value="{{ old('nama_obat') }}" required>
                                    @error('nama_obat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jenis_obat" class="form-label">Jenis/Kategori Obat</label>
                                    <input type="text" class="form-control @error('jenis_obat') is-invalid @enderror" 
                                           id="jenis_obat" name="jenis_obat" value="{{ old('jenis_obat') }}"
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
                                    <label for="tanggal_masuk" class="form-label">Tanggal Masuk Obat <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('tanggal_masuk') is-invalid @enderror" id="tanggal_masuk" name="tanggal_masuk" value="{{ old('tanggal_masuk', date('Y-m-d')) }}" required>
                                    @error('tanggal_masuk')
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
                                               id="harga_satuan" name="harga_satuan" value="{{ old('harga_satuan') }}" 
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
                                        <option value="tablet" {{ old('satuan') == 'tablet' ? 'selected' : '' }}>Tablet</option>
                                        <option value="kapsul" {{ old('satuan') == 'kapsul' ? 'selected' : '' }}>Kapsul</option>
                                        <option value="botol" {{ old('satuan') == 'botol' ? 'selected' : '' }}>Botol</option>
                                        <option value="ml" {{ old('satuan') == 'ml' ? 'selected' : '' }}>ML</option>
                                        <option value="tube" {{ old('satuan') == 'tube' ? 'selected' : '' }}>Tube</option>
                                        <option value="ampul" {{ old('satuan') == 'ampul' ? 'selected' : '' }}>Ampul</option>
                                        <option value="vial" {{ old('satuan') == 'vial' ? 'selected' : '' }}>Vial</option>
                                        <option value="strip" {{ old('satuan') == 'strip' ? 'selected' : '' }}>Strip</option>
                                        <option value="pcs" {{ old('satuan') == 'pcs' ? 'selected' : '' }}>Pcs</option>
                                    </select>
                                    @error('satuan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stok_awal" class="form-label">Stok Awal <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('stok_awal') is-invalid @enderror" 
                                           id="stok_awal" name="stok_awal" value="{{ old('stok_awal', 0) }}" 
                                           min="0" required>
                                    @error('stok_awal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                      id="keterangan" name="keterangan" rows="3" 
                                      placeholder="Keterangan tambahan tentang obat ini...">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('obat.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Obat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Format input harga saat user mengetik
document.getElementById('harga_satuan').addEventListener('input', function() {
    let value = this.value.replace(/[^\d]/g, '');
    this.value = value;
});

// Preview stok awal
document.getElementById('stok_awal').addEventListener('input', function() {
    if (this.value < 0) {
        this.value = 0;
    }
});
</script>

@endsection