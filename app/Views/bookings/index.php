<?= $this->extend('layout/main'); ?>
<?= $this->section('content'); ?>
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>📋 Daftar Booking & Approval</h3>
        <?php if(session()->get('role') == 'marketing' || session()->get('role') == 'admin'): ?>
            <a href="<?= base_url('bookings/new') ?>" class="btn btn-primary">+ Booking Baru</a>
        <?php endif; ?>
    </div>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Unit</th>
                            <th>Lead & Sales</th>
                            <th>Nominal</th>
                            <th>Bukti</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($bookings)): ?>
                            <tr><td colspan="7" class="text-center">Belum ada data booking.</td></tr>
                        <?php else: ?>
                            <?php foreach($bookings as $row): ?>
                            <tr>
                                <td><small><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></small></td>
                                <td>
                                    <strong><?= $row['block'] ?? '-' ?> - <?= $row['unit_number'] ?? '-' ?></strong><br>
                                    <small class="text-muted"><?= $row['project_name'] ?? '' ?></small>
                                </td>
                                <td>
                                    <i class="bi bi-person"></i> Cust: <?= $row['lead_name'] ?? 'Guest' ?><br>
                                    <i class="bi bi-tag"></i> Sales: <?= $row['marketing_name'] ?? '-' ?>
                                </td>
                                <td>
                                    <div class="fw-bold">Deal: <?= number_format($row['agreed_price'], 0,',','.') ?></div>
                                    <small class="text-success">Fee: <?= number_format($row['booking_fee'], 0,',','.') ?></small>
                                </td>
                                <td>
                                    <?php if(!empty($row['proof_image'])): ?>
                                        <a href="<?= base_url('uploads/proofs/' . $row['proof_image']) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                            Lihat
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($row['payment_status'] == 'pending'): ?>
                                        <span class="badge bg-warning text-dark">⏳ Pending</span>
                                    <?php elseif($row['payment_status'] == 'approved'): ?>
                                        <span class="badge bg-success">✅ Approved</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">❌ Rejected</span>
                                        <br><small class="text-danger"><?= $row['rejection_reason'] ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($row['payment_status'] == 'pending' && in_array(session()->get('role'), ['admin', 'manager', 'owner'])): ?>
        
                                        <a href="<?= base_url('bookings/approve/' . $row['id']) ?>" 
                                        class="btn btn-sm btn-success mb-1"
                                        onclick="return confirm('Approve booking ini?')">Approve</a>

                                        <button type="button" class="btn btn-sm btn-danger mb-1 btn-reject" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalReject"
                                        data-id="<?= $row['id'] ?>">Reject</button>

                                    <?php elseif($row['payment_status'] == 'approved'): ?>
        
                                        <a href="<?= base_url('closings/process/' . $row['id']) ?>" 
                                        class="btn btn-sm btn-primary text-nowrap">
                                        <i class="bi bi-file-earmark-text"></i> Proses Akad
                                        </a>

                                    <?php else: ?>
                                        <span class="text-muted">-</span>
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

<div class="modal fade" id="modalReject" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('bookings/reject') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">🚫 Tolak Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="booking_id" id="reject_booking_id">
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Script sederhana untuk oper ID ke modal
    document.addEventListener('DOMContentLoaded', function() {
        const rejectButtons = document.querySelectorAll('.btn-reject');
        rejectButtons.forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('reject_booking_id').value = this.getAttribute('data-id');
            });
        });
    });
</script>
<?= $this->endSection(); ?>