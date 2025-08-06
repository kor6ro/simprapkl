@extends('layout.main')
@section('css')
    <style>
        .profile-label {
            font-weight: 500;
            color: #6c757d;
        }

        .profile-value {
            font-size: 1rem;
            font-weight: 600;
            background: #f8f9fa;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
        }

        .card {
            border-radius: 1rem;
        }

        .card-header {
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }

        .btn-outline-primary {
            border-radius: 0.5rem;
        }

        .btn-sm {
            padding: 0.35rem 0.65rem;
            font-size: 0.875rem;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-primary">Profile</h4>
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                    <i class="fa fa-edit me-1"></i> Edit Profile
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Data Profile</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="profile-label">Name</label>
                                <div class="profile-value">{{ $profile->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="profile-label">Username</label>
                                <div class="profile-value">{{ $profile->username }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="profile-label">Email</label>
                                <div class="profile-value">{{ $profile->email }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="profile-label">Password</label>
                                <div class="profile-value">
                                    <a href="{{ route('password_request') }}" class="btn btn-sm btn-outline-primary">Lupa
                                        Password?</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="profile-label">Validasi</label>
                                <div class="profile-value">
                                    @if ($profile->validasi == 1)
                                        <span class="text-success">Sudah Tervalidasi!</span>
                                    @else
                                        <span class="text-danger">Belum Tervalidasi!</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="profile-label">Sekolah</label>
                                <div class="profile-value">{{ $profile->sekolah->nama }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="profile-label">Group</label>
                                <div class="profile-value">{{ $profile->group->nama }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="profile-label">Alamat</label>
                                <div class="profile-value">{{ $profile->alamat }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @if (session()->has('dataSaved'))
        <script>
            Swal.fire({
                icon: '{{ session()->get('dataSaved') ? 'success' : 'error' }}',
                title: '{{ session()->get('dataSaved') ? 'Success' : 'Error' }}',
                text: '{{ session()->get('message') }}',
            });
        </script>
    @endif
@endsection
