<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <title>SimPraPKL</title>

    <link href="{{ asset('assets/icons/coreui/css/free.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/icons/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/plugins/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/plugins/datatables.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/plugins/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css" rel="stylesheet" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" />

    {{-- STYLE UNTUK GLOBAL LOADER --}}
    <style>
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            display: none;
            /* Disembunyikan secara default */
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loader {
            border: 8px solid #f3f3f3;
            /* Light grey */
            border-top: 8px solid #3498db;
            /* Blue */
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    <style>
        /* Sidebar */
        .vertical-menu {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 250px;
            background-color: #2a3042;
            z-index: 1050;
            /* overflow-y: auto; */
            /* Hapus/komentari baris ini */
            display: flex;
            flex-direction: column;
        }

        /* Hilangkan scrollbar jika menggunakan simplebar */
        .vertical-menu [data-simplebar] {
            overflow: visible !important;
            max-height: none !important;
        }

        .sidebar-logo {
            background-color: #2a3042 !important;
            padding: 15px 0;
            margin: 0;
            text-align: center;
            border: none;
            box-shadow: none;
        }

        .sidebar-logo img {
            max-height: 40px;
            filter: brightness(0) invert(1);
            background-color: transparent;
        }


        #sidebar-menu {
            flex-grow: 1;
        }

        /* Konten utama bergeser */
        .main-content {
            margin-left: 250px;
            transition: margin-left 0.3s ease;
        }

        .navbar-brand-box {
            background-color: #2a3042 !important;
        }

        html,
        body {
            overflow-x: hidden;
            width: 100%;
        }


        /* Responsive Mobile */
        @media (max-width: 1024px) {
            .vertical-menu {
                left: -250px;
                transition: left 0.3s ease;
            }

            body.sidebar-open .vertical-menu {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            body.sidebar-open .main-content {
                margin-left: 250px;
            }

            /* TAMBAHKAN BARIS INI */
            .navbar-brand-box {
                display: none !important;
            }
        }

        /* Container untuk tombol action */
        .row-action {
            display: flex;
            gap: 0.25rem;
            justify-content: center;
            align-items: center;
        }

        /* Base styling untuk semua tombol action */
        .row-action .btn-action {
            width: 32px !important;
            height: 32px !important;
            min-width: 32px !important;
            min-height: 32px !important;
            max-width: 32px !important;
            max-height: 32px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 0.375rem !important;
            border: none !important;
            box-shadow: none !important;
            font-size: 0.875rem !important;
            transition: opacity 0.2s ease !important;
        }

        /* Icon sizing */
        .row-action .btn-action i {
            font-size: 14px !important;
        }

        /* Warna spesifik untuk setiap jenis tombol */
        .btn-action.btn-warning {
            background: #ffc107 !important;
            color: #ffffff !important;
            /* Dark text untuk kontras yang lebih baik */
        }

        .btn-action.btn-danger {
            background: #6c757d !important;
            color: #fff !important;
        }

        .btn-action.btn-success {
            background: #4ADE80 !important;
            color: #fff !important;
        }

        .btn-action.btn-info {
            background: #0dcaf0 !important;
            color: #ffffff !important;
        }

        .btn-action.btn-primary {
            background: #0d6efd !important;
            color: #fff !important;
        }

        .btn-action.btn-secondary {
            background: #ff0000 !important;
            color: #fff !important;
        }

        /* Hover effect */
        .btn-action:hover {
            opacity: 0.8 !important;
            transform: scale(0.98) !important;
        }

        /* Focus state untuk accessibility */
        .btn-action:focus {
            outline: 2px solid rgba(13, 110, 253, 0.25) !important;
            outline-offset: 2px !important;
        }

        /* Paksa semua tombol action memiliki ukuran yang sama persis */
        .row-action .btn-action {
            width: 32px !important;
            height: 32px !important;
            min-width: 32px !important;
            min-height: 32px !important;
            max-width: 32px !important;
            max-height: 32px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .row-action .btn-action i {
            font-size: 14px !important;
        }

        .vertical-menu {
            transition: width 0.25s ease-out;
            /* Petunjuk untuk browser agar optimasi animasi lebar */
            will-change: width;
        }

        .main-content {
            transition: margin-left 0.25s ease-out;
            /* Petunjuk untuk browser agar optimasi animasi margin */
            will-change: margin-left;
        }

        body.vertical-collpsed .main-content {
            margin-left: 70px;
            /* Sesuaikan jika lebar sidebar minimize Anda berbeda */
        }

        #page-topbar .dropdown .dropdown-menu-lg[aria-labelledby="page-header-notifications-dropdown"] {
            width: 360px !important;
            min-width: 360px !important;
        }
    </style>

    {{-- CSS --}}
    @yield('css')
</head>

<body data-sidebar="colored">

    {{-- HTML UNTUK GLOBAL LOADER --}}
    <div id="loaderOverlay" class="loader-overlay">
        <div class="loader"></div>
    </div>

    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
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
                <div class="d-flex align-items-center gap-1">
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item noti-icon waves-effect"
                            id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <i class="far fa-bell fs-5"></i>
                            {{-- Angka notifikasi akan muncul di sini --}}
                            <span class="badge bg-danger rounded-pill" id="notification-count"
                                style="display: none;"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                            aria-labelledby="page-header-notifications-dropdown">
                            <div class="p-3">
                                <h6 class="m-0">Notifikasi</h6>
                            </div>
                            <div data-simplebar style="max-height: 230px;">
                                {{-- Daftar notifikasi akan dimuat di sini oleh JavaScript --}}
                                <div id="notification-list">
                                    <p class="text-center text-muted py-3">Memuat...</p>
                                </div>
                            </div>
                            <div class="p-2 border-top">
                                <a class="btn btn-sm btn-link font-size-14 text-center w-100"
                                    href="{{ route('notifications.index') }}">
                                    <i class="mdi mdi-arrow-right-circle me-1"></i> Lihat Semua Notifikasi
                                </a>
                            </div>
                        </div>
                    </div>
                    {{-- Dropdown Profil --}}
                    <div class="dropdown">
                        <button type="button" class="btn header-item d-flex align-items-center gap-2"
                            id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            {{-- Ganti src gambar ini --}}
                            <img class="rounded-circle header-profile-user"
                                src="{{ auth()->user()->photo_profile ? asset('uploads/profiles/' . auth()->user()->photo_profile) : asset('assets/images/placeholder.jpg') }}"
                                alt="Header Avatar" height="32" width="32" style="object-fit: cover;">

                            <div class="d-none d-lg-flex flex-column align-items-start">
                                <span class="fw-semibold text-dark">{{ auth()->user()->name }}</span>
                                <span class="text-muted small">{{ auth()->user()->email }}</span>
                            </div>

                            <div class="d-flex d-lg-none flex-column align-items-start justify-content-center">
                                <span
                                    class="fw-semibold text-dark">{{ Str::words(auth()->user()->name, 1, '') }}</span>
                            </div>

                            <i class="fa fa-chevron-down ms-1" style="font-size: 10px;"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('profile.index') }}">
                                <i class="far fa-user font-size-12 align-middle me-1"></i>
                                <span>Profil</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            {{-- 1. TAMBAHKAN ID DI SINI --}}
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}" id="logout-button">
                                <i class="fa fa-sign-out-alt font-size-12 align-middle me-1 text-danger"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
        </header>

        {{-- Sidebars --}}
        <div class="vertical-menu">
            <div class="sidebar-logo">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Sidebar Logo">
                </a>
            </div>

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

        </div>
    </div>

    <div class="rightbar-overlay"></div>

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
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const BODY = document.body;
            const SIDEBAR_TOGGLE_BTN = document.getElementById('vertical-menu-btn');
            const SIDEBAR_CLOSE_BTN = document.getElementById('close-sidebar-btn');

            // --- Logic for Sidebar Persistence within a SESSION ---
            // Diubah dari localStorage ke sessionStorage
            const SIDEBAR_STATE_KEY = 'sidebar_minimized_session';
            const MINIMIZED_CLASS = 'vertical-collpsed';

            // 1. Saat halaman dimuat, cek sessionStorage dan terapkan state minimize jika ada
            if (sessionStorage.getItem(SIDEBAR_STATE_KEY) === 'true') {
                BODY.classList.add(MINIMIZED_CLASS);
            }

            // 2. Saat tombol toggle diklik, simpan state baru ke sessionStorage
            if (SIDEBAR_TOGGLE_BTN) {
                SIDEBAR_TOGGLE_BTN.addEventListener('click', function() {
                    setTimeout(() => {
                        if (BODY.classList.contains(MINIMIZED_CLASS)) {
                            sessionStorage.setItem(SIDEBAR_STATE_KEY, 'true');
                        } else {
                            sessionStorage.setItem(SIDEBAR_STATE_KEY, 'false');
                        }
                    }, 200);
                });
            }

            // --- Logic for Mobile Sidebar Overlay ---
            const MOBILE_OPEN_CLASS = 'sidebar-open';

            if (SIDEBAR_TOGGLE_BTN) {
                SIDEBAR_TOGGLE_BTN.addEventListener('click', function() {
                    BODY.classList.add(MOBILE_OPEN_CLASS);
                });
            }

            if (SIDEBAR_CLOSE_BTN) {
                SIDEBAR_CLOSE_BTN.addEventListener('click', function() {
                    BODY.classList.remove(MOBILE_OPEN_CLASS);
                });
            }
        });
    </script>

    {{-- JS --}}
    @yield('js')
    @stack('scripts')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.js"></script>
    <script>
        $(document).ready(function() {
            const NOTIFICATION_INTERVAL = 30000; // Check for new notifications every 30 seconds

            function fetchNotifications() {
                $.ajax({
                    url: "{{ route('notifications.fetch') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        updateNotificationUI(data);
                    },
                    error: function(xhr) {
                        console.error("Error fetching notifications:", xhr.responseText);
                    }
                });
            }

            function updateNotificationUI(data) {
                const count = data.unread_count;
                const $countBadge = $('#notification-count');
                const $notificationList = $('#notification-list');

                // 1. Update the notification counter badge
                if (count > 0) {
                    $countBadge.text(count).show();
                } else {
                    $countBadge.hide();
                }

                // 2. Clear the current notification list
                $notificationList.empty();

                // 3. Build the new list from unread notifications
                if (data.unread && data.unread.length > 0) {
                    data.unread.forEach(notification => {
                        const timeAgo = moment(notification.created_at).locale('id').fromNow();
                        const notificationHtml = `
                            <a href="${notification.data.url}" class="text-reset notification-item bg-light">
                                <div class="d-flex p-3">
                                    <div class="avatar-xs me-3">
                                        <span class="avatar-title bg-primary rounded-circle font-size-16">
                                            <i class="${notification.data.icon || 'far fa-bell'}"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1">${notification.data.message}</p>
                                        <div class="font-size-12 text-muted">
                                            <p class="mb-0"><i class="far fa-clock me-1"></i> ${timeAgo}</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        `;
                        $notificationList.append(notificationHtml);
                    });
                } else {
                    // If there are no unread notifications, show a message
                    $notificationList.html('<p class="text-center text-muted py-3">Tidak ada notifikasi baru.</p>');
                }
            }

            function markNotificationsAsRead() {
                // Only send request if there are unread notifications
                if (parseInt($('#notification-count').text()) > 0) {
                    $.ajax({
                        url: "{{ route('notifications.mark_as_read') }}",
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            // Update UI immediately without waiting for the next poll
                            $('#notification-count').hide();
                            $('#notification-list .notification-item').removeClass('bg-light');
                        }
                    });
                }
            }

            // When the user opens the notification dropdown, mark them as read
            $('#page-header-notifications-dropdown').on('show.bs.dropdown', function() {
                // Wait a moment so the user sees the unread state before it changes
                setTimeout(markNotificationsAsRead, 2000);
            });

            // Initial fetch when the page loads
            fetchNotifications();

            // Set an interval to fetch notifications periodically
            setInterval(fetchNotifications, NOTIFICATION_INTERVAL);

            // 2. SCRIPT UNTUK KONFIRMASI LOGOUT
            $('#logout-button').on('click', function(event) {
                event.preventDefault(); // Mencegah link langsung logout
                var logoutUrl = $(this).attr('href'); // Simpan URL logout

                Swal.fire({
                    title: 'Anda Yakin?',
                    text: "Anda akan keluar dari sesi ini.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, keluar!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika pengguna mengklik "Ya, keluar!", arahkan ke URL logout
                        window.location.href = logoutUrl;
                    }
                });
            });
        });
    </script>
</body>

</html>
