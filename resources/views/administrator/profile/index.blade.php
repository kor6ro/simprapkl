@extends('layout.main')
@section('css')
    <style>

    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Profile</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="#">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-primary">
        <div class="card-body">
            <div class="row justify-content-between align-items-center mb-4">
                <div class="col-auto">
                    <h4 class="card-title text-primary">Data Profile</h4>
                </div>
                <div class="col-auto">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="fa fa-edit me-2"></i> Edit
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-auto">
                    <div class="row mx-0">
                        <label for="name" class="col-auto col-form-label">
                            <i class="fa fa-arrow-right me-1"></i> Name
                        </label>
                        <div class="col">
                            <input type="text" readonly class="form-control-plaintext" id="name"
                                value="{{ $profile->name }}">
                        </div>
                    </div>
                </div>
                <div class="col-xl-auto">
                    <div class="row mx-0">
                        <label for="username" class="col-auto col-form-label">
                            <i class="fa fa-arrow-right me-1"></i> Username
                        </label>
                        <div class="col">
                            <input type="text" readonly class="form-control-plaintext" id="username"
                                value="{{ $profile->username }}">
                        </div>
                    </div>
                </div>
                <div class="col-xl-auto">
                    <div class="row mx-0">
                        <label for="email" class="col-auto col-form-label">
                            <i class="fa fa-arrow-right me-1"></i> Email
                        </label>
                        <div class="col">
                            <input type="text" readonly class="form-control-plaintext" id="email"
                                value="{{ $profile->email }}">
                        </div>
                    </div>
                </div>
                <div class="col-xl-auto">
                    <div class="row mx-0">
                        <label for="password" class="col-auto col-form-label">
                            <i class="fa fa-arrow-right me-1"></i> Password
                        </label>
                        <div class="col">
                            <input type="text" readonly class="form-control-plaintext" id="password"
                                value="{{ $profile->password }}">
                        </div>
                    </div>
                </div>
                <div class="col-xl-auto">
                    <div class="row mx-0">
                        <label for="validasi" class="col-auto col-form-label">
                            <i class="fa fa-arrow-right me-1"></i> Validasi
                        </label>
                        <div class="col">
                            <input type="text" readonly class="form-control-plaintext" id="validasi"
                                value="{{ $profile->validasi }}">
                        </div>
                    </div>
                </div>
                <div class="col-xl-auto">
                    <div class="row mx-0">
                        <label for="sekolah_id" class="col-auto col-form-label">
                            <i class="fa fa-arrow-right me-1"></i> Sekolah Id
                        </label>
                        <div class="col">
                            <input type="text" readonly class="form-control-plaintext" id="sekolah_id"
                                value="{{ $profile->sekolah->nama }}">
                        </div>
                    </div>
                </div>
                <div class="col-xl-auto">
                    <div class="row mx-0">
                        <label for="group_id" class="col-auto col-form-label">
                            <i class="fa fa-arrow-right me-1"></i> Group Id
                        </label>
                        <div class="col">
                            <input type="text" readonly class="form-control-plaintext" id="group_id"
                                value="{{ $profile->group->nama }}">
                        </div>
                    </div>
                </div>
                <div class="col-xl-auto">
                    <div class="row mx-0">
                        <label for="alamat" class="col-auto col-form-label">
                            <i class="fa fa-arrow-right me-1"></i> Alamat
                        </label>
                        <div class="col">
                            <input type="text" readonly class="form-control-plaintext" id="alamat"
                                value="{{ $profile->alamat }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    @if (session()->has('dataSaved') && session()->get('dataSaved') == true)
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session()->get('message') }}',
            });
        </script>
    @endif
    @if (session()->has('dataSaved') && session()->get('dataSaved') == false)
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session()->get('message') }}',
            });
        </script>
    @endif
@endsection
