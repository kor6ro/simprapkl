<div class="vertical-menu position-relative">
    <button type="button" id="close-sidebar-btn" class="d-block d-lg-none"
        style="position: absolute; top: 10px; right: 10px; z-index: 1051; background: none; border: none; padding: 0; cursor: pointer; width: 2.2rem; height: 2.2rem;">
        <span class="x-square"></span>
    </button>

    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                @if (in_array(auth()->user()->group_id, [1, 2, 3, 4, 5, 6, 7]))
                    <li class="menu-title">Utama</li>
                    <li class="{{ Route::is('dashboard') ? 'mm-active' : '' }}">
                        <a href="{{ route('dashboard') }}" class="waves-effect">
                            <i class="cil-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    {{-- Operasional PKL --}}
                    <li class="menu-title">Operasional PKL</li>
                    <li class="{{ Route::is('presensi.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('presensi.index') }}" class="waves-effect">
                            <i class="cil-calendar"></i>
                            <span>{{ auth()->user()->group_id == 4 ? 'Presensi' : 'Rekap Presensi' }}</span>
                        </a>
                    </li>
                    <li class="{{ Route::is('admin.tim.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.tim.index') }}" class="waves-effect">
                            <i class="cil-task"></i>
                            <span>Tugas</span>
                        </a>
                    </li>
                    <li class="{{ Route::is('admin.laporan.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.laporan.index') }}" class="waves-effect">
                            <i class="cil-chart"></i>
                            <span>{{ auth()->user()->group_id == 4 ? 'Laporan' : 'Rekap Laporan' }}</span>
                        </a>
                    </li>
                    @if (auth()->user()->group_id != 3)
                        <li class="{{ Route::is('admin.colect_data.*') ? 'mm-active' : '' }}">
                            <a href="{{ route('admin.colect_data.index') }}" class="waves-effect">
                                <i class="{{ auth()->user()->group_id == 4 ? 'cil-pen' : 'cil-description' }}"></i>
                                <span>{{ auth()->user()->group_id == 4 ? 'Collect Data' : 'Rekap Collect Data' }}</span>
                            </a>
                        </li>
                    @endif
                @endif

                {{-- Penilaian Akhir --}}
                @if (in_array(auth()->user()->group_id, [1, 2, 3, 6, 7]))
                    <li class="{{ Route::is('admin.penilaian.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.penilaian.index') }}" class="waves-effect">
                            <i class="cil-star"></i>
                            <span>Penilaian Akhir</span>
                        </a>
                    </li>
                @endif

                {{-- Manajemen Data + Pengaturan --}}
                @if (in_array(auth()->user()->group_id, [1, 2]))
                    <li class="menu-title">Manajemen Data</li>
                    <li class="{{ Route::is('admin.user.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.user.index') }}" class="waves-effect">
                            <i class="cil-user"></i>
                            <span>User</span>
                        </a>
                    </li>

                    <li
                        class="{{ Request::is('admin/group*') || Request::is('admin/sekolah*') || Request::is('admin/program-keahlian*') || Request::is('admin/divisi*') ? 'mm-active' : '' }}">
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                            <i class="cil-storage"></i>
                            <span>Data Master</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li class="{{ Route::is('admin.group.*') ? 'mm-active' : '' }}">
                                <a href="{{ route('admin.group.index') }}">Group</a>
                            </li>
                            <li class="{{ Route::is('admin.sekolah.*') ? 'mm-active' : '' }}">
                                <a href="{{ route('admin.sekolah.index') }}">Sekolah</a>
                            </li>
                            <li class="{{ Route::is('admin.program-keahlian.*') ? 'mm-active' : '' }}">
                                <a href="{{ route('admin.program-keahlian.index') }}">Program Keahlian</a>
                            </li>
                            <li class="{{ Route::is('admin.divisi.*') ? 'mm-active' : '' }}">
                                <a href="{{ route('admin.divisi.index') }}">Divisi</a>
                            </li>
                            <li class="{{ Route::is('admin.jenis-kegiatan') ? 'mm-active' : '' }}">
                                <a href="{{ route('admin.jenis_kegiatan.index') }}" class="waves-effect">
                                    <span>Jenis Kegiatan</span>
                                </a>
                            </li>
                            <li class="{{ Route::is('admin.periode-pkl.*') ? 'mm-active' : '' }}">
                                <a href="{{ route('admin.periode-pkl.index') }}">Periode PKL</a>
                            </li>
                        </ul>
                    </li>

                    <li class="menu-title">Pengaturan</li>
                    <li class="{{ Request::is('admin/presensi-setting*') ? 'mm-active' : '' }}">
                        <a href="{{ url('admin/presensi-setting') }}" class="waves-effect">
                            <i class="cil-settings"></i>
                            <span>Setting Presensi</span>
                        </a>
                    </li>
                @endif

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
