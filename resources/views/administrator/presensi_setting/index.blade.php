@extends('layout.main')
@section('content')
    <div class="container">
        <h4 class="mb-4">Pengaturan Waktu Presensi</h4>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.presensi_setting.update') }}" method="POST">
            @csrf
            {{-- [PERBAIKAN] Menggunakan col-6 col-md-3 agar 2x2 di mobile --}}
            <div class="row mb-3">
                <div class="col-6 col-md-3 mb-3">
                    <label for="pagi_mulai" class="form-label">Pagi Mulai</label>
                    <input type="time" name="pagi_mulai" class="form-control"
                        value="{{ old('pagi_mulai', ($setting->pagi_mulai ? $setting->pagi_mulai->format('H:i') : null) ?? '07:00') }}">
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <label for="pagi_selesai" class="form-label">Pagi Selesai</label>
                    <input type="time" name="pagi_selesai" class="form-control"
                        value="{{ old('pagi_selesai', ($setting->pagi_selesai ? $setting->pagi_selesai->format('H:i') : null) ?? '08:15') }}">
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <label for="sore_mulai" class="form-label">Sore Mulai</label>
                    <input type="time" name="sore_mulai" class="form-control"
                        value="{{ old('sore_mulai', ($setting->sore_mulai ? $setting->sore_mulai->format('H:i') : null) ?? '16:00') }}">
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <label for="sore_selesai" class="form-label">Sore Selesai</label>
                    <input type="time" name="sore_selesai" class="form-control"
                        value="{{ old('sore_selesai', ($setting->sore_selesai ? $setting->sore_selesai->format('H:i') : null) ?? '21:00') }}">
                </div>
            </div>
            <div class="row mb-3">
                {{-- Ini sudah responsif (akan 100% di mobile) --}}
                <div class="col-md-6">
                    <label for="toleransi_telat" class="form-label">Toleransi Keterlambatan (menit)</label>
                    <input type="number" class="form-control" name="toleransi_telat" id="toleransi_telat"
                        value="{{ old('toleransi_telat', $setting->toleransi_telat ?? 15) }}" min="0" max="60"
                        required>
                    <div class="form-text">
                        Waktu toleransi setelah batas normal. Contoh: Jika batas normal 08:15 dan toleransi 15 menit,
                        maka presensi sampai 08:30 masih dianggap "Terlambat".
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>

        {{-- Preview Setting --}}
        @if ($setting)
            <div class="mt-4">
                <h5>Preview Pengaturan Saat Ini:</h5>
                <div class="row">
                    {{-- [PERBAIKAN] Menambah margin-bottom di mobile --}}
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="card">
                            <div class="card-header bg-warning text-white">
                                <strong>Sesi Pagi</strong>
                            </div>
                            <div class="card-body">
                                <p>Mulai:
                                    <strong>{{ $setting->pagi_mulai ? \Carbon\Carbon::parse($setting->pagi_mulai)->format('H:i') : 'Not set' }}</strong>
                                </p>
                                <p>Batas Normal:
                                    <strong>{{ $setting->pagi_selesai ? \Carbon\Carbon::parse($setting->pagi_selesai)->format('H:i') : 'Not set' }}</strong>
                                </p>
                                <p>Batas Toleransi:
                                    <strong>{{ $setting->toleransi_telat ?? 'Not set' }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <strong>Sesi Sore</strong>
                            </div>
                            <div class="card-body">
                                <p>Mulai:
                                    <strong>{{ $setting->sore_mulai ? \Carbon\Carbon::parse($setting->sore_mulai)->format('H:i') : 'Not set' }}</strong>
                                </p>
                                <p>Batas Normal:
                                    <strong>{{ $setting->sore_selesai ? \Carbon\Carbon::parse($setting->sore_selesai)->format('H:i') : 'Not set' }}</strong>
                                </p>
                                <p>Batas Toleransi:
                                    <strong>{{ $setting->toleransi_telat ?? 'Not set' }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
