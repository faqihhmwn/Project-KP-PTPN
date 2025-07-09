    <aside class="app-sidebar bg-blue-dark shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="/" class="brand-link">
            <!--begin::Brand Image-->
            <img
              src="/assets/img/AdminLTELogo.png"
              alt="AdminLTE Logo"
              class="brand-image opacity-75 shadow"
            />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">AdminLTE 4</span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              role="menu"
              data-accordion="false"
            >
              <li class="nav-item">
                <a href="/" class="nav-link">
                  <i class="nav-icon bi bi-house"></i>
                  <p>
                    Dashboard
                  </p>
                </a>
              </li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-capsule"></i>
                  <p>
                    Farmasi
                    <i class="right bi bi-chevron-down"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview ms-3">
                  <li class="nav-item">
                    <a href="/farmasi/dashboard-obat" class="nav-link">
                      <i class="bi bi-box-seam nav-icon"></i>
                      <p>Dashboard Obat</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="/farmasi/rekapitulasi-obat" class="nav-link">
                      <i class="bi bi-clipboard-data nav-icon"></i>
                      <p>Rekapitulasi Obat</p>
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
            <!--end::Sidebar Menu-->
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>