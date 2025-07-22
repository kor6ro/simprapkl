<ul class="metismenu list-unstyled" id="side-menu">
    <li class="menu-title">List Menu</li>
    <li class="{{ Route::is('dashboard') ? 'mm-active' : '' }}">
        <a href="{{ route('dashboard') }}" class="waves-effect">
            <i class="cil-home"></i>
            <span>Dashboards</span>
        </a>
    </li>
    <li class="{{ Route::is('user.*') ? 'mm-active' : '' }}">
        <a href="{{ route('user.index') }}" class="waves-effect">
            <i class="cil-menu"></i>
            <span>User</span>
        </a>
    </li>
    <li class="{{ Route::is('group.*') ? 'mm-active' : '' }}">
        <a href="{{ route('group.index') }}" class="waves-effect">
            <i class="cil-menu"></i>
            <span>Group</span>
        </a>
    </li>
    <li class="{{ Route::is('sekolah.*') ? 'mm-active' : '' }}">
        <a href="{{ route('sekolah.index') }}" class="waves-effect">
            <i class="cil-menu"></i>
            <span>Sekolah</span>
        </a>
    </li>
    <li class="{{ Route::is('colect_data.*') ? 'mm-active' : '' }}">
        <a href="{{ route('colect_data.index') }}" class="waves-effect">
            <i class="cil-menu"></i>
            <span>ColectData</span>
        </a>
    </li>
    <li class="{{ Route::is('task_breakdown.*') ? 'mm-active' : '' }}">
        <a href="{{ route('task_breakdown.index') }}" class="waves-effect">
            <i class="cil-menu"></i>
            <span>TaskBreakdown</span>
        </a>
    </li>
    <li class="{{ Route::is('presensi.*') ? 'mm-active' : '' }}">
        <a href="{{ route('presensi.index') }}" class="waves-effect">
            <i class="cil-menu"></i>
            <span>Presensi</span>
        </a>
    </li>
    <li class="{{ Route::is('setting_presensi.*') ? 'mm-active' : '' }}">
        <a href="{{ route('setting_presensi.index') }}" class="waves-effect">
            <i class="cil-menu"></i>
            <span>SettingPresensi</span>
        </a>
    </li>
    <li class="{{ Route::is('presensi_gambar.*') ? 'mm-active' : '' }}">
        <a href="{{ route('presensi_gambar.index') }}" class="waves-effect">
            <i class="cil-menu"></i>
            <span>PresensiGambar</span>
        </a>
    </li>
    <li class="{{ Route::is('laporan.*') ? 'mm-active' : '' }}">
        <a href="{{ route('laporan.index') }}" class="waves-effect">
            <i class="cil-menu"></i>
            <span>Laporan</span>
        </a>
    </li>
    <li class="{{ Route::is('laporan_gambar.*') ? 'mm-active' : '' }}">
        <a href="{{ route('laporan_gambar.index') }}" class="waves-effect">
            <i class="cil-menu"></i>
            <span>LaporanGambar</span>
        </a>
    </li>
    <li class="{{ Route::is('jenis_laporan.*') ? 'mm-active' : '' }}">
        <a href="{{ route('jenis_laporan.index') }}" class="waves-effect">
            <i class="cil-menu"></i>
            <span>JenisLaporan</span>
        </a>
    </li>
</ul>
