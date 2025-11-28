<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            /* Ukuran font 11px lebih ideal */
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            font-weight: bold;
            /* Buat konsisten */
        }

        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
            /* [TAMBAH] */
        }

        /* [TAMBAH] CSS untuk info-table (menggantikan inline style) */
        .info-table td {
            padding: 2px 0;
        }

        .info-table .label {
            width: 100px;
            font-weight: bold;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            /* [TAMBAH] Anti-overflow/macet */
            table-layout: fixed;
        }

        .main-table th,
        .main-table td {
            border: 1px solid #ccc;
            /* Border lebih tipis/soft */
            padding: 6px;
            /* Padding yg konsisten */
            text-align: center;
        }

        .main-table th {
            /* [UBAH] Warna header lebih netral */
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .main-table td.nama {
            text-align: left;
        }

        /* [TAMBAH] Header "Nama Siswa" juga harus rata kiri */
        .main-table th.nama {
            text-align: left;
        }

        /* [TAMBAH] Zebra-striping agar mudah dibaca */
        .main-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* [TAMBAH] Footer untuk Paginasi & Tgl Cetak */
        .footer {
            position: fixed;
            bottom: 0px;
            left: 20px;
            right: 20px;
            height: 40px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            font-size: 9px;
            color: #777;
        }

        .footer .page-number {
            float: right;
        }

        .footer .print-date {
            float: left;
        }

        /* Script khusus DomPDF untuk nomor halaman */
        .footer .page-number:before {
            content: "Halaman " counter(page) " dari " counter(pages);
        }
    </style>
</head>

<body>
    <div class="footer">
        <div class="print-date">
            Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
        </div>
        <div class="page-number"></div>
    </div>

    <div class="header">
        <h1>{{ $judul }}</h1>
        <h2>Periode {{ $periode }}</h2>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Sekolah</td>
            <td>: {{ $sekolah }}</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 45%;" class="nama">Nama Siswa</th>
                <th>Hadir</th>
                <th>Sakit</th>
                <th>Izin</th>
                <th>Alpa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapData as $data)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="nama">{{ $data['nama'] }}</td>
                    <td>{{ $data['hadir'] }}</td>
                    <td>{{ $data['sakit'] }}</td>
                    <td>{{ $data['izin'] }}</td>
                    <td>{{ $data['alpa'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 15px;">
                        Tidak ada data untuk direkap berdasarkan filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
