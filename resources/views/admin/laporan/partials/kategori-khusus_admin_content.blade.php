{{-- BAGIAN APPROVE/UNAPPROVE --}}
@if($unitId && $bulan && $tahun)
    @php $isApproved = isset($approvals[$unitId . '-' . $bulan . '-' . $tahun]); @endphp
    <div class="card mb-3">
        <div class="card-body">
            <p class="fw-bold">Persetujuan Periode</p>
            @if(!$isApproved)
                <form action="{{ route('laporan.kategori-khusus.approve') }}" method="POST" onsubmit="return confirm('Yakin ingin menyetujui dan mengunci semua entri pada periode ini?')">
                    @csrf
                    <input type="hidden" name="unit_id" value="{{ $unitId }}">
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Approve Periode Ini</button>
                </form>
            @else
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-0">
                    <span>Periode ini telah disetujui pada {{ $approvals[$unitId . '-' . $bulan . '-' . $tahun]->approved_at->format('d M Y H:i') }}.</span>
                    <form action="{{ route('laporan.kategori-khusus.unapprove') }}" method="POST" onsubmit="return confirm('Yakin ingin MEMBATALKAN persetujuan untuk periode ini?')">
                        @csrf
                        <input type="hidden" name="unit_id" value="{{ $unitId }}">
                        <input type="hidden" name="bulan" value="{{ $bulan }}">
                        <input type="hidden" name="tahun" value="{{ $tahun }}">
                        <button type="submit" class="btn btn-danger btn-sm">Un-approve</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endif

{{-- BAGIAN TABEL DATA --}}
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
                @php $isRowApproved = isset($approvals[$item->unit_id . '-' . $item->bulan . '-' . $item->tahun]); @endphp
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
                        @if($isRowApproved)
                            <span class="badge bg-success">Disetujui</span>
                        @else
                            <span class="badge bg-warning text-dark">Menunggu</span>
                        @endif
                    </td>
                    <td class="d-flex flex-wrap gap-2">
                        <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">Edit</a>
                        <!-- <form action="{{ route('laporan.kategori-khusus.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini secara permanen?')"> -->
                            <!-- @csrf -->
                            <!-- @method('DELETE') -->
                            <!-- <button type="submit" class="btn btn-sm btn-danger">Hapus</button> -->
                        <!-- </form> -->
                    </td>
                </tr>
                @include('admin.laporan.modal.modal-kategori-khusus')
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