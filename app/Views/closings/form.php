<?= $this->extend('layout/main'); ?>

<?= $this->section('content'); ?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">📑 Proses Akad & Pemberkasan</h5>
        </div>
        <div class="card-body">
            
            <div class="alert alert-info">
                <strong>Unit:</strong> Blok <?= $unit['block'] ?> - <?= $unit['unit_number'] ?> <br>
                <strong>Harga Awal:</strong> Rp <?= number_format($booking['agreed_price'], 0,',','.') ?>
            </div>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <form action="<?= base_url('closings/save') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Rencana Tanggal Akad (AJB)</label>
                        <input type="date" name="akad_date" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Metode Pembayaran Final</label>
                        <select name="payment_method" class="form-select">
                            <option value="kpr">KPR (Bank)</option>
                            <option value="cash_bertahap">Cash Bertahap</option>
                            <option value="cash_keras">Cash Keras</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga Final (Di AJB)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="final_price" class="form-control" 
                               value="<?= $booking['agreed_price'] ?>" required>
                    </div>
                    <small class="text-muted">Edit jika ada perubahan harga saat proses bank/diskon akhir.</small>
                </div>

                <h6 class="mt-4 mb-3 border-bottom pb-2">📂 Upload Dokumen (PDF/JPG)</h6>
                
                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Scan KTP (Wajib)</label>
                        <input type="file" name="file_ktp" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Scan NPWP</label>
                        <input type="file" name="file_npwp" class="form-control">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Scan Kartu Keluarga (KK)</label>
                        <input type="file" name="file_kk" class="form-control">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Scan SP3K (Jika KPR)</label>
                        <input type="file" name="file_sp3k" class="form-control">
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="<?= base_url('bookings') ?>" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Simpan & Ajukan ke Owner</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>