<div class="modal fade" id="editModal<?php echo e($item->id); ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo e($item->id); ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?php echo e(route('laporan.kategori-khusus.update', $item->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel<?php echo e($item->id); ?>">Edit Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Subkategori</label>
                            <select name="subkategori_id" class="form-select edit-subkategori" data-id="<?php echo e($item->id); ?>" required>
                                <?php $__currentLoopData = $subkategoris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($sub->id); ?>" <?php echo e($item->subkategori_id == $sub->id ? 'selected' : ''); ?>>
                                        <?php echo e($sub->nama); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control" value="<?php echo e($item->nama); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <?php $__currentLoopData = ['Pekerja Tetap', 'PKWT', 'Honor', 'OS']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($status); ?>" <?php echo e($item->status == $status ? 'selected' : ''); ?>>
                                        <?php echo e($status); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6 jenis-disabilitas-group-<?php echo e($item->id); ?>" style="display: none;">
                            <label class="form-label">Jenis Disabilitas</label>
                            <select name="jenis_disabilitas" class="form-select">
                                <option value="">-- Pilih Jenis --</option>
                                <?php $__currentLoopData = ['Fisik', 'Intelektual', 'Sensorik', 'Mental']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jenis): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($jenis); ?>" <?php echo e($item->jenis_disabilitas == $jenis ? 'selected' : ''); ?>>
                                        <?php echo e($jenis); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-12 keterangan-group-<?php echo e($item->id); ?>" style="display: none;">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" value="<?php echo e($item->keterangan); ?>">
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
<?php /**PATH /mnt/c/Users/aloys/Downloads/Project-KP-PTPN1/resources/views/laporan/modal/modal-kategori-khusus.blade.php ENDPATH**/ ?>