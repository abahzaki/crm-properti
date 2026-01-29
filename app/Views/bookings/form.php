<?= $this->extend('layout/main'); ?>

<?= $this->section('content'); ?>
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">📝 Form Request Booking Unit</h5>
        </div>
        <div class="card-body">
            
            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <form action="<?= base_url('bookings') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Pilih Lead (Calon Pembeli)</label>
                        <select name="lead_id" class="form-select" required>
                            <option value="">-- Cari Nama Customer --</option>
                            <?php foreach($leads as $lead): ?>
                                <option value="<?= $lead['id'] ?>"><?= $lead['name'] ?> (<?= $lead['phone'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Pilih Unit (Available Only)</label>
                        <select name="unit_id" id="selectUnit" class="form-select" required>
                            <option value="" data-price="0">-- Pilih Unit --</option>
                            
                            <?php foreach($units as $unit): ?>
                                <option value="<?= $unit['id'] ?>" data-price="<?= $unit['price'] ?>">
                                    Blok <?= $unit['block'] ?> - <?= $unit['unit_number'] ?> 
                                    (Tipe <?= $unit['unit_type'] ?>) - Rp <?= number_format($unit['price'], 0,',','.') ?>
                                </option>
                            <?php endforeach; ?>
                            
                        </select>
                        <small class="text-muted">Hanya unit status 'Available' yang muncul.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Harga Deal (Disepakati)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="agreed_price" id="inputPrice" class="form-control" 
                                   placeholder="0" required>
                        </div>
                        <small class="text-muted">Otomatis terisi sesuai harga unit (Bisa diedit jika diskon).</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nominal Booking Fee</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="booking_fee" class="form-control" 
                                   placeholder="Contoh: 5000000" required>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Bukti Transfer</label>
                        <input type="file" name="proof_image" class="form-control" required accept="image/*">
                        <small class="text-muted">Format: JPG/PNG, Max 2MB</small>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <a href="<?= base_url('bookings') ?>" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">🚀 Ajukan Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectUnit = document.getElementById('selectUnit');
        const inputPrice = document.getElementById('inputPrice');

        // Saat pilihan unit berubah...
        selectUnit.addEventListener('change', function() {
            // Ambil harga dari atribut 'data-price' pada option yang dipilih
            const selectedOption = selectUnit.options[selectUnit.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            
            // Masukkan ke kolom input harga
            if(price) {
                inputPrice.value = price;
            } else {
                inputPrice.value = '';
            }
        });
    });
</script>

<?= $this->endSection(); ?>