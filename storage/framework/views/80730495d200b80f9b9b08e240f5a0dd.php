<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h3 class="mb-4">Laporan Kategori Khusus (Unit <?php echo e($authUser->unit->nama ?? ''); ?>)</h3>

    
    <?php if(session('success')): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo e(session('success')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if(session('error')): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo e(session('error')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    
    <div class="card mb-4">
        <div class="card-header fw-bold">Input Data Baru</div>
        <div class="card-body">
            <form method="POST" action="<?php echo e(route('laporan.kategori-khusus.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-3"><label class="form-label">Unit</label><input type="text" class="form-control" value="<?php echo e($authUser->unit->nama); ?>" disabled></div>
                    <div class="col-md-3"><label for="subkategori_id_input" class="form-label">Subkategori</label><select name="subkategori_id" id="subkategori_id_input" class="form-select" required><option value="">-- Pilih Subkategori --</option><?php $__currentLoopData = $subkategoris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($sub->id); ?>"><?php echo e($sub->nama); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                    <div class="col-md-3"><label for="nama" class="form-label">Nama Pekerja</label><input type="text" name="nama" class="form-control" placeholder="Masukkan nama" required></div>
                    <div class="col-md-3"><label for="status" class="form-label">Status</label><select name="status" class="form-select" required><option value="">-- Pilih Status --</option><option value="Pekerja Tetap">Pekerja Tetap</option><option value="PKWT">PKWT</option><option value="Honor">Honor</option><option value="OS">OS</option></select></div>
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
                <div class="col-md-3"><label>Unit</label><input type="text" class="form-control" value="<?php echo e($authUser->unit->nama); ?>" disabled></div>
                <div class="col-md-3"><label>Filter Status</label><select name="status" class="form-select"><option value="">Semua Status</option><option value="Pekerja Tetap" <?php echo e($status == 'Pekerja Tetap' ? 'selected' : ''); ?>>Pekerja Tetap</option><option value="PKWT" <?php echo e($status == 'PKWT' ? 'selected' : ''); ?>>PKWT</option><option value="Honor" <?php echo e($status == 'Honor' ? 'selected' : ''); ?>>Honor</option><option value="OS" <?php echo e($status == 'OS' ? 'selected' : ''); ?>>OS</option></select></div>
                <div class="col-md-3"><label>Cari Nama</label><input type="text" name="search_name" class="form-control" placeholder="Masukkan nama..." value="<?php echo e($searchName); ?>"></div>
                <div class="col-md-12 mt-3"><button type="submit" class="btn btn-primary w-100">Tampilkan</button></div>
            </form>
        </div>
    </div>

    
    <div id="data-content-container">
        <?php echo $__env->make('laporan.partials.kategori-khusus_table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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

    // Form Dinamis untuk Form Input
    const subSelectInput = document.getElementById('subkategori_id_input');
    const jenisGroupInput = document.getElementById('jenisDisabilitasGroup');
    const ketGroupInput = document.getElementById('keteranganGroup');

    function toggleCreateFields() {
        if (!subSelectInput) return;
        const val = parseInt(subSelectInput.value);
        jenisGroupInput.style.display = (val === 82) ? 'block' : 'none';
        ketGroupInput.style.display = [82, 83, 84, 85].includes(val) ? 'block' : 'none';
    }

    subSelectInput.addEventListener('change', toggleCreateFields);
    toggleCreateFields();

    // Form Dinamis untuk Modal Edit 
    $(document).on('change', '.edit-subkategori', function() {
        const id = $(this).data('id');
        const val = parseInt($(this).val());
        const jenisGroup = $(`.jenis-disabilitas-group-${id}`);
        const ketGroup = $(`.keterangan-group-${id}`);
        
        jenisGroup.css('display', (val === 82) ? 'block' : 'none');
        ketGroup.css('display', [82, 83, 84, 85].includes(val) ? 'block' : 'none');
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /mnt/c/Users/aloys/Downloads/Project-KP-PTPN1/resources/views/laporan/kategori-khusus.blade.php ENDPATH**/ ?>