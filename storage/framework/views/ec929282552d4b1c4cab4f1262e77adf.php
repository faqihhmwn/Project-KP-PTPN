<?php $__env->startSection('content'); ?>
    <div class="container mt-4">
        <h3 class="mb-4">Laporan Metode KB</h3>

        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Form Edit Jumlah -->
        <?php if(isset($editItem)): ?>
            <div class="card mb-4">
                <div class="card-header">Edit Jumlah untuk Subkategori: <strong><?php echo e($editItem->subkategori->nama); ?></strong></div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('laporan.metode-kb.update', $editItem->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control" value="<?php echo e($editItem->jumlah); ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="<?php echo e(route('laporan.metode-kb.index')); ?>" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form Input Laporan -->
        <form method="POST" action="<?php echo e(route('laporan.metode-kb.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <select name="bulan" id="bulan" class="form-select" required>
                        <option value="">-- Pilih Bulan --</option>
                        <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($b); ?>"><?php echo e(DateTime::createFromFormat('!m', $b)->format('F')); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <select name="tahun" id="tahun" class="form-select" required>
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
                                <input type="number" name="jumlah[<?php echo e($sub->id); ?>]" class="form-control"
                                    min="0" required>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>

        <!-- Tabel Data yang Sudah Diinputkan -->
        <hr class="my-5">
        <h5>Data Tersimpan</h5>

        
        <form method="GET" action="<?php echo e(route('laporan.metode-kb.index')); ?>" class="row g-3 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari Subkategori"
                    value="<?php echo e(request('search')); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
        </form>

        
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="bulan" class="form-select">
                        <option value="">-- Filter Bulan --</option>
                        <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($b); ?>" <?php echo e(request('bulan') == $b ? 'selected' : ''); ?>>
                                <?php echo e(\Carbon\Carbon::create()->month($b)->translatedFormat('F')); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="tahun" class="form-select">
                        <option value="">-- Filter Tahun --</option>
                        <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?php echo e($y); ?>" <?php echo e(request('tahun') == $y ? 'selected' : ''); ?>>
                                <?php echo e($y); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Subkategori</th>
                    <th>Jumlah</th>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>Unit</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php echo $__env->make('laporan.modal.modal-metode-kb', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <tr>
                        <td><?php echo e($data->firstItem() + $i); ?></td>
                        <td><?php echo e($row->subkategori->nama); ?></td>
                        <td><?php echo e($row->jumlah); ?></td>
                        <td><?php echo e(DateTime::createFromFormat('!m', $row->bulan)->format('F')); ?></td>
                        <td><?php echo e($row->tahun); ?></td>
                        <td><?php echo e($row->unit->nama); ?></td>
                        <td>
                            <a href="<?php echo e(route('laporan.metode-kb.edit', $row->id)); ?>" class="btn btn-sm btn-warning"
                                data-bs-toggle="modal" data-bs-target="#editModal<?php echo e($row->id); ?>">
                                Edit
                            </a>
                            
                        </td>
                    </tr>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-3">
            <?php echo e($data->links('pagination::bootstrap-5')); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /mnt/c/Users/aloys/Downloads/Project-KP-PTPN/resources/views/laporan/metode-kb.blade.php ENDPATH**/ ?>