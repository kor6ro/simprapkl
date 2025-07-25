<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Laravel 11</title>
    {{-- <link rel="shortcut icon" href="{{ asset('/favicon.svg') }}" type="image/x-icon"> --}}

    <!-- Vendors styles-->
    <link href="{{ asset('assets/icons/coreui/css/free.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/icons/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/plugins/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/plugins/datatables.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/plugins/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Main styles for this application-->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" />


    {{-- CSS --}}
    @yield('css')
</head>

<body data-sidebar="colored">
    <!-- Begin page -->
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="{{ route('dashboard') }}" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="img-fluid"
                                    style="max-width: 40px; height: auto; filter: grayscale(1) brightness(0) invert(1);">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="img-fluid"
                                    style="max-height: 40px; filter: grayscale(1) brightness(0) invert(1);">
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect"
                        id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>
                </div>
                <div class="d-flex">
                    <div class="dropdown d-none d-lg-inline-block ms-1">
                        <button type="button" class="btn header-item noti-icon waves-effect"
                            data-bs-toggle="fullscreen">
                            <i class="cil-fullscreen font-size-18"></i>
                        </button>
                    </div>
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item noti-icon waves-effect"
                            id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <i class="cil-bell"></i>
                            <span class="badge bg-danger rounded-pill">2</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                            aria-labelledby="page-header-notifications-dropdown">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0" key="t-notifications"> Notifications </h6>
                                    </div>
                                    <div class="col-auto">
                                        <a href="#" class="small" key="t-view-all"> View All</a>
                                    </div>
                                </div>
                            </div>
                            <div data-simplebar style="max-height: 230px;">
                                <a href="javascript:;" class="text-reset notification-item">
                                    <div class="d-flex">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title bg-primary rounded-circle font-size-16">
                                                <i class="fa fa-shopping-bag"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1" key="t-your-order">Your order is placed</h6>
                                            <div class="font-size-12 text-muted">
                                                <p class="mb-1" key="t-grammer">
                                                    If several languages coalesce the grammar
                                                </p>
                                                <p class="mb-0">
                                                    <i class="far fa-clock me-1"></i>
                                                    <span key="t-min-ago">3 min ago</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <a href="javascript:;" class="text-reset notification-item">
                                    <div class="d-flex">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title bg-success rounded-circle font-size-16">
                                                <i class="fa fa-check"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1" key="t-shipped">Your item is shipped</h6>
                                            <div class="font-size-12 text-muted">
                                                <p class="mb-1" key="t-grammer">
                                                    If several languages coalesce the grammar
                                                </p>
                                                <p class="mb-0">
                                                    <i class="far fa-clock me-1"></i>
                                                    <span key="t-min-ago">3 min ago</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user"
                                src="{{ asset('assets/images/placeholder.jpg') }}" alt="Header Avatar">
                            <span class="d-none d-xl-inline-block ms-1">Profile</span>
                            <i class="fa fa-chevron-down d-none d-xl-inline-block ms-1" style="font-size: 10px;"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!-- item-->
                            <a class="dropdown-item" href="{{ route('profile.index') }}">
                                <i class="far fa-user font-size-12 align-middle me-1"></i>
                                <span key="t-profile">Profile</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}">
                                <i class="fa fa-sign-out-alt font-size-12 align-middle me-1 text-danger"></i>
                                <span key="t-logout">Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Sidebars --}}
        <div class="vertical-menu">
            <div data-simplebar class="h-100">
                <div id="sidebar-menu">
                    @include('layout.sidebar')
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    {{-- Content --}}
                    @yield('content')
                </div>
            </div>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row justify-content-end">
                        <div class="col-auto">
                            <div class="text-sm-end d-none d-sm-block">
                                Powered By <b class="text-primary">Cegeh Ngoding</b>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- Skote and necessary plugins-->
    <script src="{{ asset('assets/js/plugins/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/metismenu.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/waves.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/datatables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/sweetalert2.min.js') }}"></script>

    <script src="{{ asset('assets/js/scripts/cookies.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>

    <script>
        const baseUrl = (path, prefix = "/admin") => "{{ url('/') }}" + prefix + path;
        const assetUrl = (path) => "{{ asset('/') }}" + path;
    </script>

    {{-- JS --}}
    @yield('js')
</body>

</html>
