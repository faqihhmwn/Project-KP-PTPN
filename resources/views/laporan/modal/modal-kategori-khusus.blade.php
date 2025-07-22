<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('laporan.kategori-khusus.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel{{ $item->id }}">Edit Laporan Kategori Khusus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" value="{{ $item->unit->nama ?? '-' }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bulan</label>
                        <select name="bulan" class="form-select" required>
                            @foreach(range(1,12) as $b)
                                <option value="{{$b}}" {{$item->bulan == $b ? 'selected' : ''}}>{{date('F',mktime(0,0,0,$b,1))}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun</label>
                        <select name="tahun" class="form-select" required>
                            @for($t=date('Y');$t>=2020;$t--)
                                <option value="{{$t}}" {{$item->tahun == $t ? 'selected' : ''}}>{{$t}}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subkategori</label>
                        <select name="subkategori_id" class="form-select edit-subkategori" data-id="{{ $item->id }}" required>
                            @foreach($subkategoris as $sub)
                                <option value="{{ $sub->id }}" {{ $item->subkategori_id == $sub->id ? 'selected' : '' }}>{{ $sub->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Pekerja</label>
                        <input type="text" name="nama" class="form-control" value="{{ $item->nama }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Pekerja Tetap" {{$item->status == 'Pekerja Tetap' ? 'selected' : ''}}>Pekerja Tetap</option>
                            <option value="PKWT" {{$item->status == 'PKWT' ? 'selected' : ''}}>PKWT</option>
                            <option value="Honor" {{$item->status == 'Honor' ? 'selected' : ''}}>Honor</option>
                            <option value="OS" {{$item->status == 'OS' ? 'selected' : ''}}>OS</option>
                        </select>
                    </div>
                    <div class="mb-3 jenis-disabilitas-group-{{$item->id}}" style="display: {{ $item->subkategori_id == 82 ? 'block' : 'none' }};">
                        <label class="form-label">Jenis Disabilitas</label>
                        <select name="jenis_disabilitas" class="form-select jenis-disabilitas-input-{{$item->id}}">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Fisik" {{ $item->jenis_disabilitas == 'Fisik' ? 'selected' : '' }}>Fisik</option>
                            <option value="Intelektual" {{ $item->jenis_disabilitas == 'Intelektual' ? 'selected' : '' }}>Intelektual</option>
                            <option value="Sensorik" {{ $item->jenis_disabilitas == 'Sensorik' ? 'selected' : '' }}>Sensorik</option>
                            <option value="Mental" {{ $item->jenis_disabilitas == 'Mental' ? 'selected' : '' }}>Mental</option>
                        </select>
                    </div>
                    <div class="mb-3 keterangan-group-{{$item->id}}" style="display: {{ in_array($item->subkategori_id, [82, 83, 84, 85]) ? 'block' : 'none' }};">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" value="{{ $item->keterangan }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>