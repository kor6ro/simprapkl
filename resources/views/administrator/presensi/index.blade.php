@extends('layout.main')
@section('css')
    <style>
        .presensi-form {
            margin-bottom: 15px;
        }
    </style>
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Presensi</h4>
        <form method="POST" action="{{ route('presensi.generate.alpa') }}">
            @csrf
            <button class="btn btn-danger">Generate Siswa Alpa Hari Ini</button>
        </form>
    </div>
        @if(session('success'))
            <div class="alert alert-success">{ session('success') }
        <form method="POST" action="{{ route('presensi.generate.alpa') }}">
            @csrf
            <button class="btn btn-danger">Generate Siswa Alpa Hari Ini</button>
        </form>
    </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{ session('error') }
        <form method="POST" action="{{ route('presensi.generate.alpa') }}">
            @csrf
            <button class="btn btn-danger">Generate Siswa Alpa Hari Ini</button>
        </form>
    </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">{ session('info') }
        <form method="POST" action="{{ route('presensi.generate.alpa') }}">
            @csrf
            <button class="btn btn-danger">Generate Siswa Alpa Hari Ini</button>
        </form>
    </div>
        @endif
        <div class="card p-3 mb-4">
            <form method="POST" action="{ route('presensi.checkin') }" enctype="multipart/form-data" class="presensi-form">
                @csrf
                <label>Bukti Foto Absen Masuk:</label>
                <input type="file" name="bukti_foto" accept="image/*" capture="environment" required class="form-control mb-2">
                <button type="submit" class="btn btn-primary w-100">Absen Masuk</button>
            </form>
            <form method="POST" action="{ route('presensi.checkout') }" enctype="multipart/form-data" class="presensi-form">
                @csrf
                <label>Bukti Foto Absen Keluar:</label>
                <input type="file" name="bukti_foto" accept="image/*" capture="environment" required class="form-control mb-2">
                <button type="submit" class="btn btn-secondary w-100">Absen Pulang</button>
            </form>
            <form method="POST" action="{ route('presensi.sakit') }" enctype="multipart/form-data" class="presensi-form">
                @csrf
                <label>Bukti Foto Surat Izin / Sakit:</label>
                <input type="file" name="bukti_foto" accept="image/*" capture="environment" required class="form-control mb-2">
                <button type="submit" class="btn btn-warning w-100">Ajukan Izin/Sakit</button>
            </form>
        <form method="POST" action="{{ route('presensi.generate.alpa') }}">
            @csrf
            <button class="btn btn-danger">Generate Siswa Alpa Hari Ini</button>
        </form>
    </div>
        <!-- Tabel presensi atau data lainnya bisa tetap di bawah -->
        <!-- ... -->
        <form method="POST" action="{{ route('presensi.generate.alpa') }}">
            @csrf
            <button class="btn btn-danger">Generate Siswa Alpa Hari Ini</button>
        </form>
    </div>
        <form method="POST" action="{{ route('presensi.generate.alpa') }}">
            @csrf
            <button class="btn btn-danger">Generate Siswa Alpa Hari Ini</button>
        </form>
    </div>
@endsection
<table class="table table-bordered table-striped" id="presensiTable">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Sekolah</th>
            <th>Tanggal</th>
            <th>Masuk</th>
            <th>Pulang</th>
            <th>Status</th>
            <th>Keterangan</th>
            <th>Bukti Foto</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $item)
        <tr>
            <td>{{ $item->user->name }}</td>
            <td>{{ $item->user->school->nama ?? '-' }}</td>
            <td>{{ $item->tanggal }}</td>
            <td>{{ $item->jam_masuk ?? '-' }}</td>
            <td>{{ $item->jam_keluar ?? '-' }}</td>
            <td>{{ $item->status }}</td>
            <td>{{ $item->keterangan }}</td>
            <td>
                @if ($item->bukti_foto)
                    <a href="{{ asset('storage/' . $item->bukti_foto) }}" target="_blank">Lihat</a>
                @else
                    -
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@push('scripts')
<script>
$(document).ready(function () {
    $('#presensiTable').DataTable();
});
</script>
@endpush
