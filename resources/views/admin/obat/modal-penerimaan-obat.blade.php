<!-- Modal Tambah Penerimaan Stok -->
<div class="modal fade" id="modalTambahStok" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Form Penerimaan Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTambahStok">
                    @csrf
                    <div class="mb-3">
                        <label for="obat_id_penerimaan" class="form-label">Nama Obat</label>
                        <select id="obat_id_penerimaan" class="form-select" required>
                            <option value="" selected disabled>-- Pilih Obat --</option>
                            @foreach ($obats as $obat)
                                <option value="{{ $obat->id }}">{{ $obat->nama_obat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="jumlah_masuk" class="form-label">Jumlah Masuk</label>
                        <input type="number" class="form-control" id="jumlah_masuk" required min="1">
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                        <input type="date" class="form-control" id="tanggal_masuk" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnSimpanPenerimaan" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
