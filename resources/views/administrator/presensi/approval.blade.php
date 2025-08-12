@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">✅ Approval Perubahan Status Presensi</h4>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">📋 Daftar Permintaan Perubahan Status</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="approvalTable">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Sekolah</th>
                            <th>Sesi</th>
                            <th>Status Awal</th>
                            <th>Status Diminta</th>
                            <th>Alasan</th>
                            <th>Bukti</th>
                            <th>Waktu Request</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingApprovals as $item)
                            <tr>
                                <td>{{ $item->tanggal_presensi->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $item->user->name }}</strong><br>
                                    <small class="text-muted">{{ $item->user->email }}</small>
                                </td>
                                <td>{{ $item->user->sekolah->nama ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->sesi === 'pagi' ? 'info' : 'warning' }}">
                                        {{ ucfirst($item->sesi) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-danger">Alpa</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $item->requested_status === 'Izin' ? 'info' : 'secondary' }}">
                                        {{ $item->requested_status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $item->keterangan }}">
                                        {{ $item->keterangan }}
                                    </div>
                                </td>
                                <td>
                                    @if ($item->bukti_foto)
                                        <button class="btn btn-sm btn-outline-primary"
                                            onclick="showImage('{{ asset('storage/' . $item->bukti_foto) }}')">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $item->updated_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-success"
                                            onclick="processApproval({{ $item->id }}, 'approve')" title="Setujui">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger"
                                            onclick="processApproval({{ $item->id }}, 'reject')" title="Tolak">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <button class="btn btn-sm btn-info"
                                            onclick="showApprovalModal({{ $item->id }}, '{{ $item->user->name }}', '{{ $item->requested_status }}')"
                                            title="Detail">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    Tidak ada permintaan approval saat ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- History Approval --}}
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">📚 History Approval (7 Hari Terakhir)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="historyTable">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Status Diminta</th>
                            <th>Keputusan</th>
                            <th>Catatan Admin</th>
                            <th>Diproses Oleh</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($approvalHistory as $item)
                            <tr>
                                <td>{{ $item->tanggal_presensi->format('d/m/Y') }}</td>
                                <td>{{ $item->user->name }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->requested_status === 'Izin' ? 'info' : 'secondary' }}">
                                        {{ $item->requested_status }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{ $item->approval_status === 'approved' ? 'success' : 'danger' }}">
                                        {{ $item->approval_status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                                    </span>
                                </td>
                                <td>{{ $item->approval_notes ?? '-' }}</td>
                                <td>{{ $item->approvedBy->name ?? '-' }}</td>
                                <td>
                                    <small>{{ $item->approved_at?->format('d/m/Y H:i') ?? '-' }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Belum ada history approval
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Approval Detail --}}
    <div class="modal fade" id="approvalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="approvalForm" method="POST">
                        @csrf
                        <input type="hidden" name="action" id="approvalAction">

                        <div class="mb-3">
                            <label class="form-label">Siswa</label>
                            <input type="text" id="studentName" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status yang Diminta</label>
                            <input type="text" id="requestedStatus" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan Admin (opsional)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Batal
                            </button>
                            <button type="button" class="btn btn-danger" onclick="submitApproval('reject')">
                                <i class="fas fa-times me-1"></i> Tolak
                            </button>
                            <button type="button" class="btn btn-success" onclick="submitApproval('approve')">
                                <i class="fas fa-check me-1"></i> Setujui
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal untuk melihat gambar --}}
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bukti Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" alt="Bukti foto">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        let currentPresensiId = null;

        // Initialize DataTables
        $(document).ready(function() {
            $('#approvalTable').DataTable({
                order: [
                    [8, 'desc']
                ], // Sort by request time
                pageLength: 10,
                responsive: true
            });

            $('#historyTable').DataTable({
                order: [
                    [6, 'desc']
                ], // Sort by approval time
                pageLength: 10,
                responsive: true
            });
        });

        // Quick approve/reject without modal
        function processApproval(presensiId, action) {
            if (!confirm(`Yakin ingin ${action === 'approve' ? 'menyetujui' : 'menolak'} permintaan ini?`)) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/presensi/approval/${presensiId}`;

            form.innerHTML = `
                @csrf
                <input type="hidden" name="action" value="${action}">
            `;

            document.body.appendChild(form);
            form.submit();
        }

        // Show approval modal with details
        function showApprovalModal(presensiId, studentName, requestedStatus) {
            currentPresensiId = presensiId;

            document.getElementById('studentName').value = studentName;
            document.getElementById('requestedStatus').value = requestedStatus;
            document.getElementById('approvalForm').action = `/presensi/approval/${presensiId}`;

            new bootstrap.Modal(document.getElementById('approvalModal')).show();
        }

        // Submit approval with notes
        function submitApproval(action) {
            document.getElementById('approvalAction').value = action;
            document.getElementById('approvalForm').submit();
        }

        // Show image in modal
        function showImage(src) {
            document.getElementById('modalImage').src = src;
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }
    </script>
@endsection
