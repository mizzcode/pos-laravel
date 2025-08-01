<!DOCTYPE html>
<html lang="id" class="light-style layout-navbar-fixed" data-theme="theme-default"
    data-assets-path="{{ asset('assets') }}/" data-template="vertical-menu-template-free">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk | POS</title>
    <!-- Sneat & Bootstrap Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}">
    <!-- Boxicons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    @yield('head')

    <style>
        body {
            background: #f7f7fb;
        }

        .navbar-brand {
            font-weight: 600;
            letter-spacing: 1px;
            font-size: 1.3rem;
        }

        .sneat-navbar {
            background: #fff;
            border-bottom: 1px solid #e3e3e3;
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: box-shadow 0.2s;
        }

        /* Global CSS untuk mengatasi masalah icon Midtrans CDN */
        img[src*="d2f3dnusg0rbp7.cloudfront.net"] {
            background: #f8f9fa !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 4px !important;
            position: relative !important;
            min-width: 60px !important;
            min-height: 30px !important;
        }

        /* Hide broken images and show text fallback */
        img[src*="d2f3dnusg0rbp7.cloudfront.net"][src*="svg"]:after {
            content: "💳" !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            font-size: 16px !important;
            z-index: 1 !important;
        }

        /* Alternative approach: completely hide CDN images */
        .hide-cdn-images img[src*="d2f3dnusg0rbp7.cloudfront.net"] {
            display: none !important;
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Navbar Sneat Style -->
    <nav class="navbar navbar-expand-lg sneat-navbar shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home.katalog') }}">
                <i class="bx bx-store-alt bx-sm me-2" style="color:#696cff"></i>
                Shop
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        @if (auth()->user()->role === 'admin')
                            <!-- Notifikasi Admin -->
                            <li class="nav-item">
                                <a class="nav-link position-relative" href="{{ route('notifications.index') }}">
                                    <i class="bx bx-bell"></i>
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                        id="notif-count" style="font-size: 0.7rem;">
                                        0
                                    </span>
                                </a>
                            </li>
                        @endif

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-user"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('home.profile') }}">
                                        <i class="bx bx-user-circle me-1"></i> Profil Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('home.myorders.index') }}">
                                        <i class="bx bx-receipt me-1"></i> Pesanan Saya
                                    </a>
                                </li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bx bx-power-off me-1"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}"><i class="bx bx-log-in"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}"><i class="bx bx-user-plus"></i> Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-xxl">
        @yield('content')
    </div>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    @if (auth()->check() && auth()->user()->role === 'admin')
        <script>
            // Update notifikasi count untuk admin
            function updateNotificationCount() {
                fetch('{{ route('notifications.count') }}')
                    .then(response => response.json())
                    .then(data => {
                        const badge = document.getElementById('notif-count');
                        if (badge) {
                            badge.textContent = data.count;
                            badge.style.display = data.count > 0 ? 'inline' : 'none';
                        }
                    })
                    .catch(error => console.log('Error fetching notification count:', error));
            }

            // Update count saat halaman dimuat dan setiap 30 detik
            document.addEventListener('DOMContentLoaded', updateNotificationCount);
            setInterval(updateNotificationCount, 30000);
        </script>
    @endif

    @stack('scripts')
    <script src="{{ asset('assets/js/main.js') }}"></script>
</body>

</html>
