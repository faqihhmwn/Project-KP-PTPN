

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h3 class="mb-4">Laporan Kependudukan</h3>

    <?php if(session('success')): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo e(session('success')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if(session('error')): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo e(session('error')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <div class="card mb-4">
        <div class="card-header fw-bold">Input Laporan Bulanan</div>
        <div class="card-body">
            <form method="POST" action="<?php echo e(route('laporan.kependudukan.store')); ?>">
                <?php echo csrf_field(); ?>
                <fieldset id="input-form">
                    <div class="row mb-3">
                        <div class="col-md-3"><label for="bulan" class="form-label">Bulan</label><select name="bulan" id="bulan" class="form-select" required><?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($b); ?>" <?php echo e(old('bulan', date('n')) == $b ? 'selected' : ''); ?>><?php echo e(DateTime::createFromFormat('!m', $b)->format('F')); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                        <div class="col-md-3"><label for="tahun" class="form-label">Tahun</label><select name="tahun" id="tahun" class="form-select" required><?php for($t = date('Y'); $t >= 2020; $t--): ?><option value="<?php echo e($t); ?>" <?php echo e(old('tahun', date('Y')) == $t ? 'selected' : ''); ?>><?php echo e($t); ?></option><?php endfor; ?></select></div>
                    </div>
                    <div id="approval-status-alert" class="alert alert-warning" style="display: none;">Data untuk periode ini sudah disetujui dan tidak dapat diubah.</div>
                    <table class="table table-bordered">
                        <thead><tr><th>Subkategori</th><th>Jumlah</th></tr></thead>
                        <tbody><?php $__currentLoopData = $subkategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><tr><td><?php echo e($sub->nama); ?></td><td><input type="number" name="jumlah[<?php echo e($sub->id); ?>]" class="form-control" min="0" value="0" required></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </fieldset>
            </form>
        </div>
    </div>

    <hr class="my-5">
    <h5 class="fw-bold">Data Tersimpan</h5>

    <table class="table table-striped">
        <thead><tr><th>No</th><th>Subkategori</th><th>Jumlah</th><th>Bulan</th><th>Tahun</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $isApproved = isset($approvals[Auth::user()->unit_id . '-' . $row->bulan . '-' . $row->tahun]);
            ?>
            <tr>
                <td><?php echo e($data->firstItem() + $i); ?></td>
                <td><?php echo e($row->subkategori->nama); ?></td>
                <td><?php echo e($row->jumlah); ?></td>
                <td><?php echo e(DateTime::createFromFormat('!m', $row->bulan)->format('F')); ?></td>
                <td><?php echo e($row->tahun); ?></td>
                <td>
                    <?php if(!$isApproved): ?>
                        <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo e($row->id); ?>">Edit</a>
                    <?php else: ?>
                        <span class="badge bg-success">Telah Disetujui</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if(!$isApproved): ?>
              <?php echo $__env->make('laporan.modal.modal-kependudukan', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="6" class="text-center">Belum ada data</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="d-flex justify-content-center mt-3"><?php echo e($data->links('pagination::bootstrap-5')); ?></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const bulanSelect = document.getElementById('bulan');
    const tahunSelect = document.getElementById('tahun');
    const formFieldset = document.getElementById('input-form');
    const statusAlert = document.getElementById('approval-status-alert');
    const approvals = <?php echo json_encode($approvals, 15, 512) ?>;
    const unitId = <?php echo e(Auth::user()->unit_id); ?>;

    function checkApprovalStatus() {
        const bulan = bulanSelect.value;
        const tahun = tahunSelect.value;
        const key = `${unitId}-${bulan}-${tahun}`;

        if (approvals[key]) {
            formFieldset.disabled = true;
            statusAlert.style.display = 'block';
        } else {
            formFieldset.disabled = false;
            statusAlert.style.display = 'none';
        }
    }
    bulanSelect.addEventListener('change', checkApprovalStatus);
    tahunSelect.addEventListener('change', checkApprovalStatus);
    checkApprovalStatus();
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /mnt/c/Users/aloys/Downloads/Project-KP-PTPN/resources/views/laporan/kependudukan.blade.php ENDPATH**/ ?>