@extends('layout.main')

@section('css')
    <style>
        .notification-item-history {
            border-bottom: 1px solid #f0f0f0;
        }

        .notification-item-history:last-child {
            border-bottom: none;
        }

        .notification-item-history .icon-circle {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #fff;
            font-size: 1.2rem;
        }
    </style>
@endsection

@section('content')
    {{-- Page Title --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Riwayat Notifikasi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Riwayat Notifikasi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if ($notifications->isEmpty())
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="far fa-bell-slash fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted">Tidak Ada Riwayat Notifikasi</h5>
                    <p>Semua notifikasi yang Anda terima akan muncul di sini.</p>
                </div>
            @else
                <div class="list-group list-group-flush">
                    @foreach ($notifications as $notification)
                        <a href="{{ $notification->data['url'] ?? '#' }}"
                            class="list-group-item list-group-item-action notification-item-history py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div
                                        class="icon-circle {{ is_null($notification->read_at) ? 'bg-primary' : 'bg-secondary' }}">
                                        <i class="{{ $notification->data['icon'] ?? 'far fa-bell' }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0 {{ is_null($notification->read_at) ? 'fw-bold' : '' }}">
                                        {{ $notification->data['message'] }}
                                    </p>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($notification->created_at)->translatedFormat('l, d F Y \p\u\k\u\l H:i') }}
                                    </small>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($notifications->hasPages())
            <div class="card-footer bg-light">
                {{-- Pagination Links --}}
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection
