<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian PKL - {{ $penilaian->siswa->name }}</title>
    <style>
        @page {
            margin: 1.5cm 2.5cm;
        }

        body {
            font-family: 'Calibri', sans-serif;
            font-size: 11pt;
        }

        /* --- BAGIAN ATAS --- */
        .logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .logo-container img {
            height: 60px;
            width: auto;
        }

        .title-section {
            text-align: center;
            font-weight: bold;
            line-height: 1.4;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .info-table td {
            vertical-align: top;
            padding: 1px 0;
            font-weight: bold;
        }


        /* --- TABEL UTAMA & WATERMARK --- */
        /* KOREKSI: Tambahkan style untuk wadah tabel & watermark */
        .table-container {
            position: relative;
            /* Kunci agar watermark bisa diposisikan di dalamnya */
        }

        .main-table {
            width: 100%;
            border: 1px solid black;
            border-collapse: collapse;
        }

        .main-table th,
        .main-table td {
            border: 1px solid black;
            padding: 4px;
        }

        .main-table th {
            background-color: #e0e0e0;
            text-align: center;
            font-weight: bold;
        }

        .main-table tfoot td {
            font-weight: bold;
        }

        .footer-label {
            text-align: center;
        }

        /* KOREKSI: Style watermark diperbarui agar pas di dalam tabel */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            z-index: -1;
            width: 100%;
            height: auto;
        }

        /* --- BAGIAN BAWAH --- */
        .bottom-container {
            width: 100%;
            margin-top: 1rem;
            table-layout: fixed;
        }

        .comment-section {
            font-size: 11pt;
            line-height: 1.3;
            font-weight: bold;
            overflow-wrap: break-word;
            word-wrap: break-word;
            margin-bottom: 1.5rem;
        }

        .grading-scale {
            font-size: 11pt;
        }

        .signature-section {
            display: inline-block;
            text-align: center;
            font-weight: bold;
            white-space: nowrap;
        }

        .signature-space {
            height: 60px;
        }

        /* --- UTILITY --- */
        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header-container">

        <div class="title-section">
            <div>PENILAIAN HASIL PRAKTIK KERJA LAPANGAN (PKL)</div>
            <div>PT SANDYA SISTEM INDONESIA – KANTOR LAYANAN PACITAN</div>
            <div>
                {{ \Carbon\Carbon::parse($penilaian->pkl_tanggal_mulai)->locale('id')->isoFormat('D MMMM') }} –
                {{ \Carbon\Carbon::parse($penilaian->pkl_tanggal_selesai)->locale('id')->isoFormat('D MMMM YYYY') }}
            </div>
        </div>
        <table class="info-table">
            <tbody>
                <tr>
                    <td style="white-space: nowrap; text-align: left;">Nama Peserta</td>
                    <td style="width: 2%; text-align: left;">:</td>
                    <td style="text-align: left;">{{ $penilaian->siswa->name ?? 'N/A' }}</td>

                    <td style="white-space: nowrap; text-align: right;">Program Keahlian</td>
                    <td style="text-align: right;">:</td>
                   {{-- Kode yang benar untuk menampilkan nama program keahlian dari relasi --}}
<td style="width: 25%; text-align: right;">
    {{ $penilaian->siswa->programKeahlian->nama ?? 'N/A' }}
</td>
                </tr>

                <tr>
                    <td style="white-space: nowrap; text-align: left;">No</td>
                    <td style="width: 2%; text-align: left;">:</td>
                    <td style="text-align: left;">G1-INT{{ str_pad($penilaian->id, 4, '0', STR_PAD_LEFT) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @if (isset($watermark))
        <img src="{{ $watermark }}" class="watermark">
    @endif
    <div class="table-container">



        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 5%;">NO</th>
                    <th>VARIABEL PENILAIAN</th>
                    <th style="width: 15%;">NILAI ANGKA</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($penilaian->detailPenilaian as $detail)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $kriteria[$detail->variabel] ?? ucfirst(str_replace('_', ' ', $detail->variabel)) }}</td>
                        <td class="text-center">{{ $detail->nilai }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="footer-label">Nilai Total</td>
                    <td class="text-center">{{ $penilaian->detailPenilaian->sum('nilai') }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="footer-label">Rata-Rata Nilai</td>
                    <td class="text-center">{{ number_format($penilaian->nilai_rata_rata, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <table class="bottom-container">
        <tr>
            <td style="width: 70%; vertical-align: top;">
                <div class="comment-section">
                    Komentar/Saran : {{ $penilaian->komentar_saran ?? '-' }}
                </div>

                <div class="grading-scale">
                    <span class="font-bold">SKALA PENILAIAN :</span><br>
                    90-100 : Memuaskan<br>
                    80-89 : Sangat Baik<br>
                    70-79 : Baik<br>
                    60-69 : Cukup<br>
                </div>
            </td>

            <td style="width: 30%; vertical-align: top; text-align: right;">
                <div class="signature-section">
                    Pacitan,
                    {{ \Carbon\Carbon::parse($penilaian->tanggal_penilaian)->locale('id')->isoFormat('D MMMM YYYY') }}<br>
                    Pembimbing Lapangan
                    <div class="signature-space"></div>
                    {{ $penilaian->penilai->name ?? 'N/A' }}<br>
                    Kepala Kantor Layanan Pacitan
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
