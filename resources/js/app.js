import './bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    const loaderOverlay = document.getElementById('loaderOverlay');

    // Fungsi untuk menampilkan loader
    const showLoader = () => {
        if (loaderOverlay) {
            loaderOverlay.style.display = 'flex';
        }
    };

    // 1. Tampilkan loader saat form di-submit di seluruh aplikasi
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            // Jangan tampilkan loader jika form membuka tab baru
            if (form.getAttribute('target') !== '_blank') {
                showLoader();
            }
        });
    });

    // 2. Tampilkan loader saat link di-klik di seluruh aplikasi
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function(event) {
            const href = link.getAttribute('href');
            
            // Kondisi untuk MENGABAIKAN loader
            if (link.getAttribute('target') === '_blank' || 
                !href || href.startsWith('#') || href.startsWith('javascript:') ||
                link.hasAttribute('download') ||
                event.ctrlKey || event.metaKey) 
            {
                return; // Jangan tampilkan loader
            }
            
            showLoader();
        });
    });
});

