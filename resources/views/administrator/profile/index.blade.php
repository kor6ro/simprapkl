@extends('layout.main')

@section('css')
    <style>
        .profile-image-wrapper {
            width: 150px;
            aspect-ratio: 1 / 1;
            margin: auto;
        }

        .profile-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #dee2e6;
        }

        /* Style untuk list data yang baru */
        .list-group-item {
            /* Mencegah kata yang sangat panjang merusak layout */
            word-break: break-word;
        }

        .list-group-item>div {
            font-weight: 500;
            color: #6c757d;
        }

        .list-group-item>strong {
            color: #212529;
        }
    </style>
@endsection

@section('content')
    {{-- Judul Halaman dan Breadcrumb --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Profil Pengguna</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Profil</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if (session('dataSaved'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Kartu Profil --}}
    <div class="card">
        <div class="card-body">
            <div class="row align-items-center">
                {{-- Kolom Foto --}}
                <div class="col-lg-3 text-center mb-4 mb-lg-0">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#profilePictureModal">
                        <div class="profile-image-wrapper">
                            <img src="{{ $profile->photo_profile ? asset('uploads/profiles/' . $profile->photo_profile) : asset('assets/images/placeholder.jpg') }}"
                                alt="Profile Picture" class="profile-image">
                        </div>
                    </a>
                    <h5 class="mt-3 mb-1">{{ $profile->name }}</h5>
                    <p class="text-muted mb-0">{{ $profile->email }}</p>
                </div>

                {{-- Kolom Detail Informasi dengan List Group --}}
                <div class="col-lg-9">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <div>Username</div>
                            <strong class="text-end">{{ $profile->username }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <div>Group</div>
                            <strong class="text-end">{{ $profile->group->nama }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <div>Status Validasi</div>
                            <strong class="text-end">
                                @if ($profile->validasi == 1)
                                <span class="badge bg-success">Tervalidasi</span>@else<span
                                        class="badge bg-warning text-dark">Belum Tervalidasi</span>
                                @endif
                            </strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <div>Alamat</div>
                            <strong class="text-end">{{ $profile->alamat ?? 'Belum diisi' }}</strong>
                        </li>

                        @if (in_array(strtolower($profile->group?->nama), ['siswa', 'pembimbing']))
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <div>Sekolah</div>
                                <strong class="text-end">{{ $profile->sekolah?->nama ?? 'Belum diatur' }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <div>Program Keahlian</div>
                                <strong class="text-end">{{ $profile->programKeahlian?->nama ?? 'Belum diatur' }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <div>Periode PKL</div>
                                <strong class="text-end">
                                    @forelse ($profile->periodePkl as $periode)
                                        {{ \Carbon\Carbon::parse($periode->awal_periode)->format('d M Y') }} -
                                        {{ \Carbon\Carbon::parse($periode->akhir_periode)->format('d M Y') }}@if (!$loop->last)
                                            <br>
                                        @endif @empty Belum terdaftar
                                    @endforelse
                                </strong>
                            </li>
                            @if (strtolower($profile->group?->nama) == 'siswa')
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <div>ID PKL</div>
                                    <strong class="text-end">{{ $profile->id_pkl ?? 'Belum diatur' }}</strong>
                                </li>
                            @endif
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-footer bg-transparent text-end">
            <a href="{{ route('profile.edit') }}" class="btn btn-primary me-1">
                <i class="fa fa-edit me-1"></i> Edit Profil
            </a>
            <a href="{{ route('profile.changePasswordForm') }}" class="btn btn-warning">
                <i class="fa fa-key me-1"></i> Ganti Password
            </a>
        </div>
    </div>
    {{-- Modal (Tidak Berubah) --}}
    <div class="modal fade" id="profilePictureModal" tabindex="-1" aria-labelledby="profilePictureModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0 position-absolute top-0 end-0" style="z-index: 10;">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 modal-body-photo text-center">
                    <img src="{{ $profile->photo_profile ? asset('uploads/profiles/' . $profile->photo_profile) : asset('assets/images/placeholder.jpg') }}"
                        alt="Foto Profil {{ $profile->name }}" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

@endsection
