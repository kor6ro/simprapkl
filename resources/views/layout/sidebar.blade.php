<!-- sidebar.blade.php -->
<div class="vertical-menu position-relative">
    <!-- Tombol X Close Sidebar: hanya tampil di mobile & tablet -->
    <button type="button" id="close-sidebar-btn" class="d-block d-lg-none"
        style="position: absolute; top: 10px; right: 10px; z-index: 1051; background: none; border: none; padding: 0; cursor: pointer; width: 2.2rem; height: 2.2rem;">
        <span class="x-square"></span>
    </button>

    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title">List Menu</li>
                <li class="{{ Route::is('dashboard') ? 'mm-active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="waves-effect">
                        <i class="cil-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="menu-title">Master Data</li>
                <li class="{{ Route::is('sekolah.*') ? 'mm-active' : '' }}">
                    <a href="{{ route('sekolah.index') }}" class="waves-effect">
                        <i class="cil-school"></i>
                        <span>Sekolah</span>
                    </a>
                </li>
                <li class="{{ Route::is('group.*') ? 'mm-active' : '' }}">
                    <a href="{{ route('group.index') }}" class="waves-effect">
                        <i class="cil-group"></i>
                        <span>Group</span>
                    </a>
                </li>
                <li class="{{ Route::is('presensi_setting.*') ? 'mm-active' : '' }}">
                    <a href="{{ route('presensi_setting.index') }}" class="waves-effect">
                        <i class="cil-settings"></i>
                        <span>Setting Presensi</span>
                    </a>
                </li>
                <li class="{{ Route::is('presensi_jenis.*') ? 'mm-active' : '' }}">
                    <a href="{{ route('presensi_jenis.index') }}" class="waves-effect">
                        <i class="cil-settings"></i>
                        <span>Jenis Presensi</span>
                    </a>
                </li>

                <li class="menu-title">Manajement User</li>
                <li class="{{ Route::is('user.*') ? 'mm-active' : '' }}">
                    <a href="{{ route('user.index') }}" class="waves-effect">
                        <i class="cil-user"></i>
                        <span>User</span>
                    </a>
                </li>

                <li class="menu-title">Manajement Data</li>
                <li class="{{ Route::is('presensi.*') ? 'mm-active' : '' }}">
                    <a href="{{ route('presensi.index') }}" class="waves-effect">
                        <i class="cil-calendar"></i>
                        <span>Presensi</span>
                    </a>
                </li>
                <li class="{{ Route::is('task_break_down.*') ? 'mm-active' : '' }}">
                    <a href="{{ route('task_break_down.index') }}" class="waves-effect">
                        <i class="cil-task"></i>
                        <span>Task Breakdown</span>
                    </a>
                </li>
                <li class="{{ Route::is('laporan.*') ? 'mm-active' : '' }}">
                    <a href="{{ route('laporan.index') }}" class="waves-effect">
                        <i class="cil-chart"></i>
                        <span>Laporan</span>
                    </a>
                </li>
                <li class="{{ Route::is('colect_data.*') ? 'mm-active' : '' }}">
                    <a href="{{ route('colect_data.index') }}" class="waves-effect">
                        <i class="cil-pen"></i>
                        <span>Colect Data</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<style>
    .x-square {
        display: inline-block;
        width: 2rem;
        height: 2rem;
        position: relative;
        border: 2px solid #fff;
        border-radius: 0.3rem;
        box-sizing: border-box;
    }

    .x-square::before,
    .x-square::after {
        content: '';
        position: absolute;
        left: 50%;
        top: 50%;
        width: 1.2rem;
        height: 2px;
        background: #fff;
        border-radius: 2px;
        transform-origin: center;
    }

    .x-square::before {
        transform: translate(-50%, -50%) rotate(45deg);
    }

    .x-square::after {
        transform: translate(-50%, -50%) rotate(-45deg);
    }

    #close-sidebar-btn:hover .x-square,
    #close-sidebar-btn:focus .x-square {
        border-color: #3b589e;
    }

    #close-sidebar-btn:hover .x-square::before,
    #close-sidebar-btn:hover .x-square::after,
    #close-sidebar-btn:focus .x-square::before,
    #close-sidebar-btn:focus .x-square::after {
        background: #3b589e;
    }
</style>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const closeBtn = document.getElementById('close-sidebar-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                document.body.classList.remove('sidebar-open');
            });
        }
    });
</script>
