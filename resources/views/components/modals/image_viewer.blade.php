@props(['id' => 'imageViewerModal'])

{{-- Modal untuk melihat gambar bukti --}}
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">Bukti Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                {{-- UBAH: Menggunakan class 'viewer-image' dan hapus id dinamis --}}
                <img class="viewer-image img-fluid" src="" alt="Bukti foto">
            </div>
        </div>
    </div>
</div>
