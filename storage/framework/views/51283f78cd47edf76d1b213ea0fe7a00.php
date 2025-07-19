
<?php if($unitId && $bulan && $tahun): ?>
    <?php $isApproved = isset($approvals[$unitId . '-' . $bulan . '-' . $tahun]); ?>
    <div class="card mb-3">
        <div class="card-body">
            <p class="fw-bold">Persetujuan Periode</p>
            <?php if(!$isApproved): ?>
                <form action="<?php echo e(route('laporan.penyakit.approve')); ?>" method="POST" onsubmit="return confirm('Yakin ingin menyetujui dan mengunci data untuk periode ini?')">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="unit_id" value="<?php echo e($unitId); ?>">
                    <input type="hidden" name="bulan" value="<?php echo e($bulan); ?>">
                    <input type="hidden" name="tahun" value="<?php echo e($tahun); ?>">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Approve Periode Ini</button>
                </form>
            <?php else: ?>
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-0">
                    <span>Periode ini telah disetujui pada <?php echo e($approvals[$unitId . '-' . $bulan . '-' . $tahun]->approved_at->format('d M Y H:i')); ?>.</span>
                    <form action="<?php echo e(route('laporan.penyakit.unapprove')); ?>" method="POST" onsubmit="return confirm('Yakin ingin MEMBATALKAN persetujuan?')">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="unit_id" value="<?php echo e($unitId); ?>">
                        <input type="hidden" name="bulan" value="<?php echo e($bulan); ?>">
                        <input type="hidden" name="tahun" value="<?php echo e($tahun); ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Un-approve</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>


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
            <td class="d-flex gap-2">
                <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo e($row->id); ?>">Edit</a>
                <form action="<?php echo e(route('laporan.penyakit.destroy', $row->id)); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini secara permanen?')">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </td>
        </tr>
        <?php echo $__env->make('admin.laporan.modal.modal-penyakit', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="8" class="text-center">Belum ada data.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<div class="d-flex justify-content-center mt-3">
    <?php echo e($data->links('pagination::bootstrap-5')); ?>

</div><?php /**PATH /mnt/c/Users/aloys/Downloads/Project-KP-PTPN1/resources/views/admin/laporan/partials/penyakit_admin_content.blade.php ENDPATH**/ ?>