@extends('layout.main')

@section('css')
    <style>
        .btn-swap {
            float: right;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Setting Anggota Tim</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Setting Tugas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Tombol Swap --}}
    <div class="row mb-3">
        <div class="col-auto ms-auto">
            <form method="POST" action="{{ route('admin.setting_tugas.swapDivisi') }}">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">
                    🔄 Tukar Semua Anggota Team Hari Ini
                </button>
            </form>
        </div>
    </div>

    {{-- Tabel Divisi Siswa --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Tim Hari Ini</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($siswa as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.setting_tugas.setDivisi') }}"
                                    class="auto-submit-form">
                                    @csrf
                                    <input type="hidden" name="siswa_id" value="{{ $user->id }}">
                                    <select name="divisi" class="form-select form-select-sm" onchange="this.form.submit()"
                                        style="max-width: 200px; margin: auto;">
                                        <option value="teknisi"
                                            {{ $user->divisiHarianToday?->divisi === 'teknisi' ? 'selected' : '' }}>Teknisi
                                        </option>
                                        <option value="sales"
                                            {{ $user->divisiHarianToday?->divisi === 'sales' ? 'selected' : '' }}>Sales
                                        </option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Setting Divisi</h4>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('js')
    <script>
        document.querySelectorAll('.auto-submit-form').forEach(form => {
            form.addEventListener('submit', function() {
                const select = form.querySelector('select');
                select.disabled = true;
                select.style.opacity = 0.6;
            });
        });
    </script>
@endsection
