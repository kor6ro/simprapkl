@extends('layout.main')

@section('css')
<style>
    .table-team {
        background: #fff;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #ddd;
    }
    .table-team th, 
    .table-team td {
        border: 1px solid #ddd !important;
        padding: 8px 12px;
        vertical-align: top;
    }
    .section-title {
        font-weight: bold;
        margin-top: 20px;
        margin-bottom: 5px;
        font-size: 1.1rem;
    }
    .empty-slot {
        color: #999;
        font-style: italic;
    }
</style>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col">
        <h4 class="fw-bold">Setting Anggota Tim</h4>
    </div>
    <div class="col-auto">
        <form method="POST" action="{{ route('admin.setting_tugas.swapDivisi') }}">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm">
                🔄 Tukar Semua Anggota Team Hari Ini
            </button>
        </form>
    </div>
</div>

{{-- SALES --}}
<div class="section-title">TIM SALES</div>
<table class="table table-bordered table-team">
    <thead>
        <tr>
            <th style="width: 40%">Ketua Tim (Admin)</th>
            <th style="width: 60%">Anggota Tim (Siswa)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($adminKetua as $ketua)
        <tr>
            <td>{{ $loop->iteration }}. {{ $ketua->name }}</td>
            <td>
                @php $i = 1; @endphp
                @foreach($anggotaSiswa as $anggota)
                    {{ $i++ }}. {{ $anggota->name }}<br>
                @endforeach
                @if($i === 1)
                    <span class="empty-slot">Belum ada anggota</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- TEKNISI --}}
<div class="section-title">TIM TEKNISI</div>
<table class="table table-bordered table-team">
    <thead>
        <tr>
            <th style="width: 40%">Ketua Tim (Admin)</th>
            <th style="width: 60%">Anggota Tim (Siswa)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($adminKetua as $ketua)
        <tr>
            <td>{{ $loop->iteration }}. {{ $ketua->name }}</td>
            <td>
                @php $i = 1; @endphp
                @foreach($anggotaSiswa as $anggota)
                    {{ $i++ }}. {{ $anggota->name }}<br>
                @endforeach
                @if($i === 1)
                    <span class="empty-slot">Belum ada anggota</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
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