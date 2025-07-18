<table class="table table-striped">
    <thead>
        <tr><th>No</th><th>Unit</th><th>Subkategori</th><th>Jumlah</th><th>Bulan</th><th>Tahun</th><th>Status</th><th>Aksi</th></tr>
    </thead>
    <tbody>
        @forelse($data as $i => $row)
        @php $isRowApproved = isset($approvals[$row->unit_id . '-' . $row->bulan . '-' . $row->tahun]); @endphp
        <tr>
            <td>{{ $data->firstItem() + $i }}</td>
            <td>{{ $row->unit->nama }}</td>
            <td>{{ $row->subkategori->nama }}</td>
            <td>{{ $row->jumlah }}</td>
            <td>{{ DateTime::createFromFormat('!m', $row->bulan)->format('F') }}</td>
            <td>{{ $row->tahun }}</td>
            <td>@if($isRowApproved)<span class="badge bg-success">Disetujui</span>@else<span class="badge bg-warning text-dark">Menunggu</span>@endif</td>
            <td>
                {{-- Tombol Edit selalu aktif untuk Admin --}}
                <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $row->id }}">Edit</a>

                {{-- Tombol Hapus hanya untuk Admin --}}
                <form action="{{ route('laporan.kependudukan.destroy', $row->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini secara permanen?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @include('admin.laporan.modal.modal-kependudukan')
        @empty
        <tr><td colspan="8" class="text-center">Belum ada data.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="d-flex justify-content-center mt-3">
    {{ $data->withQueryString()->links('pagination::bootstrap-5') }}
</div>