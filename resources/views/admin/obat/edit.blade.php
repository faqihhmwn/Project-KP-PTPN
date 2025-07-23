@extends('layout.app')

@section('title', 'Daftar Obat - Admin')

@section('content')
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>Daftar Obat (Admin)</h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.obat.dashboard') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali ke Farmasi
                        </a>
                        <a href="{{ route('admin.obat.rekapitulasi') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-chart-bar"></i> Rekapitulasi
                        </a>
                        <a href="{{ route('admin.obat.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Obat
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <form method="GET" action="<?php echo e(route('admin.obat.index')); ?>" class="d-flex gap-2">
                                <select name="unit_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Tampilkan Semua Unit --</option>
                                    <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($unit->id); ?>" <?php echo e(request('unit_id') == $unit->id ? 'selected' : ''); ?>>
                                            <?php echo e($unit->nama); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <input type="text" name="search" class="form-control" placeholder="Cari nama/jenis obat..." value="<?php echo e(request('search')); ?>">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                <a href="<?php echo e(route('admin.obat.index')); ?>" class="btn btn-secondary"><i class="fas fa-times"></i></a>
                            </form>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="text-muted">Total: <?php echo e($obats->total()); ?> obat</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Nama Obat</th>
                                    <th>Unit</th>
                                    <th class="text-center">Jenis</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-center">Stok Awal</th>
                                    <th class="text-center">Stok Sisa</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $obats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $obat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="text-center"><?php echo e($obats->firstItem() + $index); ?></td>
                                        <td class="fw-medium"><?php echo e($obat->nama_obat ?? '-'); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo e($obat->unit->nama ?? 'N/A'); ?></span></td>
                                        <td class="text-center"><?php echo e($obat->jenis_obat ?? '-'); ?></td>
                                        <td class="text-end fw-medium">Rp <?php echo e(number_format($obat->harga_satuan, 0, ',', '.')); ?></td>
                                        <td class="text-center"><?php echo e(number_format($obat->stok_awal)); ?></td>
                                        <td class="text-center">
                                            <span class="badge <?php echo e($obat->stok_sisa <= 10 ? 'bg-danger' : ($obat->stok_sisa <= 50 ? 'bg-warning text-dark' : 'bg-success')); ?>">
                                                <?php echo e(number_format($obat->stok_sisa)); ?>

                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="<?php echo e(route('admin.obat.show', $obat)); ?>" class="btn btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo e(route('admin.obat.edit', $obat)); ?>" class="btn btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="<?php echo e(route('admin.obat.destroy', $obat)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-danger" 
                                                            onclick="return confirm('Anda yakin ingin menghapus obat ini secara permanen? Tindakan ini tidak bisa dibatalkan.')" 
                                                            title="Hapus Permanen">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form> 
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <p class="text-muted">Data obat tidak ditemukan. Coba filter unit yang lain atau tambahkan obat baru.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if($obats->hasPages()): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                Menampilkan <?php echo e($obats->firstItem() ?? 0); ?> - <?php echo e($obats->lastItem() ?? 0); ?> 
                                dari <?php echo e($obats->total()); ?> data
                            </div>
                            <div>
                                <?php echo e($obats->links()); ?>

                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>