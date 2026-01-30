<footer class="footer mt-auto py-3 bg-white border-top">
    <div class="container-fluid px-4">
        <div class="row align-items-center">
            
            <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                <span class="text-muted small">
                    &copy; <?= date('Y') ?> <strong>Estato</strong>. 
                    <span class="d-none d-sm-inline text-secondary">Smart CRM for Smart Developers.</span>
                </span>
            </div>

            <div class="col-md-6 text-center text-md-end">
                <div class="small text-muted">
                    <span class="me-3">Version 1.0.0 (SaaS Edition)</span>
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
    document.addEventListener('click', function(event) {
        var isClickInside = sidebar.contains(event.target);
        var isButton = event.target.closest('button[data-bs-toggle="offcanvas"]');
        
        // Jika layar kecil (mobile), sidebar terbuka, dan klik di luar sidebar
        if (window.innerWidth < 992 && sidebar.classList.contains('show') && !isClickInside && !isButton) {
            var bsOffcanvas = bootstrap.Offcanvas.getInstance(sidebar);
            bsOffcanvas.hide();
        }
    });

    // 3. Inisialisasi Tooltip Bootstrap (Opsional, biar hover title muncul bagus)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>

<style>
    /* Pastikan body minimal setinggi layar agar footer tidak naik */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    .content-wrapper {
        flex: 1; /* Konten utama akan mendorong footer ke bawah */
    }
    .footer {
        z-index: 1030; /* Di atas elemen biasa, di bawah sidebar/navbar */
    }
</style>