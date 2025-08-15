@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <h4>Selamat datang, {{ auth()->user()->name }}</h4>
        </div>
    </div>

    @if (auth()->user()->group_id == 1 || auth()->user()->group_id == 2)
        {{-- Dashboard Admin --}}
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <p>Total Presensi Hari Ini</p>
                        <h4>{{ $todayPresensi }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <p>Total Pengguna</p>
                        <h4>{{ $totalUsers }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <p>Presensi Pagi</p>
                        <h4>{{ $pagiPresensi }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <p>Presensi Sore</p>
                        <h4>{{ $sorePresensi }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('dashboard') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="bulan" class="form-control">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="tahun" class="form-control">
                        @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Tampilkan</button>
                </div>
            </div>
        </form>


        {{-- Statistik Presensi --}}
        <div class="row">
            <div class="col-xl-8 col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h4>Statistik Presensi Bulan {{ $bulanTeks }}</h4>
                    </div>
                    <div class="card-body">
                        @if (!empty($chartData['data']) && array_sum($chartData['data']) > 0)
                            <canvas id="presensiPieChart"></canvas>
                        @else
                            <p class="text-center">Belum ada data presensi bulan ini</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-5">
                <div class="card">
                    <div class="card-header">Ringkasan Bulan Ini</div>
                    <div class="card-body">
                        <h2 class="text-primary">{{ $attendancePercentage }}%</h2>
                        <p>Tingkat Kehadiran</p>
                        <ul>
                            <li>Hadir: {{ $monthlyStats['hadir_count'] }}</li>
                            <li>Alpa: {{ $monthlyStats['alpa_count'] }}</li>
                            <li>Izin/Sakit: {{ $monthlyStats['izin_sakit_count'] }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Dashboard Siswa --}}
        <div class="alert alert-info">
            Untuk melakukan presensi, kunjungi halaman <a href="{{ route('presensi.index') }}">Presensi</a>.
        </div>
    @endif
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = @json($chartData);
            if (chartData.data && chartData.data.length > 0) {
                new Chart(document.getElementById('presensiPieChart'), {
                    type: 'pie',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            data: chartData.data,
                            backgroundColor: chartData.colors
                        }]
                    }
                });
            }
        });
    </script>
@endsection
