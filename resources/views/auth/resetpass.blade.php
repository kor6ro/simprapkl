<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Reset Password - SimpraPKL</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/logo.png') }}" type="image/x-icon">
    <link href="{{ asset('assets/icons/coreui/css/free.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/icons/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/plugins/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" />
</head>

<body class="d-flex align-items-center justify-content-center"
    style="min-height: 100vh; background: linear-gradient(180deg, #f8f9fa, #3b589e);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow p-4">
                    <div class="text-center mb-4">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="img-fluid">
                        <p class="text-muted">Masukkan password baru Anda</p>
                    </div>

                    <form method="POST" action="{{ route('password_update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="Email">
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Password Baru</label>
                            <input type="password" name="password" class="form-control" required
                                placeholder="Password baru">
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required
                                placeholder="Ulangi password">
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-success">Reset Password</button>
                        </div>
                    </form>

                    <div class="text-center mt-3" style="font-size: 14px">
                        <a href="{{ route('login') }}">← Kembali ke Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
