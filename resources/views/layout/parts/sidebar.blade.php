<aside class="app-sidebar bg-blue-dark shadow" data-bs-theme="dark">
  <!--begin::Sidebar Brand-->
  <div class="sidebar-brand">
    <a href="./index.html" class="brand-link">
      <img src="assets/img/sidebar/logo-ptpn.png" alt="AdminLTE Logo" class="brand-image opacity-75 shadow" />
      <span class="brand-text fw-light">Puskemas PTPN</span>
    </a>
  </div>
  <!--end::Sidebar Brand-->

  <!--begin::Sidebar Wrapper-->
  <div class="sidebar-wrapper">
    <nav class="mt-2">
      <!--begin::Sidebar Menu-->
      <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
          <a href="/" class="nav-link">
            <i class="nav-icon bi bi-house"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- Tombol buka offcanvas -->
        <li class="nav-item">
          <a href="#" class="nav-link" data-bs-toggle="offcanvas" data-bs-target="#laporanSidebar" aria-controls="laporanSidebar">
            <i class="nav-icon bi bi-journal-medical"></i>
            <p>Laporan Kesehatan</p>
          </a>
        </li>

        {{-- Tambahan lainnya jika diperlukan --}}
      </ul>
      <!--end::Sidebar Menu-->
    </nav>
  </div>
  <!--end::Sidebar Wrapper-->
</aside>

<!-- Offcanvas Kategori Laporan -->
<div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="laporanSidebar" aria-labelledby="laporanSidebarLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="laporanSidebarLabel">Laporan Kesehatan</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body overflow-auto">
    <ul class="list-group list-group-flush">
      @php
        $kategoris = [
          'kependudukan' => 'Kependudukan',
          'penyakit' => 'Penyakit',
          'opname' => 'Opname',
          'penyakit-kronis' => 'Penyakit Kronis',
          'konsultasi-klinik' => 'Konsultasi Klinik',
          'cuti-sakit' => 'Cuti Sakit',
          'pekerja-disabilitas' => 'Pekerja Disabilitas',
          'cuti-hamil' => 'Cuti Hamil',
          'cuti-melahirkan' => 'Cuti Melahirkan',
          'cuti-karena-istri-melahirkan' => 'Cuti Karena Istri Melahirkan',
          // Tambahkan kategori lainnya jika ada
        ];
      @endphp

      @foreach ($kategoris as $slug => $nama)
        <li class="list-group-item bg-dark border-secondary">
          <a href="{{ url('/laporan/' . $slug) }}" class="text-white text-decoration-none d-block">
            {{ $nama }}
          </a>
        </li>
      @endforeach
    </ul>
  </div>
</div>
