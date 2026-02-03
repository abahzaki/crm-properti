<?php 
// Fallback data agar tidak error jika variabel $site belum ter-load
$siteName   = $site['site_name'] ?? 'Estato App';
$companyName = $site['company_name'] ?? ''; 
$footerText = $site['footer_text'] ?? 'Smart CRM for Smart Developers.';
?>

<footer class="footer mt-auto py-3 bg-white border-top">
    <div class="container-fluid px-4">
        <div class="row align-items-center">
            
            <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                <span class="text-muted small">
                    &copy; <?= date('Y') ?> 
                    <strong><?= esc($siteName) ?></strong>. 
                    
                    <span class="d-none d-sm-inline text-secondary">
                        <?= esc($footerText) ?>
                    </span>
                </span>
            </div>

            <div class="col-md-6 text-center text-md-end">
                <div class="small text-muted">
                    <span class="me-3">Version 1.0.0</span>
                    
                    <span class="text-success fw-bold bg-success bg-opacity-10 px-2 py-1 rounded-pill" style="font-size: 11px;">
                        <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i> System Online
                    </span>
                </div>
            </div>

        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // 1. Logic Toggle Sidebar Mobile
    const sidebar = document.getElementById('sidebarMenu');
    
    // 2. Auto-Close Sidebar saat klik di luar (Mobile UX)
    if(sidebar) { // Cek if sidebar exists biar gak error di halaman login
        document.addEventListener('click', function(event) {
            var isClickInside = sidebar.contains(event.target);
            var isButton = event.target.closest('button[data-bs-toggle="offcanvas"]');
            
            // Jika layar kecil (mobile), sidebar terbuka, dan klik di luar sidebar
            if (window.innerWidth < 992 && sidebar.classList.contains('show') && !isClickInside && !isButton) {
                var bsOffcanvas = bootstrap.Offcanvas.getInstance(sidebar);
                if (bsOffcanvas) bsOffcanvas.hide();
            }
        });
    }

    // 3. Inisialisasi Tooltip Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>

<style>
    /* CSS FIX AGAR FOOTER SELALU DI BAWAH (STICKY FOOTER) */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    /* Pastikan elemen pembungkus konten utama (biasanya main atau div) punya class ini */
    .content-wrapper, main {
        flex: 1; /* Ini kuncinya: Konten utama mendorong footer ke bawah */
    }
    
    .footer {
        z-index: 1030; /* Di atas elemen biasa */
        position: relative;
    }
</style>