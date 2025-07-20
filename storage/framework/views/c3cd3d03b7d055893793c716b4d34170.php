<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h3 class="mb-4">Laporan Peserta KB (Unit <?php echo e($authUser->unit->nama ?? ''); ?>)</h3>

    
    <?php if(session('success')): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo e(session('success')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if(session('error')): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo e(session('error')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    
    <div class="card mb-4">
        <div class="card-header fw-bold">Input Laporan Bulanan</div>
        <div class="card-body">
            <form method="POST" action="<?php echo e(route('laporan.peserta-kb.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" value="<?php echo e($authUser->unit->nama); ?>" disabled>
                    </div>
                    <div class="col-md-3"><label for="bulan" class="form-label">Bulan</label><select name="bulan" id="bulan" class="form-select" required><option value="">-- Pilih Bulan --</option><?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($b); ?>"><?php echo e(DateTime::createFromFormat('!m', $b)->format('F')); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                    <div class="col-md-3"><label for="tahun" class="form-label">Tahun</label><select name="tahun" id="tahun" class="form-select" required><option value="">-- Pilih Tahun --</option><?php for($t = date('Y'); $t >= 2020; $t--): ?><option value="<?php echo e($t); ?>"><?php echo e($t); ?></option><?php endfor; ?></select></div>
                </div>

                <fieldset id="input-form-content">
                    <div id="approval-status-alert" class="alert alert-warning" style="display: none;">Data untuk periode ini sudah disetujui dan tidak dapat diubah.</div>
                    <table class="table table-bordered">
                        <thead><tr><th>Subkategori</th><th>Jumlah</th></tr></thead>
                        <tbody><?php $__currentLoopData = $subkategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><tr><td><?php echo e($sub->nama); ?></td><td><input type="number" name="jumlah[<?php echo e($sub->id); ?>]" class="form-control" min="0" value="" placeholder="Masukkan jumlah"></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Simpan Data</button>
                </fieldset>
            </form>
        </div>
    </div>

    <hr class="my-5">
    <h5 class="fw-bold">Data Tersimpan</h5>

    
    <div class="card mb-3">
        <div class="card-body">
            <p class="fw-bold">Filter Data Laporan</p>
            <form id="filter-form" method="GET" action="<?php echo e(route('laporan.peserta-kb.index')); ?>" class="row g-3 align-items-end">
                <div class="col-md-3"><label>Unit</label><input type="text" class="form-control" value="<?php echo e($authUser->unit->nama); ?>" disabled></div>
                <div class="col-md-3"><label>Filter Bulan</label><select name="bulan" class="form-select"><option value="">Semua Bulan</option><?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($b); ?>" <?php echo e($bulan == $b ? 'selected' : ''); ?>><?php echo e(\Carbon\Carbon::create()->month($b)->translatedFormat('F')); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div class="col-md-3"><label>Filter Tahun</label><select name="tahun" class="form-select"><option value="">Semua Tahun</option><?php for($y = date('Y'); $y >= 2020; $y--): ?><option value="<?php echo e($y); ?>" <?php echo e($tahun == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option><?php endfor; ?></select></div>
                <div class="col-md-3"><label>Filter Subkategori</label><select name="subkategori_id" class="form-select"><option value="">Semua Subkategori</option><?php $__currentLoopData = $subkategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($sub->id); ?>" <?php echo e($subkategoriId == $sub->id ? 'selected' : ''); ?>><?php echo e($sub->nama); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div class="col-md-12 mt-3"><button type="submit" class="btn btn-primary w-100">Tampilkan</button></div>
            </form>
        </div>
    </div>

    
    <div id="data-content-container">
        <?php echo $__env->make('laporan.partials.peserta-kb_table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // AJAX untuk Filter dan Paginasi
    function fetchData(url) {
        $.ajax({
            url: url,
            success: function(data) {
                $('#data-content-container').html(data);
                window.history.pushState({ path: url }, '', url);
            },
            error: function() { alert('Gagal memuat data.'); }
        });
    }

    $(document).on('click', '#data-content-container .pagination a', function(event) {
        event.preventDefault(); 
        fetchData($(this).attr('href'));
    });

    $('#filter-form').on('submit', function(event) {
        event.preventDefault();
        var url = $(this).attr('action') + '?' + $(this).serialize();
        fetchData(url);
    });

    // Logika untuk menonaktifkan form input jika periode sudah diapprove
    const bulanSelect = document.getElementById('bulan');
    const tahunSelect = document.getElementById('tahun');
    const formContent = document.getElementById('input-form-content'); 
    const statusAlert = document.getElementById('approval-status-alert');
    const approvals = <?php echo json_encode($approvals, 15, 512) ?>;
    const unitId = <?php echo e($authUser->unit_id); ?>;

    function checkApprovalStatus() {
        const bulan = bulanSelect.value;
        const tahun = tahunSelect.value;
        const key = `${unitId}-${bulan}-${tahun}`;

        if (approvals[key]) {
            formContent.disabled = true;
            statusAlert.style.display = 'block';
        } else {
            formContent.disabled = false;
            statusAlert.style.display = 'none';
        }
    }

    if(bulanSelect && tahunSelect) {
        bulanSelect.addEventListener('change', checkApprovalStatus);
        tahunSelect.addEventListener('change', checkApprovalStatus);
        checkApprovalStatus();
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /mnt/c/Users/aloys/Downloads/Project-KP-PTPN1/resources/views/laporan/peserta-kb.blade.php ENDPATH**/ ?>