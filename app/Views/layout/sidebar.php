<?php 
$uri = service('uri');
$current = $uri->getSegment(1); 

// Helper function: Style Active yang lebih Modern (Soft UI)
function set_active($segment, $check) {
    // Jika aktif: Background biru muda lembut + Teks Biru Tebal + Border Kanan
    if ($segment == $check) {
        return 'active-nav-item';
    }
    // Jika tidak aktif: Abu-abu
    return 'text-secondary hover-nav-item';
}

$role = session()->get('role');
$isAdminOwnerManager = in_array($role, ['admin', 'owner', 'manager']);
$canViewLogs = in_array($role, ['admin', 'manager', 'owner', 'spv']);

// Logic Dropdown
$isBotActive = in_array($current, ['bot-settings', 'knowledge-base']);
?>

<div class="offcanvas-lg offcanvas-start bg-white border-end shadow-sm sidebar-custom" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
    
    <div class="offcanvas-header bg-light border-bottom">
        <h5 class="offcanvas-title fw-bold text-primary d-flex align-items-center">
            <i class="bi bi-building-check me-2"></i> ESTATO
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-0 scrollbar-custom">
        <div class="py-4 px-3">
            
            <small class="text-uppercase text-muted fw-bold ps-3 mb-2 d-block" style="font-size: 10px; letter-spacing: 1px;">Menu Utama</small>
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link rounded-3 d-flex align-items-center py-2 px-3 <?= set_active($current, 'dashboard') ?>" href="/dashboard">
                        <i class="bi bi-grid-1x2-fill me-3"></i> 
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-3 d-flex align-items-center py-2 px-3 <?= set_active($current, 'leads') ?>" href="/leads">
                        <i class="bi bi-people-fill me-3"></i> 
                        <span>Data Leads</span>
                    </a>
                </li>
            </ul>

            <hr class="my-3 border-light">
            <small class="text-uppercase text-muted fw-bold ps-3 mb-2 d-block" style="font-size: 10px; letter-spacing: 1px;">Management</small>
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link rounded-3 d-flex align-items-center py-2 px-3 <?= set_active($current, 'bookings') ?>" href="/bookings">
                        <i class="bi bi-clipboard-check-fill me-3"></i> 
                        <span>Data Booking</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-3 d-flex align-items-center py-2 px-3 <?= set_active($current, 'units') ?>" href="/units">
                        <i class="bi bi-houses-fill me-3"></i> 
                        <span>Stok Unit</span>
                    </a>
                </li>
                
                <?php if($isAdminOwnerManager): ?>
                <li class="nav-item">
                    <a class="nav-link rounded-3 d-flex align-items-center py-2 px-3 <?= set_active($current, 'users') ?>" href="/users">
                        <i class="bi bi-person-badge-fill me-3"></i> 
                        <span>User App</span>
                    </a>
                </li>

                <li class="nav-item mt-1">
                    <a class="nav-link rounded-3 d-flex align-items-center justify-content-between py-2 px-3 <?= $isBotActive ? 'text-primary fw-bold bg-primary bg-opacity-10' : 'text-secondary hover-nav-item' ?>" 
                       data-bs-toggle="collapse" 
                       href="#collapseBot" 
                       role="button" 
                       aria-expanded="<?= $isBotActive ? 'true' : 'false' ?>" 
                       aria-controls="collapseBot">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-robot me-3"></i> <span>Chatbot AI</span>
                        </div>
                        <i class="bi bi-chevron-down" style="font-size: 0.75rem; transition: transform 0.3s ease;"></i>
                    </a>

                    <div class="collapse <?= $isBotActive ? 'show' : '' ?>" id="collapseBot">
                        <ul class="nav flex-column mt-1 ms-3 ps-2 border-start border-2" style="border-color: #e9ecef !important;">
                            <li class="nav-item">
                                <a class="nav-link py-1 ps-3 <?= set_active($current, 'bot-settings') ?>" href="/bot-settings" style="font-size: 0.9rem;">
                                    Konfigurasi Bot
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link py-1 ps-3 <?= set_active($current, 'knowledge-base') ?>" href="/knowledge-base" style="font-size: 0.9rem;">
                                    Knowledge Base
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>
            </ul>
            
            <?php if($canViewLogs): ?>
            <hr class="my-3 border-light">
            <small class="text-uppercase text-muted fw-bold ps-3 mb-2 d-block" style="font-size: 10px; letter-spacing: 1px;">Laporan</small>
            <ul class="nav flex-column gap-1">
                <?php if($isAdminOwnerManager): ?>
                <li class="nav-item">
                    <a class="nav-link rounded-3 d-flex align-items-center py-2 px-3 <?= set_active($current, 'ads-report') ?>" href="/ads-report">
                        <i class="bi bi-graph-up-arrow me-3"></i> 
                        <span>Laporan Ads</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link rounded-3 d-flex align-items-center py-2 px-3 <?= set_active($current, 'activity-logs') ?>" href="/activity-logs">
                        <i class="bi bi-activity me-3"></i> 
                        <span>Log Aktivitas</span>
                    </a>
                </li>
            </ul>
            <?php endif; ?>

        </div>
    </div>
</div>

<style>
    /* 1. STATE ACTIVE: Soft Blue Background + Bold Text */
    .active-nav-item {
        background-color: rgba(13, 110, 253, 0.08); /* Biru sangat muda transparan */
        color: #0d6efd !important; /* Biru Utama */
        font-weight: 600;
        position: relative;
    }
    
    /* Aksen garis kecil di kiri untuk menu aktif */
    .active-nav-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 10%;
        bottom: 10%;
        width: 4px;
        background-color: #0d6efd;
        border-top-right-radius: 4px;
        border-bottom-right-radius: 4px;
    }

    /* 2. HOVER EFFECT: Slide sedikit ke kanan */
    .hover-nav-item {
        transition: all 0.2s ease;
    }
    .hover-nav-item:hover {
        background-color: #f8f9fa;
        color: #212529 !important;
        transform: translateX(4px); /* Efek gerak */
    }

    /* 3. DROPDOWN ARROW ANIMATION */
    .nav-link[aria-expanded="true"] .bi-chevron-down {
        transform: rotate(180deg);
    }

    /* 4. SCROLLBAR CANTIK (Webkit only) */
    .scrollbar-custom::-webkit-scrollbar {
        width: 5px;
    }
    .scrollbar-custom::-webkit-scrollbar-track {
        background: transparent;
    }
    .scrollbar-custom::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 10px;
    }
    .scrollbar-custom::-webkit-scrollbar-thumb:hover {
        background: #adb5bd;
    }
    
    /* 5. RESPONSIVE SIZING */
    @media (min-width: 992px) {
        .sidebar-custom {
            position: fixed;
            top: 60px;
            bottom: 0;
            width: 260px; /* Sedikit lebih lebar biar lega */
            z-index: 1000;
            overflow-y: auto;
            border-right: 1px solid rgba(0,0,0,0.05) !important;
        }
    }

    @media (max-width: 991.98px) {
        .sidebar-custom {
            width: 280px; 
        }
        .offcanvas {
            z-index: 1050 !important;
        }
    }
</style>