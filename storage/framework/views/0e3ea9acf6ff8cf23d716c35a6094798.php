<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Unit</th>
                    <th>Subkategori</th>
                    <th>Jumlah</th>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $isApproved = isset($approvals[$row->unit_id . '-' . $row->bulan . '-' . $row->tahun]);
                ?>
                <tr>
                    <td><?php echo e($data->firstItem() + $i); ?></td>
                    <td><?php echo e($row->unit->nama); ?></td>
                    <td><?php echo e($row->subkategori->nama); ?></td>
                    <td><?php echo e($row->jumlah); ?></td>
                    <td><?php echo e(DateTime::createFromFormat('!m', $row->bulan)->format('F')); ?></td>
                    <td><?php echo e($row->tahun); ?></td>
                    <td>
                        <?php if($isApproved): ?>
                            <span class="badge bg-success">Disetujui</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Menunggu</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(!$isApproved): ?>
                            <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo e($row->id); ?>">Edit</a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-secondary" disabled>Edit</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if(!$isApproved): ?>
                    <?php echo $__env->make('laporan.modal.modal-cuti-sakit', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="text-center">Belum ada data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-center mt-3">
    <?php echo e($data->links('pagination::bootstrap-5')); ?>

</div><?php /**PATH /mnt/c/Users/aloys/Downloads/Project-KP-PTPN1/resources/views/laporan/partials/cuti-sakit_table.blade.php ENDPATH**/ ?>