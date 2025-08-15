{{-- Layout --}}
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

        {{-- Filter Form --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Filter Analisis</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('dashboard') }}">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Bulan</label>
                                    <select name="bulan" class="form-control">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}"
                                                {{ request('bulan') == $m ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tahun</label>
                                    <select name="tahun" class="form-control">
                                        @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                            <option value="{{ $y }}"
                                                {{ request('tahun') == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Analisis Individual (Opsional)</label>
                                    <select name="student_id" class="form-control">
                                        <option value="">Lihat Data Keseluruhan</option>
                                        @foreach ($allStudents as $student)
                                            <option value="{{ $student->id }}"
                                                {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                                {{ $student->name }} - {{ $student->username }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if (!$selectedStudent)
            {{-- Tabel Rekap Absensi --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-table me-2"></i>Rekap Absensi Siswa {{ $bulanTeks }}</h5>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportToExcel()">
                                    <i class="fas fa-file-excel me-1"></i> Export Excel
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printTable()">
                                    <i class="fas fa-print me-1"></i> Print
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @if (count($rekapAbsensiSiswa) > 0)
                                <div class="table-responsive">
                                    <table id="rekapAbsensiTable">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">NAMA SISWA</th>
                                                <th colspan="4">KETERANGAN</th>
                                            </tr>
                                            <tr>
                                                <th>Hadir</th>
                                                <th>Sakit</th>
                                                <th>Izin</th>
                                                <th>TK</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalHadir = 0;
                                                $totalSakit = 0;
                                                $totalIzin = 0;
                                                $totalTK = 0;
                                            @endphp
                                            @foreach ($rekapAbsensiSiswa as $siswa)
                                                @php
                                                    $totalHadir += $siswa['hadir'];
                                                    $totalSakit += $siswa['sakit'];
                                                    $totalIzin += $siswa['izin'];
                                                    $totalTK += $siswa['tidak_hadir'];
                                                @endphp
                                                <tr>
                                                    <td>{{ $siswa['nama'] }}</td>
                                                    <td class="text-right">{{ $siswa['hadir'] }}</td>
                                                    <td class="text-right">{{ $siswa['sakit'] }}</td>
                                                    <td class="text-right">{{ $siswa['izin'] }}</td>
                                                    <td class="text-right">{{ $siswa['tidak_hadir'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>TOTAL</th>
                                                <th class="text-right">{{ $totalHadir }}</th>
                                                <th class="text-right">{{ $totalSakit }}</th>
                                                <th class="text-right">{{ $totalIzin }}</th>
                                                <th class="text-right">{{ $totalTK }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-table fa-3x mb-3"></i>
                                    <h5>Belum ada data rekap absensi</h5>
                                    <p>Data rekap absensi untuk {{ $bulanTeks }} belum tersedia</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        // Fungsi Export Excel
        function exportToExcel() {
            const table = document.getElementById('rekapAbsensiTable');
            const ws = XLSX.utils.table_to_sheet(table, {
                raw: true
            }); // raw biar angka tetap number
            const wb = XLSX.utils.book_new();

            // Auto width kolom
            const colWidths = [];
            const rows = table.querySelectorAll("tr");
            rows.forEach(row => {
                row.querySelectorAll("td, th").forEach((cell, i) => {
                    const textLength = cell.innerText.length + 2;
                    colWidths[i] = Math.max(colWidths[i] || 10, textLength);
                });
            });
            ws['!cols'] = colWidths.map(w => ({
                wch: w
            }));

            XLSX.utils.book_append_sheet(wb, ws, "Rekap Absensi");
            const bulanTeks = "{{ $bulanTeks }}";
            XLSX.writeFile(wb, `Rekap_Absensi_${bulanTeks.replace(/\s+/g, '_')}.xlsx`);
        }

        // Fungsi Print
        function printTable() {
            const printContent = document.getElementById('rekapAbsensiTable').outerHTML;
            const bulanTeks = "{{ $bulanTeks }}";
            const printWindow = window.open('', '', 'width=900,height=700');

            printWindow.document.write(`
        <html>
        <head>
            <title>Rekap Absensi Siswa ${bulanTeks}</title>
            <style>
                body { font-family: Arial, sans-serif; }
                h2 { text-align: center; margin-bottom: 20px; }
                table { border-collapse: collapse; width: 100%; font-size: 14px; }
                th, td { border: 1px solid #000; padding: 6px; }
                th { background-color: #d9e1f2; font-weight: bold; text-align: center; }
                tfoot th { background-color: #bdd7ee; font-weight: bold; }
                .text-right { text-align: right; }
            </style>
        </head>
        <body>
            <h2>Rekap Absensi Siswa ${bulanTeks}</h2>
            ${printContent}
        </body>
        </html>
    `);

            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }
    </script>

    <style>
        #rekapAbsensiTable {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        #rekapAbsensiTable th,
        #rekapAbsensiTable td {
            border: 1px solid #000;
            padding: 6px 8px;
        }

        #rekapAbsensiTable th {
            background-color: #d9e1f2;
            font-weight: bold;
            text-align: center;
        }

        #rekapAbsensiTable td {
            background-color: #fff;
        }

        #rekapAbsensiTable tfoot th {
            background-color: #bdd7ee;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }
    </style>
@endsection
