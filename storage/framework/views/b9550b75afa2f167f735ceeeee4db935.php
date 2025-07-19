

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h3 class="mb-4">Laporan Kependudukan (Admin)</h3>

    
    <?php if(session('success')): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo e(session('success')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if(session('error')): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo e(session('error')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    
    <div class="card mb-4">
        <div class="card-header fw-bold">Input Data Atas Nama Unit</div>
        <div class="card-body">
            <form method="POST" action="<?php echo e(route('laporan.kependudukan.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="unit_id_input" class="form-label">Unit</label>
                        <select name="unit_id" id="unit_id_input" class="form-select" required>
                            <option value="">-- Pilih Unit --</option>
                            <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($unit->id); ?>"><?php echo e($unit->nama); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="bulan_input" class="form-label">Bulan</label>
                        
                        <select name="bulan" id="bulan_input" class="form-select" required>
                            <option value="">-- Pilih Bulan --</option>
                            <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($b); ?>"><?php echo e(DateTime::createFromFormat('!m', $b)->format('F')); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="tahun_input" class="form-label">Tahun</label>
                        
                        <select name="tahun" id="tahun_input" class="form-select" required>
                            <option value="">-- Pilih Tahun --</option>
                            <?php for($t = date('Y'); $t >= 2020; $t--): ?>
                                <option value="<?php echo e($t); ?>"><?php echo e($t); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Subkategori</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $subkategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($sub->nama); ?></td>
                            <td>
                                
                                <input type="number" name="jumlah[<?php echo e($sub->id); ?>]" class="form-control" min="0" value="" placeholder="Masukkan jumlah">
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary mt-3">Simpan Data</button>
            </form>
        </div>
    </div>
    
    <hr class="my-5">
    <h5 class="fw-bold">Data Tersimpan</h5>

    
    <div class="card mb-3">
        <div class="card-body">
            <p class="fw-bold">Filter dan Persetujuan</p>
            <form id="filter-form" method="GET" class="row g-3 mb-3 align-items-end">
                <div class="col-md-3"><label>Filter Unit</label><select name="unit_id" class="form-select"><option value="">Semua Unit</option><?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($unit->id); ?>" <?php echo e($unitId == $unit->id ? 'selected' : ''); ?>><?php echo e($unit->nama); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div class="col-md-3"><label>Filter Bulan</label><select name="bulan" class="form-select"><option value="">Semua Bulan</option><?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($b); ?>" <?php echo e($bulan == $b ? 'selected' : ''); ?>><?php echo e(\Carbon\Carbon::create()->month($b)->translatedFormat('F')); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div class="col-md-3"><label>Filter Tahun</label><select name="tahun" class="form-select"><option value="">Semua Tahun</option><?php for($y = date('Y'); $y >= 2020; $y--): ?><option value="<?php echo e($y); ?>" <?php echo e($tahun == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option><?php endfor; ?></select></div>
                <div class="col-md-3">
                    <label>Filter Subkategori</label>
                    <select name="subkategori_id" class="form-select">
                        <option value="">Semua Subkategori</option>
                        <?php $__currentLoopData = $subkategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($sub->id); ?>" <?php echo e($subkategoriId == $sub->id ? 'selected' : ''); ?>><?php echo e($sub->nama); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-12 mt-3">
                    <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
                </div>
            </form>
            
            <?php if($unitId && $bulan && $tahun): ?>
                <?php $isApproved = isset($approvals[$unitId . '-' . $bulan . '-' . $tahun]); ?>
                <?php if(!$isApproved): ?>
                <form action="<?php echo e(route('laporan.kependudukan.approve')); ?>" method="POST" onsubmit="return confirm('Yakin ingin menyetujui dan mengunci data untuk periode ini?')"><?php echo csrf_field(); ?><input type="hidden" name="unit_id" value="<?php echo e($unitId); ?>"><input type="hidden" name="bulan" value="<?php echo e($bulan); ?>"><input type="hidden" name="tahun" value="<?php echo e($tahun); ?>"><button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Approve Periode Ini</button></form>
                <?php else: ?>
                 <div class="alert alert-info d-flex justify-content-between align-items-center"><span>Periode ini telah disetujui pada <?php echo e($approvals[$unitId . '-' . $bulan . '-' . $tahun]->approved_at->format('d M Y H:i')); ?>.</span><form action="<?php echo e(route('laporan.kependudukan.unapprove')); ?>" method="POST" onsubmit="return confirm('Yakin ingin MEMBATALKAN persetujuan?')"><?php echo csrf_field(); ?><input type="hidden" name="unit_id" value="<?php echo e($unitId); ?>"><input type="hidden" name="bulan" value="<?php echo e($bulan); ?>"><input type="hidden" name="tahun" value="<?php echo e($tahun); ?>"><button type="submit" class="btn btn-danger btn-sm">Un-approve</button></form></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    
    <div id="data-tersimpan-container">
        <?php echo $__env->make('admin.laporan.partials.kependudukan_table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    $(document).on('click', '#data-tersimpan-container .pagination a', function(event) {
        event.preventDefault();
        var url = $(this).attr('href');
        
        $.ajax({
            url: url,
            success: function(data) {
                $('#data-tersimpan-container').html(data);
                window.history.pushState({ path: url }, '', url);
            },
            error: function() {
                alert('Gagal memuat data. Silakan coba lagi.');
            }
        });
    }

        // Menangani klik pada link paginasi
    $(document).on('click', '#data-tersimpan-container .pagination a', function(event) {
        event.preventDefault(); 
        var url = $(this).attr('href');
        fetchData(url);
    });

    // Menangani submit form filter
    $('#filter-form').on('submit', function(event) {
        event.preventDefault();
        var url = $(this).attr('action') + '?' + $(this).serialize();
        fetchData(url);
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /mnt/c/Users/aloys/Downloads/Project-KP-PTPN1/resources/views/admin/laporan/kependudukan.blade.php ENDPATH**/ ?>