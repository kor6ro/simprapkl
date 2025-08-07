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

                {{-- Admin only: Generate Alpa Button --}}
                @if (auth()->user()->group_id === 2)
                    <form method="POST" action="{{ route('presensi.generate.alpa') }}">
                        @csrf
                        <button class="btn btn-danger" onclick="return confirm('Generate siswa alpa untuk hari ini?')">
                            Generate Siswa Alpa Hari Ini
                        </button>
                    </form>
                @endif
            </div>

            {{-- Alert Messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Presensi Forms --}}
            <div class="card p-3 mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <form method="POST" action="{{ route('presensi.checkin') }}" enctype="multipart/form-data"
                            class="presensi-form">
                            @csrf
                            <label class="form-label">Bukti Foto Absen Masuk:</label>
                            <input type="file" name="bukti_foto" accept="image/*" capture="environment" required
                                class="form-control mb-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt me-1"></i> Absen Masuk
                            </button>
                        </form>
                    </div>

                    <div class="col-md-4">
                        <form method="POST" action="{{ route('presensi.checkout') }}" enctype="multipart/form-data"
                            class="presensi-form">
                            @csrf
                            <label class="form-label">Bukti Foto Absen Keluar:</label>
                            <input type="file" name="bukti_foto" accept="image/*" capture="environment" required
                                class="form-control mb-2">
                            <button type="submit" class="btn btn-secondary w-100">
                                <i class="fas fa-sign-out-alt me-1"></i> Absen Pulang
                            </button>
                        </form>
                    </div>

                    <div class="col-md-4">
                        <form method="POST" action="{{ route('presensi.sakit') }}" enctype="multipart/form-data"
                            class="presensi-form">
                            @csrf
                            <label class="form-label">Bukti Foto Surat Izin/Sakit:</label>
                            <input type="file" name="bukti_foto" accept="image/*" capture="environment" required
                                class="form-control mb-2">
                            <textarea name="keterangan" class="form-control mb-2" placeholder="Keterangan (minimal 10 karakter)" required
                                minlength="10"></textarea>
                            <select name="jenis" class="form-control mb-2" required>
                                <option value="">Pilih Jenis</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Izin">Izin</option>
                            </select>
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-file-medical me-1"></i> Ajukan Izin/Sakit
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Data Table --}}
            <div class="card">
                <div class="card-body">
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
                                    <td>{{ $item->user->sekolah->nama ?? '-' }}</td>
                                    <td>{{ $item->tanggal }}</td>
                                    <td>{{ $item->jam_masuk ?? '-' }}</td>
                                    <td>{{ $item->jam_keluar ?? '-' }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $item->status === 'Tepat Waktu' ? 'success' : ($item->status === 'Terlambat' ? 'warning' : 'secondary') }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td>{{ $item->keterangan ?? '-' }}</td>
                                    <td>
                                        @if ($item->bukti_foto)
                                            <a href="{{ asset('storage/' . $item->bukti_foto) }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#presensiTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                order: [
                    [2, 'desc']
                ] // Sort by tanggal descending
            });
        });
    </script>
@endpush
