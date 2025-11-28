@extends('layout.main')

@section('content')
    {{-- ... (kode header halaman tidak berubah) ... --}}
    <div class="card card-primary">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">Tambah Presensi</h4>
            <form action="{{ route('presensi.store') }}" method="post" enctype="multipart/form-data">
                @csrf

                {{-- Memanggil form partial yang sudah kita buat --}}
                @include('administrator.presensi.partials.form')

                {{-- Tombol navigasi --}}
                <div class="button-navigate mt-3">
                    <a href="{{ route('presensi.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
