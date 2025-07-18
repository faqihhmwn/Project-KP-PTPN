@extends('layout.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">Laporan Kependudukan</h3>

    @if (session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if (session('error'))<div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="card mb-4">
        <div class="card-header fw-bold">Input Laporan Bulanan</div>
        <div class="card-body">
            <form method="POST" action="{{ route('laporan.kependudukan.store') }}">
                @csrf
                <fieldset id="input-form">
                    <div class="row mb-3">
                        <div class="col-md-3"><label for="bulan" class="form-label">Bulan</label><select name="bulan" id="bulan" class="form-select" required>@foreach (range(1, 12) as $b)<option value="{{ $b }}" {{ old('bulan', date('n')) == $b ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $b)->format('F') }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label for="tahun" class="form-label">Tahun</label><select name="tahun" id="tahun" class="form-select" required>@for ($t = date('Y'); $t >= 2020; $t--)<option value="{{ $t }}" {{ old('tahun', date('Y')) == $t ? 'selected' : '' }}>{{ $t }}</option>@endfor</select></div>
                    </div>
                    <div id="approval-status-alert" class="alert alert-warning" style="display: none;">Data untuk periode ini sudah disetujui dan tidak dapat diubah.</div>
                    <table class="table table-bordered">
                        <thead><tr><th>Subkategori</th><th>Jumlah</th></tr></thead>
                        <tbody>@foreach ($subkategori as $sub)<tr><td>{{ $sub->nama }}</td><td><input type="number" name="jumlah[{{ $sub->id }}]" class="form-control" min="0" value="0" required></td></tr>@endforeach</tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </fieldset>
            </form>
        </div>
    </div>

    <hr class="my-5">
    <h5 class="fw-bold">Data Tersimpan</h5>

    <table class="table table-striped">
        <thead><tr><th>No</th><th>Subkategori</th><th>Jumlah</th><th>Bulan</th><th>Tahun</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($data as $i => $row)
            @php
                $isApproved = isset($approvals[Auth::user()->unit_id . '-' . $row->bulan . '-' . $row->tahun]);
            @endphp
            <tr>
                <td>{{ $data->firstItem() + $i }}</td>
                <td>{{ $row->subkategori->nama }}</td>
                <td>{{ $row->jumlah }}</td>
                <td>{{ DateTime::createFromFormat('!m', $row->bulan)->format('F') }}</td>
                <td>{{ $row->tahun }}</td>
                <td>
                    @if(!$isApproved)
                        <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $row->id }}">Edit</a>
                    @else
                        <span class="badge bg-success">Telah Disetujui</span>
                    @endif
                </td>
            </tr>
            @if(!$isApproved)
              @include('laporan.modal.modal-kependudukan')
            @endif
            @empty
            <tr><td colspan="6" class="text-center">Belum ada data</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-center mt-3">{{ $data->links('pagination::bootstrap-5') }}</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const bulanSelect = document.getElementById('bulan');
    const tahunSelect = document.getElementById('tahun');
    const formFieldset = document.getElementById('input-form');
    const statusAlert = document.getElementById('approval-status-alert');
    const approvals = @json($approvals);
    const unitId = {{ Auth::user()->unit_id }};

    function checkApprovalStatus() {
        const bulan = bulanSelect.value;
        const tahun = tahunSelect.value;
        const key = `${unitId}-${bulan}-${tahun}`;

        if (approvals[key]) {
            formFieldset.disabled = true;
            statusAlert.style.display = 'block';
        } else {
            formFieldset.disabled = false;
            statusAlert.style.display = 'none';
        }
    }
    bulanSelect.addEventListener('change', checkApprovalStatus);
    tahunSelect.addEventListener('change', checkApprovalStatus);
    checkApprovalStatus();
});
</script>
@endsection