<?= $this->extend('layout/main'); ?>

<?= $this->section('content'); ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>📊 Log Aktivitas Sistem & Sales</h3>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-striped mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="15%">Waktu</th>
                            <th width="20%">User (Pelaku)</th>
                            <th width="10%">Tipe</th>
                            <th width="15%">Aksi</th>
                            <th width="30%">Detail & Target</th>
                            <th width="10%">Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($logs)): ?>
                            <tr><td colspan="6" class="text-center py-4">Belum ada aktivitas tercatat.</td></tr>
                        <?php else: ?>
                            <?php foreach($logs as $log): ?>
                            <tr>
                                <td>
                                    <small class="fw-bold"><?= date('d/m/y', strtotime($log['created_at'])) ?></small><br>
                                    <small class="text-muted"><?= date('H:i', strtotime($log['created_at'])) ?> WIB</small>
                                </td>

                                <td>
                                    <span class="fw-bold"><?= $log['user_name'] ?></span><br>
                                    <small class="text-muted text-uppercase" style="font-size: 10px;"><?= $log['user_role'] ?></small>
                                </td>

                                <td>
                                    <?php if($log['log_type'] == 'sales'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">Sales Activity</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">System Log</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php
                                        $icon = 'bi-circle';
                                        if($log['action'] == 'login') $icon = 'bi-box-arrow-in-right';
                                        if($log['action'] == 'WhatsApp') $icon = 'bi-whatsapp text-success';
                                        if($log['action'] == 'Call') $icon = 'bi-telephone text-primary';
                                        if($log['action'] == 'Visit') $icon = 'bi-geo-alt text-danger';
                                        if($log['action'] == 'delete_lead') $icon = 'bi-trash text-danger';
                                    ?>
                                    <i class="<?= $icon ?> me-1"></i> <?= $log['action'] ?>
                                </td>

                                <td>
                                    <?php if($log['lead_id']): ?>
                                        <div class="mb-1">
                                            <span class="badge bg-light text-dark border">Lead: <?= $log['lead_name'] ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <span class="text-muted small fst-italic">"<?= $log['details'] ?>"</span>
                                </td>

                                <td>
                                    <?php if(!empty($log['attachment'])): ?>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="showImage('<?= base_url('uploads/activities/'.$log['attachment']) ?>')">
                                            <i class="bi bi-image"></i> Foto
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <?= $pager->links() ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImage" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-0">
                <img src="" id="previewImage" class="w-100 rounded">
            </div>
            <div class="modal-footer p-1 justify-content-center">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showImage(src) {
        document.getElementById('previewImage').src = src;
        var myModal = new bootstrap.Modal(document.getElementById('modalImage'));
        myModal.show();
    }
</script>
<?= $this->endSection(); ?>