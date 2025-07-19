<div class="modal fade" id="editModal<?php echo e($row->id); ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo e($row->id); ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('laporan.peserta-kb.update', $row->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel<?php echo e($row->id); ?>">Edit Laporan Peserta KB</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" value="<?php echo e($row->unit->nama); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subkategori</label>
                        <input type="text" class="form-control" value="<?php echo e($row->subkategori->nama); ?>" disabled>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bulan</label>
                            <input type="text" class="form-control" value="<?php echo e(DateTime::createFromFormat('!m', $row->bulan)->format('F')); ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun</label>
                            <input type="text" class="form-control" value="<?php echo e($row->tahun); ?>" disabled>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="jumlah" class="form-label">Jumlah</label>
                        <input type="number" name="jumlah" class="form-control" value="<?php echo e($row->jumlah); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div><?php /**PATH /mnt/c/Users/aloys/Downloads/Project-KP-PTPN1/resources/views/admin/laporan/modal/modal-peserta-kb.blade.php ENDPATH**/ ?>