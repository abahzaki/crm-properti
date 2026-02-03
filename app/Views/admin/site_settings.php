<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-gear-wide-connected me-2"></i>Identitas Aplikasi (White Label)</h5>
    </div>
    <div class="card-body">
        
        <form action="/site-settings/update" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $site['id'] ?>">

            <div class="row">
                <div class="col-md-4 text-center">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Logo Saat Ini</label>
                        <div class="p-3 border rounded bg-light">
                            <img src="<?= base_url('assets/uploads/' . $site['site_logo']) ?>" 
                                 class="img-fluid" style="max-height: 100px;" 
                                 onerror="this.src='<?= base_url('assets/img/default.png') ?>'">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ganti Logo (PNG Transparan)</label>
                        <input type="file" name="site_logo" class="form-control" accept="image/png, image/jpeg">
                        <div class="form-text small">Disarankan ukuran 300x100 px.</div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Nama Aplikasi (Di Tab Browser)</label>
                        <input type="text" name="site_name" class="form-control fw-bold" value="<?= esc($site['site_name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Perusahaan (Footer & Laporan)</label>
                        <input type="text" name="company_name" class="form-control" value="<?= esc($site['company_name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Teks Footer</label>
                        <input type="text" name="footer_text" class="form-control" value="<?= esc($site['footer_text']) ?>">
                    </div>

                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-2"></i>Simpan Perubahan</button>
                </div>
            </div>
        </form>

    </div>
</div>

<?= $this->endSection() ?>