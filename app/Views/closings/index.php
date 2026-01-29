<?= $this->extend('layout/main'); ?>

<?= $this->section('content'); ?>
<div class="container mt-4">
    <h3 class="mb-3">⚖️ Approval Akad & Closing</h3>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Tgl Pengajuan</th>
                            <th>Unit & Customer</th>
                            <th>Metode & Harga</th>
                            <th>Berkas</th>
                            <th>Status</th>
                            <th>Aksi Owner</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($closings)): ?>
                            <tr><td colspan="6" class="text-center">Belum ada pengajuan akad.</td></tr>
                        <?php else: ?>
                            <?php foreach($closings as $row): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <strong>Blok <?= $row['block'] ?> - <?= $row['unit_number'] ?></strong><br>
                                    <small><?= $row['customer_name'] ?></small><br>
                                    <span class="badge bg-info text-dark">Sales: <?= $row['sales_name'] ?></span>
                                </td>
                                <td>
                                    <span class="text-uppercase fw-bold"><?= str_replace('_',' ', $row['payment_method']) ?></span><br>
                                    Rp <?= number_format($row['final_price'], 0,',','.') ?><br>
                                    <small class="text-muted">Akad: <?= date('d M Y', strtotime($row['akad_date'])) ?></small>
                                </td>
                                <td>
                                    <?php if($row['file_ktp']): ?>
                                        <a href="<?= base_url('uploads/docs/'.$row['file_ktp']) ?>" target="_blank" class="btn btn-xs btn-outline-primary mb-1">KTP</a>
                                    <?php endif; ?>
                                    <?php if($row['file_sp3k']): ?>
                                        <a href="<?= base_url('uploads/docs/'.$row['file_sp3k']) ?>" target="_blank" class="btn btn-xs btn-outline-warning mb-1">SP3K</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($row['status'] == 'review'): ?>
                                        <span class="badge bg-warning text-dark">⏳ Menunggu Review</span>
                                    <?php elseif($row['status'] == 'approved'): ?>
                                        <span class="badge bg-success">✅ SOLD / CLOSED</span><br>
                                        <small><?= date('d/m/y', strtotime($row['approved_at'])) ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-danger">❌ Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($row['status'] == 'review' && session()->get('role') == 'owner'): ?>
                                        <form action="<?= base_url('closings/approve') ?>" method="post" class="d-inline" onsubmit="return confirm('Yakin Approve? Unit akan otomatis berubah status menjadi SOLD.')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success">✅ Approve</button>
                                        </form>
                                        
                                        <button class="btn btn-sm btn-danger mt-1" data-bs-toggle="modal" data-bs-target="#modalReject<?= $row['id'] ?>">❌ Tolak</button>

                                        <div class="modal fade" id="modalReject<?= $row['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="<?= base_url('closings/reject') ?>" method="post">
                                                        <div class="modal-header"><h5 class="modal-title">Tolak Pengajuan</h5></div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <textarea name="notes" class="form-control" placeholder="Alasan penolakan..." required></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-danger">Kirim Penolakan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>