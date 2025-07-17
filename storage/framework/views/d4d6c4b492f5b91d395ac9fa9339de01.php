 <div class="modal fade" id="editModal<?php echo e($row->id); ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo e($row->id); ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?php echo e(route('laporan.penyakit.update', $row->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel<?php echo e($row->id); ?>">Edit Laporan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="subkategori" class="form-label">Subkategori</label>
                            <select name="subkategori_id" class="form-select" required>
                                <?php $__currentLoopData = $subkategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($sub->id); ?>" <?php echo e($row->subkategori_id == $sub->id ? 'selected' : ''); ?>>
                                        <?php echo e($sub->nama); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control" value="<?php echo e($row->jumlah); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select name="bulan" class="form-select" required>
                                <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($b); ?>" <?php echo e($row->bulan == $b ? 'selected' : ''); ?>>
                                        <?php echo e(DateTime::createFromFormat('!m', $b)->format('F')); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select name="tahun" class="form-select" required>
                                <?php for($t = date('Y'); $t >= 2020; $t--): ?>
                                    <option value="<?php echo e($t); ?>" <?php echo e($row->tahun == $t ? 'selected' : ''); ?>><?php echo e($t); ?></option>
                                <?php endfor; ?>
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
</td><?php /**PATH /mnt/c/Users/aloys/Downloads/Project-KP-PTPN/resources/views/laporan/modal/modal-penyakit.blade.php ENDPATH**/ ?>