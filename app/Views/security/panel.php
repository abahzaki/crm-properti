<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Checkpoint</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { 
            background-color: #f4f6f9; /* Abu-abu terang (AdminLTE style) */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .security-card {
            border: none;
            border-top: 5px solid #dc3545; /* Garis Merah di atas menandakan area sensitif */
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            background-color: #fff;
        }
        .btn-patch {
            background-color: #dc3545;
            border: none;
        }
        .btn-patch:hover {
            background-color: #bb2d3b;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center" style="height: 100vh;">

    <div class="card security-card p-4 rounded-3" style="width: 400px;">
        
        <div class="text-center mb-4 mt-2">
            <div class="mb-3 text-danger">
                <i class="bi bi-shield-lock-fill" style="font-size: 3rem;"></i>
            </div>
            <h5 class="fw-bold text-dark">SYSTEM MAINTENANCE</h5>
            <p class="text-muted small mb-0">Authorized Developer Access Only</p>
        </div>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger d-flex align-items-center small py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?= session()->getFlashdata('error') ?></div>
            </div>
        <?php endif; ?>
        
        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success d-flex align-items-center small py-2" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div><?= session()->getFlashdata('success') ?></div>
            </div>
        <?php endif; ?>

        <form action="/system-security/patch" method="post">
            <div class="mb-3">
                <label class="form-label fw-bold small text-secondary">SECURITY KEY</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                    <input type="password" name="access_token" class="form-control" placeholder="Masukkan Password Developer" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-bold small text-secondary">INJECT TOKEN VALUE</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-coin"></i></span>
                    <input type="number" name="patch_value" class="form-control" placeholder="Contoh: 5000" required>
                </div>
                <div class="form-text small text-end mt-1">
                    *Token akan ditambahkan ke saldo aktif.
                </div>
            </div>

            <button type="submit" class="btn btn-patch w-100 text-white fw-bold py-2 shadow-sm">
                <i class="bi bi-lightning-fill me-1"></i> EXECUTE PATCH
            </button>
        </form>
        
        <div class="text-center mt-4">
            <a href="/" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

</body>
</html>