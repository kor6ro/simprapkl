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
                                <div class="profile-value text-muted">********</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="profile-label">Validasi</label>
                                <div class="profile-value">{{ $profile->validasi }}</div>
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
