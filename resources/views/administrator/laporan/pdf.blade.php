<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kegiatan</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            margin: 0;
        }

        .header p {
            font-size: 12px;
            margin: 5px 0 0 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        td {
            word-wrap: break-word;
            word-break: break-all;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            white-space: nowrap;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $judul }}</h1>
        <p>{{ $subjudul }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                {{-- [PERBAIKAN] Gunakan @cannot agar kolom selalu ada untuk admin --}}
                @cannot('is-siswa')
                    <th>Nama Siswa</th>
                @endcannot
                <th>Tim (Divisi)</th>
                <th>Jenis Kegiatan</th>
                <th>Deskripsi</th>
                <th>Tanggal Lapor</th>
                <th>Status Laporan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($laporan as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    {{-- [PERBAIKAN] Sesuaikan dengan logika di header --}}
                    @cannot('is-siswa')
                        <td>{{ $item->user->name ?? 'N/A' }}</td>
                    @endcannot
                    <td>{{ $item->tim->divisi->nama_divisi ?? 'N/A' }}</td>
                    <td>{{ $item->jenisKegiatan->nama_kegiatan ?? 'N/A' }}</td>
                    <td>{{ $item->deskripsi_kegiatan }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->isoFormat('D MMM YYYY, HH:mm') }}</td>
                    <td>
                        @php
                            $displayText = '';
                            switch ($item->status) {
                                case 'pending':
                                    $displayText = 'Menunggu Persetujuan';
                                    break;
                                case 'approved':
                                    $displayText = 'Disetujui';
                                    break;
                                case 'rejected':
                                    $displayText = 'Perlu Revisi';
                                    break;
                                default:
                                    $displayText = ucfirst($item->status);
                                    break;
                            }
                        @endphp
                        {{ $displayText }}
                    </td>
                </tr>
            @empty
                {{-- [PERBAIKAN] Hitung colspan berdasarkan jumlah kolom header yang sebenarnya --}}
                @php
                    // Kolom dasar ada 6 (#, Tim, Jenis, Deskripsi, Tanggal, Status)
                    // Jika BUKAN siswa, tambah 1 kolom lagi untuk "Nama Siswa"
                    $colspan = auth()->user()->group_id != 4 ? 7 : 6;
                @endphp
                <tr>
                    <td colspan="{{ $colspan }}" style="text-align: center;">Tidak ada data yang ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Laporan ini dibuat secara otomatis pada {{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY, HH:mm') }}
    </div>
</body>

</html>
