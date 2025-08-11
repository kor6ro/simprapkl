@extends('layout.main')
@section('content')
    <div class="container">
        <h4 class="mb-4">Pengaturan Waktu Presensi</h4>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('admin.presensi_setting.update') }}" method="POST">
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

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="toleransi_telat" class="form-label">Toleransi Keterlambatan (menit)</label>
                    <input type="number" class="form-control" name="toleransi_telat" id="toleransi_telat"
                        value="{{ old('toleransi_telat', $setting->toleransi_telat ?? 15) }}" min="0" max="60"
                        required>
                    <div class="form-text">
                        Waktu toleransi setelah batas normal. Contoh: Jika batas normal 08:15 dan toleransi 15 menit,
                        maka presensi sampai 08:30 masih dianggap "Terlambat", lewat dari itu "Sangat Terlambat"
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <h6>Status Presensi:</h6>
                        <ul class="mb-0">
                            <li><span class="badge bg-success">Tepat Waktu</span>: Dalam rentang waktu normal</li>
                            <li><span class="badge bg-warning">Terlambat</span>: Lewat batas normal tapi masih dalam
                                toleransi</li>
                            <li><span class="badge bg-danger">Sangat Terlambat</span>: Melewati batas toleransi</li>
                        </ul>
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
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <strong>Sesi Pagi</strong>
                            </div>
                            <div class="card-body">
                                <p>Mulai: <strong>{{ $setting->pagi_mulai ?? 'Not set' }}</strong></p>
                                <p>Batas Normal: <strong>{{ $setting->pagi_selesai ?? 'Not set' }}</strong></p>
                                @php
                                    $batasToleransiPagi = null;
                                    try {
                                        $batasToleransiPagi = $setting->getBatasToleransiPagi();
                                    } catch (Exception $e) {
                                        $batasToleransiPagi = 'Error: ' . $e->getMessage();
                                    }
                                @endphp
                                <p>Batas Toleransi: <strong>{{ $batasToleransiPagi ?? 'Error parsing time' }}</strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <strong>Sesi Sore</strong>
                            </div>
                            <div class="card-body">
                                <p>Mulai: <strong>{{ $setting->sore_mulai ?? 'Not set' }}</strong></p>
                                <p>Batas Normal: <strong>{{ $setting->sore_selesai ?? 'Not set' }}</strong></p>
                                @php
                                    $batasToleransiSore = null;
                                    try {
                                        $batasToleransiSore = $setting->getBatasToleransiSore();
                                    } catch (Exception $e) {
                                        $batasToleransiSore = 'Error: ' . $e->getMessage();
                                    }
                                @endphp
                                <p>Batas Toleransi: <strong>{{ $batasToleransiSore ?? 'Error parsing time' }}</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
