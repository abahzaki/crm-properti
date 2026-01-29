<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><?= $title ?></h3>
    <a href="/knowledge-base" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card shadow-sm col-lg-10 mx-auto">
    <div class="card-body p-4">
        
        <?php $isEdit = isset($item); ?>
        <form action="<?= $isEdit ? '/knowledge-base/update/'.$item['id'] : '/knowledge-base/create' ?>" method="post">
            
            <div class="mb-3">
                <label class="form-label fw-bold">Judul / Kategori</label>
                <input type="text" name="title" class="form-control" placeholder="Misal: Info Harga Villa, Lokasi, atau Promo" value="<?= $isEdit ? esc($item['title']) : '' ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Isi Pengetahuan (Context Text)</label>
                <div class="alert alert-info py-2" style="font-size: 13px;">
                    <i class="bi bi-info-circle me-1"></i> <strong>Tips Prompt:</strong> Anda bisa menggunakan format Q&A seperti contoh Cekat AI: <br>
                    <code>Jika ada user tanya: "Harga berapa?" Maka jawab: "Harga mulai 500jt".</code>
                </div>
                
                <textarea name="content_text" class="form-control font-monospace" rows="15" placeholder="Tulis data pengetahuan bot disini..." required style="font-size: 13px;"><?= $isEdit ? esc($item['content_text']) : '' ?></textarea>
            </div>

            <?php if($isEdit): ?>
            <div class="mb-4">
                <label class="form-label fw-bold">Status Data</label>
                <select name="is_active" class="form-select">
                    <option value="1" <?= $item['is_active'] ? 'selected' : '' ?>>Aktif (Digunakan Bot)</option>
                    <option value="0" <?= !$item['is_active'] ? 'selected' : '' ?>>Non-Aktif (Disimpan Saja)</option>
                </select>
            </div>
            <?php endif; ?>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save me-2"></i>Simpan Data
                </button>
            </div>

        </form>
    </div>
</div>

<?= $this->endSection() ?>