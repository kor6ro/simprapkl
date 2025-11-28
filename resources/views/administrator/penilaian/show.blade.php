{{-- resources/views/administrator/penilaian/show.blade.php --}}
@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Detail Penilaian</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.penilaian.index') }}">Penilaian</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Detail Penilaian Siswa</h4>

            {{-- INFORMASI UMUM --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td style="width: 30%;"><strong>Nama Siswa</strong></td>
                            <td>: {{ $penilaian->siswa->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Penilai</strong></td>
                            <td>: {{ $penilaian->penilai->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Penilaian</strong></td>
                            <td>: {{ date('d F Y', strtotime($penilaian->tanggal_penilaian)) }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td style="width: 30%;"><strong>Mulai PKL</strong></td>
                            <td>:
                                {{ $penilaian->pkl_tanggal_mulai ? date('d F Y', strtotime($penilaian->pkl_tanggal_mulai)) : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Selesai PKL</strong></td>
                            <td>:
                                {{ $penilaian->pkl_tanggal_selesai ? date('d F Y', strtotime($penilaian->pkl_tanggal_selesai)) : '-' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <hr>
            <h5 class="mb-3">Rincian Skor Penilaian</h5>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th>Kriteria Penilaian</th>
                        <th class="text-center" style="width: 15%;">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penilaian->detailPenilaian as $detail)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $kriteria[$detail->variabel] ?? ucfirst(str_replace('_', ' ', $detail->variabel)) }}</td>
                            <td class="text-center">{{ $detail->nilai }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-weight-bold" style="font-weight: bold;">
                        <td colspan="2" class="text-end"><strong>Nilai Rata-Rata</strong></td>
                        <td class="text-center"><strong>{{ number_format($penilaian->nilai_rata_rata, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>

            <hr>
            <div class="mb-3">
                <h5>Komentar / Saran</h5>
                <p>{{ $penilaian->komentar_saran ?? '-' }}</p>
            </div>

            <div class="button-navigate mt-4">
                <a href="{{ route('admin.penilaian.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Batal
                </a>
                <a href="{{ route('admin.penilaian.edit', $penilaian->id) }}" class="btn btn-warning">
                    <i class="fa fa-edit me-1"></i> Edit Penilaian Ini
                </a>
            </div>
        </div>
    </div>
@endsection
