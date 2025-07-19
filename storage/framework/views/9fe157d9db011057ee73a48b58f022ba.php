<table class="table table-striped">
    <thead>
        <tr><th>No</th><th>Unit</th><th>Subkategori</th><th>Jumlah</th><th>Bulan</th><th>Tahun</th><th>Status</th><th>Aksi</th></tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php $isRowApproved = isset($approvals[$row->unit_id . '-' . $row->bulan . '-' . $row->tahun]); ?>
        <tr>
            <td><?php echo e($data->firstItem() + $i); ?></td>
            <td><?php echo e($row->unit->nama); ?></td>
            <td><?php echo e($row->subkategori->nama); ?></td>
            <td><?php echo e($row->jumlah); ?></td>
            <td><?php echo e(DateTime::createFromFormat('!m', $row->bulan)->format('F')); ?></td>
            <td><?php echo e($row->tahun); ?></td>
            <td><?php if($isRowApproved): ?><span class="badge bg-success">Disetujui</span><?php else: ?><span class="badge bg-warning text-dark">Menunggu</span><?php endif; ?></td>
            <td>
                
                <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo e($row->id); ?>">Edit</a>

                
                <form action="<?php echo e(route('laporan.kependudukan.destroy', $row->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini secara permanen?')">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </td>
        </tr>
        <?php echo $__env->make('admin.laporan.modal.modal-kependudukan', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="8" class="text-center">Belum ada data.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<div class="d-flex justify-content-center mt-3">
    <?php echo e($data->withQueryString()->links('pagination::bootstrap-5')); ?>

</div><?php /**PATH /mnt/c/Users/aloys/Downloads/Project-KP-PTPN1/resources/views/admin/laporan/partials/kependudukan_table.blade.php ENDPATH**/ ?>