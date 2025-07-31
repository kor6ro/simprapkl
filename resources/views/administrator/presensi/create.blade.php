@extends('layout.main')

@section('css')
    <style>
        table th {
            width: 30%;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <h4 class="mb-4">Presensi</h4>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('presensi.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <table class="table table-bordered">
                <tr>
                    <th>Nama</th>
                    <td>{{ auth()->user()->name }}</td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td>
                        <input type="date" name="tanggal_presensi" class="form-control" value="{{ date('Y-m-d') }}"
                            readonly>
                    </td>
                </tr>
                <tr>
                    <th>Tidak Dapat Hadir</th>
                    <td>
                        <select name="presensi_status_id" class="form-control" required>
                            <option value="">Pilih Alasan</option>
                            @foreach ($presensistatus as $status)
                                @if (in_array(strtolower($status->status), ['izin', 'sakit']))
                                    <option value="{{ $status->id }}">{{ $status->status }}</option>
                                @endif
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Keterangan</th>
                    <td>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Isi jika diperlukan..."></textarea>
                    </td>
                </tr>
                <tr>
                    <th>Upload Bukti</th>
                    <td>
                        <input type="file" name="bukti" class="form-control">
                        <small class="text-muted">Format: .jpg, .jpeg, .png, max 2MB</small>
                    </td>
                </tr>
            </table>

            <div class="mt-3">
                <button type="submit" class="btn btn-success">Kirim</button>
                <a href="{{ route('presensi.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
