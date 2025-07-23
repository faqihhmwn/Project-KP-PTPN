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
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>Status Persetujuan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $i => $item)
                @php
                    $isApproved = isset($approvals[$item->unit_id . '-' . $item->bulan . '-' . $item->tahun]);
                @endphp
                <tr>
                    <td>{{ $data->firstItem() + $i }}</td>
                    <td>{{ $item->unit->nama ?? '-' }}</td>
                    <td>{{ $item->subkategori?->nama ?? 'Tidak Diketahui' }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>{{ $item->status }}</td>
                    <td>{{ $item->jenis_disabilitas ?? '-' }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                    <td>{{ DateTime::createFromFormat('!m', $item->bulan)->format('F') }}</td>
                    <td>{{ $item->tahun }}</td>
                    <td>
                        @if($isApproved)
                            <span class="badge bg-success">Disetujui</span>
                        @else
                            <span class="badge bg-warning text-dark">Menunggu</span>
                        @endif
                    </td>
                    <td>
                        @if(!$isApproved)
                            <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">Edit</a>
                        @else
                            <button class="btn btn-sm btn-secondary" disabled>Edit</button>
                        @endif
                    </td>
                </tr>
                @if(!$isApproved)
                    @include('laporan.modal.modal-kategori-khusus')
                @endif
                @empty
                <tr><td colspan="11" class="text-center">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-center mt-3">
    {{ $data->links('pagination::bootstrap-5') }}
</div>