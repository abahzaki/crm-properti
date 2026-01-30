<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom fixed-top shadow-sm" style="height: 60px; z-index: 1040;">
    <div class="container-fluid">
        
        <button class="btn btn-light d-lg-none me-2 border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
            <i class="bi bi-list fs-4"></i>
        </button>

        <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="/dashboard" style="letter-spacing: 0.5px;">
            <i class="bi bi-building-check fs-4 me-2"></i>
            <span style="font-weight: 800; font-family: sans-serif;">ESTATO</span>
        </a>

        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav align-items-center">
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-dark fw-medium d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        
                        <div class="d-none d-md-block text-start lh-1">
                            <div class="fw-bold" style="font-size: 13px;"><?= esc(session()->get('full_name')) ?></div>
                            <span class="badge bg-primary rounded-pill text-uppercase mt-1" style="font-size: 9px; letter-spacing: 0.5px;">
                                <?= session()->get('role') ?>
                            </span>
                        </div>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow mt-3 p-2 animate slideIn" style="min-width: 200px;">
                        <li><h6 class="dropdown-header text-uppercase small text-muted fw-bold">Akun Saya</h6></li>
                        
                        <li>
                            <a class="dropdown-item rounded-2 py-2" href="/bot-settings">
                                <i class="bi bi-robot me-2 text-primary"></i> Konfigurasi Bot
                            </a>
                        </li>
                        
                        <li><hr class="dropdown-divider my-2"></li>
                        
                        <li>
                            <a class="dropdown-item rounded-2 py-2 text-danger fw-bold" href="/logout">
                                <i class="bi bi-box-arrow-right me-2"></i> Keluar
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    .dropdown-item:hover {
        background-color: #f0f2f5;
        color: #0d6efd;
    }
    @media (min-width: 992px) {
        .animate {
            animation-duration: 0.3s;
            -webkit-animation-duration: 0.3s;
            animation-fill-mode: both;
            -webkit-animation-fill-mode: both;
        }
    }
    @keyframes slideIn {
        0% { transform: translateY(1rem); opacity: 0; }
        100% { transform: translateY(0rem); opacity: 1; }
    }
</style>