<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Unit</th>
                    <th>Subkategori</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th>Jenis Disabilitas</th> 
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($data->firstItem() + $i); ?></td>
                    <td><?php echo e($item->unit->nama ?? '-'); ?></td>
                    <td><?php echo e($item->subkategori?->nama ?? 'Tidak Diketahui'); ?></td>
                    <td><?php echo e($item->nama); ?></td>
                    <td><?php echo e($item->status); ?></td>
                    
                    <td><?php echo e($item->jenis_disabilitas ?? '-'); ?></td>
                    <td><?php echo e($item->keterangan ?? '-'); ?></td>
                    <td class="d-flex flex-wrap gap-2">
                        <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo e($item->id); ?>">Edit</a>
                        <form action="<?php echo e(route('laporan.kategori-khusus.destroy', $item->id)); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini secara permanen?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php echo $__env->make('admin.laporan.modal.modal-kategori-khusus', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="text-center">Belum ada data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-center mt-3">
    <?php echo e($data->links('pagination::bootstrap-5')); ?>

</div><?php /**PATH /mnt/c/Users/aloys/Downloads/Project-KP-PTPN1/resources/views/admin/laporan/partials/kategori-khusus_admin_content.blade.php ENDPATH**/ ?>