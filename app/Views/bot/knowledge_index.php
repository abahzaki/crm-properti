<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-book-half me-2"></i>Knowledge Base</h3>
    <a href="/knowledge-base/new" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Tambah Data
    </a>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th width="20%">Judul / Topik</th>
                        <th>Preview Isi (Context)</th>
                        <th width="10%">Status</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td class="fw-bold text-primary"><?= esc($item['title']) ?></td>
                        <td>
                            <small class="text-muted">
                                <?= substr(esc($item['content_text']), 0, 100) ?>...
                            </small>
                        </td>
                        <td>
                            <?php if($item['is_active']): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/knowledge-base/edit/<?= $item['id'] ?>" class="btn btn-sm btn-warning me-1">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a href="/knowledge-base/delete/<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus data ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($items)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data knowledge.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>