{{-- BAGIAN APPROVE/UNAPPROVE --}}
@if($unitId && $bulan && $tahun)
@php $isApproved = isset($approvals[$unitId . '-' . $bulan . '-' . $tahun]); @endphp
<div class="card mb-3">
    <div class="card-body">
        <p class="fw-bold">Persetujuan Periode</p>
        @if(!$isApproved)
        <form action="{{ route('laporan.imunisasi.approve') }}" method="POST" onsubmit="return confirm('Yakin ingin menyetujui dan mengunci data untuk periode ini?')">
            @csrf
            <input type="hidden" name="unit_id" value="{{ $unitId }}">
            <input type="hidden" name="bulan" value="{{ $bulan }}">
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Approve Periode Ini</button>
        </form>
        @else
        <div class="alert alert-info d-flex justify-content-between align-items-center mb-0">
            <span>Periode ini telah disetujui pada {{ $approvals[$unitId . '-' . $bulan . '-' . $tahun]->approved_at->format('d M Y H:i') }}.</span>
            <form action="{{ route('laporan.imunisasi.unapprove') }}" method="POST" onsubmit="return confirm('Yakin ingin MEMBATALKAN persetujuan?')">
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

{{-- TAMBAHKAN TOMBOL BARU DI SINI --}}
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('laporan.imunisasi.export', request()->query()) }}" class="btn btn-outline-success" target="_blank">
        <i class="fas fa-file-excel"></i> Export Excel
    </a>
</div>

{{-- BAGIAN TABEL DATA --}}
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
                @php $isRowApproved = isset($approvals[$row->unit_id . '-' . $row->bulan . '-' . $row->tahun]); @endphp
                <tr>
                    <td>{{ $data->firstItem() + $i }}</td>
                    <td>{{ $row->unit->nama }}</td>
                    <td>{{ $row->subkategori->nama }}</td>
                    <td>{{ $row->jumlah }}</td>
                    <td>{{ DateTime::createFromFormat('!m', $row->bulan)->format('F') }}</td>
                    <td>{{ $row->tahun }}</td>
                    <td>@if($isRowApproved)<span class="badge bg-success">Disetujui</span>@else<span class="badge bg-warning text-dark">Menunggu</span>@endif</td>
                    <td class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $row->id }}">Edit</a>
                        <!-- <form action="{{ route('laporan.imunisasi.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini secara permanen?')"> -->
                        <!-- @csrf -->
                        <!-- @method('DELETE') -->
                        <!-- <button type="submit" class="btn btn-sm btn-danger">Hapus</button> -->
                        <!-- </form> -->
                    </td>
                </tr>
                @include('admin.laporan.modal.modal-imunisasi')
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