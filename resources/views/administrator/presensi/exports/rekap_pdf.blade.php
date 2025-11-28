<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $judul ?? 'Laporan Presensi' }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
            /* [DIHAPUS] padding-bottom: 50px; tidak lagi diperlukan */
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: bold;
        }

        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .info-table .label {
            width: 100px;
            font-weight: bold;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 10px;
            /* Izinkan tabel terpecah antar halaman */
            page-break-inside: auto;
        }

        .main-table th,
        .main-table td {
            border: 1px solid #ccc;
            padding: 5px;
            word-wrap: break-word;
        }

        .main-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .main-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .main-table tbody tr {
            /* Biarkan 'auto' agar data bisa masuk ke halaman 1 */
            page-break-inside: auto;
            page-break-after: auto;
        }

        .text-center {
            text-align: center;
        }

        /* [PERBAIKAN FOOTER] */
        .footer {
            /* Hapus position: fixed dan properti terkait (bottom, left, right, height) */
            width: 100%;
            text-align: center;
            /* Beri jarak dari tabel di atasnya */
            margin-top: 20px;

            border-top: 1px solid #ccc;
            padding-top: 5px;
            font-size: 9px;
            color: #777;
        }

        /* Hapus .page-number dan .print-date karena tidak lagi 'float' */
        /* Hapus .page-number:before karena paginasi tidak berfungsi tanpa fixed */
    </style>
</head>

<body>
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
                <th class="text-center" style="width: 5%;">#</th>
                <th style="width: 20%;">Nama Siswa</th>
                {{-- Loop untuk membuat header tabel secara dinamis --}}
                @foreach ($selectedColumns as $label)
                    <th class="text-center">{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $row->user->name ?? '-' }}</td>

                    {{-- Loop untuk membuat isi tabel secara dinamis --}}
                    @foreach ($selectedColumns as $key => $label)
                        <td>
                            @switch($key)
                                @case('sekolah')
                                    {{ $row->user->sekolah->nama ?? '-' }}
                                @break

                                @case('tanggal')
                                    <span
                                        style="white-space: nowrap;">{{ \Carbon\Carbon::parse($row->tanggal_presensi)->format('d/m/Y') }}</span>
                                @break

                                @case('sesi')
                                    {{ ucfirst($row->sesi) }}
                                @break

                                @case('jam_presensi')
                                    {{ $row->jam_presensi ? \Carbon\Carbon::parse($row->jam_presensi)->format('H:i') : '-' }}
                                @break

                                @case('status')
                                    {{ $row->status_display }}
                                @break

                                @case('approval_status')
                                    @if ($row->approval_status)
                                        {{ ucfirst($row->approval_status) }}
                                    @else
                                        -
                                    @endif
                                @break

                                @case('keterangan')
                                    {{ $row->keterangan ?? '-' }}
                                @break
                            @endswitch
                        </td>
                    @endforeach
                </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($selectedColumns) + 2 }}" class="text-center" style="padding: 15px;">
                            Tidak ada data yang ditemukan untuk filter yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
        </div>

    </body>

    </html>
