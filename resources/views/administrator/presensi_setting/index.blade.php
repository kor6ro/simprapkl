@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Setting Presensi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Setting Presensi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pagi Mulai</th>
                        <th>Pagi Selesai</th>
                        <th>Sore Mulai</th>
                        <th>Sore Selesai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($settings as $index => $setting)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $setting->pagi_mulai }}</td>
                            <td>{{ $setting->pagi_selesai }}</td>
                            <td>{{ $setting->sore_mulai }}</td>
                            <td>{{ $setting->sore_selesai }}</td>
                            <td>
                                <a href="{{ route('presensi_setting.edit', $setting->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
