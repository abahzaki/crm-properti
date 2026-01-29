<?= $this->extend('layout/main'); ?>

<?= $this->section('content'); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?= $title ?></h5>
                </div>
                <div class="card-body">
                    
                    <form action="<?= base_url('units/save') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <input type="hidden" name="id" value="<?= $unit['id'] ?? '' ?>">

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Nama Proyek / Cluster</label>
                                <input type="text" name="project_name" class="form-control" 
                                       value="<?= $unit['project_name'] ?? 'Grand City' ?>" placeholder="Misal: Cluster Anggrek">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Blok <span class="text-danger">*</span></label>
                                <input type="text" name="block" class="form-control" required
                                       value="<?= $unit['block'] ?? '' ?>" placeholder="Misal: A">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Unit <span class="text-danger">*</span></label>
                                <input type="text" name="unit_number" class="form-control" required
                                       value="<?= $unit['unit_number'] ?? '' ?>" placeholder="Misal: 12">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tipe Unit</label>
                                <input type="text" name="unit_type" class="form-control" 
                                       value="<?= $unit['unit_type'] ?? '' ?>" placeholder="36/60">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Luas Tanah (m²)</label>
                                <input type="number" step="0.01" name="land_area" class="form-control" 
                                       value="<?= $unit['land_area'] ?? '0' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Luas Bangunan (m²)</label>
                                <input type="number" step="0.01" name="building_area" class="form-control" 
                                       value="<?= $unit['building_area'] ?? '0' ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Harga List (Cash) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="price" class="form-control" required
                                       value="<?= $unit['price'] ?? '' ?>" placeholder="500000000">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status Unit</label>
                            <select name="status" class="form-select">
                                <?php $s = $unit['status'] ?? 'available'; ?>
                                <option value="available" <?= $s=='available'?'selected':'' ?>>Available (Bisa Dijual)</option>
                                <option value="hold" <?= $s=='hold'?'selected':'' ?>>Hold (Booking Sementara)</option>
                                <option value="booked" <?= $s=='booked'?'selected':'' ?>>Booked (Sudah DP)</option>
                                <option value="sold" <?= $s=='sold'?'selected':'' ?>>Sold (Terjual/Akad)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi / Catatan</label>
                            <textarea name="description" class="form-control" rows="3"><?= $unit['description'] ?? '' ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('units') ?>" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan Data</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>