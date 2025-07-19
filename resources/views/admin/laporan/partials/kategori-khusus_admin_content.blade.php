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
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $i => $item)
                <tr>
                    <td>{{ $data->firstItem() + $i }}</td>
                    <td>{{ $item->unit->nama ?? '-' }}</td>
                    <td>{{ $item->subkategori?->nama ?? 'Tidak Diketahui' }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>{{ $item->status }}</td>
                    {{-- DATA BARU --}}
                    <td>{{ $item->jenis_disabilitas ?? '-' }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                    <td class="d-flex flex-wrap gap-2">
                        <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">Edit</a>
                        <form action="{{ route('laporan.kategori-khusus.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini secara permanen?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @include('admin.laporan.modal.modal-kategori-khusus')
                @empty
                <tr><td colspan="8" class="text-center">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-center mt-3">
    {{ $data->links('pagination::bootstrap-5') }}
</div>