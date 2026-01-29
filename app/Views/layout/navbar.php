<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom fixed-top" style="height: 60px; z-index: 1040;">
    <div class="container-fluid">
        <button class="btn btn-light d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
            <i class="bi bi-list fs-4"></i>
        </button>

        <span class="navbar-brand fw-bold text-primary" style="letter-spacing: -0.5px;">
            <i class="bi bi-building-fill me-2"></i>CRM PROPERTI
        </span>

        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-dark fw-medium" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1 text-secondary"></i> 
                        <?= session()->get('full_name') ?> 
                        <span class="badge bg-label-primary text-uppercase ms-1" style="font-size: 10px;"><?= session()->get('role') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2">
                        <li><a class="dropdown-item py-2" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="/logout"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>