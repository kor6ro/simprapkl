@extends('layout.main')

@section('content')
    <h4>Tugas Harian (Divisi: {{ ucfirst($divisi ?? '-') }})</h4>

    @if ($tugasHariIni && $tugasHariIni->mulai)
        <p><strong>Tugas dimulai:</strong> {{ $tugasHariIni->mulai->format('H:i') }}</p>
    @else
        <form method="POST" action="{{ route('tugas_harian.mulai') }}">
            @csrf
            <button class="btn btn-primary">Mulai Tugas</button>
        </form>
    @endif

    <hr>

    <h5>Template Tugas:</h5>
    <ul>
        @forelse($template as $item)
            <li>{{ $item->deskripsi }}</li>
        @empty
            <li>Tidak ada template untuk hari ini.</li>
        @endforelse
    </ul>

    @if ($tugasHariIni && $tugasHariIni->mulai)
        <form method="POST" action="{{ route('tugas_harian.lapor') }}">
            @csrf
            <div class="form-group">
                <label>Laporan:</label>
                <textarea name="laporan" class="form-control" required></textarea>
            </div>
            <button class="btn btn-success mt-2">Kirim Laporan</button>
        </form>
    @endif

    @if ($tugasHariIni && $tugasHariIni->selesai)
        <p><strong>Tugas selesai:</strong> {{ $tugasHariIni->selesai->format('H:i') }}</p>
        <p><strong>Durasi:</strong>
            {{ $tugasHariIni->mulai->diffInMinutes($tugasHariIni->selesai) }} menit
        </p>
        <p><strong>Laporan:</strong> {{ $tugasHariIni->laporan }}</p>
    @endif
@endsection
