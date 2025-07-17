

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    
    <div class="alert alert-success" id="welcome-alert">
        Selamat datang, <?php echo e($authUser->name); ?>!
        <?php if($is_admin): ?>
            Anda masuk sebagai Admin Pusat.
        <?php else: ?>
            Anda masuk dari unit <?php echo e($authUser->unit->nama ?? '-'); ?>.
        <?php endif; ?>
    </div>

    
    <form method="GET" action="<?php echo e(route('dashboard')); ?>" class="row g-2 align-items-end mb-4">
        
        <?php if($is_admin): ?>
        <div class="col-md-3">
            <label for="unit_id" class="form-label">Pilih Unit</label>
            <select name="unit_id" class="form-select" id="unit_id">
                <option value="">Semua Unit</option>
                <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($unit->id); ?>" <?php echo e($unitId == $unit->id ? 'selected' : ''); ?>>
                        <?php echo e($unit->nama); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <?php endif; ?>
        
        <div class="col-md-3">
            <label for="bulan" class="form-label">Bulan</label>
            <select name="bulan" class="form-select" id="bulan">
                <option value="">Semua Bulan</option>
                <?php for($i = 1; $i <= 12; $i++): ?>
                    <option value="<?php echo e($i); ?>" <?php echo e(($bulan == $i) ? 'selected' : ''); ?>>
                        <?php echo e(DateTime::createFromFormat('!m', $i)->format('F')); ?>

                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="tahun" class="form-label">Tahun</label>
            <select name="tahun" class="form-select" id="tahun">
                <option value="">Semua Tahun</option>
                <?php for($y = date('Y'); $y >= 2000; $y--): ?>
                    <option value="<?php echo e($y); ?>" <?php echo e(($tahun == $y) ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="search" class="form-label">Cari Subkategori</label>
            <input type="text" name="search" class="form-control" id="search" value="<?php echo e(request('search')); ?>" placeholder="Ketik nama subkategori...">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary mt-2" type="submit">Tampilkan</button>
        </div>
    </form>

    
    <ul class="nav nav-tabs mb-3" id="dashboardTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="ringkasan-tab" data-bs-toggle="tab" data-bs-target="#ringkasan" type="button" role="tab">Ringkasan</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="grafik-tab" data-bs-toggle="tab" data-bs-target="#grafik" type="button" role="tab">Grafik</button>
        </li>
    </ul>
    <div class="tab-content" id="dashboardTabContent">
        
        <div class="tab-pane fade show active" id="ringkasan" role="tabpanel">
            <div class="row g-4">
                <?php $__currentLoopData = $ringkasan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kategori): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $slug = \Illuminate\Support\Str::slug($kategori['nama'], '-');
                        $colors = ['primary', 'success', 'warning', 'info', 'danger', 'secondary'];
                        $color = $colors[$loop->index % count($colors)];
                        $filteredSubs = collect($kategori['subkategori'])->filter(function ($sub) {
                            return request('search') === null || stripos($sub['nama'], request('search')) !== false;
                        });
                    ?>

                    <?php if($filteredSubs->count()): ?>
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="card-title text-<?php echo e($color); ?>"><?php echo e($kategori['nama']); ?></h5>
                                        <span class="badge bg-<?php echo e($color); ?> rounded-pill">
                                            Total: <?php echo e($filteredSubs->sum('total')); ?>

                                        </span>
                                    </div>
                                    <ul class="list-group list-group-flush small">
                                        <?php $__currentLoopData = $filteredSubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                <?php echo e($sub['nama']); ?>

                                                <span class="badge bg-light text-dark"><?php echo e($sub['total']); ?></span>
                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                    <?php if(!$is_admin): ?>
                                    <div class="mt-3 text-end">
                                        <a href="<?php echo e(url('/laporan/' . $slug)); ?>" class="btn btn-sm btn-outline-<?php echo e($color); ?>">
                                            Lihat Detail
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="tab-pane fade" id="grafik" role="tabpanel">
            <canvas id="kategoriChart"></canvas>
        </div>
    </div>
</div>


<script>
    setTimeout(() => {
        const alert = document.getElementById('welcome-alert');
        if (alert) {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = 0;
            setTimeout(() => alert.remove(), 500);
        }
    }, 2000);
</script>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('kategoriChart').getContext('2d');
    const kategoriChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($ringkasan, 'nama')); ?>,
            datasets: [{
                label: 'Jumlah Laporan',
                data: <?php echo json_encode(array_column($ringkasan, 'total')); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /mnt/c/Users/aloys/Downloads/Project-KP-PTPN/resources/views/admin-dashboard.blade.php ENDPATH**/ ?>