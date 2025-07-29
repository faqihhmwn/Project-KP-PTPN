@extends('layout.app')

@section('title', 'Edit Obat')

@section('content')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Edit Obat: {{ $obat->nama_obat }}</h4>
                        <!-- <a href="{{ route('obat.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali -->
                        <a href="{{ request()->get('return_url', route('obat.index')) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke
                            {{ request()->has('return_url') ? 'Rekapitulasi' : 'Daftar Obat' }}
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

                        <form action="{{ route('obat.update', $obat) }}?return_url={{ request()->get('return_url') }}"
                            method="POST">
                            <input type="hidden" name="return_url" value="{{ request()->get('return_url') }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama_obat" class="form-label">Nama Obat <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('nama_obat') is-invalid @enderror"
                                            id="nama_obat" name="nama_obat" value="{{ old('nama_obat', $obat->nama_obat) }}"
                                            required>
                                        @error('nama_obat')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jenis_obat" class="form-label">Jenis/Kategori Obat</label>
                                        <input type="text" class="form-control @error('jenis_obat') is-invalid @enderror"
                                            id="jenis_obat" name="jenis_obat"
                                            value="{{ old('jenis_obat', $obat->jenis_obat) }}"
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
                                        <label for="harga_satuan" class="form-label">Harga Satuan <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number"
                                                class="form-control @error('harga_satuan') is-invalid @enderror"
                                                id="harga_satuan" name="harga_satuan"
                                                value="{{ old('harga_satuan', $obat->harga_satuan) }}" min="0"
                                                step="0.01" required>
                                        </div>
                                        @error('harga_satuan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="satuan" class="form-label">Satuan <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('satuan') is-invalid @enderror" id="satuan"
                                            name="satuan" required>
                                            <option value="">Pilih Satuan</option>
                                            <option value="tablet"
                                                {{ old('satuan', $obat->satuan) == 'tablet' ? 'selected' : '' }}>Tablet
                                            </option>
                                            <option value="kapsul"
                                                {{ old('satuan', $obat->satuan) == 'kapsul' ? 'selected' : '' }}>Kapsul
                                            </option>
                                            <option value="botol"
                                                {{ old('satuan', $obat->satuan) == 'botol' ? 'selected' : '' }}>Botol
                                            </option>
                                            <option value="ml"
                                                {{ old('satuan', $obat->satuan) == 'ml' ? 'selected' : '' }}>ML</option>
                                            <option value="tube"
                                                {{ old('satuan', $obat->satuan) == 'tube' ? 'selected' : '' }}>Tube
                                            </option>
                                            <option value="ampul"
                                                {{ old('satuan', $obat->satuan) == 'ampul' ? 'selected' : '' }}>Ampul
                                            </option>
                                            <option value="vial"
                                                {{ old('satuan', $obat->satuan) == 'vial' ? 'selected' : '' }}>Vial
                                            </option>
                                            <option value="strip"
                                                {{ old('satuan', $obat->satuan) == 'strip' ? 'selected' : '' }}>Strip
                                            </option>
                                            <option value="pcs"
                                                {{ old('satuan', $obat->satuan) == 'pcs' ? 'selected' : '' }}>Pcs</option>
                                        </select>
                                        @error('satuan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="stok_awal" class="form-label">Stok Awal <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('stok_awal') is-invalid @enderror"
                                            id="stok_awal" name="stok_awal"
                                            value="{{ old('stok_awal', $obat->stok_awal) }}" min="0" required>
                                        @error('stok_awal')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            <small>Stok saat ini: {{ $obat->stok_sisa }} {{ $obat->satuan }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan"
                                    rows="3" placeholder="Keterangan tambahan tentang obat ini...">{{ old('keterangan', $obat->keterangan) }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- <!-- Info Stok -->
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Informasi Stok Saat Ini:</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Stok Awal:</strong><br>
                                        {{ number_format($obat->stok_awal) }} {{ $obat->satuan }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Stok Sisa:</strong><br>
                                        <span
                                            class="badge {{ $obat->stok_sisa <= 10 ? 'bg-danger' : ($obat->stok_sisa <= 50 ? 'bg-warning' : 'bg-success') }}">
                                            {{ number_format($obat->stok_sisa) }} {{ $obat->satuan }}
                                        </span>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="d-flex justify-content-between">
                                {{-- <a href="{{ route('obat.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Batal
                                </a> --}}
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Obat
                                    </button>
                                </div>
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

        // // Preview stok awal
        // document.getElementById('stok_awal').addEventListener('input', function() {
        //     if (this.value < 0) {
        //         this.value = 0;
        //     }
        // });
    </script>

@endsection
