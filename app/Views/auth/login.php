<!DOCTYPE html>
<html lang="id">
<head>
    <?php 
    // Ambil data site identity dari session atau global variable
    // (Asumsi BaseController sudah me-load ini ke variable $site)
    // Fallback jika variable belum ada (agar tidak error)
    $siteName = $site['site_name'] ?? 'Estato App';
    $siteLogo = $site['site_logo'] ?? 'default.png';
    $companyName = $site['company_name'] ?? 'Smart CRM for Developers';
    $footerText = $site['footer_text'] ?? '© 2026 Estato App. All rights reserved.';
    
    // Cek apakah pakai Logo Custom
    $useCustomLogo = ($siteLogo !== 'default.png' && $siteLogo !== '');
    ?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= esc($siteName) ?></title>
    
    <link rel="icon" type="image/png" href="<?= base_url('assets/uploads/' . $siteLogo) ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --estato-blue: #0d6efd; /* Biru Utama */
            --estato-hover: #0b5ed7;
        }

        body { 
            background-color: #f4f6f9; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card { 
            width: 100%; 
            max-width: 400px; 
            padding: 35px; /* Sedikit diperlebar */
            border-radius: 15px; 
            background: white; 
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
        }

        .brand-title {
            color: var(--estato-blue);
            letter-spacing: 1px;
        }

        .form-control {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            background-color: #fff;
            border-color: var(--estato-blue);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .btn-estato { 
            background-color: var(--estato-blue); 
            border: none; 
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }

        .btn-estato:hover { 
            background-color: var(--estato-hover); 
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
            color: white;
        }
        
        /* Helper untuk logo gambar agar responsif di login box */
        .login-logo-img {
            max-width: 100%; 
            max-height: 80px; /* Batasi tinggi agar tidak merusak layout */
            object-fit: contain;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="text-center mb-4">
            
            <?php if ($useCustomLogo): ?>
                <img src="<?= base_url('assets/uploads/' . $siteLogo) ?>" 
                     alt="<?= esc($siteName) ?>" class="login-logo-img">
                
                <?php else: ?>
                <div class="mb-2">
                    <i class="bi bi-building-check text-primary" style="font-size: 3rem;"></i>
                </div>
                <h2 class="fw-bold brand-title mb-1"><?= strtoupper(esc($siteName)) ?></h2>
            <?php endif; ?>

            <p class="text-muted small mb-0"><?= esc($companyName) ?></p>
        </div>
        
        <?php if(session()->getFlashdata('error')):?>
            <div class="alert alert-danger py-2 small d-flex align-items-center">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <div><?= session()->getFlashdata('error') ?></div>
            </div>
        <?php endif;?>

        <form action="/auth/login" method="post">
            <div class="mb-3">
                <label for="username" class="form-label small fw-bold text-secondary">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" name="username" class="form-control border-start-0 ps-0" id="username" required autofocus placeholder="Masukkan username">
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label small fw-bold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-0" id="password" required placeholder="Masukkan password">
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-estato">
                    Masuk Aplikasi
                </button>
            </div>
        </form>

        <div class="text-center mt-4 text-muted">
            <small style="font-size: 11px;"><?= esc($footerText) ?></small>
        </div>
    </div>

</body>
</html>