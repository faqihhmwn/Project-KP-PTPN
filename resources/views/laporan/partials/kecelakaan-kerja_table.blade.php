{{-- TAMBAHKAN TOMBOL BARU DI SINI --}}
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('laporan.kecelakaan-kerja.export', request()->query()) }}" class="btn btn-outline-success" target="_blank">
        <i class="fas fa-file-excel"></i> Export Excel
    </a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Unit</th>
                    <th>Subkategori</th>
                    <th>Jumlah</th>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $i => $row)
                @php
                $isApproved = isset($approvals[$row->unit_id . '-' . $row->bulan . '-' . $row->tahun]);
                @endphp
                <tr>
                    <td>{{ $data->firstItem() + $i }}</td>
                    <td>{{ $row->unit->nama }}</td>
                    <td>{{ $row->subkategori->nama }}</td>
                    <td>{{ $row->jumlah }}</td>
                    <td>{{ DateTime::createFromFormat('!m', $row->bulan)->format('F') }}</td>
                    <td>{{ $row->tahun }}</td>
                    <td>
                        @if($isApproved)
                        <span class="badge bg-success">Disetujui</span>
                        @else
                        <span class="badge bg-warning text-dark">Menunggu</span>
                        @endif
                    </td>
                    <td>
                        @if(!$isApproved)
                        <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $row->id }}">Edit</a>
                        @else
                        <button class="btn btn-sm btn-secondary" disabled>Edit</button>
                        @endif
                    </td>
                </tr>
                @if(!$isApproved)
                @include('laporan.modal.modal-kecelakaan-kerja')
                @endif
                @empty
                <tr>
                    <td colspan="8" class="text-center">Belum ada data.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-center mt-3">
    {{ $data->links('pagination::bootstrap-5') }}
</div>