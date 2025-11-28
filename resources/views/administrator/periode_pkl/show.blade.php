@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Detail Periode PKL</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.periode-pkl.index') }}">Periode PKL</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Informasi Periode</h4>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Awal Periode:</strong>
                        {{ \Carbon\Carbon::parse($periodePkl->awal_periode)->format('d F Y') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Akhir Periode:</strong>
                        {{ \Carbon\Carbon::parse($periodePkl->akhir_periode)->format('d F Y') }}</p>
                </div>
            </div>
            <hr>
            {{-- PERBAIKAN DI SINI --}}
            <h4 class="card-title text-primary mb-4">Daftar Peserta ({{ $periodePkl->users->count() }})</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nama Peserta</th>
                        <th scope="col">Grup</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- PERBAIKAN DI SINI --}}
                    @forelse ($periodePkl->users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $user->name }}</td>
                            <td>
                                <span class="badge bg-success">{{ $user->group->nama ?? 'N/A' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Belum ada peserta di periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="button-navigate mt-4">
                <a href="{{ route('admin.periode-pkl.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Batal
                </a>
            </div>
        </div>
    </div>
@endsection
