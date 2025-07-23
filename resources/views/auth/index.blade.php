<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login SimpraPKL</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/logo.png') }}" type="image/x-icon">

    <!-- Bootstrap & Icons -->
    <link href="{{ asset('assets/icons/coreui/css/free.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/icons/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/plugins/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" />
</head>

<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background-color: #f8f9fa;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow p-4">
                    <div class="text-center mb-4">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="img-fluid">
                        <p class="text-muted">Masukkan Username dan password Anda</p>
                    </div>

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('authenticate') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="username">Username</label>
                            <input type="text" name="username" class="form-control" id="username"
                                value="{{ old('username') }}" placeholder="Masukkan username" required>
                            @error('username')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="Masukkan Password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <span style="font-size: 14px">Belum punya akun? <a
                                href="{{ route('register.form') }}">Register</a></span>
                    </div>
                </div>
            </div>
        </div>
    </div>S

    <!-- Toggle Password Visibility -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('password');
            const icon = this.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        });
    </script>
</body>

</html>
