@extends('layout.main')

@section('content')
    <div class="container">
        <h4 class="mb-4">Pengaturan Waktu Presensi</h4>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('presensi_setting.update') }}" method="POST">
            @csrf

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="pagi_mulai">Pagi Mulai</label>
                    <input type="time" name="pagi_mulai" class="form-control"
                        value="{{ old('pagi_mulai', $setting->pagi_mulai) }}" required>
                </div>
                <div class="col-md-3">
                    <label for="pagi_selesai">Pagi Selesai</label>
                    <input type="time" name="pagi_selesai" class="form-control"
                        value="{{ old('pagi_selesai', $setting->pagi_selesai) }}" required>
                </div>
                <div class="col-md-3">
                    <label for="sore_mulai">Sore Mulai</label>
                    <input type="time" name="sore_mulai" class="form-control"
                        value="{{ old('sore_mulai', $setting->sore_mulai) }}" required>
                </div>
                <div class="col-md-3">
                    <label for="sore_selesai">Sore Selesai</label>
                    <input type="time" name="sore_selesai" class="form-control"
                        value="{{ old('sore_selesai', $setting->sore_selesai) }}" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
@endsection
