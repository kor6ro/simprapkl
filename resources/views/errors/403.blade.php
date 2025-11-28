<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>403 - Akses Ditolak</title>
    <style>
        /* CSS Reset Sederhana */
        body,
        html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        /* Styling Utama Halaman */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8fafc;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .container {
            background-color: #ffffff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            max-width: 500px;
            border: 1px solid #e2e8f0;
        }

        h1 {
            font-size: 90px;
            margin: 0;
            color: #e2e8f0;
            font-weight: 700;
        }

        p {
            font-size: 1.1rem;
            margin-top: 10px;
            margin-bottom: 30px;
            color: #64748b;
        }

        .btn-dashboard {
            display: inline-block;
            padding: 12px 28px;
            background-color: #3b82f6;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .btn-dashboard:hover {
            background-color: #2563eb;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>403</h1>
        <p>
            <strong>Akses Ditolak</strong><br>
            Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.
        </p>
        <a href="{{ route('dashboard') }}" class="btn-dashboard">Kembali ke Dashboard</a>
    </div>
</body>

</html>
