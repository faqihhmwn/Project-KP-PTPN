<nav class="app-header navbar navbar-expand bg-light shadow" data-bs-theme="light">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Start Navbar Links-->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
        </ul>
        <!--end::Start Navbar Links-->
        <!--begin::End Navbar Links-->
        <ul class="navbar-nav ms-auto">
            <!--begin::Navbar Search-->

            <!--end::Navbar Search-->
            <!--begin::Messages Dropdown Menu-->
            <!--end::Messages Dropdown Menu-->
            <!--begin::Notifications Dropdown Menu-->
            <!--end::Notifications Dropdown Menu-->
            <!--begin::Fullscreen Toggle-->
            <li class="nav-item">
                <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                    <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                    <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
                </a>
            </li>
            <!--end::Fullscreen Toggle-->
            <!--begin::User Menu Dropdown-->
            <li class="nav-item dropdown user-menu">
                @auth
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="{{ asset('assets/img/user2-160x160.jpg') }}" class="user-image rounded-circle shadow"
                            alt="User Image" />
                        <span class="d-none d-md-inline">
                            {{ Auth::user()->name }} - {{ Auth::user()->unit->nama ?? '-' }}
                        </span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                        <li class="user-header text-bg-primary">
                            <img src="{{ asset('assets/img/user2-160x160.jpg') }}" class="rounded-circle shadow"
                                alt="User Image" />
                            <p>
                                {{ Auth::user()->name }} - {{ Auth::user()->unit->nama ?? '-' }}
                                <small>Bergabung sejak {{ Auth::user()->created_at->translatedFormat('F Y') }}</small>
                            </p>
                        </li>
                        <!-- lainnya... -->
                    </ul>
                @endauth

            </li>
            <!--end::User Image-->
            <!--begin::Menu Body-->
            <li class="user-body">
                <!--begin::Row-->
                {{-- <div class="row">
                            <div class="col-4 text-center"><a href="#">Followers</a></div>
                            <div class="col-4 text-center"><a href="#">Sales</a></div>
                            <div class="col-4 text-center"><a href="#">Friends</a></div>
                        </div> --}}
                <!--end::Row-->
            </li>
            <!--end::Menu Body-->
            <!--begin::Menu Footer-->
            <li class="user-footer">    
                <a href="{{ route('profile.edit') }}" class="btn btn-default btn-flat">Ganti Password</a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline float-end">
                    @csrf
                    <button type="submit" class="btn btn-default btn-flat">Sign out</button>
                </form>
            </li>
            <!--end::Menu Footer-->
        </ul>
        </li>
        <!--end::User Menu Dropdown-->
        </ul>
        <!--end::End Navbar Links-->
    </div>
    <!--end::Container-->
</nav>
