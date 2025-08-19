<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rekap Absensi Siswa {{ $bulanTeks }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif,
                font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header h2 {
            margin: 5px 0 0 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .info-table .label {
            width: 150px;
            font-weight: bold;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
        }

        .main-table th {
            background-color: #d9e1f2;
            font-weight: bold;
        }

        .main-table .nama-column {
            text-align: left;
            width: 40%;
        }

        .main-table .number-column {
            text-align: center;
            width: 15%;
        }

        .main-table tfoot th {
            background-color: #bdd7ee;
            font-weight: bold;
        }

        .summary-box {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #000;
            background-color: #f8f9fa;
        }

        .summary-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .summary-item {
            display: inline-block;
            margin-right: 30px;
            margin-bottom: 5px;
        }

        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 200px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
            padding-top: 5px;
        }

        @page {
            margin: 2cm 1.5cm;
        }

        /* Print styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Rekap Absensi Siswa</h1>
        <h2>{{ $bulanTeks }}</h2>
    </div>

    <!-- Info Table -->
    <table class="info-table">
        <tr>
            <td class="label">Periode</td>
            <td>: {{ $bulanTeks }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Cetak</td>
            <td>: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="label">Total Siswa</td>
            <td>: {{ count($rekapData) }} orang</td>
        </tr>
    </table>

    <!-- Main Table -->
    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" class="nama-column">NAMA SISWA</th>
                <th colspan="4">KETERANGAN</th>
            </tr>
            <tr>
                <th class="number-column">Hadir</th>
                <th class="number-column">Sakit</th>
                <th class="number-column">Izin</th>
                <th class="number-column">TK</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rekapData as $index => $siswa)
                <tr>
                    <td class="nama-column">{{ $index + 1 }}. {{ $siswa['nama'] }}</td>
                    <td class="number-column">{{ $siswa['hadir'] }}</td>
                    <td class="number-column">{{ $siswa['sakit'] }}</td>
                    <td class="number-column">{{ $siswa['izin'] }}</td>
                    <td class="number-column">{{ $siswa['tidak_hadir'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th class="nama-column">TOTAL</th>
                <th class="number-column">{{ $totalHadir }}</th>
                <th class="number-column">{{ $totalSakit }}</th>
                <th class="number-column">{{ $totalIzin }}</th>
                <th class="number-column">{{ $totalTK }}</th>
            </tr>
        </tfoot>
    </table>

    <!-- Keterangan -->
    <div style="margin-top: 20px; font-size: 11px;">
        <strong>Keterangan:</strong><br>
        - <strong>Hadir:</strong> Siswa hadir tepat waktu atau terlambat<br>
        - <strong>Sakit:</strong> Siswa tidak hadir karena sakit dengan keterangan<br>
        - <strong>Izin:</strong> Siswa tidak hadir dengan izin<br>
        - <strong>TK (Tanpa Keterangan):</strong> Siswa tidak hadir dan tidak memberikan keterangan (Alpa)
    </div>

</body>

</html>
