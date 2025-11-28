@extends('layout.main')

@section('content')
    {{-- Judul Halaman dan Breadcrumb --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Ganti Password</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('profile.index') }}">Profil</a></li>
                        <li class="breadcrumb-item active">Ganti Password</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Kartu Form Utama --}}
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Form Ganti Password</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.updatePassword') }}" method="post">
                        @csrf
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <input type="password" class="form-control" name="current_password" id="current_password"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="new_password" id="new_password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    data-target="new_password"><i class="fas fa-eye-slash"></i></button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="new_password_confirmation"
                                    id="new_password_confirmation" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    data-target="new_password_confirmation"><i class="fas fa-eye-slash"></i></button>
                            </div>
                        </div>

                        {{-- Tombol Aksi Responsif --}}
                        <div class="border-top pt-3 mt-3">
                            {{-- d-grid untuk mobile, d-md-flex untuk desktop. Tombol jadi full-width di mobile --}}
                            <div class="d-grid d-md-flex gap-2">
                                <a href="{{ route('profile.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-times me-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i> Simpan Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    {{-- Javascript tidak berubah --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.dataset.target;
                    const passwordInput = document.getElementById(targetId);
                    const icon = this.querySelector('i');

                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                });
            });
        });
    </script>
@endsection
