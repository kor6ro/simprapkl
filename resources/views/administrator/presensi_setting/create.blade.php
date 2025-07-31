@extends('layout.main')
@section('css')
    <style>
        .button-navigate {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Setting Presensi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Edit Setting Presensi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Edit Setting Presensi</h4>
            <form action="{{ route('presensi_setting.update', $presensiSetting->id) }}" method="post">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-primary mb-3">Sesi Pagi</h5>
                        <div class="row">
                            <div class="col-xl-6 mb-3">
                                <label for="pagi_mulai" class="form-label">Jam Mulai Pagi</label>
                                <input class="form-control" type="time" name="pagi_mulai" id="pagi_mulai"
                                    value="{{ old('pagi_mulai', $presensiSetting->pagi_mulai) }}" required>
                                @error('pagi_mulai')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-xl-6 mb-3">
                                <label for="pagi_selesai" class="form-label">Jam Selesai Pagi</label>
                                <input class="form-control" type="time" name="pagi_selesai" id="pagi_selesai"
                                    value="{{ old('pagi_selesai', $presensiSetting->pagi_selesai) }}" required>
                                @error('pagi_selesai')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="text-primary mb-3">Sesi Sore</h5>
                        <div class="row">
                            <div class="col-xl-6 mb-3">
                                <label for="sore_mulai" class="form-label">Jam Mulai Sore</label>
                                <input class="form-control" type="time" name="sore_mulai" id="sore_mulai"
                                    value="{{ old('sore_mulai', $presensiSetting->sore_mulai) }}" required>
                                @error('sore_mulai')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-xl-6 mb-3">
                                <label for="sore_selesai" class="form-label">Jam Selesai Sore</label>
                                <input class="form-control" type="time" name="sore_selesai" id="sore_selesai"
                                    value="{{ old('sore_selesai', $presensiSetting->sore_selesai) }}" required>
                                @error('sore_selesai')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <label class="form-label d-block">Status Aktif</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                {{ $presensiSetting->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aktifkan Setting Ini</label>
                        </div>
                        <small class="text-muted">*Hanya satu setting yang dapat aktif pada satu waktu</small>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <a href="{{ route('presensi_setting.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        // Add client-side validation for time ranges
        $(document).ready(function() {
            // Validate pagi time range
            $('#pagi_selesai').on('change', function() {
                var pagiMulai = $('#pagi_mulai').val();
                var pagiSelesai = $(this).val();

                if (pagiMulai && pagiSelesai && pagiMulai >= pagiSelesai) {
                    alert('Jam selesai pagi harus lebih besar dari jam mulai pagi');
                    $(this).val('');
                }
            });

            // Validate sore time range
            $('#sore_selesai').on('change', function() {
                var soreMulai = $('#sore_mulai').val();
                var soreSelesai = $(this).val();

                if (soreMulai && soreSelesai && soreMulai >= soreSelesai) {
                    alert('Jam selesai sore harus lebih besar dari jam mulai sore');
                    $(this).val('');
                }
            });

            // Validate sore must be after pagi
            $('#sore_mulai').on('change', function() {
                var pagiSelesai = $('#pagi_selesai').val();
                var soreMulai = $(this).val();

                if (pagiSelesai && soreMulai && soreMulai <= pagiSelesai) {
                    alert('Jam mulai sore harus lebih besar dari jam selesai pagi');
                    $(this).val('');
                }
            });
        });
    </script>
@endsection
