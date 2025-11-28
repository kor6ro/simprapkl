<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Sertifikat - {{ $nama_penerima }}</title>
    <style>
        @font-face {
            font-family: 'Good Vibrations Script';
            src: url('{{ public_path('fonts/GoodVibrations-Script-400.ttf') }}');
        }

        @font-face {
            font-family: 'DIN Next';
            font-weight: 400;
            src: url('{{ public_path('fonts/DINNextW1G-Regular.otf') }}');
        }

        @font-face {
            font-family: 'DIN Next';
            font-weight: 500;
            src: url('{{ public_path('fonts/DINNextW1G-Medium.otf') }}');
        }

        @font-face {
            font-family: 'DIN Next';
            font-weight: 900;
            src: url('{{ public_path('fonts/DINNextW1G-Black.otf') }}');
        }

        @page {
            margin: 0;
        }

        body {
            background-image: url("{{ $background_base64 }}");
            background-size: 100% 100%;
            background-repeat: no-repeat;

            font-family: 'DIN Next', sans-serif;
            font-weight: 400;
            font-size: 14pt;
            padding: 1cm 2.5cm;
            box-sizing: border-box;
            color: #0D497C
        }

        .container {
            text-align: center;
            width: 100%;
            height: 100%;
        }

        .logo {
            height: 72px;
            margin-bottom: 0.5cm;
        }

        .title {
            font-family: 'DIN Next', sans-serif;
            font-weight: 500;
            font-size: 36pt;
            margin: 0;
            padding-bottom: 0.5cm;
            border-bottom: 2px solid #333;
            display: inline-block;
        }

        .intro-text {
            font-size: 14pt;
            font-weight: 500;
            padding-top: 0.5cm;
            margin-bottom: 0;
        }

        .recipient-name {
            font-family: 'Good Vibrations Script', cursive;
            font-size: 48pt;
            color: #d09632;
            line-height: 0.8;
            margin: 0.5cm 0;
        }

        .school-name {
            font-family: 'DIN Next';
            color: #3863c7;
            font-weight: 500;
            font-size: 24pt;
            margin-bottom: 15px;
        }

        .description {
            font-weight: 500;
            font-size: 12pt;
            line-height: 1.2;
        }

        .signature-block {
            font-family: 'DIN Next';
            font-weight: 500;
            font-size: 14pt;
            margin-top: 1cm;
            text-align: center;
            width: 100%;
            position: absolute;
            bottom: 1cm;
            left: 0;
        }

        .signature-block p {
            font-weight: 900;
        }

        .signature-name {
            display: inline-block;
            position: relative;
            font-family: 'DIN Next';
            font-weight: 900;
            margin-top: 72px;
            padding-bottom: 2px;
            padding-top: 5px;
        }

        .signature-name::after {
            font-weight: 900;
            content: "";
            position: absolute;
            left: 50%;
            bottom: 100%;
            width: 60%;
            height: 1px;
            background: #333;
            transform: translateX(-50%);
        }
    </style>
</head>

<body>

    <div class="container">
        @if (isset($logo_base64) && $logo_base64)
            <img src="{{ $logo_base64 }}" class="logo" alt="Logo Perusahaan">
        @endif

        <div class="content">
            <h1 class="title">SERTIFIKAT PENGHARGAAN</h1>
            <div class="intro-text">Sertifikat Penghargaan ini diberikan kepada</div>

            <div class="recipient-name">{{ $nama_penerima }}</div>

            <div class="school-name">{{ strtoupper($asal_sekolah) }}</div>

            <div class="description">
                yang telah melaksanakan Praktik Kerja Lapangan (PKL)<br>
                di PT Sandya Sistem Indonesia pada periode
                {{ \Carbon\Carbon::parse($tanggal_mulai)->locale('id')->isoFormat('D MMMM') }} &ndash;
                {{ \Carbon\Carbon::parse($tanggal_selesai)->locale('id')->isoFormat('D MMMM YYYY') }}
            </div>
        </div>

        <div class="signature-block">
            <p>{{ $kota_penerbitan }}, {{ $tanggal_penerbitan->locale('id')->isoFormat('D MMMM YYYY') }}</p>
            <div class="signature-name" style="letter-spacing: 2px; font-weight: 900;">{{ $nama_penandatangan }}
            </div>
            <div>{{ $jabatan_penandatangan }}</div>
        </div>
    </div>

</body>

</html>
