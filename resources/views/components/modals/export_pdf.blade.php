@props(['id' => 'exportPdfModal'])

<div class="modal fade" id="{{ $id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kustomisasi Laporan PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="export-pdf-form-{{ $id }}">
                <div class="modal-body">
                    <p>Laporan akan dibuat berdasarkan filter yang sedang aktif di halaman ini. Silakan pilih format
                        laporan yang Anda inginkan.</p>

                    <div class="mb-3">
                        <label for="report_type-{{ $id }}" class="form-label"><strong>1. Pilih Jenis
                                Laporan</strong></label>
                        <select id="report_type-{{ $id }}" name="report_type" class="form-select">
                            <option value="detail">Laporan Detail (Per entri presensi)</option>
                            <option value="rekap">Laporan Rekap (Ringkasan per siswa)</option>
                        </select>
                    </div>

                    <div id="export-options-container-{{ $id }}">
                        <label class="form-label"><strong>2. Pilih Kolom (Untuk Laporan Detail)</strong></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                        value="sekolah" id="col_sekolah-{{ $id }}" checked><label
                                        class="form-check-label" for="col_sekolah-{{ $id }}">Sekolah</label>
                                </div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                        value="tanggal" id="col_tanggal-{{ $id }}" checked><label
                                        class="form-check-label" for="col_tanggal-{{ $id }}">Tanggal</label>
                                </div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                        value="sesi" id="col_sesi-{{ $id }}" checked><label
                                        class="form-check-label" for="col_sesi-{{ $id }}">Sesi</label></div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                        value="jam_presensi" id="col_jam-{{ $id }}" checked><label
                                        class="form-check-label" for="col_jam-{{ $id }}">Jam Presensi</label>
                                </div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                        value="status" id="col_status-{{ $id }}" checked><label
                                        class="form-check-label" for="col_status-{{ $id }}">Status</label>
                                </div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                        value="approval_status" id="col_approval-{{ $id }}" checked><label
                                        class="form-check-label" for="col_approval-{{ $id }}">Status
                                        Approval</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]"
                                        value="keterangan" id="col_keterangan-{{ $id }}" checked><label
                                        class="form-check-label"
                                        for="col_keterangan-{{ $id }}">Keterangan</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">> Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-download me-1"></i> Generate &
                        Download</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalElement = document.getElementById('{{ $id }}');
            if (!modalElement) return;

            const reportTypeSelect = modalElement.querySelector('#report_type-{{ $id }}');
            const optionsContainer = modalElement.querySelector('#export-options-container-{{ $id }}');
            const exportForm = modalElement.querySelector('#export-pdf-form-{{ $id }}');

            function toggleOptions() {
                optionsContainer.style.display = reportTypeSelect.value === 'rekap' ? 'none' : 'block';
            }

            reportTypeSelect.addEventListener('change', toggleOptions);

            exportForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const bulan = document.getElementById('filter-bulan')?.value ?? '';
                const sekolah = document.getElementById('filter-sekolah')?.value ?? '';
                const approval = document.getElementById('filter-approval')?.value ?? '';
                const search = document.getElementById('custom-search')?.value ?? '';
                const url = new URL('{{ route('presensi.export.pdf') }}');
                url.search = new URLSearchParams(new FormData(exportForm)).toString();
                url.searchParams.append('filter_bulan', bulan);
                url.searchParams.append('filter_sekolah', sekolah);
                url.searchParams.append('filter_approval', approval);
                url.searchParams.append('search[value]', search);
                window.open(url.toString(), '_blank');
                bootstrap.Modal.getInstance(modalElement)?.hide();
            });

            // Set initial state when modal is shown
            modalElement.addEventListener('shown.bs.modal', toggleOptions);
        });
    </script>
@endpush
