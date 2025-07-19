<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h3 class="mb-4">Laporan Kategori Khusus (Admin)</h3>

    
    <?php if(session('success')): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo e(session('success')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if(session('error')): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo e(session('error')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    
    <div class="card mb-4">
        <div class="card-header fw-bold">Input Data Atas Nama Unit</div>
        <div class="card-body">
            <form method="POST" action="<?php echo e(route('laporan.kategori-khusus.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-3"><label class="form-label">Unit</label><select name="unit_id" class="form-select" required><option value="">-- Pilih Unit --</option><?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($unit->id); ?>"><?php echo e($unit->nama); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                    <div class="col-md-3"><label class="form-label">Subkategori</label><select name="subkategori_id" id="subkategori_id_input" class="form-select" required><option value="">-- Pilih Subkategori --</option><?php $__currentLoopData = $subkategoris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($sub->id); ?>"><?php echo e($sub->nama); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                    <div class="col-md-3"><label class="form-label">Nama Pekerja</label><input type="text" name="nama" class="form-control" placeholder="Masukkan nama" required></div>
                    <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select" required><option value="">-- Pilih Status --</option><option value="Pekerja Tetap">Pekerja Tetap</option><option value="PKWT">PKWT</option><option value="Honor">Honor</option><option value="OS">OS</option></select></div>
                    <div class="col-md-3" id="jenisDisabilitasGroup" style="display: none;"><label class="form-label">Jenis Disabilitas</label><select name="jenis_disabilitas" class="form-select"><option value="">-- Pilih Jenis --</option><option value="Fisik">Fisik</option><option value="Intelektual">Intelektual</option><option value="Sensorik">Sensorik</option><option value="Mental">Mental</option></select></div>
                    <div class="col-md-3" id="keteranganGroup" style="display: none;"><label class="form-label">Keterangan</label><input type="text" name="keterangan" class="form-control"></div>
                    <div class="col-md-12"><button type="submit" class="btn btn-primary mt-3">Simpan</button></div>
                </div>
            </form>
        </div>
    </div>
    
    <hr class="my-5">
    <h5 class="fw-bold">Data Tersimpan</h5>

    
    <div class="card mb-3">
        <div class="card-body">
            <p class="fw-bold">Filter Data Laporan</p>
            <form id="filter-form" method="GET" action="<?php echo e(route('laporan.kategori-khusus.index')); ?>" class="row g-3 align-items-end">
                <div class="col-md-3"><label>Filter Unit</label><select name="unit_id" class="form-select"><option value="">Semua Unit</option><?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($unit->id); ?>" <?php echo e($unitId == $unit->id ? 'selected' : ''); ?>><?php echo e($unit->nama); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div class="col-md-3"><label>Filter Status</label><select name="status" class="form-select"><option value="">Semua Status</option><option value="Pekerja Tetap" <?php echo e($status == 'Pekerja Tetap' ? 'selected' : ''); ?>>Pekerja Tetap</option><option value="PKWT" <?php echo e($status == 'PKWT' ? 'selected' : ''); ?>>PKWT</option><option value="Honor" <?php echo e($status == 'Honor' ? 'selected' : ''); ?>>Honor</option><option value="OS" <?php echo e($status == 'OS' ? 'selected' : ''); ?>>OS</option></select></div>
                <div class="col-md-3"><label>Cari Nama</label><input type="text" name="search_name" class="form-control" placeholder="Masukkan nama..." value="<?php echo e($searchName); ?>"></div>
                <div class="col-md-12 mt-3"><button type="submit" class="btn btn-primary w-100">Tampilkan</button></div>
            </form>
        </div>
    </div>

    
    <div id="data-content-container">
        <?php echo $__env->make('admin.laporan.partials.kategori-khusus_admin_content', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // AJAX
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

    // Form Dinamis
    function dynamicFields(selector, jenisGroup, ketGroup) {
        const subSelect = document.querySelector(selector);
        if (!subSelect) return;
        
        const jenisDiv = document.querySelector(jenisGroup);
        const ketDiv = document.querySelector(ketGroup);

        function toggleFields() {
            const val = parseInt(subSelect.value);
            if(jenisDiv) jenisDiv.style.display = (val === 82) ? 'block' : 'none';
            if(ketDiv) ketDiv.style.display = [82, 83, 84, 85].includes(val) ? 'block' : 'none';
        }

        subSelect.addEventListener('change', toggleFields);
        toggleFields();
    }
    
    // Panggil untuk form input utama
    dynamicFields('#subkategori_id_input', '#jenisDisabilitasGroup', '#keteranganGroup');
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /mnt/c/Users/aloys/Downloads/Project-KP-PTPN1/resources/views/admin/laporan/kategori-khusus.blade.php ENDPATH**/ ?>