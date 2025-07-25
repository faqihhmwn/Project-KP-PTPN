<aside class="app-sidebar bg-blue-dark shadow" data-bs-theme="dark">
  <!--begin::Sidebar Brand-->
  <div class="sidebar-brand">
    <a href="/" class="brand-link">
      <img src="{{ asset('assets/img/sidebar/logo-ptpn.png') }}" class="brand-image opacity-75 shadow" />
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

        <!-- rekap biaya -->
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fa-solid fa-money-bill-transfer"></i>
            <p>
              Rekapitulasi Biaya
              <i class="right bi bi-chevron-down"></i>
            </p>
          </a>
        <ul class="nav nav-treeview ps-3">
          <li class="nav-item">
            <a href="{{ route('rekap.regional.index') }}" class="nav-link">
              <i class="bi bi-chevron-right nav-icon"></i>
              <p>PTPN I Regional 7</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('rekap.bpjs.index') }}" class="nav-link">
              <i class="bi bi-chevron-right nav-icon"></i>
              <p>Iuran BPJS</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('rekap.kapitasi.index') }}" class="nav-link">
              <i class="bi bi-chevron-right nav-icon"></i>
              <p>Dana Kapitasi</p>
            </a>
        </ul>
      </li>

      @php
        $is_admin = Auth::guard('admin')->check();
        $obat_route = $is_admin ? route('admin.obat.dashboard') : route('obat.dashboard');
        $is_active = $is_admin ? request()->routeIs('admin.obat.*') : request()->routeIs('obat.*');
      @endphp
      <li class="nav-item">
        <a href="{{ $obat_route }}" class="nav-link {{ $is_active ? 'active' : '' }}">
          <i class="nav-icon bi bi-capsule"></i>
          <p>
          Farmasi
          </p>
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
          'peserta-kb' => 'Peserta KB',
          'metode-kb' => 'Metode KB',
          'kehamilan' => 'Kehamilan',
          'imunisasi' => 'Imunisasi',
          'kematian' => 'Kematian',
          'klaim-asuransi' => 'Klaim Asuransi',
          'kecelakaan-kerja' => 'Kecelakaan Kerja',
          'sakit-berkepanjangan' => 'Sakit Berkepanjangan',
          'absensi-dokter-honorer' => 'Absensi Dokter Honorer',
          'kategori-khusus' => 'Kategori Khusus',
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
