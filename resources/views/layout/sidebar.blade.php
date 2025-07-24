<ul class="metismenu list-unstyled" id="side-menu">
    <li class="menu-title">List Menu</li>
    <li class="{{ Route::is('dashboard') ? 'mm-active' : '' }}">
   
        <a href="{{ route('dashboard') }}" class="waves-effect">
            <i class="cil-home"></i>
            <span>DASHBOARD</span>
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
    <li class="{{ Route::is('setting_presensi.*') ? 'mm-active' : '' }}">
        <a href="{{ route('setting_presensi.index') }}" class="waves-effect">
            <i class="cil-settings"></i>
            <span>Setting Presensi</span>
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
    {{-- <li class="{{ Route::is('laporan_gambar.*') ? 'mm-active' : '' }}">
        <a href="{{ route('laporan_gambar.index') }}" class="waves-effect">
            <i class="cil-menu"></i>
            <span>LaporanGambar</span>
        </a>
    </li> --}}
    {{-- <li class="{{ Route::is('presensi_gambar.*') ? 'mm-active' : '' }}">
        <a href="{{ route('presensi_gambar.index') }}" class="waves-effect">
            <i class="cil-menu"></i>
            <span>PresensiGambar</span>
        </a>
    </li> --}}
</ul>
