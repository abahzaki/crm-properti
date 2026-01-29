<?= $this->extend('layout/main'); ?>

<?= $this->section('content'); ?>
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>🏠 Stok Unit Properti</h3>
        
        <?php if(in_array(session()->get('role'), ['admin', 'manager', 'owner'])): ?>
            <a href="<?= base_url('units/new') ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Unit
            </a>
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
                <table class="table table-hover align-middle table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Unit</th>
                            <th>Tipe / Spek</th>
                            <th>Harga (Cash)</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($units as $row): ?>
                        <tr>
                            <td>
                                <h5 class="mb-0">Blok <?= $row['block'] ?> - <?= $row['unit_number'] ?></h5>
                                <small class="text-muted"><?= $row['project_name'] ?></small>
                            </td>
                            <td>
                                <strong>Tipe <?= $row['unit_type'] ?></strong><br>
                                <small>LT: <?= $row['land_area'] ?>m² | LB: <?= $row['building_area'] ?>m²</small>
                            </td>
                            <td>
                                Rp <?= number_format($row['price'], 0, ',', '.') ?>
                            </td>
                            <td>
                                <?php 
                                    $badge = 'bg-secondary';
                                    if($row['status'] == 'available') $badge = 'bg-success';
                                    if($row['status'] == 'booked') $badge = 'bg-warning text-dark';
                                    if($row['status'] == 'sold') $badge = 'bg-danger';
                                    if($row['status'] == 'hold') $badge = 'bg-info text-dark';
                                ?>
                                <span class="badge <?= $badge ?> text-uppercase"><?= $row['status'] ?></span>
                            </td>
                            <td>
                                <?php if(in_array(session()->get('role'), ['admin', 'manager', 'owner'])): ?>
                                    <a href="<?= base_url('units/edit/' . $row['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" 
                                            data-bs-toggle="modal" data-bs-target="#modalDelete" 
                                            data-id="<?= $row['id'] ?>" data-name="Blok <?= $row['block'] ?>-<?= $row['unit_number'] ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <small class="text-muted">View Only</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDelete" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('units/delete') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="unit_id" id="delete_id">
                    <p>Yakin ingin menghapus unit <strong id="delete_name"></strong>?</p>
                    <small class="text-danger">Unit yang sudah pernah dibooking tidak bisa dihapus.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Script oper data ke Modal Delete
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.btn-delete');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('delete_id').value = this.getAttribute('data-id');
                document.getElementById('delete_name').innerText = this.getAttribute('data-name');
            });
        });
    });
</script>
<?= $this->endSection(); ?>