<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><?= $title ?></h3>
    <a href="/knowledge-base" class="btn btn-secondary">Kembali</a>
</div>

<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="card shadow-sm col-lg-10 mx-auto">
    <div class="card-body p-4">
        
        <?php $isEdit = isset($item); ?>
        
        <form action="<?= $isEdit ? '/knowledge-base/update/'.$item['id'] : '/knowledge-base/create' ?>" method="post" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label class="form-label fw-bold">Judul / Topik</label>
                <input type="text" name="title" class="form-control" value="<?= $isEdit ? esc($item['title']) : '' ?>" required>
            </div>

            <?php if(!$isEdit): ?>
            <div class="mb-3 p-3 bg-light border rounded">
                <label class="form-label fw-bold text-danger"><i class="bi bi-file-earmark-pdf me-2"></i>Upload Sumber PDF (Opsional)</label>
                <input type="file" name="pdf_file" class="form-control" accept="application/pdf">
                <div class="form-text">
                    Upload brosur/pricelist PDF. Sistem akan otomatis menyalin teks di dalamnya menjadi hafalan bot.
                </div>
            </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label fw-bold">Isi Pengetahuan (Teks)</label>
                <textarea name="content_text" class="form-control font-monospace" rows="15" placeholder="Tulis manual atau hasil ekstrak PDF akan muncul disini..."><?= $isEdit ? esc($item['content_text']) : '' ?></textarea>
            </div>

            <?php if($isEdit): ?>
            <div class="mb-3">
                <select name="is_active" class="form-select">
                    <option value="1" <?= $item['is_active'] ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= !$item['is_active'] ? 'selected' : '' ?>>Non-Aktif</option>
                </select>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary btn-lg w-100">Simpan Data</button>

        </form>
    </div>
</div>

<?= $this->endSection() ?>