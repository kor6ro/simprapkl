<!doctype html>
<html lang="id">

<head>
    <meta charset="utf--8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login SimpraPKL</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/logo.png') }}" type="image/x-icon">

    <link href="{{ asset('assets/css/plugins/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/icons/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" />

    <style>
        body {
            background: linear-gradient(180deg, #f8f9fa, #3b589e);
        }

        .invalid-feedback {
            display: block;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow p-4">
                    <div class="text-center mb-4">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="img-fluid mb-2">
                        <h4 class="card-title">Selamat Datang</h4>
                        <p class="text-muted">Silakan masuk untuk melanjutkan</p>
                    </div>

                    {{-- Menampilkan pesan error umum (cth: kredensial salah) dari controller --}}
                    @if (session('error'))
                        {{-- TAMBAHKAN id="lockout-message" JIKA DALAM MODE TERKUNCI --}}
                        <div class="alert alert-danger" @if (session('locked')) id="lockout-message" @endif>
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Menampilkan pesan status (cth: password berhasil direset) --}}
                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    <form action="{{ route('authenticate') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username"
                                class="form-control @error('username') is-invalid @enderror"
                                value="{{ old('username') }}" placeholder="Masukkan username" required autofocus
                                @if (session('locked')) disabled @endif>

                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Masukkan Password" required
                                    @if (session('locked')) disabled @endif>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword"
                                    @if (session('locked')) disabled @endif>
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary"
                                @if (session('locked')) disabled @endif>Login</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <p class="text-muted">Jika Lupa Password Silahkan Hubungi Admin</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('password');
            const icon = this.querySelector('i');
            const isPassword = input.type === "password";

            input.type = isPassword ? "text" : "password";
            icon.classList.toggle("fa-eye", !isPassword);
            icon.classList.toggle("fa-eye-slash", isPassword);
        });

        document.addEventListener('DOMContentLoaded', function() {
            const lockoutMessage = document.getElementById('lockout-message');

            // Jika elemen pesan lockout ada, jalankan countdown
            if (lockoutMessage) {
                const formElements = document.querySelectorAll('input, button'); // Ambil semua input dan button

                // Ambil angka dari teks pesan error
                let textContent = lockoutMessage.textContent;
                let seconds = parseInt(textContent.match(/(\d+)/)[0]);

                const countdown = setInterval(function() {
                    seconds--;
                    // Update pesan setiap detik
                    lockoutMessage.textContent =
                        `Terlalu banyak percobaan login. Silakan coba lagi dalam ${seconds} detik.`;

                    // Jika waktu habis
                    if (seconds <= 0) {
                        clearInterval(countdown); // Hentikan countdown
                        lockoutMessage.textContent = 'Anda sudah bisa mencoba login kembali.';
                        lockoutMessage.classList.remove('alert-danger');
                        lockoutMessage.classList.add('alert-success'); // Ubah warna jadi hijau

                        // Aktifkan kembali semua input dan tombol
                        formElements.forEach(function(element) {
                            element.disabled = false;
                        });

                        // Fokuskan kembali ke input username
                        document.getElementById('username').focus();
                    }
                }, 1000); // Jalankan setiap 1 detik
            }
        });
    </script>
</body>

</html>
