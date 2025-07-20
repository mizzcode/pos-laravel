<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr"
    data-theme="theme-default" data-assets-path="{{ asset('assets') }}/" data-template="vertical-menu-template-free">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard - POS</title>
    <!-- Sneat Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}">
    <!-- Boxicons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <style>
        .layout-menu {
            min-height: 100vh;
        }

        .app-brand-text {
            font-size: 1.2rem;
        }

        body {
            background: #f7f7fb;
        }

        @media (max-width: 991px) {
            .layout-menu {
                min-height: auto;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Sidebar Sneat -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo py-3 mb-2">
                    <a href="{{ route('supplier.dashboard') }}" class="app-brand-link">
                        <span class="app-brand-text fw-bolder ms-2">Shop</span>
                    </a>
                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                        <i class="bx bx-chevron-left bx-sm align-middle"></i>
                    </a>
                </div>
                <ul class="menu-inner py-1">
                    <li class="menu-item {{ request()->routeIs('supplier.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('supplier.dashboard') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div>Dashboard</div>
                        </a>
                    </li>
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">Manajemen Produk</span>
                    </li>
                    <li class="menu-item {{ request()->routeIs('supplier.products.index') ? 'active' : '' }}">
                        <a href="{{ route('supplier.products.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-package"></i>
                            <div>Kelola Produk Saya</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('supplier.products.toko') ? 'active' : '' }}">
                        <a href="{{ route('supplier.products.toko') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-store"></i>
                            <div>Lihat Produk Toko</div>
                        </a>
                    </li>
                </ul>
            </aside>
            <!-- /Sidebar Sneat -->

            <!-- Layout page (Navbar + Content) -->
            <div class="layout-page">
                <!-- Navbar -->
                <nav class="layout-navbar navbar navbar-expand-xl navbar-detached bg-navbar-theme px-3">
                    <div class="navbar-nav-right d-flex align-items-center ms-auto">
                        <!-- User Dropdown -->
                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <li class="nav-item dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="#" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-online">
                                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <span class="fw-semibold d-block">{{ Auth::user()->name ?? 'Supplier' }}</span>
                                                    <small class="text-muted">Supplier</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                                            @csrf
                                            <button class="dropdown-item" type="submit">
                                                <i class="bx bx-power-off me-2"></i>
                                                <span class="align-middle">Log Out</span>
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                        <!--/ User Dropdown -->
                    </div>
                </nav>
                <!-- /Navbar -->

                <!-- Main Content -->
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('content')
                    </div>
                </div>
                <!-- /Main Content -->
            </div>
            <!-- /Layout page -->
        </div>
        <!-- Overlay for menu toggle -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- Sneat JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    @stack('scripts')
</body>

</html>