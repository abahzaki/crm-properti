<?php 
$uri = service('uri');
$current = $uri->getSegment(1); 

// Helper function untuk cek active state
function set_active($segment, $check) {
    return ($segment == $check) ? 'bg-primary text-white shadow-sm' : 'text-secondary hover-bg-light';
}

$role = session()->get('role');
$isAdminOwnerManager = in_array($role, ['admin', 'owner', 'manager']);
$canViewLogs = in_array($role, ['admin', 'manager', 'owner', 'spv']);

// Logika: Apakah Dropdown Chatbot AI harus terbuka?
// Dropdown terbuka jika URL sekarang adalah 'bot-settings' atau 'knowledge-base'
$isBotActive = in_array($current, ['bot-settings', 'knowledge-base']);
?>

<div class="offcanvas-lg offcanvas-start bg-white border-end shadow-sm sidebar-custom" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
    
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-bold text-primary">CRM PROPERTI</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-0">
        <div class="py-3 px-3">
            
            <small class="text-uppercase text-muted fw-bold ps-2" style="font-size: 11px;">Menu Utama</small>
            <ul class="nav flex-column gap-1 mt-2">
                <li class="nav-item">
                    <a class="nav-link rounded-2 d-flex align-items-center py-2 <?= set_active($current, 'dashboard') ?>" href="/dashboard">
                        <i class="bi bi-grid-1x2-fill me-3"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-2 d-flex align-items-center py-2 <?= set_active($current, 'leads') ?>" href="/leads">
                        <i class="bi bi-people-fill me-3"></i> Data Leads
                    </a>
                </li>
            </ul>

            <hr class="my-3 text-muted">
            <small class="text-uppercase text-muted fw-bold ps-2" style="font-size: 11px;">Management</small>
            <ul class="nav flex-column gap-1 mt-2">
                <li class="nav-item">
                    <a class="nav-link rounded-2 d-flex align-items-center py-2 <?= set_active($current, 'bookings') ?>" href="/bookings">
                        <i class="bi bi-clipboard-check-fill me-3"></i> Data Booking
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-2 d-flex align-items-center py-2 <?= set_active($current, 'units') ?>" href="/units">
                        <i class="bi bi-houses-fill me-3"></i> Stok Unit
                    </a>
                </li>
                
                <?php if($isAdminOwnerManager): ?>
                <li class="nav-item">
                    <a class="nav-link rounded-2 d-flex align-items-center py-2 <?= set_active($current, 'users') ?>" href="/users">
                        <i class="bi bi-person-badge-fill me-3"></i> User App
                    </a>
                </li>

                <li class="nav-item mt-1">
                    <a class="nav-link rounded-2 d-flex align-items-center justify-content-between py-2 <?= $isBotActive ? 'text-primary fw-bold bg-light' : 'text-secondary hover-bg-light' ?>" 
                       data-bs-toggle="collapse" 
                       href="#collapseBot" 
                       role="button" 
                       aria-expanded="<?= $isBotActive ? 'true' : 'false' ?>" 
                       aria-controls="collapseBot">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-robot me-3"></i> Chatbot AI
                        </div>
                        <i class="bi bi-chevron-down" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
                    </a>

                    <div class="collapse <?= $isBotActive ? 'show' : '' ?> mt-1 ps-3" id="collapseBot">
                        <ul class="nav flex-column gap-1 border-start border-2 ps-2" style="border-color: #dee2e6;">
                            <li class="nav-item">
                                <a class="nav-link rounded-2 py-1 <?= set_active($current, 'bot-settings') ?>" href="/bot-settings" style="font-size: 0.9rem;">
                                    Konfigurasi Bot
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link rounded-2 py-1 <?= set_active($current, 'knowledge-base') ?>" href="/knowledge-base" style="font-size: 0.9rem;">
                                    Knowledge Base
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>
            </ul>
            
            <?php if($canViewLogs): ?>
            <hr class="my-3 text-muted">
            <small class="text-uppercase text-muted fw-bold ps-2" style="font-size: 11px;">Monitoring & Laporan</small>
            <ul class="nav flex-column gap-1 mt-2">
                <?php if($isAdminOwnerManager): ?>
                <li class="nav-item">
                    <a class="nav-link rounded-2 d-flex align-items-center py-2 <?= set_active($current, 'ads-report') ?>" href="/ads-report">
                        <i class="bi bi-graph-up-arrow me-3"></i> Laporan Ads
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link rounded-2 d-flex align-items-center py-2 <?= set_active($current, 'activity-logs') ?>" href="/activity-logs">
                        <i class="bi bi-activity me-3"></i> Log Aktivitas
                    </a>
                </li>
            </ul>
            <?php endif; ?>

        </div>
    </div>
</div>

<style>
    /* STYLE UMUM */
    .hover-bg-light:hover { background-color: #f8f9fa; color: #000 !important; }
    
    /* Animasi Panah Dropdown saat dibuka */
    .nav-link[aria-expanded="true"] .bi-chevron-down {
        transform: rotate(180deg);
    }

    /* STYLE KHUSUS DESKTOP (Layar Besar > 992px) */
    @media (min-width: 992px) {
        .sidebar-custom {
            position: fixed;
            top: 60px;      /* Jarak dari Navbar */
            bottom: 0;
            width: 250px;
            z-index: 1000;  /* Di bawah Navbar, di atas konten */
            overflow-y: auto;
        }
    }

    /* STYLE KHUSUS MOBILE (Layar Kecil < 992px) */
    @media (max-width: 991.98px) {
        .sidebar-custom {
            width: 280px; 
        }
        .offcanvas {
            z-index: 1050 !important;
        }
    }
</style>