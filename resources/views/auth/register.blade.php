<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Register Siswa PKL</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/logo.png') }}" type="image/x-icon">

    <!-- Bootstrap & Icons -->
    <link href="{{ asset('assets/icons/coreui/css/free.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/icons/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/plugins/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" />
</head>
<style>
    body {
        background: linear-gradient(180deg, #f8f9fa, #3b589e);
    }
</style>

<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow p-4">
                    <div class="text-center mb-1">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="img-fluid">
                        <h3 class="mt-2 text-primary">Registrasi SimPraPKL</h3>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('register.siswa') }}">
                        @csrf
                        <input type="hidden" name="group_id" value="3">
                        <input type="hidden" name="validasi" value="0">

                        <div class="mb-1">
                            <label>Nama</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                required placeholder="Masukkan Nama Lengkap">
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-1">
                            <label>Alamat</label>
                            <input type="text" name="alamat" class="form-control" value="{{ old('alamat') }}"
                                required placeholder="Masukkan Alamat">
                            @error('alamat')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-1">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" value="{{ old('username') }}"
                                required placeholder="Masukkan Username">
                            @error('username')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-1">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                                required placeholder="Masukkan Email">
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-1">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required
                                placeholder="Masukkan Password">
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-1">
                            <label>Asal Sekolah</label>
                            <select name="sekolah_id" class="form-select" required>
                                <option disabled selected>-- Pilih Sekolah --</option>
                                @foreach ($sekolahList as $val)
                                    <option value="{{ $val->id }}">{{ $val->nama }}</option>
                                @endforeach
                            </select>
                            @error('sekolah_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary">Daftar Sekarang</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p style="font-size: 14px">Sudah punya akun? <a href="{{ route('login') }}"
                                class="text-decoration-none">Login di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script src="{{ asset('assets/js/plugins/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
