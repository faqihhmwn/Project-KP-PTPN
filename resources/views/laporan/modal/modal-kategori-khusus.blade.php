<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('laporan.kategori-khusus.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel{{ $item->id }}">Edit Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Subkategori</label>
                            <select name="subkategori_id" class="form-select edit-subkategori" data-id="{{ $item->id }}" required>
                                @foreach ($subkategoris as $sub)
                                    <option value="{{ $sub->id }}" {{ $item->subkategori_id == $sub->id ? 'selected' : '' }}>
                                        {{ $sub->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control" value="{{ $item->nama }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                @foreach(['Pekerja Tetap', 'PKWT', 'Honor', 'OS'] as $status)
                                    <option value="{{ $status }}" {{ $item->status == $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 jenis-disabilitas-group-{{ $item->id }}" style="display: none;">
                            <label class="form-label">Jenis Disabilitas</label>
                            <select name="jenis_disabilitas" class="form-select">
                                <option value="">-- Pilih Jenis --</option>
                                @foreach(['Fisik', 'Intelektual', 'Sensorik', 'Mental'] as $jenis)
                                    <option value="{{ $jenis }}" {{ $item->jenis_disabilitas == $jenis ? 'selected' : '' }}>
                                        {{ $jenis }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 keterangan-group-{{ $item->id }}" style="display: none;">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" value="{{ $item->keterangan }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
