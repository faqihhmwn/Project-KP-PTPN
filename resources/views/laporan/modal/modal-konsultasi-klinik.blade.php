 <div class="modal fade" id="editModal{{ $row->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $row->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('laporan.konsultasi-klinik.update', $row->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel{{ $row->id }}">Edit Laporan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="subkategori" class="form-label">Subkategori</label>
                            <select name="subkategori_id" class="form-select" required>
                                @foreach($subkategori as $sub)
                                    <option value="{{ $sub->id }}" {{ $row->subkategori_id == $sub->id ? 'selected' : '' }}>
                                        {{ $sub->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control" value="{{ $row->jumlah }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select name="bulan" class="form-select" required>
                                @foreach(range(1, 12) as $b)
                                    <option value="{{ $b }}" {{ $row->bulan == $b ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $b)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select name="tahun" class="form-select" required>
                                @for($t = date('Y'); $t >= 2020; $t--)
                                    <option value="{{ $t }}" {{ $row->tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endfor
                            </select>
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
</td>